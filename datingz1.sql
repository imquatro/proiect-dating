-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gazdă: 127.0.0.1
-- Timp de generare: aug. 05, 2025 la 11:08 AM
-- Versiune server: 10.4.32-MariaDB
-- Versiune PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Bază de date: `datingz1`
--

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `friend_requests`
--

CREATE TABLE `friend_requests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `friend_requests`
--

INSERT INTO `friend_requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`, `responded_at`) VALUES
(1, 1, 2, 'accepted', '2025-08-03 21:41:45', '2025-08-03 21:42:02'),
(2, 1, 3, 'accepted', '2025-08-04 09:36:33', '2025-08-04 09:37:21'),
(3, 3, 2, 'accepted', '2025-08-04 13:41:44', '2025-08-04 13:43:37');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`) VALUES
(1, 1, 2, 'salut', '2025-08-04 05:25:13'),
(2, 2, 1, 'salut', '2025-08-04 05:25:30'),
(3, 1, 2, 'salut', '2025-08-04 09:17:55'),
(8, 1, 3, 'salut', '2025-08-04 17:19:48'),
(9, 1, 2, 'salut iar', '2025-08-04 17:50:42'),
(10, 1, 2, 'salut', '2025-08-04 17:50:48'),
(11, 1, 2, 'salut', '2025-08-04 17:50:52'),
(12, 1, 2, 'salut', '2025-08-04 17:50:56'),
(13, 2, 1, 'da ma salut salut', '2025-08-04 17:51:32'),
(14, 2, 1, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2025-08-04 17:51:51'),
(15, 1, 2, 'aaaa', '2025-08-04 18:06:17'),
(16, 1, 2, 'aaaa', '2025-08-04 18:06:23'),
(17, 1, 2, 'aaaa', '2025-08-04 18:06:33'),
(18, 1, 2, 'aa', '2025-08-04 18:16:40'),
(19, 1, 2, 'a', '2025-08-04 18:27:56'),
(20, 1, 2, 'a', '2025-08-04 18:28:26'),
(21, 1, 2, 'a', '2025-08-04 18:41:01'),
(22, 1, 2, 'abc', '2025-08-04 18:41:15'),
(23, 2, 1, 'abcd', '2025-08-04 18:41:41'),
(24, 2, 1, 'aaaaa', '2025-08-04 18:41:57'),
(25, 1, 2, 'aaaaaa', '2025-08-04 18:42:02'),
(26, 2, 1, 'aa', '2025-08-04 18:52:30'),
(27, 1, 2, 'aa', '2025-08-04 18:52:39'),
(28, 2, 1, 'aaa', '2025-08-04 18:52:45'),
(29, 2, 1, 'aaaa', '2025-08-04 18:52:49'),
(30, 2, 1, 'aaaa', '2025-08-04 18:52:50'),
(31, 2, 1, 'aaaa', '2025-08-04 18:53:00'),
(32, 1, 2, 'aaaa', '2025-08-04 18:54:29');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `country` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('masculin','feminin') DEFAULT 'masculin',
  `description` text DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gallery_status` varchar(20) DEFAULT 'pending',
  `is_admin` tinyint(1) DEFAULT 0,
  `last_active` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `country`, `city`, `age`, `gender`, `description`, `gallery`, `created_at`, `gallery_status`, `is_admin`, `last_active`) VALUES
(1, 'quatro', 'serverboost93@gmail.com', '$2y$10$XUP9QK9AU/EgETee1NVvKemxG2xpWWKWzCbg1.AkRIWjmKLbdeLDW', 'Romania', 'Suceava', 31, 'masculin', 'descrieea mea se fura asa ca :))', 'photo_20250804_144449_10.jpg', '2025-07-21 21:56:14', 'approved', 1, '2025-08-04 22:36:01'),
(2, 'quatro93', 'quatro93@gmail.com', '$2y$10$D2nwnAS7tkNt7MZTzuB2jOGSt8dWPFXbxHMQy/6hpJT/FRPlce3B2', 'Romania', 'Suceava', 31, 'masculin', 'aaaaaa', 'photo_20250804_154134_92.jpg', '2025-07-22 00:47:31', 'pending', 0, '2025-08-04 23:30:00'),
(3, 'Catalina', 'albacazapada@gmail.com', '$2y$10$oSYlVjpRUK3.cEJRMinID.DkjFTAzxSVykk0V88SefTegqkIeHsy.', 'Romania', 'suceava', 23, 'feminin', '', 'photo_20250804_152843_95.jpg,photo_20250804_152854_27.jpg,photo_20250804_152832_62.jpg,photo_20250804_152823_92.jpg,photo_20250804_104412_80.jpg', '2025-08-04 08:42:45', 'approved,approved,pe', 0, '2025-08-04 20:51:11');

--
-- Indexuri pentru tabele eliminate
--

--
-- Indexuri pentru tabele `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pair` (`sender_id`,`receiver_id`),
  ADD KEY `receiver_idx` (`receiver_id`);

--
-- Indexuri pentru tabele `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pentru tabele eliminate
--

--
-- AUTO_INCREMENT pentru tabele `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pentru tabele `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pentru tabele `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constrângeri pentru tabele eliminate
--

--
-- Constrângeri pentru tabele `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD CONSTRAINT `fr_receiver_fk` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fr_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
