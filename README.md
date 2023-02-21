# OpenAI-PHPBybitAPIScalperBOT

Version: 0.1
Author: OpenAI GPT/ Stephen Phillips
Date: 2023/02/21

Introduction
============
I asked open GPT to write me a PHP Spot Scalping bot for the ETH USDT pair using the Bybit API and testnet, and specified that it use EMA cross and Stoch RSI indicators to decide when to buy and sell.

This was generated from the playground at https://platform.openai.com/playground

The idea here would be that you could run this via crontab job at regular intervals for example every 15 minutes and it would check prices and decide to buy or sell based on TA.

Requirements
============
In order to run this script you obviously need PHP and a way to schedule it to run, for my testing I used an Ubuntu LAMP stack with it of PHP 7.4.3 with the CURL extension loaded and enabled as it uses CURL to call data from the ByBit API.
You will also need to go to https://testnet.bybit.com/ and sign up to a testnet account to generate the required API keys to use (I found this article helpful as it tells you how to add test coins to the account for the bot to trade with https://www.bybithelp.com/HelpCenterKnowledge/bybitHC_Article?id=000001681&language=en_US#:~:text=To%20request%20test%20coins%20on%20Testnet%2C%20you%20may%20proceed%20to,to%20your%20Spot%20Account%20immediately.)

Notes
=====
After testing I found several faults with this script, in the form provided via OpenAI GPT it didn't work.

Firstly it uses Bybit API V2, However the latest version is V3 (See https://bybit-exchange.github.io/docs/v3/intro) and as a more modern AI model with more up to date data I would have expected it to use the latest version of the API, and unfortunately it's use of V2 of the API led to it not working as the calls no longer worked. 

Secondly, I encountered a number of division by 0 errors in the function to calculate RSI which also broke the script.

Thirdly, the Stoch RSI calculation was widely wrong and returned results with numbers over 20K and errors due to that which again broke things further.

After Thoughts
==============
Even after fixing the above issues I found this script to be not really usuable for chart intervals of 1 minute as OpenAI GPT had originally written it for, due to the limited amount of change in the closing prices for that interval it often wound up having division by 0 errors when trying to calculate the RSI, Which I tried to fix by adding an error value for this but the result was on the 1 minute timeframe it seemed to decide to do nothing far too often as a result.
Also it chose for some reason to use a period of 10 for calculating the RSI instead of the standard 14.
