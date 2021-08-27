-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 28, 2021 at 12:27 AM
-- Server version: 5.7.32-cll-lve
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `limonneg_admin`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE `blog` (
  `name` varchar(60) COLLATE utf8mb4_persian_ci NOT NULL,
  `updated_at` int(10) UNSIGNED DEFAULT NULL,
  `created_at` int(10) UNSIGNED DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `title` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `logo` varchar(16) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `token` varchar(32) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `verify_token` varchar(11) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `verify_at` int(11) UNSIGNED DEFAULT NULL,
  `reset_token` varchar(11) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `reset_at` int(11) UNSIGNED DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `mobile` varchar(15) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `language` varchar(8) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `telegram_bot_token` varchar(63) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `params` text COLLATE utf8mb4_persian_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_account`
--

CREATE TABLE `blog_account` (
  `id` int(11) NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_persian_ci NOT NULL,
  `identity` varchar(60) COLLATE utf8mb4_persian_ci NOT NULL,
  `identity_type` varchar(15) COLLATE utf8mb4_persian_ci NOT NULL,
  `blog_name` varchar(31) COLLATE utf8mb4_persian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `price_initial` double NOT NULL,
  `cnt` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `blog_name` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `cache_parents_active_status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `title` varchar(64) COLLATE utf8mb4_persian_ci NOT NULL,
  `params` text COLLATE utf8mb4_persian_ci,
  `blog_name` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `cache_parents_active_status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `color`
--

CREATE TABLE `color` (
  `id` int(11) NOT NULL,
  `title` varchar(31) COLLATE utf8mb4_persian_ci NOT NULL,
  `code` varchar(31) COLLATE utf8mb4_persian_ci NOT NULL,
  `blog_name` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `token` varchar(32) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `verify_token` varchar(11) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `verify_at` int(11) DEFAULT NULL,
  `reset_token` varchar(11) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `reset_at` int(11) DEFAULT NULL,
  `mobile` varchar(15) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `name` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `params` text COLLATE utf8mb4_persian_ci,
  `blog_name` varchar(60) COLLATE utf8mb4_persian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `field`
--

CREATE TABLE `field` (
  `id` int(11) NOT NULL,
  `title` varchar(64) COLLATE utf8mb4_persian_ci NOT NULL,
  `seq` int(11) DEFAULT NULL,
  `in_summary` tinyint(1) DEFAULT '1',
  `params` text COLLATE utf8mb4_persian_ci,
  `category_id` int(11) DEFAULT NULL,
  `blog_name` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `unit` varchar(64) COLLATE utf8mb4_persian_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `name` varchar(16) COLLATE utf8mb4_persian_ci NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `type` varchar(12) COLLATE utf8mb4_persian_ci NOT NULL,
  `telegram_id` varchar(127) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `blog_name` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `name` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `mobile` varchar(15) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `phone` varchar(24) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `price` double NOT NULL,
  `carts_count` int(11) NOT NULL,
  `params` text COLLATE utf8mb4_persian_ci,
  `blog_name` varchar(60) COLLATE utf8mb4_persian_ci NOT NULL,
  `customer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_item`
--

CREATE TABLE `invoice_item` (
  `id` int(11) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `title` varchar(64) COLLATE utf8mb4_persian_ci NOT NULL,
  `code` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `image` varchar(16) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `price` double NOT NULL,
  `color_code` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `cnt` int(11) NOT NULL,
  `params` text COLLATE utf8mb4_persian_ci,
  `package_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `blog_name` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_admin`
--

CREATE TABLE `log_admin` (
  `id` int(11) NOT NULL,
  `blog_name` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `ip` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `method` varchar(11) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `is_ajax` tinyint(1) DEFAULT NULL,
  `url` varchar(2047) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `response_http_code` int(11) DEFAULT NULL,
  `created_date` varchar(19) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `data_post` text COLLATE utf8mb4_persian_ci,
  `user_agent` varchar(2047) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `controller` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `action` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `model_id` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `model_parent_id` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_api`
--

CREATE TABLE `log_api` (
  `id` int(11) NOT NULL,
  `blog_name` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `ip` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `method` varchar(11) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `is_ajax` tinyint(1) DEFAULT NULL,
  `url` varchar(2047) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `response_http_code` int(11) DEFAULT NULL,
  `created_date` varchar(19) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `data_post` varchar(4096) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `user_agent` varchar(2047) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `controller` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `action` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `model_id` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `model_category_id` int(11) DEFAULT NULL,
  `model_parent_id` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_persian_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migration`
--

CREATE TABLE `migration` (
  `version` varchar(180) COLLATE utf8mb4_persian_ci NOT NULL,
  `apply_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package`
--

CREATE TABLE `package` (
  `id` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `status` varchar(12) COLLATE utf8mb4_persian_ci NOT NULL,
  `price` double NOT NULL,
  `cache_stock` int(11) DEFAULT '0',
  `color_code` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `blog_name` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `params` text COLLATE utf8mb4_persian_ci,
  `cache_parents_active_status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page`
--

CREATE TABLE `page` (
  `id` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `body` text COLLATE utf8mb4_persian_ci,
  `entity` varchar(15) COLLATE utf8mb4_persian_ci NOT NULL,
  `page_type` varchar(31) COLLATE utf8mb4_persian_ci NOT NULL,
  `entity_id` varchar(15) COLLATE utf8mb4_persian_ci NOT NULL,
  `blog_name` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `title` varchar(64) COLLATE utf8mb4_persian_ci NOT NULL,
  `code` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `price_min` double DEFAULT NULL,
  `price_max` double DEFAULT NULL,
  `des` varchar(160) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `view` int(11) DEFAULT '0',
  `params` text COLLATE utf8mb4_persian_ci,
  `image` varchar(16) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `blog_name` varchar(31) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `cache_parents_active_status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_field`
--

CREATE TABLE `product_field` (
  `field` varchar(64) COLLATE utf8mb4_persian_ci NOT NULL,
  `value` varchar(64) COLLATE utf8mb4_persian_ci NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `blog_name` varchar(31) COLLATE utf8mb4_persian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`name`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `logo` (`logo`),
  ADD KEY `mobile` (`mobile`);

--
-- Indexes for table `blog_account`
--
ALTER TABLE `blog_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_name` (`blog_name`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `blog_name` (`blog_name`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`,`blog_name`),
  ADD KEY `blog_name` (`blog_name`);

--
-- Indexes for table `color`
--
ALTER TABLE `color`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_name` (`blog_name`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_name` (`blog_name`);

--
-- Indexes for table `field`
--
ALTER TABLE `field`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`,`category_id`,`blog_name`),
  ADD KEY `blog_name` (`blog_name`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`name`),
  ADD KEY `blog_name` (`blog_name`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `blog_name` (`blog_name`);

--
-- Indexes for table `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_name` (`blog_name`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `log_admin`
--
ALTER TABLE `log_admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_api`
--
ALTER TABLE `log_api`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migration`
--
ALTER TABLE `migration`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `package`
--
ALTER TABLE `package`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `blog_name` (`blog_name`);

--
-- Indexes for table `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_name` (`blog_name`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_name` (`blog_name`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `image` (`image`);

--
-- Indexes for table `product_field`
--
ALTER TABLE `product_field`
  ADD PRIMARY KEY (`field`,`value`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `blog_name` (`blog_name`),
  ADD KEY `category` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blog_account`
--
ALTER TABLE `blog_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `color`
--
ALTER TABLE `color`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `field`
--
ALTER TABLE `field`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_item`
--
ALTER TABLE `invoice_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_admin`
--
ALTER TABLE `log_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_api`
--
ALTER TABLE `log_api`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package`
--
ALTER TABLE `package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `page`
--
ALTER TABLE `page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blog`
--
ALTER TABLE `blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`logo`) REFERENCES `gallery` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `blog_account`
--
ALTER TABLE `blog_account`
  ADD CONSTRAINT `blog_account_ibfk_1` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `package` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `color`
--
ALTER TABLE `color`
  ADD CONSTRAINT `color_ibfk_1` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `field`
--
ALTER TABLE `field`
  ADD CONSTRAINT `field_ibfk_1` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invoice_item`
--
ALTER TABLE `invoice_item`
  ADD CONSTRAINT `invoice_item_ibfk_1` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_item_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `invoice_item_ibfk_3` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `package`
--
ALTER TABLE `package`
  ADD CONSTRAINT `package_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `package_ibfk_2` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `page_ibfk_2` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_ibfk_2` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `product_ibfk_3` FOREIGN KEY (`image`) REFERENCES `gallery` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_field`
--
ALTER TABLE `product_field`
  ADD CONSTRAINT `field_string_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_field_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_field_ibfk_2` FOREIGN KEY (`blog_name`) REFERENCES `blog` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
