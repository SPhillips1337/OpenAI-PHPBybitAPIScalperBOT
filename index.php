<html>
<head>
<meta http-equiv="refresh" content="300">
</head>
<body>
<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

echo "<h1>Startup</h1>";

echo "<h2>Bybit API PHP XRPUSDT Spot Scalping Bot</h2>";

echo "<h3>Using 5m close for RSI</h3>";

echo "<p>Set to buy on bullish EMA and RSI below 40</p>";

echo "<p>Set to sell on bearish EMA and RSI above 60</p><br><hr>";
// Bybit API Key
$api_key = "your_bybit_api_key";
// Bybit API Secret
$api_secret = "your_bybit_api_secret";

// Get current XRPUSD price from Bybit API
$url = 'https://api-testnet.bybit.com/spot/v3/public/quote/ticker/24hr?symbol=XRPUSDT';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);
curl_close ($ch);
$data = json_decode($output,true);

var_dump($data);

$xrpusd_price = $data['result']['lp'];

$url = 'https://api-testnet.bybit.com/spot/v3/public/quote/kline?symbol=XRPUSDT&interval=5m&limit=20';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);
curl_close ($ch);
$data = json_decode($output,true);

$ema_price_data = $data['result']['list'];

// Calculate Exponential Moving Average (EMA)
echo "<hr><br>Calculate Exponential Moving Average (EMA)<br>";
$ema_short = calculateEMA($ema_price_data, "short");
$ema_long = calculateEMA($ema_price_data, "long");

echo "ema_short: $ema_short<br>";
echo "ema_long: $ema_long<br>";

if ($ema_short > $ema_long) {
    echo "<strong>EMA cross is bullish</strong><br>";
}

if ($ema_short < $ema_long) {
    echo "<strong>EMA cross is bearish</strong><br />";
}

if ($ema_short==$ema_long) {
    echo "<strong>EMA No reading</strong><br />";
}
// Get RSI value
echo "Get RSI value<br><hr>";
$rsi = getRSI($xrpusd_price);

// Check if EMA is bullish
echo "<hr>Check if EMA is bullish and RSI is below 40<br>";
if ($ema_short > $ema_long && $rsi < 40 && $rsi!=-1) {
    echo "<strong>bullish signal</strong><br>";
    // Place a buy order
    echo "<strong>Place a buy order</strong><br";

    // we need to calculate a minimum order qty for market orders (there is a lower limit it refuses)
    $minimum_order_value = 25;
    $orderQTY = ceil($minimum_order_value / $xrpusd_price);

    $url = 'https://api-testnet.bybit.com/spot/v3/private/order';
    $orderLinkId=uniqid();

    // Market Buy Order
    $postdata = array(
        "symbol" => "XRPUSDT",
        "side" => "BUY",
        "orderLinkId" => $orderLinkId,
        "orderType" => "Market",
        "orderQty" => strval($orderQTY),
        "timeInForce" => "GTC",
    );
    echo "<br><hr>";
    var_dump($postdata);
    echo "<hr>";

    // Limit buy order
    /*
    $postdata = array(
        "symbol" => "XRPUSDT",
        "side" => "Buy",
        "orderLinkId" => $orderLinkId,
        "orderType" => "Limit",
        "orderQty" => "1",
        "orderPrice" => $xrpusd_price,
        "timeInForce" => "GTC",
    );*/

    $timestamp = time() * 1000;
    $params = json_encode($postdata);

    $params_for_signature= $timestamp . $api_key . "5000" . $params;
    $signature = hash_hmac('sha256', $params_for_signature, $api_secret);

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
        "X-BAPI-API-KEY: $api_key",
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
        
    if ($data['retMsg'] == 'OK') {
        // Order placed successfully
        echo "Order placed successfully<br />";
    }
    die('Done');
}

// Check if EMA is bearish
echo "Check if EMA is bearish and RSI is above 60<br />";

echo "<br>RSI: $rsi<br>";
if ($ema_short < $ema_long && $rsi > 60 && $rsi!=-1) {
    echo "<strong>bearish signal</strong><br />";

// we need to calculate a minimum order qty for market orders (there is a lower limit it refuses)
$minimum_order_value = 25;
$orderQTY = ceil($minimum_order_value / $xrpusd_price);

$url = 'https://api-testnet.bybit.com/spot/v3/private/order';
$orderLinkId=uniqid();

    // Market Buy Order
    $postdata = array(
        "symbol" => "XRPUSDT",
        "side" => "SELL",
        "orderLinkId" => $orderLinkId,
        "orderType" => "Market",
        "orderQty" => strval($orderQTY),
        "timeInForce" => "GTC",
    );
    echo "<br><hr>";
    var_dump($postdata);
    echo "<hr>";

    // Limit sell order
    /*
    $postdata = array(
        "symbol" => "XRPUSDT",
        "side" => "SELL",
        "orderLinkId" => $orderLinkId,
        "orderType" => "Limit",
        "orderQty" => "1",
        "orderPrice" => $xrpusd_price,
        "timeInForce" => "GTC",
    );*/

    $timestamp = time() * 1000;
    $params = json_encode($postdata);

    $params_for_signature= $timestamp . $api_key . "5000" . $params;
    $signature = hash_hmac('sha256', $params_for_signature, $api_secret);

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
        "X-BAPI-API-KEY: $api_key",
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
    
    if ($data['retMsg'] == 'OK') {
        // Order placed successfully
        echo "Order placed successfully<br />";
    }
    
    die('Done');
}

die('Do Nothing');

function calculateEma($xrpPrice, $time_period) {
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
			$ema = $xrpPrice[$i]['c'];
		}
		else{
			$ema = ($xrpPrice[$i]['c'] * $alpha) + ($previousEMA * (1 - $alpha));
		}
		
		$previousEMA = $ema;
	}
	
	return $ema;
}
// Function to calculate EMA
function calculateWeightedEMA($price, $time_period)
{
    if ($time_period == "short") {
        $time_period = 10;
    } else if ($time_period == "long") {
        $time_period = 20;
    }
    
    // Calculate EMA
    $multiplier = (2 / ($time_period + 1));
    $ema = ($price * $multiplier) + ($price * (1 - $multiplier));
    
    return $ema;
}

// Function to get RSI
function getRSI($price)
{
    // Get last 10 prices
    $url = 'https://api-testnet.bybit.com/spot/v3/public/quote/kline?symbol=XRPUSDT&interval=5m&limit=14';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close ($ch);
    $data = json_decode($output,true);
    
    $prices = array();
    foreach ($data['result']['list'] as $kline) {
        $prices[] = $kline['c'];
    }

    var_dump($prices);

    // Calculate RSI
    $rsi = getRSIValue($prices);
    
    echo "<br>RSI: $rsi<br>";
    return $rsi;
}

// Function to calculate RSI
function getRSIValue($prices)
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
?>
</body>
</html>
