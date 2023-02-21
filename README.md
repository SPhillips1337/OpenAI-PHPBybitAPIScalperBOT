# OpenAI-PHPBybitAPIScalperBOT

Version: 0.2
Author: OpenAI GPT/ Stephen Phillips
Date: 2023/02/21

Introduction
============
CAUTION THIS SCRIPT IS IN TESTING ONLY

I asked open GPT to write me a PHP Spot Scalping bot for the ETH USDT pair using the Bybit API and testnet, and specified that it use EMA cross and Stoch RSI indicators to decide when to buy and sell.

This was generated from the playground at https://platform.openai.com/playground

The idea here would be that you could run this via crontab job at regular intervals for example every 15 minutes and it would check prices and decide to buy or sell based on TA.

Requirements
============
In order to run this script you obviously need PHP and a way to schedule it to run, for my testing I used an Ubuntu LAMP stack with it of PHP 7.4.3 with the CURL extension loaded and enabled as it uses CURL to call data from the ByBit API.

You will also need to go to https://testnet.bybit.com/ and sign up to a testnet account to generate the required API keys to use (I found this article helpful as it tells you how to add test coins to the account for the bot to trade with https://www.bybithelp.com/HelpCenterKnowledge/bybitHC_Article?id=000001681&language=en_US#:~:text=To%20request%20test%20coins%20on%20Testnet%2C%20you%20may%20proceed%20to,to%20your%20Spot%20Account%20immediately.)

Notes
=====
After testing I found several faults with the original script as provided by OpenAI GPT, which in the form provided via OpenAI GPT it didn't work.

Firstly it used Bybit API V2, However the latest version is V3 (See https://bybit-exchange.github.io/docs/v3/intro) and as a more modern AI model with more up to date data I would have expected it to use the latest version of the API, but unfortunately it's use of V2 of the API led to the original script not working as the calls were no longer valid. 

Secondly, I encountered a number of division by 0 errors in the function to calculate RSI which also broke the script.

Thirdly, the Stoch RSI calculation was widely wrong and returned results with numbers over 20K and errors due to that which again broke things further.

Fourthly, I think the EMA calculation was incorrect also as it basically always returned the same value for both long and short.

Changes
=======
This script is the result of my testing and debugging of the script provided via OpenAI GPT, It's in somewhat of a messy state.

I fixed the various errors detailed above, and have added several debug messages to the script to help with testing it.

I removed the broken Stoch RSI calculation and replaced it to simply use the RSI calculation instead for the time being.

I switched away from using the ETHUSDT pair for testing as for some reason on the BYBIT testnet ETHUSDT had a fixed flat price which of course meant the script could not work properly.

The chart interval has been changed to 5 minutes to provide better results in regards of RSI calculation and EMA here as well.

The Bybit API calls which it uses have also been replaced with the correct V3 API calls.

I added in a quick calculation for a minimum order qty to buy and sell based around $25 worth of the token as I found with MARKET orders the API rejected buying only 1 XRP for $0.38 saying it is below the lower allowed limit, oddly enough with LIMIT though orders you can buy and sell only 1 XRP as much as you like with the API!

I asked OpenAI for another new function to calculate the EMA values, although to be honest I am still not entirely sure it is working properly.

Currently for testing I wrapped everything in HTML and added a META refresh of 5 minutes so you can just load it in a browser and watch it run, it did place a buy order earlier.

Next Steps
==============
The next step here would be to test the script using buy and sell requests to see how it performed, and then I think I would look at adding some level of accounting to it where it remembered trade data and how much it had started with and invested, so as to allow more dynamic trading such as reinvesting of profits, rather than just simply always trying to buy or sell the same value of only 1 XRP every time, which to be fair is somewhat more akin to DCA than scalping, where typically you would set the script to invest a percentage of you total capital into the coin when buying along with a level of take profit and stop loss settings, with for example it waiting for a profit of a set percentage before selling as well as looking at TA to decide also.
I think the addition of a configuration file would be good as well to hold various parameters relating to the script.
Using a database such as MySQL to record transactions and data would offer more options as well.

Related Links
=============
Stoch RSI calculation formulas (The bot kinda had it right originally but was using the min and max price instead of RSI from the period, I did play with trying to fix it before just going with RSI) https://www.investopedia.com/terms/s/stochrsi.asp

How Is the Exponential Moving Average (EMA) Formula Calculated? https://www.investopedia.com/ask/answers/122314/what-exponential-moving-average-ema-formula-and-how-ema-calculated.asp

bybit-exchange / api-usage-examples https://github.com/bybit-exchange/api-usage-examples
This was helpful in getting the order placing API calls correctly into V3, see https://github.com/bybit-exchange/api-usage-examples/blob/master/V3_demo/api_demo/spot/Encryption_HMAC.php

ByBit V3 API Documentation for the ticker (used to get current XRP price) https://bybit-exchange.github.io/docs/spot/public/tickers
ByBit V3 API Documentation for kline (used to get values to calculate RSI and EMA) https://bybit-exchange.github.io/docs/spot/public/kline
ByBit V3 API Documentation for placing orders (self-explanatory) https://bybit-exchange.github.io/docs/spot/trade/place-order
