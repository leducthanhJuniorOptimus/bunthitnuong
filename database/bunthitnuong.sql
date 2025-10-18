-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 18, 2025 at 06:26 AM
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
-- Database: `bunthitnuong`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE `blog` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `intro` text NOT NULL,
  `toc` text NOT NULL,
  `ingredients` text NOT NULL,
  `ingredients_images` text DEFAULT NULL,
  `content` text NOT NULL,
  `preparation_steps` text DEFAULT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `intro_image` varchar(255) DEFAULT NULL,
  `author` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog`
--

INSERT INTO `blog` (`id`, `title`, `slug`, `intro`, `toc`, `ingredients`, `ingredients_images`, `content`, `preparation_steps`, `thumbnail`, `intro_image`, `author`, `created_at`, `views`) VALUES
(3, 'Cách Nấu Một Nồi Thịt Chó Siêu Ngon', 'Cach-Nau-Mot-Noi-Thit-Cho-Sieu-Ngon', 'Chào các bạn hôm nay anh thành sẽ giới thiệu cho các bạn. Cách nấu một con chó thành 7 món giả cày siêu ngon, ăn là bao ghiền, đảm bảo không tốn một đồng nào ', '1.Nguyên Liệu\r\n1.2 Cách Lấy Nguyên Liệu\r\n1.3 Cách Làm Sạch Nguyên Liệu\r\n2. Chế Biến', 'Đầu tiên, các bạn ra bắt 1 con chó mà bạn ghét, đặc biệt phải có rọ mỏm và bã chó .', 'uploads/blog/68f315dd4d16e-anhsanphambunthitnuong.jpg|uploads/blog/68f315dd4fecd-bunthitnuongbama.jpg|uploads/blog/68f315dd4ff93-bunthitnuongbama.png|uploads/blog/68f315dd5008e-bunthitnuongbama1.jpg', 'vậy đó', '[{\"title\":\"háhs\",\"content\":\"dhsdhsd\",\"images\":[\"uploads\\/blog\\/68f315dd50124-Menu.png\"]}]', 'uploads/blog/68f315dd4cfb2-AnhThanh.png', 'uploads/blog/68f315dd4d0d7-combobunthitnuong.png', 'Lê Đức Thành', '2025-10-18 04:21:49', 3);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_special` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `image`, `is_special`, `created_at`) VALUES
(2, 'Bún Thịt Nướng Thịt ', 'bun-thit-nuong', 38000.00, 'uploads/product/68f2f94202132-anhsanphambunthitnuong.jpg', 1, '2025-10-18 02:19:46'),
(3, 'Bún Thịt Nướng Chả Giò', 'bun-thit-nuong', 35000.00, 'uploads/product/68f2f9610e966-bunthitnuongbama.jpg', 1, '2025-10-18 02:20:17'),
(4, 'Bún Thịt Nướng Đầy Đủ', 'bun-thit-nuong', 35000.00, 'uploads/product/68f2f979a1ffb-bunthitnuongbama1.jpg', 1, '2025-10-18 02:20:41'),
(7, 'Combo 3 Bún Thịt Nướng ', 'combo', 100000.00, 'uploads/product/68f2fa811e807-bunthitnuongbama.png', 1, '2025-10-18 02:25:05'),
(8, 'Chả Giò Thêm', 'dothem', 5000.00, 'uploads/product/68f2fbc8cab7e-z7110859099691_77b7f336838dc4ee3e9b35d050110b1d.jpg', 0, '2025-10-18 02:30:32'),
(9, 'Bún Thêm', 'dothem', 5000.00, 'uploads/product/68f2fc071923c-bunthem.jpg', 1, '2025-10-18 02:31:35'),
(10, 'Thịt Thêm', 'dothem', 15000.00, 'uploads/product/68f2fc7585453-bunthitnuongbama1.jpg', 1, '2025-10-18 02:33:25'),
(11, 'Coca-Cola', 'nuocngot', 15000.00, 'uploads/product/68f2fd3327e36-cocabunthitnuong.jpg', 1, '2025-10-18 02:36:35'),
(12, 'Pepsi', 'nuocngot', 15000.00, 'uploads/product/68f2fd409c905-download.jpg', 1, '2025-10-18 02:36:48'),
(13, 'Cơm Chiên Dương Châu', 'com-chien', 35000.00, 'uploads/product/68f2fe26a4eb4-comchien.jpg', 1, '2025-10-18 02:40:30'),
(14, 'Cơm Chiên Đùi Gà', 'com-chien', 38000.00, 'uploads/product/68f2fe3ac7844-comchienduiga.jpg', 1, '2025-10-18 02:40:58'),
(15, 'Sting', 'nuocngot', 15000.00, 'uploads/product/68f2fe626fbe3-stingbunthitnuongbama.jpg', 0, '2025-10-18 02:41:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `diachi` varchar(255) DEFAULT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL,
  `user_role` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `diachi`, `create_at`, `avatar`, `user_role`) VALUES
(1, 'thanhkhoro1230', '$2y$10$wk4ZfZxOHVSwrna7sR1PPO2P.3vpVEXnSYLW4n/eYh47b7TAsOQyq', 'thanh0330958@gmail.com', '012345679', '33/39 nguyen sy sach', '2025-10-16 07:48:33', '/uploads/avatars/user_1_1760672369.png', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blog`
--
ALTER TABLE `blog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
