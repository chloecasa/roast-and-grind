-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 02:12 AM
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
(6, 3, 'default_avatar.png', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

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
(5, 'Sample Coffee', 'A coffee bean is the seed of the Coffea plant and serves as the foundation for the beloved beverage that we know as coffee. Despite the name, coffee beans are not technically beans but rather seeds found inside the fruit, often referred to as a coffee cherry. These beans come in various types and flavors, offering a diverse and robust experience for coffee enthusiasts.\r\n\r\nArabica and Robusta are the two main varieties of coffee plants, with Arabica accounting for around 60% of global coffee production. Known for its fine and mild flavor, Arabica beans thrive in specific temperature and rainfall conditions at higher altitudes. On the other hand, Robusta beans, making up the remaining 30% of production, have a stronger and more bitter taste, along with a higher caffeine content.\r\n\r\nTo enhance the flavors and aromas we associate with coffee, the green coffee bean undergoes a process of roasting. This process brings out the distinct characteristics of each bean, resulting in the rich and nuanced flavors we love.', 'Belgium', 'Coffee Beans', 200.00, 350.00, '1790922396_primary_NoirBrew__x_Creavora__creavorastudio___packagingdesign__coffeebranding.jpg'),
(6, 'Drip Coffee', 'Drip coffee bags have become a popular choice for those seeking high-quality, single-serve coffee.\r\n\r\nEssentially, they are small filter bags that open at the top and are filled with ground coffee. They have fold out handles that allow the bag to sit over the top of a cup.\r\n\r\nCustomers simply have to open the pouch, remove the filter, and tear off the top. Then, they must shake it to level the coffee inside. Each handle is placed over the sides of a cup, and hot water is slowly poured over the grounds, allowing it to drip into the vessel below.\r\n\r\nModern drip coffee bags are similar to those that have been available since the 1970s. However, a key difference lies in how it is brewed.\r\n\r\nTeabag-style coffee bags brew by immersion and often produce a full-bodied cup similar to using a French press.', 'Amsterdam', 'Drip Bags', 150.00, 230.00, '1790950492_primary_download__16_.jpg'),
(7, 'Sample Coffee 3', '...', 'Belgium', 'Brewing Gear', 200.00, 300.00, '1791250094_primary_GjDk85EawAAEpPD__1080__1350_.jpg'),
(8, 'Sample Coffee 4', '...', 'Belgium', 'Drip Bags', 200.00, 300.00, '1791250116_primary_download__15_.jpg'),
(9, 'Sample Coffee 5', '...', 'Belgium', 'Coffee Beans', 200.00, 300.00, '1791250133_primary_______.jpg'),
(10, 'Sample Coffee 6', '...', 'Belgium', 'Coffee Beans', 200.00, 300.00, '1791250149_primary_coffe_drip.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `item_images`
--

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
(10, 10, '1791250149_0_NoirBrew__x_Creavora__creavorastudio___packagingdesign__coffeebranding.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `status` enum('Pending','Processing','Shipped','Delivered','Canceled') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `payment_method`, `status`) VALUES
(1, 2, '2026-10-23 12:03:45', 300.00, 'Cash on Delivery', 'Canceled'),
(2, 2, '2026-10-23 12:14:23', 350.00, 'GCash', 'Canceled'),
(3, 2, '2026-10-23 12:14:52', 230.00, 'Bank Transfer', 'Canceled'),
(4, 2, '2026-10-23 12:26:38', 300.00, 'Credit Card', 'Delivered'),
(5, 2, '2026-10-24 00:19:42', 900.00, 'Cash on Delivery', 'Delivered'),
(6, 2, '2026-10-24 08:12:18', 300.00, 'Cash on Delivery', 'Processing'),
(7, 1, '2026-10-27 01:01:25', 0.00, 'Cash on Delivery', 'Pending'),
(8, 2, '2026-10-27 01:02:22', 0.00, 'Cash on Delivery', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

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
(7, 6, 7, 'Sample Coffee 3', 300.00, 200.00, 1),
(8, 7, 8, 'Sample Coffee 4', 300.00, 200.00, 1),
(9, 7, 9, 'Sample Coffee 5', 300.00, 200.00, 3),
(10, 8, 9, 'Sample Coffee 5', 300.00, 200.00, 3);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

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
(3, 7, 2, 'very good', '2026-10-24 08:26:46');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`item_id`, `quantity`) VALUES
(5, 5),
(6, 3),
(7, 5),
(8, 7),
(9, 4),
(10, 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

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
(3, 'chloecasanoba1@gmail.com', '$2y$10$/VabRfJMzGdL6uZdA/rkAeCtWJFOgha3lErif8lVb0V471cJqZgzu', 'user', 'active', '2026-10-23 14:52:28');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_order_transaction_details`
-- (See below for the actual view)
--
CREATE TABLE `view_order_transaction_details` (
`order_id` int(11)
,`user_id` int(11)
,`order_date` timestamp
,`total_amount` decimal(10,2)
,`payment_method` varchar(30)
,`order_status` enum('Pending','Processing','Shipped','Delivered','Canceled')
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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_order_transaction_details`  AS SELECT `o`.`order_id` AS `order_id`, `o`.`user_id` AS `user_id`, `o`.`order_date` AS `order_date`, `o`.`total_amount` AS `total_amount`, `o`.`payment_method` AS `payment_method`, `o`.`status` AS `order_status`, `oi`.`order_item_id` AS `order_item_id`, `oi`.`item_name` AS `item_name`, `oi`.`unit_price` AS `unit_price`, `oi`.`quantity` AS `quantity` FROM (`orders` `o` join `order_items` `oi` on(`o`.`order_id` = `oi`.`order_id`)) ;

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
  ADD KEY `order_id` (`order_id`);

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
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `item_images`
--
ALTER TABLE `item_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

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
