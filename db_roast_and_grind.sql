-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 02, 2026 at 04:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_roast_and_grind`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_photo` varchar(255) DEFAULT 'default_avatar.png',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(150) NOT NULL DEFAULT '',
  `town` varchar(50) NOT NULL DEFAULT '',
  `postal_code` varchar(10) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `user_id`, `profile_photo`, `first_name`, `last_name`, `address`, `town`, `postal_code`, `phone`) VALUES
(1, 1, 'default_avatar.png', 'Chloe', 'Casanoba', 'Gracely Store Purok 2 Roldan Street New Lowe Bicutan', 'TAGUIG CITY', '1636', '09984800690'),
(5, 2, 'default_avatar.png', 'Chloe', 'Casanoba', 'GATE 2 AGUINALDO COMPOUND CADENA DE AMOR ST.', 'TAGUIG CITY', '1636', '09984800690'),
(6, 3, 'default_avatar.png', '', '', '', '', '', ''),
(7, 4, 'default_avatar.png', '', '', '', '', '', ''),
(8, 5, '1792847946_prof_5.jpg', 'Jazz', 'Ray', 'Block 5 Emerald Street, Central Village', 'Taguig City', '2600', '093748298374'),
(9, 6, 'default_avatar.png', '', '', '', '', '', ''),
(10, 7, 'default_avatar.png', '', '', '', '', '', ''),
(11, 8, '1793167854_prof_8.png', 'rhey', 'raymundo', '34 streer', 'taguig', '1630', '09778867886'),
(12, 9, 'default_avatar.png', 'Jazz', 'Ray', 'Block 5 Emerald Street, Central Village', 'taguig', '2600', '09778867886'),
(13, 10, 'default_avatar.png', '', '', '', '', '', '');



DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `origin` varchar(100) NOT NULL DEFAULT 'N/A',
  `category` varchar(50) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `sell_price` decimal(10,2) NOT NULL,
  `img_path` varchar(255) NOT NULL DEFAULT 'default_coffee.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`item_id`, `item_name`, `description`, `origin`, `category`, `cost_price`, `sell_price`, `img_path`) VALUES
