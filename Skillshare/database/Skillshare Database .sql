-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 12, 2025 at 05:48 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skillshare`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `message`, `timestamp`, `is_read`) VALUES
(8, 2, 6, 'Hi', '2025-04-20 16:28:03', 0),
(9, 2, 6, 'Hello', '2025-04-20 16:32:47', 0),
(10, 2, 1, 'HI Saubhagya', '2025-04-20 16:32:57', 1),
(11, 1, 2, 'Hi Himanshu', '2025-04-20 16:33:18', 1),
(12, 1, 2, 'Kaise ho', '2025-04-20 16:33:22', 1),
(13, 2, 1, 'Accha', '2025-04-20 16:33:28', 1),
(14, 1, 2, 'Sab mast?', '2025-04-20 17:50:07', 1),
(15, 7, 2, 'Trial Meesage!!', '2025-04-23 10:42:26', 1),
(16, 8, 2, 'hii himanshu', '2025-04-24 12:00:19', 1),
(17, 2, 8, 'hi rajj//', '2025-04-24 12:00:38', 1),
(18, 2, 1, 'heee', '2025-04-28 11:52:42', 1),
(19, 9, 10, 'hi', '2025-04-28 12:13:05', 1),
(20, 10, 9, 'hello', '2025-04-28 12:13:22', 1),
(21, 9, 10, 'can mam give us full marks for this project??', '2025-04-28 12:31:50', 1),
(22, 10, 9, 'Mam is very kind bro.I hope she will understand our academics situation.', '2025-04-28 12:33:27', 0);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `from_id` int(11) DEFAULT NULL,
  `to_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `seen` tinyint(4) DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','accepted','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `from_id`, `to_id`, `message`, `seen`, `timestamp`, `status`) VALUES
(3, 1, 2, NULL, 0, '2025-04-10 00:10:19', 'rejected'),
(4, 4, 2, NULL, 0, '2025-04-10 00:11:09', 'rejected'),
(5, 2, 1, NULL, 0, '2025-04-10 00:19:09', 'accepted'),
(6, 5, 1, NULL, 0, '2025-04-10 01:04:22', 'accepted'),
(7, 6, 2, NULL, 0, '2025-04-11 06:55:35', 'accepted'),
(8, 6, 1, NULL, 0, '2025-04-11 06:55:49', 'accepted'),
(9, 7, 1, NULL, 0, '2025-04-23 05:07:13', 'pending'),
(10, 2, 7, NULL, 0, '2025-04-23 05:07:29', 'accepted'),
(11, 7, 2, NULL, 0, '2025-04-23 05:11:07', 'pending'),
(12, 2, 8, NULL, 0, '2025-04-24 06:29:08', 'accepted'),
(13, 10, 9, NULL, 0, '2025-04-28 06:42:37', 'accepted');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `skill_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `type` enum('offer','request') DEFAULT NULL,
  `skill_img` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `user_id`, `title`, `description`, `category`, `type`, `skill_img`, `created_at`, `updated_at`) VALUES
(6, 1, 'AI', 'I know how to work with AI Agents', 'ML', 'offer', '1744244338_ChatGPT Image Apr 6 2025 Logo design for RunDown.png', '2025-04-10 00:18:58', '2025-07-12 15:20:59'),
(8, 2, 'C++', 'I am very good in C++ language.', 'Programming', 'offer', '1745300695_C++ Logo.png', '2025-04-22 05:44:55', '2025-07-12 15:20:59'),
(9, 2, 'Python', 'I know beginner level python and need help', 'Programming', 'request', '1745301274_Python Logo Dec 26 2022.png', '2025-04-22 05:54:34', '2025-07-12 15:20:59'),
(10, 7, 'python', 'i m a python dev', 'Programming', 'offer', '1745384805_Python Logo Dec 26 2022.png', '2025-04-23 05:06:45', '2025-07-12 15:20:59'),
(11, 2, 'Python', 'I want to learn', 'Programming', 'offer', '1745384937_Python Logo Dec 26 2022.png', '2025-04-23 05:08:57', '2025-07-12 15:20:59'),
(12, 8, 'Java', ' want to learn', 'Programming', 'request', '1745476107_WhatsApp Image 2025-04-24 at 09.50.48.jpeg', '2025-04-24 06:28:27', '2025-07-12 15:20:59'),
(13, 9, 'Data Analytics', 'I know Data Analytics', 'Programming', 'offer', '1745822326_Data_Analytics.jpg', '2025-04-28 06:38:46', '2025-07-12 15:20:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `bio`, `profile_pic`, `role`, `created_at`) VALUES
(1, 'Saubhagya Kashyap', 'admin@email.com', '$2y$10$EkvANc.6ivdDjRhirf30Ie2I1AKEqbTskMp41jhicg3647LKMRWKG', NULL, NULL, 'user', '2025-04-09 19:56:20'),
(2, 'Himanshu', 'himanshu@email.com', '$2y$10$h.hUVu2fy2Wmndj6gQdofue/02k2iGGMQWmssggzn/MTjVuxHsW/2', NULL, NULL, 'user', '2025-04-09 20:01:44'),
(4, 'test', 'test@email.com', '$2y$10$9ry9RWW51mEUOhA2aylcOuhG3pEw.dTyr9cUmi8b0xE0egHLezg6C', NULL, NULL, 'user', '2025-04-10 00:10:49'),
(5, 'Arjun', 'arjun@email.com', '$2y$10$EsDWDipKr9JyZgU.a12xI..rZFG4SdH7Cfl6d.DJAt2exmUNBUSOm', NULL, NULL, 'user', '2025-04-10 01:03:20'),
(6, 'Abhijeet', 'abhijeet@email.com', '$2y$10$sic.QiLaOhEysKQc92OTHuU/DeDGcRftAGIVRMY1KQNqetr1eFM2m', NULL, NULL, 'user', '2025-04-11 06:54:49'),
(7, 'Demo', 'demo@email.com', '$2y$10$dlbTx1j9uqHKSQk3G/PIuOmJDowtVC8cY9sO.ToEmOZIA48eBVOE2', NULL, NULL, 'user', '2025-04-23 05:05:03'),
(8, 'raaj', 'raj123@gmail.com', '$2y$10$F6mtDNR7/dBpNTonGjJ3qO//I.uz4TRJBk8yQOYR5.SkGN4IOz8.m', NULL, NULL, 'user', '2025-04-24 06:26:46'),
(9, 'Mukul', 'mukul@email.com', '$2y$10$WqJHJV6sLPJXRQBBl3JXzucbHuQE5/fFerrSTBNJtj7BFXwdMQXV2', NULL, NULL, 'user', '2025-04-28 06:35:43'),
(10, 'Mayank', 'mayank@email.com', '$2y$10$SrJR6Pa0Urqv8P9qORRi7emdDrhkXuSzVAlNcpcKbZau7wpeo6LDq', NULL, NULL, 'user', '2025-04-28 06:36:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_id` (`from_id`),
  ADD KEY `to_id` (`to_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `skill_id` (`skill_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`from_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`to_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
