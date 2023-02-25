
<?php

class BybitScalper {
    
    public $apiKey;
    public $apiSecret;
    public $conn;
    public $config;
    public $current_price;
    public $ema_price_data;

    public function __construct($apiKey, $apiSecret, $conn) 
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->conn = $conn;
        // let's connect to our db
        $this->connect();
        $this->getConfig();
    }

    public function getConfig()
    {

        // lets fetch the script config from the database
        $sql = "SELECT * FROM `config`";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {

            // lets load our config vars
            $this->config = $result->fetch_assoc();

            // check if we are live or on testnet, the default is testnet
            if($this->config['api_testnet']=='live') {
                $this->config['base_url'] ='https://api.bybit.com';
            }
            else{
                $this->config['base_url'] ='https://api-testnet.bybit.com';
            }

            /* These vars below in comments are just left for reference */

            // get the base currency symbol for the pair
            // TODO: In future we might specifiy base currency such as BTC or USDT etc, for now I have set it to USDT
            //$base_symbol = $config['base_symbol'];
            // TODO: We might also allow users to specify a range of token symbols to trade via a frontend dashboard which would also need to be loaded
            // For now we are using XRP for testing
            $this->config['symbol'] = 'XRP';
            $this->config['symbol_pair'] = $this->config['symbol'].$this->config['base_symbol'];

            // let's get our take profit and stop loss settings
            // TODO: these values are not currently used, but may be in the future, with logging of trades in the database we can check if any orders match these before we then issue a sell order based on the TA
            //$take_profit = $config['take_profit']; // default is 1.5
            //$stop_loss = $config['stop_loss']; // default is 5    

            // set minimum order value, required for MARKET orders in the bybit API as under a lower threshold they will be rejected
            //$minimum_order_value = $config['minimum_order_value']; // default is 25
            // set default order type, the default is MARKET
            //$order_type = $config['order_type'];
            // get the required time in seconds for the script to refresh
            //$meta_refresh = $config['meta_refresh']; // default is 300 seconds or 5 minutes which matches the periods used in TA
        } else {
            die('Error: No config data found');
        }

    }
        
    public function getCurrentPrice()
    {
        $url = $this->config['base_url'].'/spot/v3/public/quote/ticker/24hr?symbol='.$this->config['symbol_pair'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close ($ch);
        $data = json_decode($output,true);

        var_dump($data);

        return $data['result']['lp'];
    }

    public function getKline($interval, $limit) 
    {
        $url =  $this->config['base_url'].'/spot/v3/public/quote/kline?symbol='.$this->config['symbol_pair'].'&interval='.$interval.'m&limit='.$limit;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close ($ch);
        $data = json_decode($output,true);
    
        return $data['result']['list'];
    }

    public function calculateEma($time_period) 
    {
        if ($time_period == "short") {
            $time_period = 10;
        } else if ($time_period == "long") {
            $time_period = 20;
        }    
        $ema = 0;
        $previousEMA = 0;
        $alpha = 2/($time_period + 1);
        
        for ($i=0; $i < $time_period; $i++){
            if ($i == 0){
                $ema = $this->ema_price_data[$i]['c'];
            }
            else{
                $ema = ($this->ema_price_data[$i]['c'] * $alpha) + ($previousEMA * (1 - $alpha));
            }
            
            $previousEMA = $ema;
        }
        
        return $ema;
    }        

    // Function to get RSI
    public function getRSI()
    {
        // fetch the past 14 5minute prices to use for the RSI calculation
        $rsi_price_data = $this->getKline(5, 14);
        
        $rsi_prices = array();
        foreach ($rsi_price_data as $kline) {
            $rsi_prices[] = $kline['c'];
        }

        var_dump($rsi_prices);

        // Calculate RSI
        $rsi = $this->getRSIValue($rsi_prices);
        
        echo "<br>RSI: $rsi<br>";
        return $rsi;
    }

    // Function to calculate RSI
    private function getRSIValue($prices)
    {
        $length = count($prices);
        $average_gain = 0;
        $average_loss = 0;
        for ($i = 1; $i < $length; $i++) {
            $change = $prices[$i] - $prices[$i - 1];
            if ($change > 0) {
                $average_gain += $change;
            } else {
                $average_loss += abs($change);
            }
        }
        if($average_gain!=0&&$average_loss!=0){
            $average_gain /= $length;
            $average_loss /= $length;
            $rs = $average_gain / $average_loss;
            $rsi = 100 - (100 / (1 + $rs));
        }
        else{
            $rsi = -1;
        }
        echo "<br>RSI: $rsi<br>";
        return $rsi;
    }

    public function placeOrder($side,$orderQty)
    {
    
        $url =  $this->config['base_url'].'/spot/v3/private/order';
        $orderLinkId=uniqid();
 
        if($this->config['order_type']=='MARKET'){
            // Market Buy Order
            $postdata = array(
                "symbol" => $this->config['symbol_pair'],
                "side" => $side,
                "orderLinkId" => $orderLinkId,
                "orderType" => "Market",
                "orderQty" => strval($orderQty),
                "timeInForce" => "GTC",
            );
            echo "<br><hr>";
            var_dump($postdata);
            echo "<hr>";
        }
        else{
            // Limit buy order
            $postdata = array(
                "symbol" => $this->config['symbol_pair'],
                "side" => $side,
                "orderLinkId" => $orderLinkId,
                "orderType" => "Limit",
                "orderQty" => strval($orderQty),
                "orderPrice" => $this->current_price,
                "timeInForce" => "GTC",
            );
        }
        $timestamp = time() * 1000;
        $params = json_encode($postdata);
    
        $params_for_signature= $timestamp . $this->apiKey . "5000" . $params;
        $signature = hash_hmac('sha256', $params_for_signature, $this->apiSecret);
    
        // TODO: At this point we can add the response to the database table trade_orders to use to track trades profit loss to use later when selling etc.
    
        $ch = curl_init();
    
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
            "X-BAPI-API-KEY: $this->apiKey",
            "X-BAPI-SIGN: $signature",
            "X-BAPI-SIGN-TYPE: 2",
            "X-BAPI-TIMESTAMP: $timestamp",
            "X-BAPI-RECV-WINDOW: 5000",
            "Content-Type: application/json"
            ),
        ));
    
        $output = curl_exec($ch);
        curl_close ($ch);
        $data = json_decode($output,true);
    
        echo "<br><hr>";
        var_dump($data);
        echo "<hr>";        
    
        if ($data['retMsg'] == 'OK') {
            // Order placed successfully
            echo "<strong>".$orderType." ".$side." Order placed successfully</strong><br />";
    
            // TODO: At this point we can add the response to the database table order_reponses to use to track trades.
    
        }
    }

    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }
    
    public function setApiSecret($apiSecret) {
        $this->apiSecret = $apiSecret;
    }
    
    public function getApiKey() {
        return $this->apiKey;
    }
    
    public function getApiSecret() {
        return $this->apiSecret;
    }
    
    public function getConn() {
        return $this->conn;
    }
    
    public function connect() {
        $this->conn = new mysqli($this->conn['host'], $this->conn['user'], $this->conn['pass'], $this->conn['db']);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function insertOrder($orderData){
        $sql = "INSERT INTO trade_orders (order_id, symbol, side, quantity, price, status)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssdds", $orderData['order_id'], $orderData['symbol'], $orderData['side'], $orderData['quantity'], $orderData['price'], $orderData['status']);
        $stmt->execute();
        $stmt->close();
    }
    
}