(5, 'Ethiopia Yirgacheffe Light Roast 250g', 'A coffee bean is the seed of the Coffea plant and serves as the foundation for the beloved beverage that we know as coffee. Despite the name, coffee beans are not technically beans but rather seeds found inside the fruit, often referred to as a coffee cherry. These beans come in various types and flavors, offering a diverse and robust experience for coffee enthusiasts.\r\n\r\nArabica and Robusta are the two main varieties of coffee plants, with Arabica accounting for around 60% of global coffee production. Known for its fine and mild flavor, Arabica beans thrive in specific temperature and rainfall conditions at higher altitudes. On the other hand, Robusta beans, making up the remaining 30% of production, have a stronger and more bitter taste, along with a higher caffeine content.\r\n\r\nTo enhance the flavors and aromas we associate with coffee, the green coffee bean undergoes a process of roasting. This process brings out the distinct characteristics of each bean, resulting in the rich and nuanced flavors we love.', 'Ethiopia', 'Coffee Beans', 200.00, 350.00, '1790922396_primary_NoirBrew__x_Creavora__creavorastudio___packagingdesign__coffeebranding.jpg'),
(6, 'Benguet Arabica Drip Bags (10 pcs)', 'Drip coffee bags have become a popular choice for those seeking high-quality, single-serve coffee.\r\n\r\nEssentially, they are small filter bags that open at the top and are filled with ground coffee. They have fold out handles that allow the bag to sit over the top of a cup.\r\n\r\nCustomers simply have to open the pouch, remove the filter, and tear off the top. Then, they must shake it to level the coffee inside. Each handle is placed over the sides of a cup, and hot water is slowly poured over the grounds, allowing it to drip into the vessel below.\r\n\r\nModern drip coffee bags are similar to those that have been available since the 1970s. However, a key difference lies in how it is brewed.\r\n\r\nTeabag-style coffee bags brew by immersion and often produce a full-bodied cup similar to using a French press.', 'Philippines', 'Drip Bags', 150.00, 230.00, '1790950492_primary_download__16_.jpg'),
(7, 'Ceramic V60 Coffee Dripper', 'A cone-shaped ceramic pour-over dripper with spiral ribs for an even extraction and a clean, bright cup. Sits on most standard mugs and uses size 02 paper filters. Warm it with hot water before brewing.', 'Japan', 'Brewing Gear', 200.00, 300.00, '1793632634_primary_4a2bb0e0573d1b3df9c6d6a0835421ae.jpg'),
(8, 'Colombian Medium Roast Drip Bags (10 pcs)', 'Ten drip bags of medium-roasted Colombian coffee with a balanced body, caramel sweetness and a gentle nutty finish. Ready in minutes at home, at the office or while travelling.', 'Colombia', 'Drip Bags', 200.00, 300.00, '1791250116_primary_download__15_.jpg'),
(9, 'Brazil Santos Medium Roast 250g', 'Medium-roasted whole beans from the Santos region of Brazil. Low acidity with a smooth, nutty body and notes of milk chocolate. A reliable everyday coffee for espresso, French press or drip. 250g.', 'Brazil', 'Coffee Beans', 200.00, 300.00, '1791250133_primary_______.jpg'),
(10, 'Guatemala Antigua Dark Roast 250g', 'Dark-roasted beans from the volcanic Antigua valley of Guatemala. Rich and full-bodied with notes of dark chocolate, spice and a hint of smoke. Ideal for French press, moka pot or espresso. 250g.', 'Guatemala', 'Coffee Beans', 200.00, 300.00, '1791250149_primary_coffe_drip.jpg'),
(12, 'Kenya AA Medium Roast 250g', 'Medium-roasted whole beans from Kenya with a juicy, wine-like acidity and notes of blackcurrant and brown sugar. Great as a pour-over or filter coffee. 250g.', 'Kenya', 'Coffee Beans', 230.00, 380.00, '1793633723_primary_0e50a70d3a73202d6b88f92298efcebf.jpg'),
(13, 'Ethiopia Guji Drip Bags (10 pcs)', 'Ten drip bags of light-roasted Guji coffee with bright berry and citrus notes. Just hang, pour and sip.', 'Ethiopia', 'Drip Bags', 170.00, 260.00, '1793633927_primary_Benguet_Drip_Bag.jpg'),
(14, 'House Blend Drip Bags (10 pcs)', 'Our everyday blend of Brazilian and Colombian beans in ten easy drip bags. Balanced, smooth and slightly sweet.', 'Brazil & Colombia', 'Drip Bags', 130.00, 210.00, '1793634260_primary_house1.jpg'),
(15, 'Stainless Steel Gooseneck Kettle 1L', 'A 1-litre gooseneck kettle with a narrow spout for slow, controlled pouring. Stainless steel body with a heat-resistant handle. Works on gas and electric stoves.', 'China', 'Brewing Gear', 700.00, 1150.00, '1793634394_primary_9d4eef266b6f095d3511065e660e9d85.jpg'),
(16, 'Manual Burr Coffee Grinder', 'A hand-crank grinder with ceramic burrs and an adjustable grind size, from espresso to French press. Compact and quiet, ideal for travel and small kitchens.', 'Taiwan', 'Brewing Gear', 900.00, 1450.00, '1793634557_primary_02142d2d04ede4a791c2609877f7af53.jpg'),
(17, 'Digital Coffee Scale with Timer', 'Weigh your beans and water to 0.1g with a built-in timer for consistent pour-overs. Auto-off and a large backlit display.', 'China', 'Brewing Gear', 550.00, 890.00, '1793634813_primary_b9606dcad78846c52b98774ccb81242f.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `item_images`
--

DROP TABLE IF EXISTS `item_images`;
CREATE TABLE `item_images` (
  `image_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `img_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `item_images`
--

INSERT INTO `item_images` (`image_id`, `item_id`, `img_path`) VALUES
(1, 5, '1790922396_0_GjDk85EawAAEpPD__1080__1350_.jpg'),
(2, 5, '1790922396_1_NoirBrew__x_Creavora__creavorastudio___packagingdesign__coffeebranding.jpg'),
(3, 6, '1790950492_0_______.jpg'),
(4, 6, '1790950492_1_coffe_drip.jpg'),
(5, 6, '1790950492_2_____.jpg'),
(6, 6, '1790950492_3_download__15_.jpg'),
(7, 7, '1791250094_0_NoirBrew__x_Creavora__creavorastudio___packagingdesign__coffeebranding.jpg'),
(8, 8, '1791250116_0_NoirBrew__x_Creavora__creavorastudio___packagingdesign__coffeebranding.jpg'),
(9, 9, '1791250133_0_NoirBrew__x_Creavora__creavorastudio___packagingdesign__coffeebranding.jpg'),
(10, 10, '1791250149_0_NoirBrew__x_Creavora__creavorastudio___packagingdesign__coffeebranding.jpg'),
(13, 12, '1793633723_0_89ba5dcd3d1a8603e7467fc0f0b7361c.jpg'),
(14, 13, '1793633927_0_Benguet_Drip_Bag1.jpg'),
(15, 14, '1793634260_0_House.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(30) NOT NULL,
  `status` enum('Pending','Processing','Shipped','Delivered','Canceled') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `payment_method`, `status`) VALUES
(1, 2, '2026-10-23 12:03:45', 'Cash on Delivery', 'Canceled'),
(2, 2, '2026-10-23 12:14:23', 'GCash', 'Canceled'),
(3, 2, '2026-10-23 12:14:52', 'Bank Transfer', 'Canceled'),
(4, 2, '2026-10-23 12:26:38', 'Credit Card', 'Delivered'),
(5, 2, '2026-10-24 00:19:42', 'Cash on Delivery', 'Processing'),
(6, 4, '2026-10-26 16:12:32', 'Cash on Delivery', 'Pending'),
(7, 4, '2026-10-26 16:43:58', 'Cash on Delivery', 'Processing'),
(8, 6, '2026-10-26 17:17:26', 'Cash on Delivery', 'Shipped'),
(9, 4, '2026-10-27 01:13:18', 'Cash on Delivery', 'Pending'),
(10, 4, '2026-10-28 05:17:34', 'Cash on Delivery', 'Pending'),
(11, 7, '2026-10-28 05:52:02', 'Cash on Delivery', 'Pending'),
(12, 8, '2026-10-28 06:02:30', 'Cash on Delivery', 'Pending'),
(13, 8, '2026-10-28 06:10:06', 'Cash on Delivery', 'Shipped'),
(14, 8, '2026-11-02 10:12:49', 'Cash on Delivery', 'Delivered');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(150) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `item_name`, `unit_price`, `unit_cost`, `quantity`) VALUES
(1, 1, 7, 'Sample Coffee 3', 300.00, 200.00, 1),
(2, 2, 5, 'Sample Coffee', 350.00, 200.00, 1),
(3, 3, 6, 'Drip Coffee', 230.00, 150.00, 1),
(4, 4, 7, 'Sample Coffee 3', 300.00, 200.00, 1),
(5, 5, 7, 'Sample Coffee 3', 300.00, 200.00, 1),
(6, 5, 8, 'Sample Coffee 4', 300.00, 200.00, 2),
(7, 6, 6, 'Drip Coffee', 230.00, 150.00, 1),
(8, 7, 7, 'Sample Coffee 3', 300.00, 200.00, 1),
(9, 8, 6, 'Drip Coffee', 230.00, 150.00, 1),
(10, 9, 6, 'Drip Coffee', 230.00, 150.00, 1),
(11, 10, 7, 'Sample Coffee 3', 300.00, 200.00, 1),
(12, 11, 9, 'Sample Coffee 5', 300.00, 200.00, 1),
(13, 11, 5, 'Sample Coffee', 350.00, 200.00, 1),
(14, 12, 7, 'Sample Coffee 3', 300.00, 200.00, 1),
(15, 13, 7, 'Sample Coffee 3', 300.00, 200.00, 1),
(16, 14, 8, 'Sample Coffee 4', 300.00, 200.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `item_id`, `user_id`, `review_text`, `created_at`) VALUES
(1, 5, 2, 'not good ****', '2026-10-23 14:58:01'),
(3, 9, 7, 'bitter sweet', '2026-10-28 05:53:13'),
(5, 6, 4, 'nice', '2026-10-28 06:13:44');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

DROP TABLE IF EXISTS `stock`;
CREATE TABLE `stock` (
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`item_id`, `quantity`) VALUES
(5, 4),
(6, 0),
(7, 2),
(8, 7),
(9, 9),
(10, 10),
(12, 3),
(13, 40),
(14, 50),
(15, 15),
(16, 12),
(17, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
(1, 'admin@roastandgrind.com', '$2y$10$efDvenHYJ5Fu/xxt1ANbXuRx5/TuzNs/s4k6keUiiFvr2ueE0GmrG', 'admin', 'active', '2026-10-23 11:21:06'),
(2, 'chloecasanoba@gmail.com', '$2y$10$sdbeGPMLATw7lw23KGM52OPhzt5UsOY6ZbrTf9NGgrl/.zRUGb4qm', 'user', 'active', '2026-10-23 11:41:36'),
(3, 'chloecasanoba1@gmail.com', '$2y$10$/VabRfJMzGdL6uZdA/rkAeCtWJFOgha3lErif8lVb0V471cJqZgzu', 'user', 'active', '2026-10-23 14:52:28'),
(4, 'jazz@gmail.com', '$2y$10$TvIr3SQcOac0vA2CI9kXK.2UKyR1EIZtxOQqc48LKWHmqM2i3FNTm', 'admin', 'active', '2026-10-24 07:25:31'),
(5, 'JAZZRAY@GMAIL.COM', '$2y$10$K2uvUg/6JUw5fGWvFQhRb.gGfRNr5ClRPThwRfmDNroFv5BHTzuYO', 'user', 'active', '2026-10-24 13:07:46'),
(6, 'oncleejazzz@gmail.com', '$2y$10$IsfY3GCGmi/VsZE4ZwFHGuKpzGWL1yx4Ps6rjLkdQ5Ow3TEZ5gbxG', 'user', 'active', '2026-10-26 17:17:18'),
(7, 'j@gmail.com', '$2y$10$mnVnJPSkham3OeaWjNzTJuMoTh8e4GJjox8q9/o/yyI7E8VK0fbua', 'user', 'active', '2026-10-28 05:51:07'),
(8, 'rhey@gmail.com', '$2y$10$JQD9p39/ZDTn9/vhRBt9rO5SUbUxMiLkMKMzFM6TxjcrpH5CIPfqe', 'user', 'active', '2026-10-28 06:01:44'),
(9, 'test@gmail.com', '$2y$10$LBHQLFcpD40anjMstPg1AeycFG7CpIpBSLRxQYeVCVLurBlfm8Dv.', 'user', 'active', '2026-10-31 17:20:31'),
(10, 'gojo@gmail.com', '$2y$10$uMKVNmSBSXd/jR2MCNYFxOFS.kAQbF0W0txra/EB3smuZx6SH6o46', 'user', 'active', '2026-11-02 07:12:08');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_order_transaction_details`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `view_order_transaction_details`;
CREATE TABLE `view_order_transaction_details` (
`order_id` int(11)
,`user_id` int(11)
,`order_date` timestamp
,`order_status` enum('Pending','Processing','Shipped','Delivered','Canceled')
,`payment_method` varchar(30)
,`customer_email` varchar(255)
,`customer_name` varchar(101)
,`phone` varchar(20)
,`address` varchar(150)
,`town` varchar(50)
,`postal_code` varchar(10)
,`order_item_id` int(11)
,`item_name` varchar(150)
,`unit_price` decimal(10,2)
,`quantity` int(11)
);

-- --------------------------------------------------------

--
-- Structure for view `view_order_transaction_details`
--
DROP TABLE IF EXISTS `view_order_transaction_details`;

DROP VIEW IF EXISTS `view_order_transaction_details`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_order_transaction_details`  AS SELECT `o`.`order_id` AS `order_id`, `o`.`user_id` AS `user_id`, `o`.`order_date` AS `order_date`, `o`.`status` AS `order_status`, `o`.`payment_method` AS `payment_method`, `u`.`email` AS `customer_email`, concat(`c`.`first_name`,' ',`c`.`last_name`) AS `customer_name`, `c`.`phone` AS `phone`, `c`.`address` AS `address`, `c`.`town` AS `town`, `c`.`postal_code` AS `postal_code`, `oi`.`order_item_id` AS `order_item_id`, `oi`.`item_name` AS `item_name`, `oi`.`unit_price` AS `unit_price`, `oi`.`quantity` AS `quantity` FROM (((`orders` `o` join `users` `u` on(`o`.`user_id` = `u`.`user_id`)) left join `customers` `c` on(`u`.`user_id` = `c`.`user_id`)) join `order_items` `oi` on(`o`.`order_id` = `oi`.`order_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `item_images`
--
ALTER TABLE `item_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_user_item` (`user_id`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `item_images`
--
ALTER TABLE `item_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `item_images`
--
ALTER TABLE `item_images`
  ADD CONSTRAINT `item_images_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
