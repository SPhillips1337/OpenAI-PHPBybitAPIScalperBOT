<?php
# OpenAI-PHPBybitAPIScalperBOT
/*
Version: 0.3
Author: OpenAI GPT/ Stephen Phillips
Date: 2023/02/21
*/

// initial config
include("./includes/config.inc.php");
// load classes
include("./classes/bybitscalper.class.php");
// TODO: WE can add market crash prevention in future to the script by watching TA on bitcoin as well, IE only enable buying when BTC is in uptrend etc.

// Initialize the BybitScalper class 
$bybitScalper = new BybitScalper($api_key, $api_secret, array(
    'host' => $db_host, 
    'user' => $db_user,
    'pass' => $db_pass,
    'db' => $db_name
));

?>
<html>
<head>
<meta http-equiv="refresh" content="<?php echo $bybitScalper->config['meta_refresh']; ?>">
</head>
<body>
<?php
// Output some details about what the script is doing
// TODO: These visual information messages will eventually be removed after testing, with perhaps a log updated to record information on what the bot is doing for debugging purposes
echo "<h1>Startup</h1>";
echo "<h2>Bybit API PHP XRPUSDT Spot Scalping Bot</h2>";
echo "<h3>Using 5m close for RSI</h3>";
echo "<p>Set to buy on bullish EMA and RSI below ".$bybitScalper->config['rsi_oversold']."</p>";
echo "<p>Set to sell on bearish EMA and RSI above ".$bybitScalper->config['rsi_overbought']."</p><br><hr>";

// Get current XRPUSD price from Bybit API
$bybitScalper->current_price = $bybitScalper->getCurrentPrice();

// get last 20 prices from Bybit API on 5 minute period
$bybitScalper->ema_price_data = $bybitScalper->getKline(5, 20);

// Calculate Exponential Moving Average (EMA)
echo "<hr><br>Calculate Exponential Moving Average (EMA)<br>";
$ema_short = $bybitScalper->calculateEMA("short");
$ema_long = $bybitScalper->calculateEMA("long");

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
// Get RSI value if this returns -1 it means no change in RSI value or no data to work out RSI - included as a catch all error earlier in script development
echo "Get RSI value<br><hr>";
$rsi = $bybitScalper->getRSI();

// we need to calculate a minimum order qty for market orders (there is a lower limit it refuses)
$orderQTY = ceil($bybitScalper->config['minimum_order_value'] / $bybitScalper->current_price);

// Check if EMA is bullish
echo "<hr>Check if EMA is bullish and RSI is below ".$bybitScalper->config['rsi_oversold']."<br>";
if ($ema_short > $ema_long && $rsi < $bybitScalper->config['rsi_oversold'] && $rsi!=-1) 
{
    echo "<strong>bullish signal</strong><br>";
    // Place a buy order
    echo "<strong>Place a BUY order</strong><br";

    $result = $bybitScalper->placeOrder("BUY",$orderQTY);

    die('Done');
}

// Check if EMA is bearish
echo "Check if EMA is bearish and RSI is above ".$bybitScalper->config['rsi_overbought']."<br />";

echo "<br>RSI: $rsi<br>";
if ($ema_short < $ema_long && $rsi > $bybitScalper->config['rsi_overbought'] && $rsi!=-1) 
{
    echo "<strong>bearish signal</strong><br />";
    // Place a buy order
    echo "<strong>Place a SELL order</strong><br";

    $result = $bybitScalper->placeOrder("SELL",$orderQTY);

    die('Done');
}

die('Do Nothing');

// NOTE: Old broken function to calculate EMA as provided by OpenAI
/*
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
*/

// NOTE: Old broken function to Stochastic RSI as provided by OpenAI
// The final equation to calculate Stoch RSI is roughly right but it should be using RSI values not prices to calculate it, and the standard period is 14 not 10
// I did have a quick go at fixing it but eventually left it and decided to use plain RSI instead
// TODO: It would be good to fix this and get it working to offer a range of different indicators to use with the script via config in future
/*
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
*/


?>
</body>
</html>