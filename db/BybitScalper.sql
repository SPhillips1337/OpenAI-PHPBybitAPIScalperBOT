-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 25, 2023 at 11:16 AM
-- Server version: 8.0.32-0ubuntu0.20.04.2
-- PHP Version: 7.4.3-4ubuntu2.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `BybitScalper`
--

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int NOT NULL,
  `base_symbol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'USDT',
  `api_testnet` enum('testnet','live','') NOT NULL DEFAULT 'testnet',
  `rsi_oversold` int NOT NULL DEFAULT '40',
  `rsi_overbought` int NOT NULL DEFAULT '60',
  `order_type` enum('MARKET','LIMIT','') NOT NULL DEFAULT 'MARKET',
  `minimum_order_value` float NOT NULL DEFAULT '25',
  `meta_refresh` int NOT NULL DEFAULT '300',
  `take_profit` float NOT NULL DEFAULT '1.5',
  `stop_loss` float NOT NULL DEFAULT '5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Various script configuration variables';

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `base_symbol`, `api_testnet`, `rsi_oversold`, `rsi_overbought`, `order_type`, `minimum_order_value`, `meta_refresh`, `take_profit`, `stop_loss`) VALUES
(1, 'USDT', 'testnet', 40, 60, 'MARKET', 25, 300, 1.5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `order_responses`
--

CREATE TABLE `order_responses` (
  `id` int NOT NULL,
  `retCode` int NOT NULL,
  `retMsg` varchar(255) NOT NULL,
  `orderId` varchar(255) NOT NULL,
  `orderLinkId` varchar(13) NOT NULL,
  `symbol` varchar(255) NOT NULL,
  `createTime` int NOT NULL,
  `orderPrice` float NOT NULL,
  `orderQty` int NOT NULL,
  `orderType` enum('LIMIT','MARKET','') NOT NULL,
  `side` enum('BUY','SELL','') NOT NULL,
  `status` varchar(255) NOT NULL,
  `timeInForce` varchar(255) NOT NULL,
  `accountId` int NOT NULL,
  `execQty` int NOT NULL,
  `orderCategory` int NOT NULL,
  `retExtInfo` int NOT NULL,
  `time` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Order responses to trade requests';

-- --------------------------------------------------------

--
-- Table structure for table `trade_orders`
--

CREATE TABLE `trade_orders` (
  `id` int NOT NULL,
  `symbol` varchar(255) NOT NULL,
  `side` enum('BUY','SELL','') NOT NULL,
  `orderLinkId` varchar(13) NOT NULL,
  `orderType` enum('Market','Limit','') NOT NULL,
  `orderQty` int NOT NULL,
  `timeInForce` varchar(255) NOT NULL,
  `status` enum('pending','complete','') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Trade orders place by the both';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_responses`
--
ALTER TABLE `order_responses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trade_orders`
--
ALTER TABLE `trade_orders`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_responses`
--
ALTER TABLE `order_responses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trade_orders`
--
ALTER TABLE `trade_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
