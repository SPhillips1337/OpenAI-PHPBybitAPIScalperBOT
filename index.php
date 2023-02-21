
<?php
// Bybit API Key
$api_key = "your_bybit_api_key";
// Bybit API Secret
$api_secret = "your_bybit_api_secret";


// Get current ETHUSD price from Bybit API
$url = 'https://api-testnet.bybit.com/v2/public/tickers';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);
curl_close ($ch);
$data = json_decode($output,true);

$ethusd_price = $data['result'][0]['last_price'];
    
// Calculate Exponential Moving Average (EMA)
$ema_short = calculateEMA($ethusd_price, "short");
$ema_long = calculateEMA($ethusd_price, "long");
    
// Get Stochastic RSI value
$stoch_rsi = getStochRSI($ethusd_price);

// Check if EMA cross is bullish
if ($ema_short > $ema_long && $stoch_rsi < 30) {
    // Place a buy order
    $url = 'https://api-testnet.bybit.com/v2/private/order/create';
    $postdata = array(
        "api_key" => $api_key,
        "symbol" => "ETHUSD",
        "side" => "Buy",
        "order_type" => "Market",
        "qty" => 1,
        "price" => $ethusd_price,
    );
    $signature = hash_hmac('sha256', json_encode($postdata), $api_secret);
    $postdata['sign'] = $signature;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close ($ch);
    $data = json_decode($output,true);
    
    if ($data['ret_msg'] == 'OK') {
        // Order placed successfully
    }
}

// Check if EMA cross is bearish
if ($ema_short < $ema_long && $stoch_rsi > 70) {
    // Place a sell order
    $url = 'https://api-testnet.bybit.com/v2/private/order/create';
    $postdata = array(
        "api_key" => $api_key,
        "symbol" => "ETHUSD",
        "side" => "Sell",
        "order_type" => "Market",
        "qty" => 1,
        "price" => $ethusd_price,
    );
    $signature = hash_hmac('sha256', json_encode($postdata), $api_secret);
    $postdata['sign'] = $signature;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close ($ch);
    $data = json_decode($output,true);
    
    if ($data['ret_msg'] == 'OK') {
        // Order placed successfully
    }
}

// Function to calculate EMA
function calculateEMA($price, $time_period)
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

// Function to get StochRSI value
function getStochRSI($price)
{
    // Get last 10 prices
    $url = 'https://api-testnet.bybit.com/v2/public/kline/list?symbol=ETHUSD&interval=1m&limit=10';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close ($ch);
    $data = json_decode($output,true);
    
    $prices = array();
    foreach ($data['result'] as $kline) {
        $prices[] = $kline['close'];
    }
    
    // Calculate RSI
    $rsi = getRSI($prices);
    
    // Calculate StochRSI
    $stoch_rsi = (($rsi - min($prices)) / (max($prices) - min($prices))) * 100;
    
    return $stoch_rsi;
}

// Function to calculate RSI
function getRSI($prices)
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
    $average_gain /= $length;
    $average_loss /= $length;
    $rs = $average_gain / $average_loss;
    $rsi = 100 - (100 / (1 + $rs));
    
    return $rsi;
}
?>