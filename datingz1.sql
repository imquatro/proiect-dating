-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gazdă: 127.0.0.1
-- Timp de generare: sept. 19, 2025 la 10:15 AM
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
-- Structură tabel pentru tabel `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `harvest` int(11) DEFAULT 0,
  `sales` int(11) DEFAULT 0,
  `level` int(11) DEFAULT 0,
  `xp` int(11) DEFAULT 0,
  `item_id` int(11) DEFAULT NULL,
  `years` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `achievements`
--

INSERT INTO `achievements` (`id`, `title`, `harvest`, `sales`, `level`, `xp`, `item_id`, `years`, `image`) VALUES
(2, 'Max level', 0, 0, 120, 0, NULL, 0, 'img/achievements/120.png'),
(3, 'One Year', 0, 0, 0, 0, NULL, 1, 'img/achievements/oneyear.png'),
(4, 'Xp card 100', 0, 0, 0, 100, NULL, 0, 'img/achievements/xp100.png'),
(5, 'xp Card 1000', 0, 0, 0, 1000, NULL, 0, 'img/achievements/xp1000.png'),
(6, 'xp Card 10000', 0, 0, 0, 10000, NULL, 0, 'img/achievements/xp10000.png'),
(7, 'xp Card 100.000', 0, 0, 0, 100000, NULL, 0, 'img/achievements/xp100000.png'),
(8, 'cp Card 1000000', 0, 0, 0, 1000000, NULL, 0, 'img/achievements/xp1000000.png'),
(9, 'xp Card 10.000.000', 0, 0, 0, 10000000, NULL, 0, 'img/achievements/xp10000000.png'),
(10, 'xp Card 100.000.000', 0, 0, 0, 100000000, NULL, 0, 'img/achievements/xp100000000.png'),
(11, 'xp Card 500000000', 0, 0, 0, 500000000, NULL, 0, 'img/achievements/xp500000000.png'),
(13, 'MR PATATO', 0, 200000, 0, 0, 22, 0, 'img/achievements/patato.png');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `bank_deposits`
--

CREATE TABLE `bank_deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `interest` int(11) NOT NULL,
  `hours` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `claimed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `bank_deposits`
--

INSERT INTO `bank_deposits` (`id`, `user_id`, `amount`, `interest`, `hours`, `start_time`, `end_time`, `claimed`) VALUES
(47, 2, 1000000, 200, 2, '2025-09-07 16:42:36', '2025-09-07 18:42:36', 1),
(62, 3, 1000000, 30000, 24, '2025-09-07 17:08:56', '2025-09-08 19:07:08', 1),
(63, 3, 1000000, 30000, 24, '2025-09-07 17:08:58', '2025-09-08 19:07:11', 1),
(64, 3, 1000000, 30000, 24, '2025-09-07 17:08:59', '2025-09-08 19:07:16', 1),
(68, 1, 1000000, 30000, 24, '2025-09-07 17:14:39', '2025-09-08 11:56:39', 1),
(70, 1, 1000000, 30000, 24, '2025-09-07 17:28:34', '2025-09-08 12:03:25', 1),
(71, 1, 1000000, 30000, 24, '2025-09-07 17:28:35', '2025-09-08 17:04:12', 1),
(82, 1, 1000000, 100, 1, '2025-09-08 16:04:40', '2025-09-08 17:04:40', 1),
(85, 1, 1000000, 100, 1, '2025-09-08 16:08:15', '2025-09-08 17:08:15', 1),
(88, 1, 1000000, 2000, 1, '2025-09-08 16:28:51', '2025-09-08 19:06:05', 1),
(89, 1, 1000000, 2000, 1, '2025-09-08 16:43:26', '2025-09-08 19:06:12', 1),
(91, 1, 1000000, 2000, 1, '2025-09-08 16:43:50', '2025-09-08 19:06:18', 1),
(92, 1, 1000000, 1000, 1, '2025-09-09 10:55:16', '2025-09-09 16:54:31', 1),
(93, 1, 1000000, 1000, 1, '2025-09-09 10:55:23', '2025-09-09 16:54:36', 1),
(94, 1, 1000000, 1000, 1, '2025-09-09 15:54:40', '2025-09-12 18:59:14', 1),
(95, 1, 1000000, 1000, 1, '2025-09-09 15:54:42', '2025-09-12 18:59:16', 1),
(96, 1, 1000000, 1000, 1, '2025-09-09 15:54:43', '2025-09-12 18:59:17', 1),
(97, 3, 1000000, 1000, 1, '2025-09-09 16:02:40', '2025-09-15 13:25:16', 1),
(98, 1, 1000000, 1000, 1, '2025-09-15 08:40:47', '2025-09-15 11:41:54', 1),
(99, 1, 1000000, 1000, 1, '2025-09-15 08:40:47', '2025-09-15 11:41:55', 1),
(100, 1, 1000000, 1000, 1, '2025-09-15 08:40:50', '2025-09-15 11:41:53', 1),
(101, 1, 1000000, 1000, 1, '2025-09-15 08:40:51', '2025-09-15 11:41:56', 1),
(102, 1, 1000000, 1000, 1, '2025-09-15 08:40:55', '2025-09-15 11:41:57', 1),
(103, 3, 1000000, 1000, 1, '2025-09-15 12:25:05', '2025-09-15 14:25:05', 0),
(104, 3, 1000000, 1000, 1, '2025-09-15 12:25:07', '2025-09-15 14:25:07', 0),
(105, 3, 1000000, 1000, 1, '2025-09-15 12:25:07', '2025-09-15 14:25:07', 0),
(106, 3, 1000000, 1000, 1, '2025-09-15 12:25:21', '2025-09-15 14:25:21', 0),
(107, 1, 1000000, 1000, 1, '2025-09-17 22:57:49', '2025-09-18 00:57:49', 0),
(108, 1, 1000000, 1000, 1, '2025-09-17 22:57:50', '2025-09-18 00:57:50', 0),
(109, 1, 1000000, 1000, 1, '2025-09-17 22:57:51', '2025-09-18 00:57:51', 0),
(110, 1, 1000000, 1000, 1, '2025-09-17 22:57:51', '2025-09-18 00:57:51', 0),
(111, 1, 1000000, 1000, 1, '2025-09-17 22:57:52', '2025-09-18 00:57:52', 0);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `bank_loans`
--

CREATE TABLE `bank_loans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `amount_due` int(11) NOT NULL,
  `amount_repaid` int(11) NOT NULL DEFAULT 0,
  `start_time` datetime NOT NULL,
  `repaid_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `bank_loans`
--

INSERT INTO `bank_loans` (`id`, `user_id`, `amount`, `amount_due`, `amount_repaid`, `start_time`, `repaid_time`) VALUES
(1, 3, 1000, 2000, 2000, '2025-09-07 17:10:14', '2025-09-07 18:10:33');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `bank_loan_payments`
--

CREATE TABLE `bank_loan_payments` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sale_total` int(11) NOT NULL,
  `applied` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `bank_loan_payments`
--

INSERT INTO `bank_loan_payments` (`id`, `loan_id`, `item_id`, `item_name`, `quantity`, `sale_total`, `applied`, `created_at`) VALUES
(1, 1, 15, 'Strawberry', 520, 520000, 2000, '2025-09-07 18:10:33');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `default_slots`
--

CREATE TABLE `default_slots` (
  `slot_number` int(11) NOT NULL,
  `slot_type` varchar(20) NOT NULL DEFAULT 'crop',
  `unlocked` tinyint(1) NOT NULL DEFAULT 0,
  `required_level` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `default_slots`
--

INSERT INTO `default_slots` (`slot_number`, `slot_type`, `unlocked`, `required_level`) VALUES
(1, 'crop', 1, 0),
(2, 'crop', 1, 0),
(3, 'crop', 1, 0),
(4, 'crop', 0, 4),
(5, 'crop', 0, 5),
(6, 'crop', 1, 0),
(7, 'crop', 1, 0),
(8, 'crop', 1, 0),
(9, 'crop', 0, 9),
(10, 'crop', 0, 10),
(11, 'crop', 0, 11),
(12, 'crop', 0, 12),
(13, 'crop', 0, 13),
(14, 'crop', 0, 14),
(15, 'crop', 0, 15),
(16, 'crop', 0, 16),
(17, 'crop', 0, 17),
(18, 'crop', 0, 18),
(19, 'crop', 0, 19),
(20, 'crop', 0, 20),
(21, 'crop', 0, 21),
(22, 'crop', 0, 22),
(23, 'crop', 0, 23),
(24, 'crop', 0, 24),
(25, 'crop', 0, 25),
(26, 'crop', 0, 26),
(27, 'crop', 0, 27),
(28, 'crop', 0, 28),
(29, 'crop', 0, 29),
(30, 'crop', 0, 30),
(31, 'crop', 0, 0),
(32, 'crop', 0, 0),
(33, 'crop', 0, 0),
(34, 'crop', 0, 0),
(35, 'crop', 0, 0);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `farm_items`
--

CREATE TABLE `farm_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `item_type` enum('plant','animal') NOT NULL,
  `slot_type` varchar(50) NOT NULL,
  `image_plant` varchar(255) NOT NULL,
  `image_ready` varchar(255) NOT NULL,
  `image_product` varchar(255) NOT NULL,
  `water_interval` int(11) NOT NULL DEFAULT 0,
  `feed_interval` int(11) NOT NULL DEFAULT 0,
  `water_times` int(11) NOT NULL DEFAULT 0,
  `feed_times` int(11) NOT NULL DEFAULT 0,
  `price` int(11) NOT NULL DEFAULT 0,
  `sell_price` int(11) NOT NULL DEFAULT 0,
  `production` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `barn_capacity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `farm_items`
--

INSERT INTO `farm_items` (`id`, `name`, `item_type`, `slot_type`, `image_plant`, `image_ready`, `image_product`, `water_interval`, `feed_interval`, `water_times`, `feed_times`, `price`, `sell_price`, `production`, `active`, `barn_capacity`) VALUES
(11, 'Preaty Siren', 'animal', 'pool', 'img/sirena.png', 'img/sirena.png', 'img/sirena.png', 0, 3600, 0, 100, 100000, 1000000, 1, 1, 0),
(13, 'Tomato', 'plant', 'crop', 'img/rosie.png', 'img/rosie.png', 'img/rosie.png', 45, 0, 25, 0, 40, 1, 100, 1, 0),
(14, 'Trident King', 'animal', 'pool', 'img/tridentlord.png', 'img/tridentlord.png', 'img/tridentlord.png', 0, 3600, 0, 100, 100000, 1000000, 1, 1, 0),
(15, 'Strawberry', 'plant', 'crop', 'img/strawberry.png', 'img/strawberry.png', 'img/strawberry.png', 40, 0, 20, 0, 50, 1, 100, 1, 0),
(16, 'Corn', 'plant', 'crop', 'img/porumb.png', 'img/porumb.png', 'img/porumb.png', 80, 0, 20, 0, 100, 1, 1000, 1, 0),
(21, 'Watermelon', 'plant', 'crop', 'img/harbuz.png', 'img/harbuz.png', 'img/harbuz.png', 105, 0, 50, 0, 1000, 5000, 1, 1, 0),
(22, 'Potato', 'plant', 'crop', 'img/cartofi.png', 'img/cartofi.png', 'img/cartofi.png', 60, 0, 15, 0, 100, 10, 100, 1, 0),
(23, 'Carrot', 'plant', 'crop', 'img/carrot.png', 'img/carrot.png', 'img/carrot.png', 45, 0, 20, 0, 100, 5, 150, 1, 0);

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
(2, 1, 3, 'accepted', '2025-08-04 09:36:33', '2025-08-04 09:37:21'),
(3, 3, 2, 'accepted', '2025-08-04 13:41:44', '2025-08-04 13:43:37'),
(4, 4, 1, 'accepted', '2025-08-05 19:33:14', '2025-08-05 19:33:29'),
(5, 4, 2, 'accepted', '2025-08-05 19:33:25', '2025-08-06 13:23:44'),
(6, 6, 1, 'accepted', '2025-08-06 12:06:37', '2025-08-06 12:42:14'),
(7, 6, 3, 'accepted', '2025-08-06 12:06:38', '2025-08-06 14:48:07'),
(8, 6, 4, 'accepted', '2025-08-06 12:06:39', '2025-08-28 20:24:28'),
(9, 6, 7, 'accepted', '2025-08-06 12:06:39', '2025-08-06 12:44:42'),
(10, 2, 1, 'accepted', '2025-08-06 13:25:39', '2025-08-06 13:27:07'),
(11, 3, 4, 'accepted', '2025-08-06 14:46:50', '2025-08-28 20:24:27'),
(12, 1, 8, 'pending', '2025-08-07 17:45:19', NULL),
(14, 10, 1, 'accepted', '2025-08-10 10:57:00', '2025-08-10 10:57:30'),
(15, 9, 3, 'accepted', '2025-08-10 12:35:34', '2025-08-10 12:35:45'),
(16, 7, 1, 'accepted', '2025-08-28 14:48:06', '2025-08-28 14:48:12'),
(17, 9, 1, 'accepted', '2025-08-28 15:31:42', '2025-08-28 15:31:48'),
(18, 1, 5, 'pending', '2025-08-28 21:59:16', NULL),
(19, 3, 5, 'pending', '2025-08-28 22:01:07', NULL),
(20, 11, 9, 'accepted', '2025-08-29 17:51:30', '2025-09-15 16:47:00'),
(22, 4, 7, 'pending', '2025-08-30 13:57:02', NULL),
(23, 11, 2, 'accepted', '2025-09-02 12:43:57', '2025-09-02 15:04:16'),
(28, 11, 1, 'accepted', '2025-09-02 13:49:37', '2025-09-02 13:49:50'),
(29, 11, 3, 'accepted', '2025-09-02 14:46:39', '2025-09-02 14:46:52'),
(30, 11, 4, 'accepted', '2025-09-02 14:59:53', '2025-09-08 14:22:38');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `helpers`
--

CREATE TABLE `helpers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `image` varchar(255) NOT NULL,
  `message_file` varchar(255) NOT NULL,
  `waters` int(11) NOT NULL DEFAULT 0,
  `feeds` int(11) NOT NULL DEFAULT 0,
  `harvests` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `helpers`
--

INSERT INTO `helpers` (`id`, `name`, `image`, `message_file`, `waters`, `feeds`, `harvests`) VALUES
(1, 'Helper Mofit', 'helper1M', 'helper1M', 200, 200, 70),
(2, 'Helper Zarina', 'helper1F', 'helper1F', 200, 200, 70);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `is_read`) VALUES
(1, 1, 2, 'salut', '2025-08-04 05:25:13', 1),
(2, 2, 1, 'salut', '2025-08-04 05:25:30', 1),
(3, 1, 2, 'salut', '2025-08-04 09:17:55', 1),
(9, 1, 2, 'salut iar', '2025-08-04 17:50:42', 1),
(10, 1, 2, 'salut', '2025-08-04 17:50:48', 1),
(11, 1, 2, 'salut', '2025-08-04 17:50:52', 1),
(12, 1, 2, 'salut', '2025-08-04 17:50:56', 1),
(13, 2, 1, 'da ma salut salut', '2025-08-04 17:51:32', 1),
(14, 2, 1, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2025-08-04 17:51:51', 1),
(15, 1, 2, 'aaaa', '2025-08-04 18:06:17', 1),
(16, 1, 2, 'aaaa', '2025-08-04 18:06:23', 1),
(17, 1, 2, 'aaaa', '2025-08-04 18:06:33', 1),
(18, 1, 2, 'aa', '2025-08-04 18:16:40', 1),
(19, 1, 2, 'a', '2025-08-04 18:27:56', 1),
(20, 1, 2, 'a', '2025-08-04 18:28:26', 1),
(21, 1, 2, 'a', '2025-08-04 18:41:01', 1),
(22, 1, 2, 'abc', '2025-08-04 18:41:15', 1),
(23, 2, 1, 'abcd', '2025-08-04 18:41:41', 1),
(24, 2, 1, 'aaaaa', '2025-08-04 18:41:57', 1),
(25, 1, 2, 'aaaaaa', '2025-08-04 18:42:02', 1),
(26, 2, 1, 'aa', '2025-08-04 18:52:30', 1),
(27, 1, 2, 'aa', '2025-08-04 18:52:39', 1),
(28, 2, 1, 'aaa', '2025-08-04 18:52:45', 1),
(29, 2, 1, 'aaaa', '2025-08-04 18:52:49', 1),
(30, 2, 1, 'aaaa', '2025-08-04 18:52:50', 1),
(31, 2, 1, 'aaaa', '2025-08-04 18:53:00', 1),
(32, 1, 2, 'aaaa', '2025-08-04 18:54:29', 1),
(60, 2, 1, 'aaaaaaa', '2025-08-05 18:46:08', 1),
(61, 1, 2, 'aaaaaaaaaaaaaaaaaa', '2025-08-05 18:46:29', 1),
(62, 4, 1, 'ce faci waaaaaaaa', '2025-08-05 19:34:14', 1),
(63, 1, 4, 'bini wa', '2025-08-05 19:34:24', 1),
(64, 4, 1, 'bini', '2025-08-05 19:34:27', 1),
(65, 1, 4, 'ti pup baaaaaaa', '2025-08-05 19:34:41', 1),
(66, 4, 1, 'shoo', '2025-08-05 19:36:45', 1),
(67, 4, 1, '..', '2025-08-05 19:37:17', 1),
(68, 4, 1, '.', '2025-08-05 19:37:17', 1),
(69, 4, 1, '..', '2025-08-05 19:37:17', 1),
(70, 4, 1, '.', '2025-08-05 19:37:17', 1),
(71, 4, 1, '.', '2025-08-05 19:37:17', 1),
(72, 4, 1, '.', '2025-08-05 19:37:17', 1),
(73, 4, 1, '.', '2025-08-05 19:37:18', 1),
(74, 4, 1, '.', '2025-08-05 19:37:18', 1),
(75, 4, 1, '.', '2025-08-05 19:37:18', 1),
(76, 4, 1, '.', '2025-08-05 19:37:19', 1),
(77, 4, 1, '.', '2025-08-05 19:37:19', 1),
(78, 4, 1, '.', '2025-08-05 19:37:19', 1),
(79, 4, 1, '..', '2025-08-05 19:37:20', 1),
(80, 4, 1, '..', '2025-08-05 19:37:20', 1),
(81, 4, 1, '..', '2025-08-05 19:37:20', 1),
(82, 1, 4, 'aa', '2025-08-05 19:37:30', 1),
(83, 1, 4, 'a', '2025-08-05 19:37:30', 1),
(84, 1, 4, 'a', '2025-08-05 19:37:31', 1),
(85, 1, 4, 'a', '2025-08-05 19:37:32', 1),
(86, 1, 4, 'a', '2025-08-05 19:37:34', 1),
(87, 1, 4, 'a', '2025-08-05 19:37:35', 1),
(88, 1, 4, 'a', '2025-08-05 19:37:37', 1),
(89, 1, 4, 'poti da ctrl+F5 daca iti mai apare pagini care par ca nus sunt bune , daca raman tot rele e de la mine dar daca nu i de la chache', '2025-08-05 19:55:56', 1),
(90, 4, 1, 'ooo ai facut astea cu semintele', '2025-08-06 08:30:37', 1),
(91, 4, 1, 'sloturile', '2025-08-06 08:30:39', 1),
(92, 4, 1, 'e biness', '2025-08-06 08:30:41', 1),
(93, 4, 1, ':))', '2025-08-06 08:30:42', 1),
(94, 1, 4, 'mai am de lucru la ele', '2025-08-06 09:01:46', 1),
(95, 1, 4, 'vreau sa le configurez cu auto zoom incadrat pe orice dispozitiv', '2025-08-06 09:02:15', 1),
(96, 1, 4, 'adica tele browser diferit etc , sa le incadreze fara sa fie mici sa mai mari pentru scroll', '2025-08-06 09:02:49', 1),
(97, 1, 4, 'si dupa ce fac asta vreau sa fac mesageria sa apara cand primesti mesaj nou sa apara o bulina rosie care falfaie', '2025-08-06 09:03:44', 1),
(98, 1, 4, 'si sa setez la ce ora sa scris mesajul', '2025-08-06 09:04:04', 1),
(99, 4, 1, 'e bines', '2025-08-06 09:16:35', 1),
(100, 4, 1, 'eu n-am ce face si ma uit la filmul cu ISUS', '2025-08-06 09:16:45', 1),
(101, 1, 4, 'merge greu azi treaba , ca sunt detalii care nu se vad vizual dar au o prioritate maxima ca fara ele nu se poate', '2025-08-06 10:24:31', 1),
(102, 1, 4, 'insa tot am facut multe si azi', '2025-08-06 10:24:48', 1),
(103, 4, 1, 'mai ai inca multe de facut', '2025-08-06 10:49:34', 1),
(104, 1, 4, 'clar , acum ma chinuiesc cu poza default la profil', '2025-08-06 11:12:59', 1),
(105, 1, 4, 'ca stau de mult deja , cand fac fisiere noi imi face fara greseli dar cand am de editat stau cu zilele', '2025-08-06 11:13:35', 1),
(106, 1, 4, 'salut', '2025-08-06 13:43:21', 1),
(109, 1, 3, 'salut', '2025-08-06 13:49:10', 1),
(110, 1, 3, 'salut', '2025-08-06 13:49:24', 1),
(111, 1, 3, 'salut', '2025-08-06 14:03:46', 1),
(112, 1, 3, 'salut', '2025-08-06 14:07:01', 1),
(113, 1, 3, 'salut', '2025-08-06 14:07:27', 1),
(114, 1, 3, 'salut', '2025-08-06 14:07:35', 1),
(115, 1, 3, 'salut', '2025-08-06 14:07:50', 1),
(116, 3, 1, 'Salut', '2025-08-06 14:20:47', 1),
(117, 3, 1, 'Salut', '2025-08-06 14:20:54', 1),
(118, 3, 1, 'Salut', '2025-08-06 14:21:08', 1),
(119, 3, 1, 'Salut', '2025-08-06 14:21:15', 1),
(120, 3, 1, 'Salut', '2025-08-06 14:21:21', 1),
(121, 3, 1, 'Salut', '2025-08-06 14:21:27', 1),
(122, 1, 3, 'salut', '2025-08-06 14:21:44', 1),
(123, 1, 3, 'salut', '2025-08-06 14:21:49', 1),
(124, 1, 3, 'salut', '2025-08-06 14:21:54', 1),
(125, 1, 3, 'salut', '2025-08-06 14:22:00', 1),
(126, 1, 3, 'salut', '2025-08-06 14:22:08', 1),
(127, 1, 3, 'salut', '2025-08-06 14:22:17', 1),
(128, 3, 1, 'Salut', '2025-08-06 14:22:23', 1),
(129, 3, 1, 'Salut', '2025-08-06 14:22:28', 1),
(130, 3, 1, 'Salut', '2025-08-06 14:22:37', 1),
(131, 3, 1, 'Salut', '2025-08-06 14:28:53', 1),
(132, 1, 3, 'salut', '2025-08-06 14:29:12', 1),
(133, 1, 3, 'salut', '2025-08-06 14:29:26', 1),
(134, 1, 3, 'salut', '2025-08-06 14:29:31', 1),
(135, 1, 3, 'sal', '2025-08-06 14:29:41', 1),
(136, 1, 3, 'salut', '2025-08-06 14:29:55', 1),
(137, 1, 3, 'salut', '2025-08-06 14:30:34', 1),
(138, 1, 3, 'salut', '2025-08-06 14:30:48', 1),
(139, 1, 3, 'salut', '2025-08-06 14:37:15', 1),
(140, 1, 3, 'salut', '2025-08-06 14:37:31', 1),
(141, 1, 3, 'sal', '2025-08-06 14:37:39', 1),
(142, 1, 3, 'sal', '2025-08-06 14:37:45', 1),
(143, 1, 3, 'sal', '2025-08-06 14:37:51', 1),
(144, 1, 3, 'sal', '2025-08-06 14:37:57', 1),
(145, 3, 1, 'Sal', '2025-08-06 14:38:33', 1),
(146, 3, 1, 'Sal', '2025-08-06 14:38:38', 1),
(147, 3, 1, 'Sal', '2025-08-06 14:46:32', 1),
(148, 1, 3, 'salut', '2025-08-06 14:50:30', 1),
(149, 1, 3, 'salut', '2025-08-06 14:50:39', 1),
(150, 1, 3, 'salut', '2025-08-06 14:50:55', 1),
(151, 1, 3, 'salut', '2025-08-06 14:51:07', 1),
(152, 3, 1, 'Salutare', '2025-08-06 15:16:36', 1),
(153, 3, 1, 'Salut', '2025-08-06 15:17:41', 1),
(154, 1, 3, 'salut', '2025-08-06 15:18:19', 1),
(155, 1, 3, 'salut', '2025-08-06 15:18:47', 1),
(156, 1, 3, 'catalina te iiubesc fa :))', '2025-08-06 15:22:52', 1),
(157, 1, 3, 'auzi?', '2025-08-06 15:23:20', 1),
(158, 1, 3, 'sau nu?', '2025-08-06 15:23:46', 1),
(159, 3, 1, 'Da ma aud 🤣', '2025-08-06 15:24:08', 1),
(160, 1, 3, 'bine tu bambolino', '2025-08-06 15:24:27', 1),
(161, 1, 3, 'auzi', '2025-08-06 15:25:00', 1),
(162, 1, 3, 'dar daca', '2025-08-06 15:25:08', 1),
(163, 3, 1, 'Daca ce', '2025-08-06 15:25:23', 1),
(164, 1, 3, 'nimic .. lasa', '2025-08-06 15:25:38', 1),
(165, 1, 3, 'ca daca zic', '2025-08-06 15:25:56', 1),
(166, 1, 3, 'tu orcum', '2025-08-06 15:26:04', 1),
(167, 3, 1, 'Hai nu ma freca , zici sau nu ?', '2025-08-06 15:26:38', 1),
(168, 3, 1, '?', '2025-08-06 15:26:49', 1),
(169, 3, 1, 'Nu , lasa', '2025-08-06 15:27:07', 1),
(170, 3, 1, '🤭', '2025-08-06 15:27:38', 1),
(171, 3, 1, '😘', '2025-08-06 15:28:02', 1),
(172, 3, 1, 'Auzi , dar tu ma iubesti?', '2025-08-06 15:28:15', 1),
(173, 1, 3, 'nuuu ...', '2025-08-06 15:28:34', 1),
(174, 1, 3, 'normal ca da tu ce crezi???', '2025-08-06 15:28:52', 1),
(175, 3, 1, 'Stiam eu', '2025-08-06 15:29:19', 1),
(176, 4, 1, 'ooo', '2025-08-06 15:30:33', 1),
(177, 4, 1, 'ai pus delete', '2025-08-06 15:30:35', 1),
(178, 4, 1, 'ce se aude', '2025-08-06 15:30:38', 1),
(179, 4, 1, 'zici ca pica apa', '2025-08-06 15:30:41', 1),
(180, 4, 1, ':))', '2025-08-06 15:30:42', 1),
(181, 3, 1, 'AuI', '2025-08-06 15:36:02', 1),
(182, 3, 1, 'Auzi', '2025-08-06 15:36:14', 1),
(183, 3, 1, 'Ba', '2025-08-06 15:37:57', 1),
(184, 1, 4, 'hahahahah asa lam facut eu', '2025-08-06 15:38:34', 1),
(185, 4, 1, 'daa', '2025-08-06 15:38:38', 1),
(186, 4, 1, 'se aude cand tastezi', '2025-08-06 15:38:45', 1),
(187, 4, 1, 'ma joc cs si nu stiam ce se aude', '2025-08-06 15:38:55', 1),
(188, 1, 4, 'pizdai seama', '2025-08-06 15:38:58', 1),
(189, 4, 1, 'imi pune 2 mseaje o data', '2025-08-06 15:39:08', 1),
(190, 4, 1, '/', '2025-08-06 15:39:08', 1),
(191, 1, 4, 'trebuie sa stergi chacheurile si istoricul si intrii iar , mie nu imi pune 2 mesaje de o data', '2025-08-06 15:39:39', 1),
(192, 3, 1, 'Baa', '2025-08-06 15:40:17', 1),
(193, 3, 1, 'Auzi', '2025-08-06 15:40:23', 1),
(194, 1, 3, 'da fa iubirea mea', '2025-08-06 15:40:46', 1),
(195, 4, 1, 'cum sterg', '2025-08-06 15:40:59', 1),
(196, 4, 1, 'chacheurle', '2025-08-06 15:41:02', 1),
(197, 1, 4, 'esti pe browser?', '2025-08-06 15:41:16', 1),
(198, 4, 1, 'da', '2025-08-06 15:43:16', 1),
(199, 2, 4, 'Bau', '2025-08-06 15:47:58', 1),
(200, 2, 1, 'Bai', '2025-08-06 15:48:22', 1),
(201, 4, 1, 'sdasdad', '2025-08-06 15:48:37', 1),
(202, 4, 1, 'asdasd', '2025-08-06 15:48:38', 1),
(203, 3, 1, 'Acum', '2025-08-06 16:02:59', 1),
(204, 3, 1, 'Acum', '2025-08-06 16:03:13', 1),
(205, 3, 1, 'Acum', '2025-08-06 16:03:42', 1),
(206, 1, 3, 'salut', '2025-08-06 16:04:30', 1),
(207, 2, 1, 'Salut', '2025-08-06 16:11:18', 1),
(208, 3, 1, 'Salut', '2025-08-06 16:11:28', 1),
(209, 3, 1, 'Salut', '2025-08-06 20:04:40', 1),
(210, 1, 3, 'salutare', '2025-08-06 20:05:01', 1),
(211, 3, 1, 'Ce faci', '2025-08-06 20:05:22', 1),
(212, 1, 3, 'bine uite pe acasa tu ?', '2025-08-06 20:05:33', 1),
(213, 1, 3, 'bau', '2025-08-06 21:52:09', 1),
(214, 3, 1, '🤭', '2025-08-06 21:52:29', 1),
(215, 3, 1, 'Ce bau ma?', '2025-08-06 21:52:41', 1),
(216, 3, 1, 'Bau ce nu ai auzit de bau?', '2025-08-06 21:53:04', 1),
(217, 1, 4, 'carei viata nexus?', '2025-08-07 10:09:29', 1),
(218, 4, 1, 'a?', '2025-08-07 10:17:47', 1),
(219, 4, 1, 'astept sa vina mama din oras si sa merg la dentist', '2025-08-07 10:18:01', 1),
(220, 1, 4, 'e bun', '2025-08-07 10:18:41', 1),
(221, 4, 1, 'ce face baiatuu', '2025-08-07 12:32:38', 1),
(222, 4, 1, '??', '2025-08-07 12:32:39', 1),
(223, 1, 4, 'lucrez aici dar is asa de obosit ...', '2025-08-07 12:32:58', 1),
(224, 1, 4, 'tu cf', '2025-08-07 12:33:00', 1),
(225, 4, 1, 'am venit de la dentist', '2025-08-07 12:33:13', 1),
(226, 1, 4, 'totu bine?', '2025-08-07 12:33:20', 1),
(227, 4, 1, 'da', '2025-08-07 12:33:25', 1),
(228, 4, 1, 'tu', '2025-08-07 12:33:28', 1),
(229, 1, 4, 'poti da click pe sloturi acuma asta am facut abia acum am terminat', '2025-08-07 12:33:39', 1),
(230, 1, 4, 'da inafara ca sunt rupt de obosit degeaba ,in rest da', '2025-08-07 12:33:55', 1),
(231, 4, 1, 'inainte cand dadeam click', '2025-08-07 12:34:41', 1),
(232, 4, 1, 'se deschidea', '2025-08-07 12:34:44', 1),
(233, 4, 1, 'acuma nu se intampla nmc', '2025-08-07 12:34:49', 1),
(234, 1, 4, 'da ctrl+F5', '2025-08-07 12:35:03', 1),
(235, 1, 4, 'si dai iar', '2025-08-07 12:35:11', 1),
(236, 1, 4, 'zimi daca merge', '2025-08-07 12:35:25', 1),
(237, 4, 1, 'OU DA', '2025-08-07 12:36:27', 1),
(238, 4, 1, 'merge', '2025-08-07 12:36:28', 1),
(239, 1, 4, 'am verificat pt telefon nu apare', '2025-08-07 12:36:49', 1),
(240, 1, 4, 'bagameas , credeam c am scapat cu panoul asta', '2025-08-07 12:37:01', 1),
(241, 4, 1, 'nu apare?', '2025-08-07 12:37:16', 1),
(242, 4, 1, 'ia stai sa vad eu', '2025-08-07 12:37:19', 1),
(243, 1, 4, 'nu deschide cand dau nu', '2025-08-07 12:37:29', 1),
(244, 1, 4, 'ba da a mers am dat de 2 orii stergere de istoric si de date', '2025-08-07 12:38:27', 1),
(245, 1, 4, 'bun numai ca e prea mare si nu are buton de back pentru telefon', '2025-08-07 12:38:59', 1),
(246, 4, 1, 'merge pe tel', '2025-08-07 12:39:32', 1),
(247, 4, 1, 'sa stii', '2025-08-07 12:39:33', 1),
(248, 1, 4, 'da merge , am vazut', '2025-08-07 12:39:40', 1),
(249, 1, 4, 'trebuie sa il fac mai mic ca nu poti da click pe langa daca vrei sa te razganesti', '2025-08-07 12:40:01', 1),
(250, 4, 1, 'ala din lemn de pe background', '2025-08-07 12:40:21', 1),
(251, 4, 1, 'poti', '2025-08-07 12:40:22', 1),
(252, 1, 4, 'acuma il fac manual', '2025-08-07 12:40:33', 1),
(253, 1, 4, 'ia vezi cum pare?', '2025-08-07 12:40:52', 1),
(254, 4, 1, 'bines', '2025-08-07 12:41:18', 1),
(255, 1, 4, 'mie imi pare ok', '2025-08-07 12:41:23', 1),
(256, 4, 1, 'acuma am vazut ca am money', '2025-08-07 12:41:35', 1),
(257, 1, 4, 'ca poti da tap pe ecran pe langa si dispare', '2025-08-07 12:41:37', 1),
(258, 1, 4, 'stai sa vezi ca iti adaug din baza de date sa vezi cum urca, sau scade , ca cred ca ti-am dat maxim fii atent si imi zici daca vezi efectul', '2025-08-07 12:42:13', 1),
(259, 1, 4, 'aveai mazim bani si am modificat , ai vazut efectul live cum scadeau banii?', '2025-08-07 12:43:28', 1),
(260, 4, 1, 'nu eram pe faza', '2025-08-07 12:43:57', 1),
(261, 4, 1, ':(', '2025-08-07 12:43:58', 1),
(262, 1, 4, 'vreau ca banii verzi sa faca parte din joc si banii gold sa ii poata cumpara online cu bani reali', '2025-08-07 12:44:04', 1),
(263, 1, 4, 'mai fac o data fii atent', '2025-08-07 12:44:10', 1),
(264, 4, 1, 'daaa', '2025-08-07 12:44:34', 1),
(265, 4, 1, 'ce smechera animatia', '2025-08-07 12:44:38', 1),
(266, 1, 4, 'asa o sa se vada cand cumperi cand vinzi', '2025-08-07 12:44:46', 1),
(267, 1, 4, ':)', '2025-08-07 12:44:52', 1),
(268, 4, 1, '=D', '2025-08-07 12:44:56', 1),
(269, 1, 4, 'numai ca o sa am nevoie de un site bun', '2025-08-07 12:45:09', 1),
(270, 1, 4, 'sa cumpar o luna sa fac poze noi profesionale', '2025-08-07 12:45:26', 1),
(271, 4, 1, 'vezi pe filelist', '2025-08-07 12:45:35', 1),
(272, 1, 4, 'astea sunt doar asa sa ma ajute sa il fac', '2025-08-07 12:45:39', 1),
(273, 4, 1, 'poate e photoshop crack', '2025-08-07 12:45:42', 1),
(274, 1, 4, 'nu ca nu am voie sa iau poze', '2025-08-07 12:45:53', 1),
(275, 4, 1, 'sa editezi', '2025-08-07 12:46:06', 1),
(276, 1, 4, 'trebuie sa am grija la drepturi de autor', '2025-08-07 12:46:07', 1),
(277, 1, 4, 'aaaa', '2025-08-07 12:46:09', 1),
(278, 1, 4, 'nu e prea greu', '2025-08-07 12:46:14', 1),
(279, 1, 4, 'eu vreau cu ai sa generez', '2025-08-07 12:46:21', 1),
(280, 4, 1, 'aaa', '2025-08-07 12:46:27', 1),
(281, 1, 4, 'o sa caut subscritie de o luna sa le fac cu promturi adica sa ii zic cum vreau imaginea', '2025-08-07 12:46:45', 1),
(282, 4, 1, 'dupa ce termini jocu', '2025-08-07 12:47:00', 1),
(283, 1, 4, 'da na sa fac intai tot ce trebuie si la urma', '2025-08-07 12:47:03', 1),
(284, 4, 1, 'bagi niste add-uri', '2025-08-07 12:47:13', 1),
(285, 1, 4, 'dupa ce il termin vreau sa ii cumpar host', '2025-08-07 12:47:14', 1),
(286, 1, 4, 'si sa ii fac reclama platita', '2025-08-07 12:47:24', 1),
(287, 1, 4, 'dar mai e mult pana atunci desii pare ca e ca si gata', '2025-08-07 12:47:35', 1),
(288, 4, 1, 'oare il termini intr-un an?', '2025-08-07 12:47:38', 1),
(289, 1, 4, 'am treaba multa inca', '2025-08-07 12:47:42', 1),
(290, 1, 4, 'daaaa', '2025-08-07 12:47:47', 1),
(291, 1, 4, 'ma gandesc ca in 2 luni e gata', '2025-08-07 12:47:56', 1),
(292, 4, 1, 'meama', '2025-08-07 12:48:00', 1),
(293, 1, 4, 'ca e bun tare codex asta', '2025-08-07 12:48:07', 1),
(294, 1, 4, 'e codex direct din chat gbt', '2025-08-07 12:48:20', 1),
(295, 1, 4, 'cu acelas cont', '2025-08-07 12:48:27', 1),
(296, 1, 4, 'acuma depinde si cat lucrez dar codex e foarte puternic', '2025-08-07 12:48:43', 1),
(297, 4, 1, 'poti sa faci in hambar rafturi de alea nu?', '2025-08-07 12:48:47', 1),
(298, 1, 4, 'da clar', '2025-08-07 12:48:52', 1),
(299, 4, 1, 'poate vad eu si fac', '2025-08-07 12:48:58', 1),
(300, 4, 1, ':))', '2025-08-07 12:48:59', 1),
(301, 4, 1, 'o incercare', '2025-08-07 12:49:03', 1),
(302, 1, 4, 'numai ca le fac cu logica ca sa nu incep sa editez', '2025-08-07 12:49:03', 1),
(303, 1, 4, 'ca e foarte rau pe editat', '2025-08-07 12:49:09', 1),
(304, 1, 4, 'daca faci din prima iti face traba buna daca faci editari stai foarte mult', '2025-08-07 12:49:35', 1),
(305, 1, 4, 'si trebuie sa stii si tu multe chestii ca daca nu e foarte greu', '2025-08-07 12:49:51', 1),
(306, 1, 4, 'tu ce vrei sa faci?', '2025-08-07 12:49:59', 1),
(307, 4, 1, 'fac rafturile alea', '2025-08-07 12:50:08', 1),
(308, 4, 1, 'ma uit si eu', '2025-08-07 12:50:11', 1),
(309, 1, 4, 'adica te uiti? :))', '2025-08-07 12:50:30', 1),
(310, 1, 4, 'nu cred ca ma apuc azi de ele', '2025-08-07 12:50:35', 1),
(311, 1, 4, 'ca vreau sa fac tipuri de sloturi', '2025-08-07 12:50:45', 1),
(312, 1, 4, 'din panoul care se dechide', '2025-08-07 12:50:56', 1),
(313, 1, 4, 'si de acolo sa le poti schimba cu bani si sa le afiseze pe profil', '2025-08-07 12:51:12', 1),
(314, 4, 1, 'meama', '2025-08-07 12:51:30', 1),
(315, 1, 4, 'si trebuie sa fac si un start default de sloturi ,', '2025-08-07 12:51:46', 1),
(316, 1, 4, 'adica fiecare cont nou sa aibe un start de sloturi apoi sa poti debloca sloturile cand faci level , le trebuie anumite imagini anumite conditii e greu', '2025-08-07 12:52:32', 1),
(317, 1, 4, 'e multa treaba dar invizibila', '2025-08-07 12:52:41', 1),
(318, 1, 4, 'trebuie dupa ce fac sloturile default pentru conturile noi', '2025-08-07 12:53:02', 1),
(319, 1, 4, 'sa fac si o pagina similara cum e ferma noastra doar ca de vizita la alti fermieri', '2025-08-07 12:53:26', 1),
(320, 4, 1, 'ai multa treaba', '2025-08-07 12:53:51', 1),
(321, 1, 4, 'apoi daca esti prieten sa ii poti ajuta daca nu il vizitezi doar', '2025-08-07 12:53:53', 1),
(322, 1, 4, 'dah', '2025-08-07 12:53:55', 1),
(323, 1, 4, 'mai ales pe tipurile de sloturi', '2025-08-07 12:54:08', 1),
(324, 1, 4, 'ca acolo trebuie facute tipul  de slot , ce fel de plantare se poate pune pe acel slot', '2025-08-07 12:54:30', 1),
(325, 1, 4, 'cat costa , la cat timp uzi sau hranesti', '2025-08-07 12:54:45', 1),
(326, 1, 4, 'is multe chestii orcum', '2025-08-07 12:54:55', 1),
(327, 4, 1, 'da', '2025-08-07 12:55:09', 1),
(328, 1, 4, 'dar nu stiu daca mai fac azi ca is rupt', '2025-08-07 12:55:09', 1),
(329, 4, 1, 'tragi un somn', '2025-08-07 12:55:27', 1),
(330, 4, 1, 'si dupa', '2025-08-07 12:55:29', 1),
(331, 4, 1, 'mai faci', '2025-08-07 12:55:33', 1),
(332, 1, 4, 'nici nu stiu doar ca is rupt , as face ca vad tot ca avansez', '2025-08-07 12:56:17', 1),
(333, 1, 4, 'aseara am facut sistemul de bani', '2025-08-07 12:56:30', 1),
(334, 1, 4, 'si azi m-am chinui enorm doar sa pot afisa pe toata imaginea sloturile sa le vada pe toata pagina fara scroling', '2025-08-07 12:57:07', 1),
(335, 1, 4, 'ca eu verifica sa fie bun si pentru telefon', '2025-08-07 12:57:34', 1),
(336, 4, 1, 'daa', '2025-08-07 12:57:39', 1),
(337, 1, 4, 'ca daca nu dupa aia e foarte greu de facut', '2025-08-07 12:57:42', 1),
(338, 1, 4, 'mai ales caeu as vrea sa il pun si in store play', '2025-08-07 12:58:06', 1),
(339, 1, 4, 'ii fac o aplicatie doar sa tina loc de browser', '2025-08-07 12:58:19', 1),
(340, 1, 4, 'si daca imi accepta astia de la steam , dar deja gandesc prea departe', '2025-08-07 12:58:38', 1),
(341, 1, 4, 'pe rand toate', '2025-08-07 12:58:55', 1),
(342, 1, 4, 'dar orcum cand e gata jocu vreau sa ii fac reclama platita', '2025-08-07 12:59:10', 1),
(343, 1, 4, 'ca sa pot castiga bani din ce cumpara astia', '2025-08-07 12:59:27', 1),
(344, 4, 1, ';)', '2025-08-07 12:59:33', 1),
(345, 1, 4, 'vip si gold', '2025-08-07 12:59:34', 1),
(346, 1, 4, 'cacatu ala de big barn world face bani de aproape 15 ani de zile', '2025-08-07 12:59:56', 1),
(347, 1, 4, 'au aia vip luna de luna si e cam scump', '2025-08-07 13:00:09', 1),
(348, 1, 4, 'e cam 100 si ceva de lei pe luna', '2025-08-07 13:00:23', 1),
(349, 4, 1, 'mama', '2025-08-07 13:01:01', 1),
(350, 1, 4, 'dah imagineazati 20 de persoane sa isi ia vip pe luna doar', '2025-08-07 13:01:22', 1),
(351, 1, 4, 'dar asta e un minim pus asa', '2025-08-07 13:01:31', 1),
(352, 1, 4, 'daca jocu e popular si frumos faci bani', '2025-08-07 13:01:43', 1),
(353, 4, 1, 'is peste 10.000 de persoane pe  big barn world', '2025-08-07 13:01:51', 1),
(354, 1, 4, 'cred ca da', '2025-08-07 13:01:58', 1),
(355, 1, 4, 'o sa le fac si eu beneficii vip atragatoare si fac vipul iestin', '2025-08-07 13:02:23', 1),
(356, 1, 4, 'ieftin', '2025-08-07 13:02:29', 1),
(357, 1, 4, 'ultimele 5 sloturi de jos sa poata fi folosite doar cu vip sau gold', '2025-08-07 13:02:53', 1),
(358, 4, 1, '100.000 de instalări are big barn world', '2025-08-07 13:02:55', 1),
(359, 1, 4, 'nah are si 15 ani de zile aproape', '2025-08-07 13:03:09', 1),
(360, 4, 1, 'daa', '2025-08-07 13:03:17', 1),
(361, 1, 4, 'dar ei nu au facut reclame masiv', '2025-08-07 13:03:19', 1),
(362, 1, 4, 'de aia se merita daca faci reclama ca se castiga', '2025-08-07 13:03:30', 1),
(363, 1, 4, 'numai ca na e mult de munca pana e gata', '2025-08-07 13:03:37', 1),
(364, 1, 4, 'vreau mai la final sa fac un panou admin', '2025-08-07 13:03:56', 1),
(365, 1, 4, 'care sa poata adauga direct tipuri de sloturi si setarile doar selectatnd la cat timp se uda ce poza are cum arata plantat cum arata semi crescut cum arta full crescut gata de recoltat', '2025-08-07 13:04:50', 1),
(366, 1, 4, 'etc s ce produce , aste as fie setari si eu sa bag dor imaginile', '2025-08-07 13:05:06', 1),
(367, 1, 4, 'ca sa imi fie usor pe viitor ca sa fac magazinul mare', '2025-08-07 13:05:20', 1),
(368, 4, 1, 'e bines', '2025-08-07 13:05:41', 1),
(369, 1, 4, 'ideea e top', '2025-08-07 13:05:47', 1),
(370, 1, 4, 'doar ca e greu', '2025-08-07 13:05:52', 1),
(371, 1, 4, 'si trebuie sa fiu atent in ce oride fac si ce fac', '2025-08-07 13:06:07', 1),
(372, 1, 4, 'ordine', '2025-08-07 13:06:13', 1),
(373, 4, 1, 'uita te olc whatsapp', '2025-08-07 13:07:07', 1),
(374, 1, 4, 'nus unde e tel stai', '2025-08-07 13:07:36', 1),
(375, 1, 4, 'ma apuc de panoul de la shop si cel de la schimbare slot poate le pot face', '2025-08-07 16:44:55', 1),
(376, 4, 1, 'phoa ce smecher e panou de la shop', '2025-08-08 16:00:23', 1),
(377, 4, 1, 'si ala de la change plot', '2025-08-08 16:00:43', 1),
(378, 1, 4, 'e grozav de greu de facut', '2025-08-08 16:21:12', 1),
(379, 1, 4, 'acuma lucram la el', '2025-08-08 16:21:18', 1),
(380, 1, 4, 'sa vezi ca iti ia din bani cand schimbi :)) sa te uiti la animatie', '2025-08-08 16:21:49', 1),
(381, 1, 4, 'ma intepat inima acum cateva minute asa de tare plm', '2025-08-08 16:24:18', 1),
(382, 4, 1, 'am vazut', '2025-08-08 21:27:32', 1),
(383, 4, 1, 'ma joc cs 1.6', '2025-08-08 21:27:35', 1),
(384, 10, 1, 'salut', '2025-08-10 10:59:02', 1),
(385, 1, 10, 'aiai', '2025-08-10 10:59:13', 1),
(386, 10, 1, 'iti crapa capu', '2025-08-10 10:59:15', 1),
(387, 1, 10, ':)))) bines', '2025-08-10 10:59:19', 1),
(388, 1, 10, 'o sa stez volum mai mic , bine ca mi-ai zis', '2025-08-10 10:59:46', 1),
(389, 10, 1, 'da', '2025-08-10 11:00:06', 1),
(390, 1, 10, 'vezi ca ai bani acuma si poti sa schimbi alea', '2025-08-10 11:00:14', 1),
(391, 1, 10, 'o sa urmeze sa fac tipuri de animale , plante etc si cu setari de udare hranire etc', '2025-08-10 11:00:41', 1),
(392, 1, 10, 'jocul nu e 3d eu stiu ce ai zis doar ca e prea greu cu alea , eu il fac totul cs php css si js doar', '2025-08-10 11:01:13', 1),
(393, 1, 10, 'si imagini', '2025-08-10 11:01:22', 1),
(394, 1, 10, 'e in stilul de joc big barn world daca il cunosti', '2025-08-10 11:01:55', 1),
(395, 10, 1, 'nu stiu', '2025-08-10 11:02:33', 1),
(396, 1, 10, 'vrei sa iti dau si un level mai mare sa testezi sloturile alea blocate daa le deblocheaza?', '2025-08-10 11:02:35', 1),
(397, 10, 1, 'de ce mi se pune de 2 ori', '2025-08-10 11:02:43', 1),
(398, 10, 1, 'uite amu nu', '2025-08-10 11:02:50', 1),
(399, 1, 10, 'nustiu cateodata pune de 2 ori dar numai pt tine nu si la mine', '2025-08-10 11:03:02', 1),
(400, 1, 10, 'daa numai cateodata da nustiu dc', '2025-08-10 11:03:29', 1),
(401, 10, 1, 'da', '2025-08-10 11:03:37', 1),
(402, 1, 10, 'uitate cum se schimba la bani sus', '2025-08-10 11:03:50', 1),
(403, 1, 10, 'cand schimbi terenul :)))', '2025-08-10 11:03:57', 1),
(404, 1, 10, 'iti dau level 20 sa vezi ca iti deblocheaza sloturile alea blocate', '2025-08-10 11:04:28', 1),
(405, 1, 10, 'pff eu prima data am gresit ti-am dat gold fara bani si am pus level de 5000 :) ti-am dat level 20 acum sa vezi', '2025-08-10 11:05:43', 1),
(406, 1, 10, 'si bani', '2025-08-10 11:05:47', 1),
(407, 10, 1, 'am vazut', '2025-08-10 11:06:06', 1),
(408, 10, 1, 'am vazyr', '2025-08-10 11:06:14', 1),
(409, 1, 10, 'urmeaza sa fac si vizita de ferma la alti utilizatori dar nu o fac inca ca am de lucru la cehestii esentiale si de aia', '2025-08-10 11:06:36', 1),
(410, 1, 10, 'si sa il fac daca esti prieten sa poti sa il ajuti', '2025-08-10 11:06:50', 1),
(411, 10, 1, 'da', '2025-08-10 11:06:58', 1),
(412, 1, 10, 'sa uzi sa ii hranesti animalele', '2025-08-10 11:07:00', 1),
(413, 1, 10, 'orcum am inca foarte multa treaba', '2025-08-10 11:07:11', 1),
(414, 1, 10, 'dar am facut extrem de mult in 3 saptamani', '2025-08-10 11:07:22', 1),
(415, 1, 10, 'o sa fac in loc de alea 5 sloturi de jos o sa fac 10 care necesita gold , si gold sa il poti cumpara cu bani', '2025-08-10 11:07:51', 1),
(416, 10, 1, 'da da', '2025-08-10 11:08:08', 1),
(417, 1, 10, 'daca stiai cum e big barn world iti placea jocul , dar daca nu stii cum e nu stii sa faci o comparatie', '2025-08-10 11:08:27', 1),
(418, 10, 1, 'da', '2025-08-10 11:08:42', 1),
(419, 10, 1, 'hai am pelcat spor', '2025-08-10 11:08:55', 1),
(420, 1, 10, 'joc cacatu ala de joc de 14 ani :)', '2025-08-10 11:09:03', 1),
(421, 1, 10, 'om mersi ca ai intrat', '2025-08-10 11:09:09', 1),
(422, 1, 10, 'ok merisi', '2025-08-10 11:09:20', 1),
(423, 1, 10, 'pe ctrl+F5 o sa vezi modificari , ca isi sterge broserul ceva chache pentru siteul in care esti cand dai', '2025-08-10 12:06:13', 1),
(424, 4, 1, 'eu tot', '2025-08-10 12:15:24', 1),
(425, 4, 1, '=)))))', '2025-08-10 12:15:25', 1),
(426, 1, 4, 'e bine', '2025-08-10 12:16:37', 1),
(427, 4, 1, 'trebuie sa mai faci plantele', '2025-08-10 12:17:18', 1),
(428, 1, 4, 'nu le fac azi poate', '2025-08-10 12:17:32', 1),
(429, 1, 4, 'ca ma nevoie sa fac treaba asta bine', '2025-08-10 12:17:40', 1),
(430, 1, 4, 'nu sa stau sa editez dupa', '2025-08-10 12:17:48', 1),
(431, 4, 1, 'aaa', '2025-08-10 12:18:25', 1),
(432, 1, 4, 'la tine nu vad sloturile deblocate', '2025-08-10 12:18:27', 1),
(433, 4, 1, 'eu ma joc cs 1.6', '2025-08-10 12:18:29', 1),
(434, 1, 4, 'ai toate deblocate?', '2025-08-10 12:18:34', 1),
(435, 4, 1, 'nu alea cu gold', '2025-08-10 12:18:42', 1),
(436, 1, 4, 'da inafara de alea da stiu', '2025-08-10 12:18:49', 1),
(437, 1, 4, ', inseamna ca trebuie sa rezolv si asata', '2025-08-10 12:19:00', 1),
(438, 1, 4, 'acum', '2025-08-10 12:19:02', 1),
(439, 1, 4, 'eu nu le vad la tine acum ci doar la nesu e ceva undeva gresit', '2025-08-10 12:19:29', 1),
(440, 4, 1, 'nais', '2025-08-10 12:20:03', 1),
(441, 1, 4, 'ai vazut pagina de loading?', '2025-08-10 12:20:15', 1),
(442, 1, 4, 'te poti deloga de sus in dreapta si logheazate iar daca nu ai vazut', '2025-08-10 12:20:43', 1),
(443, 1, 4, 'schimba niste sloturi sa vad daca mi le arata', '2025-08-10 12:22:49', 1),
(444, 1, 4, 'sa vad daca nu se vede doar sloturile deblocate sau si cele schimbate', '2025-08-10 12:23:06', 1),
(445, 1, 4, 'am rezolvat , si asat acuma se vede', '2025-08-10 12:39:25', 1),
(446, 1, 4, 'pregatesc in panoul de admin un panou care sa ma ajute sa implementez sistemul de plantare', '2025-08-10 14:54:45', 1),
(447, 1, 4, 'si dupa o sa fac si panoul de admin sa fie configurat doar daca esti admin dar momentan ma intereseaza ce e important', '2025-08-10 14:55:16', 1),
(448, 1, 4, 'daca vreusesc sa fac panoul sa pot adauga plante si animale prin niste setari de butoane si cu imagini si merge  apoi pot face si asta cum ii zice , barn', '2025-08-10 14:56:49', 1),
(449, 1, 4, 'sa mi le afiseze la recoltare si desingul la barn', '2025-08-10 14:57:04', 1),
(450, 1, 4, 'dar', '2025-08-10 14:57:11', 1),
(451, 4, 1, 'meama', '2025-08-10 16:31:49', 1),
(452, 1, 4, 'am treaba multa inca', '2025-08-10 16:32:09', 1),
(453, 4, 1, 'mi-a rupt capu sunetu', '2025-08-10 16:32:29', 1),
(454, 1, 4, 'cae sunet', '2025-08-10 16:36:14', 1),
(455, 1, 4, 'ca si ion o zis asa', '2025-08-10 16:36:20', 1),
(456, 1, 3, 'salut', '2025-08-11 16:47:55', 1),
(457, 3, 1, 'Salut', '2025-08-11 16:48:08', 1),
(458, 10, 1, 'da inca nu ai adaugat apa si hraa', '2025-08-16 15:41:40', 1),
(459, 10, 1, 'hrana', '2025-08-16 15:41:44', 1),
(460, 1, 10, 'nu', '2025-08-16 15:42:23', 1),
(461, 10, 1, 'a', '2025-08-16 15:42:32', 1),
(462, 10, 1, 'e bine incet le pui', '2025-08-16 15:42:41', 1),
(463, 1, 10, 'dah', '2025-08-16 15:42:50', 1),
(464, 1, 10, 'ce spui arata bine?', '2025-08-16 15:42:58', 1),
(465, 10, 1, 'da', '2025-08-16 15:43:04', 1),
(466, 1, 10, 'sarumana', '2025-08-16 15:43:09', 1),
(467, 10, 1, 'ai pus si meniu de setari e bine', '2025-08-16 15:43:44', 1),
(468, 1, 10, 'da si fac si meniul de admin ala care adaug plantele , si o sa fac meniu de setari pentru jucatori separat', '2025-08-16 15:44:49', 0),
(469, 4, 1, 'revin', '2025-08-27 13:34:54', 1),
(470, 4, 1, 'merg sa mut dulapi', '2025-08-27 13:34:59', 1),
(471, 4, 1, '=))', '2025-08-27 13:35:01', 1),
(472, 1, 4, 'ok , eu il tin deschis', '2025-08-27 13:35:46', 1),
(473, 4, 1, 'am revenit', '2025-08-27 13:42:03', 1),
(474, 1, 4, 'trebuie sa iti stergi chacheul sa mearga', '2025-08-27 13:43:21', 1),
(475, 1, 4, 'uitate in barn', '2025-08-27 13:44:11', 1),
(476, 1, 4, 'poti vinde ce ai cules', '2025-08-27 13:44:23', 1),
(477, 1, 4, 'si poti sa maresti barnul', '2025-08-27 13:44:32', 1),
(478, 1, 4, 'ai vazut sau nu?', '2025-08-27 13:59:25', 1),
(479, 4, 1, 'acuma am vazut', '2025-08-28 20:18:50', 1),
(480, 4, 1, 'e smecher rau', '2025-08-28 20:18:52', 1),
(481, 1, 4, 'sarumana', '2025-08-28 20:23:55', 1),
(482, 4, 1, 'ci faci', '2025-08-28 20:24:06', 1),
(483, 1, 4, 'eram la baie cand am vazut ca ai intrat dami cerere si pe', '2025-08-28 20:24:10', 1),
(484, 1, 4, 'contul cu catalina', '2025-08-28 20:24:15', 1),
(485, 4, 1, 'aaa', '2025-08-28 20:24:20', 1),
(486, 4, 1, 'stai', '2025-08-28 20:24:21', 1),
(487, 1, 4, 'eram la baie pana mea , ca fac diareee de la antibiotice , dar le iau asa', '2025-08-28 20:25:04', 1),
(488, 4, 1, 'aaa', '2025-08-28 20:25:13', 1),
(489, 1, 4, 'am facut o gramada de treaba la joc , dar mai am multe idee', '2025-08-28 20:25:37', 1),
(490, 1, 4, 'acum vreau sa fac un pic sistemul de vip dar numai pentru ca sa pot planta pe toate sloturile care sunt pentru acea planta si disponibile de plantat', '2025-08-28 20:26:09', 1),
(491, 1, 4, 'doar pt vip', '2025-08-28 20:26:13', 1),
(492, 4, 1, 'am vazut', '2025-08-28 20:26:41', 1),
(493, 4, 1, 'ca nu pot cumpara mai mult de 1 :)))', '2025-08-28 20:26:47', 1),
(494, 4, 1, 'pe slot', '2025-08-28 20:26:48', 1),
(495, 1, 4, 'eu am vip dar acuma la vip nu poate cumpara nici pe 1 macar', '2025-08-28 20:27:05', 1),
(496, 4, 1, 'aa', '2025-08-28 20:27:14', 1),
(497, 1, 4, 'ca acum ce m-am apucat de el sa il fac nu de mult', '2025-08-28 20:27:16', 1),
(498, 1, 4, 'is curios daca se poate face sa plantez asa cum vreau eu cu vip', '2025-08-28 20:27:42', 1),
(499, 4, 1, 'gen cumperi pe un slot toate 27?', '2025-08-28 20:27:59', 1),
(500, 4, 1, 'sa le planteze automan?', '2025-08-28 20:28:03', 1),
(501, 1, 4, 'da', '2025-08-28 20:28:06', 1),
(502, 4, 1, 'automat*', '2025-08-28 20:28:10', 1),
(503, 1, 4, 'daca nu ai plantate acele sloturi ti le citeste automat cate ai goale', '2025-08-28 20:28:23', 1),
(504, 1, 4, 'dacaa vrei testeaza si tu sterge o plantatie', '2025-08-28 20:28:34', 1),
(505, 1, 4, 'si vezi ca apare un  numar in plus', '2025-08-28 20:28:43', 1),
(506, 1, 4, 'si daca il selectezi sa poata sa planteze pe acele locuri libere', '2025-08-28 20:28:58', 1),
(507, 4, 1, 'mai am olc si le iau pe toate', '2025-08-28 20:29:02', 1),
(508, 1, 4, 'am facut gen pentru planta si pentru acel tip de slot , sa calculeze daca sunt libere si cate sunt libere', '2025-08-28 20:29:55', 1),
(509, 4, 1, '100 pentru sirena', '2025-08-28 20:29:56', 1),
(510, 1, 4, 'am facut siere am plin :)))', '2025-08-28 20:30:07', 1),
(511, 4, 1, ':))', '2025-08-28 20:30:07', 1),
(512, 1, 4, 'in barn', '2025-08-28 20:30:10', 1),
(513, 4, 1, 'meama', '2025-08-28 20:30:14', 1),
(514, 1, 4, 'ca eu cand testez aici stau si ma si joc', '2025-08-28 20:30:21', 1),
(515, 4, 1, 'aa', '2025-08-28 20:30:39', 1),
(516, 4, 1, 'cand mi-ai dat mesaj am crezut ca am primit notificare de la facebook', '2025-08-28 20:30:53', 1),
(517, 1, 4, 'am 15 king trident si 9 sirene', '2025-08-28 20:31:00', 1),
(518, 1, 4, ':)))', '2025-08-28 20:31:09', 1),
(519, 4, 1, '1500 deci', '2025-08-28 20:31:19', 1),
(520, 4, 1, 'si 900', '2025-08-28 20:31:22', 1),
(521, 4, 1, ':)))', '2025-08-28 20:31:29', 1),
(522, 1, 4, 'ti las ca ma mai duc sa fac acolo pt vip poate reusesc', '2025-08-28 20:31:49', 1),
(523, 1, 4, 'imi poti uda si la mine si la orce prieten', '2025-08-28 20:32:00', 1),
(524, 4, 1, 'am udat', '2025-08-28 20:32:06', 1),
(525, 1, 4, 'si iti arata statistica acolo sus', '2025-08-28 20:32:08', 1),
(526, 1, 4, 'e bine', '2025-08-28 20:32:14', 1),
(527, 4, 1, 'ia ti', '2025-08-28 20:32:23', 1),
(528, 4, 1, 'harbuzu', '2025-08-28 20:32:24', 1),
(529, 4, 1, 'ca e gata', '2025-08-28 20:32:26', 1),
(530, 4, 1, ':))', '2025-08-28 20:32:30', 1),
(531, 1, 4, 'stai ca eu vip nu pot planta deloc acuma', '2025-08-28 20:33:14', 1),
(532, 1, 4, 'nici normal , brb ma duc sa rezolv', '2025-08-28 20:33:24', 1),
(533, 4, 1, 'bini', '2025-08-28 20:33:30', 1),
(534, 1, 4, 'merge sa plantez pe toate care le selectezi:))', '2025-08-28 21:09:18', 1),
(535, 1, 4, 'iti dau vi sa incerci', '2025-08-28 21:09:24', 1),
(536, 4, 1, 'bine', '2025-08-28 21:09:31', 1),
(537, 1, 4, 'gata', '2025-08-28 21:10:04', 1),
(538, 1, 4, 'ai nevoie de mai multe sloturi goale de acelas tip sa mearga', '2025-08-28 21:10:17', 1),
(539, 4, 1, 'daa', '2025-08-28 21:10:25', 1),
(540, 4, 1, 'is plantate toate', '2025-08-28 21:10:33', 1),
(541, 4, 1, 'smecher', '2025-08-28 21:10:38', 1),
(542, 4, 1, 'eu ma culc', '2025-08-28 21:10:40', 1),
(543, 4, 1, 'spor la coding', '2025-08-28 21:10:43', 1),
(544, 4, 1, ':))', '2025-08-28 21:10:46', 1),
(545, 1, 4, 'dai pe ele cat arata timpul si le stergi', '2025-08-28 21:10:49', 1),
(546, 1, 4, 'si plantezi cand ai 2 sau 3 goale sa vezi si tu', '2025-08-28 21:11:03', 1),
(547, 1, 4, 'ok nb , mersi', '2025-08-28 21:11:17', 1),
(548, 4, 1, 'pup', '2025-08-28 21:11:24', 1),
(549, 1, 4, 'pup', '2025-08-28 21:11:50', 1),
(550, 4, 1, 'ooo', '2025-08-30 13:43:16', 1),
(551, 4, 1, 'da cum iti arata quatro', '2025-08-30 13:43:21', 1),
(552, 4, 1, 'ai rainbow numele', '2025-08-30 13:43:27', 1),
(553, 4, 1, ':))', '2025-08-30 13:43:28', 1),
(554, 1, 4, 'si tu', '2025-08-30 13:43:37', 1),
(555, 1, 4, 'dute pe ferma ta sa ud sa vezi ceva cand ud eu la tine', '2025-08-30 13:44:03', 1),
(556, 4, 1, 'daa', '2025-08-30 13:44:31', 1),
(557, 4, 1, ':)P)', '2025-08-30 13:44:32', 1),
(558, 1, 4, 'recolteaza si planteaza si lasama sa ud si uitate cand ud eu', '2025-08-30 13:45:09', 1),
(559, 4, 1, 'daa', '2025-08-30 13:46:26', 1),
(560, 1, 4, ':)', '2025-08-30 13:46:36', 1),
(561, 4, 1, 'cf', '2025-08-30 13:47:28', 1),
(562, 1, 4, 'stau', '2025-08-30 13:47:49', 1),
(563, 1, 4, 'tu?', '2025-08-30 13:47:56', 1),
(564, 1, 4, 'ce asa greu ai intrat?', '2025-08-30 13:48:02', 1),
(565, 4, 1, 'am taiat lemne', '2025-08-30 13:50:34', 1),
(566, 4, 1, 'am pana ma insor de crapat lemne', '2025-08-30 13:50:41', 1),
(567, 4, 1, 'vezi ca nu se face stack de watermelon', '2025-08-30 13:51:24', 1),
(568, 1, 4, 'asa l-am setat eu', '2025-08-30 13:57:52', 1),
(569, 4, 1, 'aaaaa', '2025-08-30 13:58:04', 1),
(570, 1, 4, 'ca harbuzu normal', '2025-08-30 13:58:06', 1),
(571, 4, 1, ';)))', '2025-08-30 13:58:11', 1),
(572, 1, 4, 'cand creste nu produce mai multe ca nu are cum e unl singur care creste :))', '2025-08-30 13:58:28', 1),
(573, 1, 4, 'cred ca are pret mare la vanzare', '2025-08-30 13:58:39', 1),
(574, 4, 1, 'nuj cat are', '2025-08-30 13:58:50', 1),
(575, 1, 4, 'cand incerci sa il vinzi iti spune', '2025-08-30 13:59:08', 1),
(576, 4, 1, 'stiu numa ca iti ud barabulele cu macro :))))))))))', '2025-08-30 13:59:14', 1),
(577, 4, 1, '5k 1', '2025-08-30 13:59:27', 1),
(578, 1, 4, 'e bun atunci', '2025-08-30 14:00:33', 1),
(579, 4, 1, 'yep', '2025-08-30 14:00:52', 1),
(580, 4, 1, 'cf', '2025-09-02 14:50:32', 1),
(581, 1, 4, 'bn', '2025-09-02 14:51:32', 1),
(582, 1, 4, 'pe aici', '2025-09-02 14:51:36', 1),
(583, 4, 1, 'eu bag picioru', '2025-09-02 14:51:47', 1),
(584, 4, 1, 'astept fan curier de azi dimineata si nu vine', '2025-09-02 14:51:59', 1),
(585, 1, 4, 'cu ce', '2025-09-02 14:52:25', 1),
(586, 4, 1, 'cu niste papuci', '2025-09-02 14:52:55', 1),
(587, 4, 1, 'am lag', '2025-09-02 14:53:01', 1),
(588, 4, 1, 'as iesi in seara asta cu gabi la o shaorma da daca vin astia', '2025-09-02 14:53:26', 1),
(589, 4, 1, 'cf', '2025-09-08 14:15:33', 1),
(590, 4, 1, 'bagi puta cu anti cheat u lor', '2025-09-08 14:15:47', 1),
(591, 1, 4, 'peaici', '2025-09-08 14:15:54', 1),
(592, 4, 1, 'direct ban pe HWID', '2025-09-08 14:15:55', 1),
(593, 1, 4, ':)))', '2025-09-08 14:15:59', 1),
(594, 1, 4, 'ai datourat', '2025-09-08 14:16:05', 1),
(595, 4, 1, 'da', '2025-09-08 14:16:14', 1),
(596, 4, 1, 'acuma mi-am schimbat HWID cu spoofer', '2025-09-08 14:16:22', 1),
(597, 4, 1, '=)))', '2025-09-08 14:16:23', 1),
(598, 4, 1, 'ce roman scrii? :)))', '2025-09-08 14:17:39', 1),
(599, 1, 4, 'trebuie sa joci mai ascuns toate informatiilecare le ai de la cod , trebuie sa iti ai in minte sa tii cont ca normalnu le stii , si sa faci situatii care creazaconfirmare de informatii , adica? cand vezi jucatorii de departe nu te du cu tinta pe ie dar tine cont saiti creezi situatii in care ai putea sa ii vezi sau auzi tragand apoi actionezi , codultrebuie sa te ajute doar pe bataia deaproapecand auzi ca vine si tu stii cand sadai prefire atat', '2025-09-08 14:18:43', 1),
(600, 1, 4, 'ca daca te folosesti altfel de el ei ban rapid', '2025-09-08 14:18:58', 1),
(601, 1, 4, 'trebuie sa inveti sa joci cu cod', '2025-09-08 14:19:20', 1),
(602, 4, 1, 'il vrei', '2025-09-08 14:19:34', 1),
(603, 4, 1, '?', '2025-09-08 14:19:35', 1),
(604, 1, 4, 'nu cred', '2025-09-08 14:19:42', 1),
(605, 4, 1, 'am 1 luna', '2025-09-08 14:19:48', 1),
(606, 4, 1, 'iti dau loaderu si cheia', '2025-09-08 14:19:56', 1),
(607, 1, 4, 's-ar putea sa iau si eu ban si nu are rost sa il joc', '2025-09-08 14:20:19', 1),
(608, 1, 4, 'degeaba ca nu milplace deloc', '2025-09-08 14:20:25', 1),
(609, 1, 4, 'eu joccajucati voi', '2025-09-08 14:20:31', 1),
(610, 4, 1, 'nice', '2025-09-08 14:20:39', 1),
(611, 4, 1, 'oare merge spoofer?', '2025-09-08 14:20:43', 1),
(612, 1, 4, 'normal ar trebuii sa mearga', '2025-09-08 14:20:53', 1),
(613, 4, 1, 'mi-a schimbad uid', '2025-09-08 14:20:58', 1),
(614, 4, 1, 'uite pe whats app', '2025-09-08 14:21:08', 1),
(615, 1, 4, 'eu am facut sistemde banca aici dar nu e gata', '2025-09-08 14:21:15', 1),
(616, 1, 4, 'de la setari si bank il gasesti', '2025-09-08 14:21:29', 1),
(617, 1, 4, 'la vip acum poti selecta sa vezi conbinatii de rame si carduri inainte sa le pui', '2025-09-08 14:22:19', 1),
(618, 1, 4, ', la banca sa depui bani si iei dobanda', '2025-09-08 14:22:38', 1),
(619, 4, 1, 'smecher', '2025-09-08 14:22:48', 1),
(620, 1, 4, 'dar sa nu pui pe o ora ca re eroare', '2025-09-08 14:22:49', 1),
(621, 1, 4, 'pe minim 2 ore', '2025-09-08 14:23:01', 1),
(622, 4, 1, 'aa', '2025-09-08 14:23:05', 1),
(623, 1, 4, 'sau mai mult', '2025-09-08 14:23:06', 1),
(624, 4, 1, 'ce rapid iti iei ban pe pubg', '2025-09-08 14:23:13', 1),
(625, 4, 1, 'au aticheat bun rau atunci', '2025-09-08 14:23:18', 1),
(626, 1, 4, 'normalnu iei rapid ban dar ai luat multe reporturi', '2025-09-08 14:23:37', 1),
(627, 1, 4, 'si au vazut pe demo cum jucai', '2025-09-08 14:23:47', 1),
(628, 4, 1, 'PUBG employs multiple anti-cheat methods including its proprietary Zakynthos system, the third-party BattleEye solution, and advanced behavioral analysis using machine learning and an AI-powered system. A new kernel-based anti-cheat was introduced in August 2025 that operates at the deepest level of the operating system to detect advanced cheats. These systems work to identify and block illegal software through real-time scanning and analysis, kicking and banning players caught using cheats.', '2025-09-08 14:23:48', 1),
(629, 4, 1, 'numa cu ai fac astia', '2025-09-08 14:23:57', 1),
(630, 1, 4, 'poate', '2025-09-08 14:24:05', 1),
(631, 4, 1, 'vreau sa vad daca imi mai iau ban', '2025-09-08 14:24:24', 1),
(632, 4, 1, 'pe contu mare', '2025-09-08 14:24:27', 1),
(633, 4, 1, 'ca mi-am schimbat uid', '2025-09-08 14:24:31', 1),
(634, 4, 1, 'aveam 24h', '2025-09-08 14:24:40', 1),
(635, 4, 1, 'am scos secure boot', '2025-09-08 14:27:55', 1),
(636, 4, 1, 'ce i ala?', '2025-09-08 14:27:57', 1),
(637, 1, 4, 'nu stiu', '2025-09-08 14:28:22', 1),
(638, 4, 1, 'ceva de malware', '2025-09-08 14:28:44', 1),
(639, 1, 4, 'nu siut', '2025-09-08 14:29:54', 1);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `profile_comments`
--

CREATE TABLE `profile_comments` (
  `id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `profile_comments`
--

INSERT INTO `profile_comments` (`id`, `target_id`, `author_id`, `comment`, `created_at`) VALUES
(1, 1, 1, 'aaaa', '2025-09-15 20:04:57'),
(2, 1, 1, 'aaaaa', '2025-09-17 22:01:02');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `slot_helpers`
--

CREATE TABLE `slot_helpers` (
  `owner_id` int(11) NOT NULL,
  `slot_number` int(11) NOT NULL,
  `helper_id` int(11) NOT NULL,
  `water_clicks` int(11) NOT NULL DEFAULT 0,
  `feed_clicks` int(11) NOT NULL DEFAULT 0,
  `last_action_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `slot_helpers`
--

INSERT INTO `slot_helpers` (`owner_id`, `slot_number`, `helper_id`, `water_clicks`, `feed_clicks`, `last_action_at`) VALUES
(1, 1, 1, 3, 0, '2025-09-07 20:28:43'),
(1, 1, 2, 16, 0, '2025-09-15 16:28:05'),
(1, 1, 3, 72, 0, '2025-09-15 13:24:44'),
(1, 1, 4, 7, 0, '2025-09-02 17:50:17'),
(1, 1, 11, 10, 0, '2025-09-08 20:41:26'),
(1, 2, 1, 4, 0, '2025-09-07 20:28:44'),
(1, 2, 2, 17, 0, '2025-09-15 16:28:05'),
(1, 2, 3, 86, 0, '2025-09-15 13:24:44'),
(1, 2, 4, 7, 0, '2025-09-02 17:50:17'),
(1, 2, 7, 24, 0, '2025-08-28 19:03:02'),
(1, 2, 11, 10, 0, '2025-09-08 20:41:26'),
(1, 3, 1, 4, 0, '2025-09-07 20:28:45'),
(1, 3, 2, 17, 0, '2025-09-15 16:28:07'),
(1, 3, 3, 88, 0, '2025-09-15 13:24:45'),
(1, 3, 4, 14, 0, '2025-09-02 17:50:17'),
(1, 3, 7, 22, 0, '2025-08-28 19:03:03'),
(1, 3, 11, 10, 0, '2025-09-08 20:41:29'),
(1, 4, 1, 3, 0, '2025-09-07 20:28:46'),
(1, 4, 2, 17, 0, '2025-09-15 16:28:07'),
(1, 4, 3, 86, 0, '2025-09-15 13:24:46'),
(1, 4, 4, 14, 0, '2025-09-02 17:50:18'),
(1, 4, 7, 35, 0, '2025-08-28 19:03:09'),
(1, 4, 11, 10, 0, '2025-09-08 20:41:29'),
(1, 5, 1, 2, 0, '2025-09-07 20:28:47'),
(1, 5, 2, 17, 0, '2025-09-15 16:28:08'),
(1, 5, 3, 85, 0, '2025-09-15 13:24:46'),
(1, 5, 4, 16, 0, '2025-09-02 17:50:19'),
(1, 5, 11, 10, 0, '2025-09-08 20:41:32'),
(1, 6, 1, 3, 0, '2025-09-07 20:28:44'),
(1, 6, 2, 17, 0, '2025-09-15 16:28:05'),
(1, 6, 3, 127, 0, '2025-09-15 13:24:47'),
(1, 6, 4, 15, 0, '2025-09-02 17:50:23'),
(1, 6, 11, 10, 0, '2025-09-08 20:41:25'),
(1, 7, 1, 2, 0, '2025-09-07 20:28:24'),
(1, 7, 2, 15, 4, '2025-09-15 16:28:06'),
(1, 7, 3, 102, 13, '2025-09-15 13:24:45'),
(1, 7, 4, 8, 6, '2025-09-02 17:50:22'),
(1, 7, 7, 0, 12, '2025-08-28 19:04:53'),
(1, 7, 9, 0, 9, '2025-08-28 19:07:11'),
(1, 7, 11, 10, 0, '2025-09-08 20:41:27'),
(1, 8, 1, 3, 0, '2025-09-07 20:28:46'),
(1, 8, 2, 16, 5, '2025-09-15 16:28:06'),
(1, 8, 3, 103, 13, '2025-09-15 13:24:45'),
(1, 8, 4, 8, 6, '2025-09-02 17:50:21'),
(1, 8, 7, 0, 13, '2025-08-28 19:04:54'),
(1, 8, 9, 0, 9, '2025-08-28 19:07:11'),
(1, 8, 11, 12, 0, '2025-09-08 20:41:29'),
(1, 9, 1, 3, 0, '2025-09-07 20:28:46'),
(1, 9, 2, 17, 4, '2025-09-15 16:28:07'),
(1, 9, 3, 102, 13, '2025-09-15 13:24:46'),
(1, 9, 4, 8, 6, '2025-09-02 17:50:20'),
(1, 9, 7, 0, 12, '2025-08-28 19:04:54'),
(1, 9, 9, 0, 10, '2025-08-28 19:07:12'),
(1, 9, 11, 10, 0, '2025-09-08 20:41:30'),
(1, 10, 1, 3, 0, '2025-09-07 20:28:47'),
(1, 10, 2, 17, 0, '2025-09-15 16:28:08'),
(1, 10, 3, 111, 0, '2025-09-15 13:24:46'),
(1, 10, 4, 15, 0, '2025-09-02 17:50:19'),
(1, 10, 11, 10, 0, '2025-09-08 20:41:32'),
(1, 11, 1, 3, 0, '2025-09-07 20:28:39'),
(1, 11, 2, 18, 0, '2025-09-15 16:28:09'),
(1, 11, 3, 86, 40, '2025-09-15 13:24:47'),
(1, 11, 4, 17, 0, '2025-09-02 17:50:23'),
(1, 11, 11, 10, 0, '2025-09-08 20:41:25'),
(1, 12, 1, 2, 0, '2025-09-07 20:28:39'),
(1, 12, 2, 18, 0, '2025-09-15 16:28:09'),
(1, 12, 3, 89, 42, '2025-09-15 13:24:47'),
(1, 12, 4, 16, 0, '2025-09-02 17:50:24'),
(1, 12, 7, 14, 0, '2025-08-28 19:03:01'),
(1, 12, 11, 10, 0, '2025-09-08 20:41:27'),
(1, 13, 1, 2, 0, '2025-09-07 20:28:40'),
(1, 13, 2, 19, 0, '2025-09-15 16:28:10'),
(1, 13, 3, 80, 46, '2025-09-15 13:24:47'),
(1, 13, 4, 16, 0, '2025-09-02 17:50:24'),
(1, 13, 7, 13, 0, '2025-08-28 19:03:01'),
(1, 13, 11, 9, 0, '2025-09-08 20:41:28'),
(1, 14, 1, 2, 0, '2025-09-07 20:28:41'),
(1, 14, 2, 19, 0, '2025-09-15 16:28:10'),
(1, 14, 3, 77, 37, '2025-09-15 13:24:48'),
(1, 14, 4, 15, 0, '2025-09-02 17:50:25'),
(1, 14, 7, 14, 0, '2025-08-28 19:03:14'),
(1, 14, 11, 10, 0, '2025-09-08 20:41:31'),
(1, 15, 1, 2, 0, '2025-09-07 20:28:41'),
(1, 15, 2, 19, 0, '2025-09-15 16:28:10'),
(1, 15, 3, 68, 41, '2025-09-15 13:24:48'),
(1, 15, 4, 15, 0, '2025-09-02 17:50:25'),
(1, 15, 11, 10, 0, '2025-09-08 20:41:31'),
(1, 16, 1, 3, 0, '2025-09-07 20:28:38'),
(1, 16, 2, 16, 2, '2025-09-15 16:28:11'),
(1, 16, 3, 99, 70, '2025-09-15 13:24:49'),
(1, 16, 4, 13, 0, '2025-08-30 21:30:04'),
(1, 16, 11, 10, 0, '2025-09-08 20:41:24'),
(1, 17, 1, 3, 0, '2025-09-07 20:28:40'),
(1, 17, 2, 16, 2, '2025-09-15 16:28:11'),
(1, 17, 3, 97, 31, '2025-09-15 13:24:49'),
(1, 17, 4, 14, 0, '2025-08-30 21:30:04'),
(1, 17, 11, 10, 0, '2025-09-08 20:41:28'),
(1, 18, 1, 3, 0, '2025-09-07 20:28:40'),
(1, 18, 2, 15, 3, '2025-09-15 16:28:11'),
(1, 18, 3, 92, 41, '2025-09-15 13:24:49'),
(1, 18, 4, 14, 0, '2025-08-30 21:30:05'),
(1, 18, 11, 10, 0, '2025-09-08 20:41:28'),
(1, 19, 1, 4, 0, '2025-09-07 20:28:41'),
(1, 19, 2, 15, 2, '2025-09-15 16:28:12'),
(1, 19, 3, 75, 33, '2025-09-15 13:24:50'),
(1, 19, 4, 16, 0, '2025-09-02 17:50:26'),
(1, 19, 11, 10, 0, '2025-09-08 20:41:31'),
(1, 20, 1, 2, 0, '2025-09-07 20:28:20'),
(1, 20, 2, 16, 2, '2025-09-15 16:28:12'),
(1, 20, 3, 74, 31, '2025-09-15 13:24:50'),
(1, 20, 4, 15, 0, '2025-09-02 17:50:25'),
(1, 20, 11, 10, 0, '2025-09-08 20:41:31'),
(1, 21, 1, 2, 0, '2025-09-07 20:28:13'),
(1, 21, 2, 17, 0, '2025-09-15 16:28:13'),
(1, 21, 3, 95, 39, '2025-09-15 13:24:51'),
(1, 21, 4, 13, 0, '2025-08-30 21:30:06'),
(1, 21, 11, 10, 0, '2025-09-08 20:41:24'),
(1, 22, 1, 2, 0, '2025-09-07 20:28:12'),
(1, 22, 2, 18, 0, '2025-09-15 16:28:13'),
(1, 22, 3, 96, 40, '2025-09-15 13:24:51'),
(1, 22, 4, 14, 0, '2025-08-30 21:30:07'),
(1, 22, 11, 10, 0, '2025-09-08 20:41:24'),
(1, 23, 1, 2, 0, '2025-09-07 20:28:16'),
(1, 23, 2, 18, 0, '2025-09-15 16:28:14'),
(1, 23, 3, 97, 38, '2025-09-15 13:24:51'),
(1, 23, 4, 14, 0, '2025-08-30 21:30:07'),
(1, 23, 11, 10, 0, '2025-09-08 20:41:23'),
(1, 24, 1, 3, 0, '2025-09-07 20:28:17'),
(1, 24, 2, 17, 0, '2025-09-15 16:28:14'),
(1, 24, 3, 86, 39, '2025-09-15 13:24:51'),
(1, 24, 4, 13, 0, '2025-08-30 21:30:07'),
(1, 24, 11, 10, 0, '2025-09-08 20:41:23'),
(1, 25, 1, 3, 0, '2025-09-07 20:28:42'),
(1, 25, 2, 18, 0, '2025-09-15 16:28:14'),
(1, 25, 3, 79, 39, '2025-09-15 13:24:52'),
(1, 25, 4, 13, 0, '2025-08-30 21:30:05'),
(1, 25, 11, 10, 0, '2025-09-08 20:41:22'),
(1, 26, 2, 17, 0, '2025-09-15 16:28:15'),
(1, 26, 3, 91, 0, '2025-09-15 13:24:52'),
(1, 26, 4, 8, 0, '2025-08-30 21:30:06'),
(1, 26, 11, 10, 0, '2025-09-08 20:41:20'),
(1, 27, 2, 17, 0, '2025-09-15 16:28:15'),
(1, 27, 3, 91, 0, '2025-09-15 13:24:52'),
(1, 27, 4, 8, 0, '2025-08-30 21:30:06'),
(1, 27, 11, 10, 0, '2025-09-08 20:41:20'),
(1, 28, 1, 2, 0, '2025-09-07 20:28:16'),
(1, 28, 2, 17, 0, '2025-09-15 16:28:16'),
(1, 28, 3, 94, 0, '2025-09-15 13:24:53'),
(1, 28, 4, 8, 0, '2025-08-30 21:30:06'),
(1, 28, 11, 10, 0, '2025-09-08 20:41:21'),
(1, 29, 1, 2, 0, '2025-09-07 20:28:17'),
(1, 29, 2, 17, 0, '2025-09-15 16:28:17'),
(1, 29, 3, 85, 0, '2025-09-15 13:24:54'),
(1, 29, 4, 8, 0, '2025-08-30 21:30:06'),
(1, 29, 11, 10, 0, '2025-09-08 20:41:21'),
(1, 30, 1, 2, 0, '2025-09-07 20:28:19'),
(1, 30, 2, 17, 0, '2025-09-15 16:28:18'),
(1, 30, 3, 84, 0, '2025-09-15 13:24:54'),
(1, 30, 4, 8, 0, '2025-08-30 21:30:06'),
(1, 30, 11, 10, 0, '2025-09-08 20:41:22'),
(1, 31, 2, 14, 0, '2025-09-15 16:28:15'),
(1, 31, 3, 39, 0, '2025-09-15 13:24:55'),
(1, 31, 4, 8, 0, '2025-08-30 21:30:07'),
(1, 31, 11, 10, 0, '2025-09-08 20:41:19'),
(1, 32, 2, 15, 0, '2025-09-15 16:28:16'),
(1, 32, 3, 53, 0, '2025-09-15 13:24:53'),
(1, 32, 4, 9, 0, '2025-08-30 21:30:06'),
(1, 32, 11, 10, 0, '2025-09-08 20:41:20'),
(1, 33, 1, 2, 0, '2025-09-07 20:28:16'),
(1, 33, 2, 15, 0, '2025-09-15 16:28:16'),
(1, 33, 3, 41, 0, '2025-09-15 13:24:53'),
(1, 33, 4, 9, 0, '2025-08-30 21:30:07'),
(1, 33, 11, 10, 0, '2025-09-08 20:41:21'),
(1, 34, 1, 4, 0, '2025-09-07 20:28:18'),
(1, 34, 2, 15, 0, '2025-09-15 16:28:17'),
(1, 34, 3, 40, 0, '2025-09-15 13:24:54'),
(1, 34, 4, 8, 0, '2025-08-30 21:30:07'),
(1, 34, 11, 10, 0, '2025-09-08 20:41:22'),
(1, 35, 1, 5, 0, '2025-09-07 20:28:43'),
(1, 35, 2, 15, 0, '2025-09-15 16:28:17'),
(1, 35, 3, 40, 0, '2025-09-15 13:24:54'),
(1, 35, 4, 8, 0, '2025-08-30 21:30:07'),
(1, 35, 11, 10, 0, '2025-09-08 20:41:42'),
(2, 1, 1, 5, 0, '2025-09-07 17:49:09'),
(2, 1, 3, 9, 0, '2025-09-09 16:47:33'),
(2, 1, 4, 6, 0, '2025-08-30 16:56:14'),
(2, 2, 1, 5, 0, '2025-09-07 17:49:10'),
(2, 2, 3, 9, 0, '2025-09-09 16:47:33'),
(2, 2, 4, 6, 0, '2025-08-30 16:56:14'),
(2, 3, 1, 5, 0, '2025-09-07 17:49:11'),
(2, 3, 3, 9, 0, '2025-09-09 16:47:34'),
(2, 3, 4, 6, 0, '2025-08-30 16:56:15'),
(2, 4, 1, 5, 0, '2025-09-07 17:49:11'),
(2, 4, 3, 9, 0, '2025-09-09 16:47:35'),
(2, 4, 4, 6, 0, '2025-08-30 16:56:14'),
(2, 5, 1, 5, 0, '2025-09-07 17:49:12'),
(2, 5, 3, 9, 0, '2025-09-09 16:47:36'),
(2, 5, 4, 6, 0, '2025-08-30 16:56:15'),
(2, 6, 1, 5, 0, '2025-09-07 17:49:09'),
(2, 6, 3, 8, 0, '2025-09-09 16:47:33'),
(2, 6, 4, 5, 0, '2025-08-30 16:56:16'),
(2, 7, 1, 5, 0, '2025-09-07 17:49:10'),
(2, 7, 3, 8, 0, '2025-09-09 16:47:34'),
(2, 7, 4, 5, 0, '2025-08-30 16:56:16'),
(2, 8, 1, 5, 0, '2025-09-07 17:49:10'),
(2, 8, 3, 8, 0, '2025-09-09 16:47:34'),
(2, 8, 4, 5, 0, '2025-08-30 16:56:15'),
(2, 9, 1, 5, 0, '2025-09-07 17:49:11'),
(2, 9, 3, 8, 0, '2025-09-09 16:47:35'),
(2, 9, 4, 5, 0, '2025-08-30 16:56:15'),
(2, 10, 1, 5, 0, '2025-09-07 17:49:11'),
(2, 10, 3, 8, 0, '2025-09-09 16:47:36'),
(2, 10, 4, 5, 0, '2025-08-30 16:56:15'),
(2, 11, 1, 1, 0, '2025-09-07 17:49:09'),
(2, 11, 3, 5, 0, '2025-09-09 16:47:32'),
(2, 12, 1, 1, 0, '2025-09-07 17:49:08'),
(2, 12, 3, 5, 0, '2025-09-09 16:47:32'),
(3, 1, 1, 65, 32, '2025-09-17 21:59:54'),
(3, 1, 2, 36, 9, '2025-09-07 20:13:12'),
(3, 1, 4, 5, 0, '2025-08-30 16:59:39'),
(3, 1, 9, 5, 0, '2025-09-15 19:47:19'),
(3, 1, 11, 12, 0, '2025-09-08 20:41:03'),
(3, 2, 1, 81, 0, '2025-09-17 21:59:54'),
(3, 2, 2, 40, 0, '2025-09-07 20:13:13'),
(3, 2, 4, 6, 0, '2025-08-30 16:59:38'),
(3, 2, 9, 5, 0, '2025-09-15 19:47:19'),
(3, 2, 11, 10, 0, '2025-09-08 20:41:04'),
(3, 3, 1, 71, 15, '2025-09-17 21:59:54'),
(3, 3, 2, 39, 6, '2025-09-07 20:13:15'),
(3, 3, 4, 6, 0, '2025-08-30 16:59:38'),
(3, 3, 9, 5, 0, '2025-09-15 19:47:20'),
(3, 3, 11, 11, 0, '2025-09-08 20:41:09'),
(3, 4, 1, 73, 0, '2025-09-17 21:59:56'),
(3, 4, 2, 39, 0, '2025-09-07 20:13:15'),
(3, 4, 4, 6, 0, '2025-08-30 16:59:38'),
(3, 4, 9, 4, 0, '2025-09-15 19:47:20'),
(3, 4, 11, 11, 0, '2025-09-08 20:41:09'),
(3, 5, 1, 70, 0, '2025-09-17 21:59:56'),
(3, 5, 2, 40, 0, '2025-09-07 20:13:17'),
(3, 5, 4, 6, 0, '2025-08-30 16:59:38'),
(3, 5, 9, 4, 0, '2025-09-15 19:47:21'),
(3, 5, 11, 11, 0, '2025-09-08 20:41:10'),
(3, 6, 1, 77, 0, '2025-09-17 21:59:52'),
(3, 6, 2, 40, 0, '2025-09-07 20:13:14'),
(3, 6, 4, 3, 0, '2025-08-30 16:59:39'),
(3, 6, 9, 5, 0, '2025-09-15 19:47:19'),
(3, 6, 11, 13, 0, '2025-09-08 20:41:03'),
(3, 7, 1, 77, 0, '2025-09-17 21:59:52'),
(3, 7, 2, 40, 0, '2025-09-07 20:13:14'),
(3, 7, 4, 3, 0, '2025-08-30 16:59:39'),
(3, 7, 9, 5, 0, '2025-09-15 19:47:19'),
(3, 7, 11, 12, 0, '2025-09-08 20:41:04'),
(3, 8, 1, 79, 0, '2025-09-17 21:59:54'),
(3, 8, 2, 39, 0, '2025-09-07 20:13:15'),
(3, 8, 4, 3, 0, '2025-08-30 16:59:39'),
(3, 8, 9, 5, 0, '2025-09-15 19:47:20'),
(3, 8, 11, 12, 0, '2025-09-08 20:41:08'),
(3, 9, 1, 77, 0, '2025-09-17 21:59:55'),
(3, 9, 2, 40, 0, '2025-09-07 20:13:16'),
(3, 9, 4, 3, 0, '2025-08-30 16:59:39'),
(3, 9, 9, 5, 0, '2025-09-15 19:47:20'),
(3, 9, 11, 13, 0, '2025-09-08 20:41:11'),
(3, 10, 1, 80, 0, '2025-09-17 21:59:56'),
(3, 10, 2, 40, 0, '2025-09-07 20:13:17'),
(3, 10, 4, 3, 0, '2025-08-30 16:59:39'),
(3, 10, 9, 5, 0, '2025-09-15 19:47:20'),
(3, 10, 11, 13, 0, '2025-09-08 20:41:11'),
(3, 11, 1, 72, 0, '2025-09-17 21:59:53'),
(3, 11, 2, 41, 0, '2025-09-07 20:13:18'),
(3, 11, 4, 3, 0, '2025-08-30 16:59:40'),
(3, 11, 9, 5, 0, '2025-09-15 19:47:21'),
(3, 11, 11, 12, 0, '2025-09-08 20:41:01'),
(3, 12, 1, 75, 0, '2025-09-17 21:59:53'),
(3, 12, 2, 42, 0, '2025-09-07 20:13:19'),
(3, 12, 4, 3, 0, '2025-08-30 16:59:40'),
(3, 12, 9, 7, 0, '2025-09-15 19:47:22'),
(3, 12, 11, 12, 0, '2025-09-08 20:41:05'),
(3, 13, 1, 78, 0, '2025-09-17 21:59:55'),
(3, 13, 2, 38, 0, '2025-09-07 20:13:19'),
(3, 13, 4, 3, 0, '2025-08-30 16:59:40'),
(3, 13, 9, 5, 0, '2025-09-15 19:47:23'),
(3, 13, 11, 12, 0, '2025-09-08 20:41:08'),
(3, 14, 1, 75, 0, '2025-09-17 21:59:55'),
(3, 14, 2, 40, 0, '2025-09-07 20:13:19'),
(3, 14, 4, 3, 0, '2025-08-30 16:59:40'),
(3, 14, 9, 5, 0, '2025-09-15 19:47:23'),
(3, 14, 11, 13, 0, '2025-09-08 20:41:12'),
(3, 15, 1, 79, 0, '2025-09-17 21:59:57'),
(3, 15, 2, 40, 0, '2025-09-07 20:13:20'),
(3, 15, 4, 3, 0, '2025-08-30 16:59:40'),
(3, 15, 9, 5, 0, '2025-09-15 19:47:24'),
(3, 15, 11, 13, 0, '2025-09-08 20:41:12'),
(3, 16, 1, 70, 0, '2025-09-17 21:59:57'),
(3, 16, 2, 42, 0, '2025-09-07 20:13:21'),
(3, 16, 4, 3, 0, '2025-08-30 16:59:41'),
(3, 16, 9, 5, 0, '2025-09-15 19:47:22'),
(3, 16, 11, 8, 0, '2025-09-08 20:41:01'),
(3, 17, 1, 72, 0, '2025-09-17 21:59:57'),
(3, 17, 2, 40, 0, '2025-09-07 20:13:21'),
(3, 17, 4, 3, 0, '2025-08-30 16:59:41'),
(3, 17, 9, 3, 0, '2025-09-15 19:47:22'),
(3, 17, 11, 7, 0, '2025-09-08 20:41:05'),
(3, 18, 1, 70, 0, '2025-09-17 21:59:57'),
(3, 18, 2, 38, 0, '2025-09-07 20:13:22'),
(3, 18, 4, 3, 0, '2025-08-30 16:59:41'),
(3, 18, 9, 4, 0, '2025-09-15 19:47:23'),
(3, 18, 11, 8, 0, '2025-09-08 20:41:07'),
(3, 19, 1, 69, 0, '2025-09-17 21:59:58'),
(3, 19, 2, 38, 0, '2025-09-07 20:13:22'),
(3, 19, 4, 3, 0, '2025-08-30 16:59:41'),
(3, 19, 9, 5, 0, '2025-09-15 19:47:23'),
(3, 19, 11, 8, 0, '2025-09-08 20:41:13'),
(3, 20, 1, 72, 0, '2025-09-17 21:59:58'),
(3, 20, 2, 38, 0, '2025-09-07 20:13:23'),
(3, 20, 4, 2, 0, '2025-08-30 16:59:41'),
(3, 20, 9, 5, 0, '2025-09-15 19:47:23'),
(3, 20, 11, 8, 0, '2025-09-08 20:41:13'),
(3, 21, 1, 57, 0, '2025-09-17 21:59:59'),
(3, 21, 2, 28, 0, '2025-09-07 20:13:24'),
(3, 21, 4, 2, 0, '2025-08-30 16:59:42'),
(3, 21, 9, 1, 0, '2025-09-15 19:47:24'),
(3, 21, 11, 10, 0, '2025-09-08 20:41:00'),
(3, 22, 1, 57, 0, '2025-09-17 21:59:59'),
(3, 22, 2, 28, 0, '2025-09-07 20:13:24'),
(3, 22, 4, 2, 0, '2025-08-30 16:59:42'),
(3, 22, 9, 1, 0, '2025-09-15 19:47:24'),
(3, 22, 11, 10, 0, '2025-09-08 20:41:06'),
(3, 23, 1, 55, 0, '2025-09-17 22:00:04'),
(3, 23, 2, 26, 0, '2025-09-07 20:13:25'),
(3, 23, 4, 2, 0, '2025-08-30 16:59:42'),
(3, 23, 9, 1, 0, '2025-09-15 19:47:26'),
(3, 23, 11, 9, 0, '2025-09-08 20:41:06'),
(3, 24, 1, 57, 0, '2025-09-17 22:00:04'),
(3, 24, 2, 27, 0, '2025-09-07 20:13:25'),
(3, 24, 4, 3, 0, '2025-08-30 16:59:42'),
(3, 24, 9, 1, 0, '2025-09-15 19:47:26'),
(3, 24, 11, 9, 0, '2025-09-08 20:41:13'),
(3, 25, 1, 57, 0, '2025-09-17 22:00:06'),
(3, 25, 2, 26, 0, '2025-09-07 20:13:29'),
(3, 25, 4, 3, 0, '2025-08-30 16:59:42'),
(3, 25, 9, 1, 0, '2025-09-15 19:47:28'),
(3, 25, 11, 8, 0, '2025-09-08 20:41:14'),
(3, 26, 1, 57, 0, '2025-09-17 21:59:59'),
(3, 26, 2, 29, 0, '2025-09-07 20:13:25'),
(3, 26, 4, 2, 0, '2025-08-30 16:59:42'),
(3, 26, 9, 1, 0, '2025-09-15 19:47:24'),
(3, 26, 11, 10, 0, '2025-09-08 20:40:56'),
(3, 27, 1, 59, 0, '2025-09-17 21:59:59'),
(3, 27, 2, 28, 0, '2025-09-07 20:13:26'),
(3, 27, 4, 2, 0, '2025-08-30 16:59:42'),
(3, 27, 9, 1, 0, '2025-09-15 19:47:25'),
(3, 27, 11, 11, 0, '2025-09-08 20:40:57'),
(3, 28, 1, 56, 0, '2025-09-17 22:00:03'),
(3, 28, 2, 27, 0, '2025-09-07 20:13:27'),
(3, 28, 4, 2, 0, '2025-08-30 16:59:43'),
(3, 28, 9, 1, 0, '2025-09-15 19:47:25'),
(3, 28, 11, 9, 0, '2025-09-08 20:40:58'),
(3, 29, 1, 56, 0, '2025-09-17 22:00:05'),
(3, 29, 2, 27, 0, '2025-09-07 20:13:28'),
(3, 29, 4, 2, 0, '2025-08-30 16:59:43'),
(3, 29, 9, 1, 0, '2025-09-15 19:47:27'),
(3, 29, 11, 9, 0, '2025-09-08 20:40:58'),
(3, 30, 1, 56, 0, '2025-09-17 22:00:05'),
(3, 30, 2, 26, 0, '2025-09-07 20:13:29'),
(3, 30, 4, 2, 0, '2025-08-30 16:59:43'),
(3, 30, 9, 1, 0, '2025-09-15 19:47:28'),
(3, 30, 11, 8, 0, '2025-09-08 20:41:00'),
(3, 31, 1, 55, 0, '2025-09-17 22:00:00'),
(3, 31, 2, 26, 0, '2025-09-07 20:13:26'),
(3, 31, 4, 2, 0, '2025-08-30 16:59:44'),
(3, 31, 9, 1, 0, '2025-09-15 19:47:25'),
(3, 31, 11, 9, 0, '2025-09-08 20:40:56'),
(3, 32, 1, 56, 0, '2025-09-17 22:00:01'),
(3, 32, 2, 27, 0, '2025-09-07 20:13:27'),
(3, 32, 4, 2, 0, '2025-08-30 16:59:43'),
(3, 32, 9, 1, 0, '2025-09-15 19:47:25'),
(3, 32, 11, 8, 0, '2025-09-08 20:40:57'),
(3, 33, 1, 54, 0, '2025-09-17 22:00:02'),
(3, 33, 2, 26, 0, '2025-09-07 20:13:30'),
(3, 33, 4, 2, 0, '2025-08-30 16:59:43'),
(3, 33, 9, 1, 0, '2025-09-15 19:47:25'),
(3, 33, 11, 8, 0, '2025-09-08 20:40:57'),
(3, 34, 1, 51, 0, '2025-09-17 22:00:05'),
(3, 34, 2, 25, 0, '2025-09-07 20:13:28'),
(3, 34, 4, 2, 0, '2025-08-30 16:59:43'),
(3, 34, 9, 1, 0, '2025-09-15 19:47:27'),
(3, 34, 11, 8, 0, '2025-09-08 20:40:59'),
(3, 35, 1, 55, 0, '2025-09-17 22:00:05'),
(3, 35, 2, 26, 0, '2025-09-07 20:13:28'),
(3, 35, 4, 2, 0, '2025-08-30 16:59:44'),
(3, 35, 9, 1, 0, '2025-09-15 19:47:27'),
(3, 35, 11, 8, 0, '2025-09-08 20:40:59'),
(4, 1, 1, 28, 0, '2025-09-15 18:41:24'),
(4, 1, 2, 4, 0, '2025-09-15 18:50:48'),
(4, 1, 3, 6, 0, '2025-09-15 13:22:30'),
(4, 2, 1, 0, 47, '2025-09-15 18:41:24'),
(4, 2, 2, 0, 6, '2025-09-15 18:50:49'),
(4, 2, 3, 0, 12, '2025-09-15 13:22:52'),
(4, 3, 1, 18, 45, '2025-09-15 18:41:25'),
(4, 3, 2, 4, 6, '2025-09-15 18:50:49'),
(4, 3, 3, 3, 8, '2025-09-15 13:22:31'),
(4, 4, 1, 0, 45, '2025-09-15 18:41:25'),
(4, 4, 2, 0, 6, '2025-09-15 18:50:50'),
(4, 4, 3, 0, 13, '2025-09-15 13:22:52'),
(4, 5, 1, 35, 0, '2025-09-15 18:41:25'),
(4, 5, 2, 9, 0, '2025-09-15 18:50:50'),
(4, 5, 3, 9, 0, '2025-09-15 13:22:31'),
(4, 6, 1, 35, 0, '2025-09-15 18:41:26'),
(4, 6, 2, 20, 0, '2025-09-15 18:50:51'),
(4, 6, 3, 9, 0, '2025-09-15 13:22:32'),
(4, 7, 1, 35, 0, '2025-09-15 18:41:26'),
(4, 7, 2, 9, 0, '2025-09-15 18:50:51'),
(4, 7, 3, 8, 0, '2025-09-15 13:22:30'),
(4, 8, 1, 35, 0, '2025-09-15 18:41:27'),
(4, 8, 2, 17, 0, '2025-09-15 18:50:51'),
(4, 8, 3, 9, 0, '2025-09-15 13:22:31'),
(4, 9, 1, 35, 0, '2025-09-15 18:41:27'),
(4, 9, 2, 12, 0, '2025-09-15 18:50:51'),
(4, 9, 3, 10, 0, '2025-09-15 13:22:33'),
(4, 10, 1, 35, 0, '2025-09-15 18:41:27'),
(4, 10, 2, 9, 0, '2025-09-15 18:50:52'),
(4, 10, 3, 9, 0, '2025-09-15 13:22:35'),
(4, 11, 1, 28, 0, '2025-09-15 18:41:28'),
(4, 11, 2, 18, 0, '2025-09-15 18:50:53'),
(4, 11, 3, 8, 0, '2025-09-15 13:22:33'),
(4, 12, 1, 28, 0, '2025-09-15 18:41:28'),
(4, 12, 2, 19, 0, '2025-09-15 18:50:53'),
(4, 12, 3, 8, 0, '2025-09-15 13:22:32'),
(4, 13, 1, 27, 0, '2025-09-15 18:41:28'),
(4, 13, 2, 22, 0, '2025-09-15 18:50:54'),
(4, 13, 3, 9, 0, '2025-09-15 13:22:33'),
(4, 14, 1, 27, 0, '2025-09-15 18:41:28'),
(4, 14, 2, 10, 0, '2025-09-15 18:50:54'),
(4, 14, 3, 7, 0, '2025-09-15 13:22:34'),
(4, 15, 1, 27, 0, '2025-09-15 18:41:29'),
(4, 15, 2, 14, 0, '2025-09-15 18:50:55'),
(4, 15, 3, 7, 0, '2025-09-15 13:22:34'),
(4, 16, 1, 27, 0, '2025-09-15 18:41:30'),
(4, 16, 2, 21, 0, '2025-09-15 18:50:56'),
(4, 16, 3, 9, 0, '2025-09-15 13:22:35'),
(4, 17, 1, 27, 0, '2025-09-15 18:41:30'),
(4, 17, 2, 21, 0, '2025-09-15 18:50:53'),
(4, 17, 3, 9, 0, '2025-09-15 13:22:35'),
(4, 18, 1, 29, 0, '2025-09-15 18:41:31'),
(4, 18, 2, 18, 0, '2025-09-15 18:50:54'),
(4, 18, 3, 9, 0, '2025-09-15 13:22:38'),
(4, 19, 1, 29, 0, '2025-09-15 18:41:31'),
(4, 19, 2, 13, 0, '2025-09-15 18:50:55'),
(4, 19, 3, 7, 0, '2025-09-15 13:22:39'),
(4, 20, 1, 29, 0, '2025-09-15 18:41:32'),
(4, 20, 2, 15, 0, '2025-09-15 18:50:55'),
(4, 20, 3, 8, 0, '2025-09-15 13:22:39'),
(4, 21, 1, 28, 0, '2025-09-15 18:41:30'),
(4, 21, 2, 20, 0, '2025-09-15 18:50:56'),
(4, 21, 3, 9, 0, '2025-09-15 13:22:35'),
(4, 22, 1, 28, 0, '2025-09-15 18:41:31'),
(4, 22, 2, 9, 0, '2025-09-15 18:50:56'),
(4, 22, 3, 8, 0, '2025-09-15 13:22:36'),
(4, 23, 1, 28, 0, '2025-09-15 18:41:31'),
(4, 23, 2, 12, 0, '2025-09-15 18:50:59'),
(4, 23, 3, 8, 0, '2025-09-15 13:22:38'),
(4, 24, 1, 27, 0, '2025-09-15 18:41:31'),
(4, 24, 2, 14, 0, '2025-09-15 18:50:59'),
(4, 24, 3, 7, 0, '2025-09-15 13:22:39'),
(4, 25, 1, 28, 0, '2025-09-15 18:41:32'),
(4, 25, 2, 14, 0, '2025-09-15 18:51:00'),
(4, 25, 3, 7, 0, '2025-09-15 13:22:40'),
(4, 26, 1, 27, 0, '2025-09-15 18:41:33'),
(4, 26, 2, 19, 0, '2025-09-15 18:50:57'),
(4, 26, 3, 8, 0, '2025-09-15 13:22:36'),
(4, 27, 1, 27, 0, '2025-09-15 18:41:33'),
(4, 27, 2, 7, 0, '2025-09-15 18:50:57'),
(4, 27, 3, 8, 0, '2025-09-15 13:22:36'),
(4, 28, 1, 28, 0, '2025-09-15 18:41:33'),
(4, 28, 2, 18, 0, '2025-09-15 18:50:59'),
(4, 28, 3, 8, 0, '2025-09-15 13:22:38'),
(4, 29, 1, 28, 0, '2025-09-15 18:41:34'),
(4, 29, 2, 15, 0, '2025-09-15 18:50:59'),
(4, 29, 3, 8, 0, '2025-09-15 13:22:40'),
(4, 30, 1, 28, 0, '2025-09-15 18:41:35'),
(4, 30, 2, 14, 0, '2025-09-15 18:51:00'),
(4, 30, 3, 7, 0, '2025-09-15 13:22:40'),
(4, 31, 1, 19, 0, '2025-09-15 18:41:33'),
(4, 31, 2, 4, 0, '2025-09-15 18:50:57'),
(4, 31, 3, 3, 0, '2025-09-15 13:22:37'),
(4, 32, 1, 20, 0, '2025-09-15 18:41:33'),
(4, 32, 2, 4, 0, '2025-09-15 18:50:58'),
(4, 32, 3, 3, 0, '2025-09-15 13:22:37'),
(4, 33, 1, 19, 0, '2025-09-15 18:41:34'),
(4, 33, 2, 4, 0, '2025-09-15 18:50:58'),
(4, 33, 3, 3, 0, '2025-09-15 13:22:38'),
(4, 34, 1, 19, 0, '2025-09-15 18:41:34'),
(4, 34, 2, 4, 0, '2025-09-15 18:51:00'),
(4, 34, 3, 3, 0, '2025-09-15 13:22:40'),
(4, 35, 1, 19, 0, '2025-09-15 18:41:35'),
(4, 35, 2, 4, 0, '2025-09-15 18:51:00'),
(4, 35, 3, 3, 0, '2025-09-15 13:22:41'),
(10, 2, 1, 0, 6, '2025-09-17 22:00:40'),
(10, 4, 1, 0, 6, '2025-09-17 22:00:41'),
(10, 6, 1, 20, 0, '2025-09-01 01:19:32'),
(10, 7, 1, 10, 0, '2025-09-01 01:19:45'),
(10, 8, 1, 20, 0, '2025-09-01 01:19:31'),
(10, 9, 1, 10, 0, '2025-09-01 01:19:39'),
(10, 10, 1, 10, 0, '2025-09-01 01:19:46'),
(11, 1, 1, 2, 0, '2025-09-08 20:29:14'),
(11, 1, 3, 2, 0, '2025-09-07 11:26:05'),
(11, 2, 1, 2, 0, '2025-09-08 20:29:14'),
(11, 2, 3, 2, 0, '2025-09-07 11:26:05'),
(11, 3, 1, 2, 0, '2025-09-08 20:29:15'),
(11, 3, 3, 2, 0, '2025-09-07 11:26:06'),
(11, 4, 1, 2, 0, '2025-09-08 20:29:15'),
(11, 4, 3, 2, 0, '2025-09-07 11:26:07'),
(11, 6, 1, 2, 0, '2025-09-08 20:29:13'),
(11, 6, 3, 2, 0, '2025-09-07 11:26:05'),
(11, 7, 1, 2, 0, '2025-09-08 20:29:14'),
(11, 7, 3, 2, 0, '2025-09-07 11:26:06'),
(11, 8, 1, 2, 0, '2025-09-08 20:29:14'),
(11, 8, 3, 2, 0, '2025-09-07 11:26:06');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `typing_status`
--

CREATE TABLE `typing_status` (
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `last_typing` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `vip` tinyint(1) NOT NULL DEFAULT 0,
  `vip_frame` varchar(100) DEFAULT NULL,
  `vip_card` varchar(255) DEFAULT NULL,
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
  `last_active` datetime DEFAULT NULL,
  `money` int(11) NOT NULL DEFAULT 10000,
  `gold` int(11) NOT NULL DEFAULT 0,
  `level` int(11) NOT NULL DEFAULT 1,
  `xp` int(11) NOT NULL DEFAULT 0,
  `harvests` int(11) NOT NULL DEFAULT 0,
  `sales` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `users`
--

INSERT INTO `users` (`id`, `vip`, `vip_frame`, `vip_card`, `username`, `email`, `password`, `country`, `city`, `age`, `gender`, `description`, `gallery`, `created_at`, `gallery_status`, `is_admin`, `last_active`, `money`, `gold`, `level`, `xp`, `harvests`, `sales`, `updated_at`) VALUES
(1, 1, '27.png', 'a30.png', 'quatro', 'serverboost93@gmail.com', '$2y$10$XUP9QK9AU/EgETee1NVvKemxG2xpWWKWzCbg1.AkRIWjmKLbdeLDW', 'Romania', 'Suceava', 31, 'masculin', '╔════༺†༻✝️༺†༻════╗\r\n  JESUS CHRIST IS LORD  \r\n╚════༺†༻✝️༺†༻════╝', 'photo_20250809_230730_64.jpg,photo_20250806_144906_20.jpg', '2025-07-21 21:56:14', 'pending,pending', 1, '2025-09-18 00:12:12', 26250, 4900, 120, 500034169, 21350, 24210, '2025-09-18 00:12:12'),
(2, 0, NULL, NULL, 'quatro93', 'quatro93@gmail.com', '$2y$10$D2nwnAS7tkNt7MZTzuB2jOGSt8dWPFXbxHMQy/6hpJT/FRPlce3B2', 'Romania', 'Suceava', 31, 'masculin', 'Miauuuuuuu 😅', '', '2025-07-22 00:47:31', '', 0, '2025-09-15 19:04:18', 5000000, 100, 32, 28355, 1000, 0, '2025-09-15 19:04:18'),
(3, 1, '16.png', 'a7.png', 'Catalina', 'albacazapada@gmail.com', '$2y$10$oSYlVjpRUK3.cEJRMinID.DkjFTAzxSVykk0V88SefTegqkIeHsy.', 'Romania', 'suceava', 23, 'feminin', '', 'photo_20250806_144758_94.jpeg,photo_20250806_164731_41.jpeg', '2025-08-04 08:42:45', 'pending,pending', 1, '2025-09-15 13:25:57', 999250, 100, 120, 579159, 5551, 9576, '2025-09-15 13:25:57'),
(4, 1, '28.png', 'a28.png', 'Nexus', 'ionut.fidirovici2@gmail.com', '$2y$10$KuZ/MTsqYGSjN9ebtmsfa.6C/ROwsTEcUcWBSpWCGib8p9pjp31t2', 'Romania', 'Radauti', 18, 'masculin', 'Sunt smecher', 'photo_20250806_172705_30.png,photo_20250805_212441_56.png', '2025-08-05 19:24:00', 'pending,pending,pend', 0, '2025-09-08 17:22:43', 5000000, 4950, 120, 566535, 33002, 36853, '2025-09-08 19:16:44'),
(5, 0, NULL, NULL, 'test', 'test@gmail.com', '$2y$10$VblPlxQqXNqXsbHtHL/Qd.6oIVy8ZcICXbXyl/zWVi4c7uBafmmTW', 'Romania', 'suceava', 31, 'masculin', NULL, NULL, '2025-08-06 09:19:36', 'pending', 0, '2025-08-29 00:05:43', 200000, 0, 1, 0, 0, 0, '2025-09-07 18:57:30'),
(6, 0, NULL, NULL, 'test2', 'test2@gmail.com', '$2y$10$UEnt3nQ4YTUCLBXQqd9N1.EBh0qsWtiE9oHyMpU5BBLpwknjyOwR6', 'Romania', 'suceava', 33, 'masculin', NULL, NULL, '2025-08-06 10:08:10', 'pending', 0, '2025-08-06 15:43:54', 10000, 0, 1, 0, 0, 0, '2025-09-07 18:57:30'),
(7, 0, NULL, NULL, 'Test3', 'teat3@gmail.com', '$2y$10$J5YGNEPj1eQ1bu3LRKNTY.JoUa5RrhYuJIOOYqJFxJ0x6XdwHz8fG', 'Romania', 'România', 33, 'masculin', NULL, NULL, '2025-08-06 10:35:38', 'pending', 0, '2025-08-30 16:39:54', 10000, 0, 1, 0, 0, 0, '2025-09-07 18:57:30'),
(8, 0, NULL, NULL, 'Olena', 'frecja@spoko.pl', '$2y$10$aHKKJu9sFIjhWNLQTz80xetYaccbZRjVO3SLCDy0uKjzhFvp9um9.', 'Polska', 'Gdańsk', 25, 'feminin', NULL, NULL, '2025-08-07 17:41:08', 'pending', 0, '2025-08-07 20:43:23', 10000, 5000, 1, 0, 0, 0, '2025-09-08 19:17:43'),
(9, 0, NULL, NULL, 'Test5', 'test5@gmail.com', '$2y$10$Kj2df/QnJwoVqJ1aL4KQFuTs7rRMe8bnjxi6eJk6XOL0mglTH.uLu', 'România', 'suceava', 31, 'masculin', NULL, NULL, '2025-08-10 10:14:24', 'pending', 0, '2025-09-15 19:49:48', 10000, 0, 1, 41, 0, 0, '2025-09-15 19:53:36'),
(10, 0, NULL, NULL, 'Nesu', 'nesu@gmail.com', '$2y$10$lC61FaNSMDGmQ7u2xHsiGOdWVI1zDjLjAXyMmF.T/R3nuuHT4dEdG', 'ro', 'm', 18, 'masculin', NULL, NULL, '2025-08-10 10:50:57', 'pending', 0, '2025-08-16 18:44:05', 7933000, 5000000, 120, 0, 0, 0, '2025-09-08 19:18:11'),
(11, 0, NULL, NULL, 'Test6', 'serverboost@gmail.com', '$2y$10$A4qFTX72OlVsUMZNQN62GOCzD3s26k4.KSpxs.DGqIDvXVrB7ZTXi', 'Romania', 'România', 30, 'masculin', NULL, NULL, '2025-08-29 07:05:24', 'pending', 1, '2025-09-08 20:43:49', 616500, 0, 8, 2599, 0, 0, '2025-09-08 20:43:49');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user_achievements`
--

CREATE TABLE `user_achievements` (
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `selected` tinyint(1) DEFAULT 0,
  `achieved_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `user_achievements`
--

INSERT INTO `user_achievements` (`user_id`, `achievement_id`, `selected`, `achieved_at`) VALUES
(1, 1, 0, '2025-09-01 15:53:17'),
(1, 2, 0, '2025-09-01 15:53:17'),
(1, 4, 0, '2025-09-01 23:44:43'),
(1, 5, 0, '2025-09-01 23:44:43'),
(1, 6, 0, '2025-09-01 23:44:43'),
(1, 7, 0, '2025-09-01 23:44:43'),
(1, 8, 0, '2025-09-01 23:44:43'),
(1, 9, 0, '2025-09-01 23:44:43'),
(1, 10, 0, '2025-09-01 23:44:43'),
(1, 11, 1, '2025-09-01 23:44:43'),
(2, 4, 0, '2025-09-02 15:11:53'),
(2, 5, 0, '2025-09-02 15:11:53'),
(2, 6, 1, '2025-09-02 15:11:53'),
(3, 1, 0, '2025-09-01 15:58:00'),
(3, 2, 0, '2025-09-01 15:58:00'),
(3, 4, 0, '2025-09-02 10:23:29'),
(3, 5, 0, '2025-09-02 10:23:30'),
(3, 6, 0, '2025-09-02 10:23:30'),
(3, 7, 1, '2025-09-02 10:23:30'),
(4, 2, 0, '2025-09-02 17:48:17'),
(4, 4, 0, '2025-09-02 17:48:21'),
(4, 5, 0, '2025-09-02 17:48:21'),
(4, 6, 0, '2025-09-02 17:48:21'),
(4, 7, 1, '2025-09-02 17:48:21'),
(11, 4, 1, '2025-09-02 10:44:26'),
(11, 5, 0, '2025-09-02 17:53:28');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user_barn`
--

CREATE TABLE `user_barn` (
  `user_id` int(11) NOT NULL,
  `slot_number` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `user_barn`
--

INSERT INTO `user_barn` (`user_id`, `slot_number`, `item_id`, `quantity`) VALUES
(2, 1, 13, 200),
(2, 2, 16, 1000),
(2, 3, 16, 1000),
(2, 4, 16, 1000),
(2, 5, 22, 1000),
(3, 1, 13, 260),
(3, 2, 16, 410),
(3, 3, 14, 1);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user_barn_info`
--

CREATE TABLE `user_barn_info` (
  `user_id` int(11) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `user_barn_info`
--

INSERT INTO `user_barn_info` (`user_id`, `capacity`) VALUES
(1, 10),
(2, 4),
(3, 4),
(4, 10),
(7, 4),
(9, 4),
(10, 4),
(11, 4);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user_helpers`
--

CREATE TABLE `user_helpers` (
  `user_id` int(11) NOT NULL,
  `helper_id` int(11) NOT NULL,
  `waters` int(11) NOT NULL DEFAULT 0,
  `feeds` int(11) NOT NULL DEFAULT 0,
  `harvests` int(11) NOT NULL DEFAULT 0,
  `last_action_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `user_helpers`
--

INSERT INTO `user_helpers` (`user_id`, `helper_id`, `waters`, `feeds`, `harvests`, `last_action_date`) VALUES
(1, 1, 200, 0, 0, '2025-09-17'),
(2, 2, 200, 0, 5, '2025-09-15'),
(3, 2, 200, 0, 0, '2025-09-15');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user_last_helpers`
--

CREATE TABLE `user_last_helpers` (
  `owner_id` int(11) NOT NULL,
  `helper_id` int(11) NOT NULL,
  `action` enum('water','feed') NOT NULL,
  `helped_at` datetime NOT NULL,
  `clicks` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `user_last_helpers`
--

INSERT INTO `user_last_helpers` (`owner_id`, `helper_id`, `action`, `helped_at`, `clicks`) VALUES
(1, 2, 'water', '2025-09-15 16:28:18', 35),
(2, 3, 'water', '2025-09-09 16:47:36', 12),
(3, 1, 'water', '2025-09-17 22:00:06', 35),
(4, 2, 'water', '2025-09-15 18:51:00', 31),
(10, 1, 'feed', '2025-09-17 22:00:41', 2),
(11, 1, 'water', '2025-09-08 20:29:15', 7);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user_plants`
--

CREATE TABLE `user_plants` (
  `user_id` int(11) NOT NULL,
  `slot_number` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `planted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `user_plants`
--

INSERT INTO `user_plants` (`user_id`, `slot_number`, `item_id`, `planted_at`) VALUES
(1, 1, 15, '2025-09-17 23:57:19'),
(1, 2, 15, '2025-09-17 23:57:19'),
(1, 3, 15, '2025-09-17 23:57:19'),
(1, 4, 15, '2025-09-17 23:57:19'),
(1, 5, 15, '2025-09-17 23:57:22'),
(2, 1, 22, '2025-09-07 20:14:27'),
(2, 2, 22, '2025-09-07 20:14:29'),
(2, 3, 22, '2025-09-07 20:14:32'),
(2, 4, 22, '2025-09-07 20:14:34'),
(2, 5, 22, '2025-09-07 20:14:53'),
(2, 11, 15, '2025-09-01 01:34:25'),
(2, 12, 15, '2025-09-01 01:48:00'),
(3, 1, 15, '2025-09-15 13:20:15'),
(3, 2, 15, '2025-09-15 13:20:15'),
(3, 3, 15, '2025-09-15 13:20:15'),
(3, 4, 15, '2025-09-15 13:20:15'),
(3, 5, 15, '2025-09-15 13:20:15'),
(3, 6, 15, '2025-09-15 13:20:15'),
(3, 7, 15, '2025-09-15 13:20:15'),
(3, 8, 15, '2025-09-15 13:20:15'),
(3, 9, 15, '2025-09-15 13:20:15'),
(3, 10, 15, '2025-09-15 13:20:15'),
(3, 11, 15, '2025-09-15 13:20:15'),
(3, 12, 15, '2025-09-15 13:20:15'),
(3, 13, 15, '2025-09-15 13:20:15'),
(3, 14, 15, '2025-09-15 13:20:15'),
(3, 15, 15, '2025-09-15 13:20:15'),
(3, 16, 15, '2025-09-15 13:20:15'),
(3, 17, 15, '2025-09-15 13:20:15'),
(3, 18, 15, '2025-09-15 13:20:15'),
(3, 19, 15, '2025-09-15 13:20:15'),
(3, 20, 15, '2025-09-15 13:20:15'),
(3, 21, 15, '2025-09-15 13:20:15'),
(3, 22, 15, '2025-09-15 13:20:15'),
(3, 23, 15, '2025-09-15 13:20:15'),
(3, 24, 15, '2025-09-15 13:20:15'),
(3, 25, 15, '2025-09-15 13:20:15'),
(3, 26, 15, '2025-09-15 13:20:15'),
(3, 27, 15, '2025-09-15 13:20:15'),
(3, 28, 15, '2025-09-15 13:20:15'),
(3, 29, 15, '2025-09-15 13:20:15'),
(3, 30, 15, '2025-09-15 13:20:15'),
(3, 31, 15, '2025-09-15 13:20:15'),
(3, 32, 15, '2025-09-15 13:20:15'),
(3, 33, 15, '2025-09-15 13:20:15'),
(3, 34, 15, '2025-09-15 13:20:15'),
(3, 35, 15, '2025-09-15 13:20:15'),
(4, 1, 23, '2025-09-08 17:18:16'),
(4, 2, 14, '2025-09-02 17:49:49'),
(4, 3, 23, '2025-09-08 17:18:26'),
(4, 4, 14, '2025-09-02 17:49:52'),
(4, 5, 23, '2025-09-08 17:18:26'),
(4, 6, 23, '2025-09-08 17:18:26'),
(4, 7, 23, '2025-09-08 17:18:26'),
(4, 8, 23, '2025-09-08 17:18:26'),
(4, 9, 23, '2025-09-08 17:18:26'),
(4, 10, 23, '2025-09-08 17:18:26'),
(4, 11, 23, '2025-09-08 17:18:26'),
(4, 12, 23, '2025-09-08 17:18:26'),
(4, 13, 23, '2025-09-08 17:18:26'),
(4, 14, 23, '2025-09-08 17:18:26'),
(4, 15, 23, '2025-09-08 17:18:26'),
(4, 16, 23, '2025-09-08 17:18:26'),
(4, 17, 23, '2025-09-08 17:18:26'),
(4, 18, 23, '2025-09-08 17:18:26'),
(4, 19, 23, '2025-09-08 17:18:26'),
(4, 20, 23, '2025-09-08 17:18:26'),
(4, 21, 23, '2025-09-08 17:18:26'),
(4, 22, 23, '2025-09-08 17:18:26'),
(4, 23, 23, '2025-09-08 17:18:26'),
(4, 24, 23, '2025-09-08 17:18:26'),
(4, 25, 23, '2025-09-08 17:18:26'),
(4, 26, 23, '2025-09-08 17:18:26'),
(4, 27, 23, '2025-09-08 17:18:26'),
(4, 28, 23, '2025-09-08 17:18:26'),
(4, 29, 23, '2025-09-08 17:18:26'),
(4, 30, 23, '2025-09-08 17:18:26'),
(4, 31, 23, '2025-09-08 17:18:26'),
(4, 32, 23, '2025-09-08 17:18:26'),
(4, 33, 23, '2025-09-08 17:18:26'),
(4, 34, 23, '2025-09-08 17:18:26'),
(4, 35, 23, '2025-09-08 17:18:26'),
(9, 1, 22, '2025-09-15 19:53:22'),
(9, 2, 22, '2025-09-15 19:53:24'),
(9, 3, 22, '2025-09-15 19:53:27'),
(9, 6, 22, '2025-09-15 19:53:30'),
(9, 7, 22, '2025-09-15 19:53:32'),
(9, 8, 22, '2025-09-15 19:53:34'),
(10, 2, 11, '2025-08-16 18:39:11'),
(10, 4, 14, '2025-08-16 18:39:19'),
(10, 6, 13, '2025-08-16 18:39:26'),
(10, 7, 15, '2025-08-16 18:39:29'),
(10, 8, 13, '2025-08-16 18:39:37'),
(10, 9, 15, '2025-08-16 18:39:40'),
(10, 10, 15, '2025-08-16 18:39:43'),
(11, 1, 22, '2025-09-02 18:00:11'),
(11, 2, 22, '2025-09-02 18:00:13'),
(11, 3, 22, '2025-09-02 18:00:15'),
(11, 4, 22, '2025-09-02 18:00:16'),
(11, 6, 22, '2025-09-02 18:00:18'),
(11, 7, 22, '2025-09-02 18:00:20'),
(11, 8, 22, '2025-09-02 18:00:22');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user_slots`
--

CREATE TABLE `user_slots` (
  `user_id` int(11) NOT NULL,
  `slot_number` int(11) NOT NULL,
  `unlocked` tinyint(1) NOT NULL DEFAULT 0,
  `required_level` int(11) NOT NULL DEFAULT 0,
  `slot_type` varchar(20) NOT NULL DEFAULT 'crop',
  `item_id` int(11) DEFAULT NULL,
  `plant_date` datetime DEFAULT NULL,
  `water_interval` int(11) NOT NULL DEFAULT 0,
  `feed_interval` int(11) NOT NULL DEFAULT 0,
  `water_remaining` int(11) NOT NULL DEFAULT 0,
  `feed_remaining` int(11) NOT NULL DEFAULT 0,
  `timer_type` varchar(10) DEFAULT NULL,
  `timer_end` datetime DEFAULT NULL,
  `water_times` int(11) NOT NULL DEFAULT 0,
  `feed_times` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `user_slots`
--

INSERT INTO `user_slots` (`user_id`, `slot_number`, `unlocked`, `required_level`, `slot_type`, `item_id`, `plant_date`, `water_interval`, `feed_interval`, `water_remaining`, `feed_remaining`, `timer_type`, `timer_end`, `water_times`, `feed_times`) VALUES
(1, 1, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 2, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 3, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 4, 1, 5, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 5, 1, 10, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 6, 1, 0, 'crop', 3, '2025-08-12 19:47:32', 0, 20, 0, 100, 'grow', '2025-08-12 18:47:33', 0, 100),
(1, 7, 1, 0, 'crop', 3, '2025-08-12 00:24:14', 0, 20, 0, 20, NULL, NULL, 0, 0),
(1, 8, 1, 0, 'crop', 3, '2025-08-12 19:27:27', 0, 20, 0, 100, 'grow', '2025-08-12 18:27:28', 0, 100),
(1, 9, 1, 15, 'crop', 3, '2025-08-12 19:57:18', 0, 20, 0, 100, 'grow', '2025-08-12 18:57:19', 0, 100),
(1, 10, 1, 20, 'crop', 3, '2025-08-12 20:01:16', 0, 20, 0, 100, 'grow', '2025-08-12 19:01:17', 0, 100),
(1, 11, 1, 25, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 12, 1, 30, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 13, 1, 35, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 14, 1, 40, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 15, 1, 45, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 16, 1, 50, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 17, 1, 55, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 18, 1, 60, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 19, 1, 65, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 20, 1, 70, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 21, 1, 75, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 22, 1, 80, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 23, 1, 85, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 24, 1, 90, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 25, 1, 95, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 26, 1, 100, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 27, 1, 105, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 28, 1, 110, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 29, 1, 115, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 30, 1, 120, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 31, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 32, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 33, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 34, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(1, 35, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 1, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 2, 1, 0, 'crop', 3, '2025-08-12 20:02:04', 0, 20, 0, 100, 'grow', '2025-08-12 19:02:05', 0, 100),
(2, 3, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 4, 1, 5, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 5, 1, 10, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 6, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 7, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 8, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 9, 1, 15, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 10, 1, 20, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 11, 1, 25, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 12, 1, 30, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 13, 0, 35, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 14, 0, 40, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 15, 0, 45, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 16, 0, 50, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 17, 0, 55, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 18, 0, 60, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 19, 0, 65, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 20, 0, 70, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 21, 0, 75, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 22, 0, 80, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 23, 0, 85, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 24, 0, 90, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 25, 0, 95, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 26, 0, 100, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 27, 0, 105, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 28, 0, 110, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 29, 0, 115, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(2, 30, 0, 120, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 1, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 2, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 3, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 4, 1, 5, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 5, 1, 10, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 6, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 7, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 8, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 9, 1, 15, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 10, 1, 20, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 11, 1, 25, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 12, 1, 30, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 13, 1, 35, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 14, 1, 40, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 15, 1, 45, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 16, 1, 50, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 17, 1, 55, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 18, 1, 60, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 19, 1, 65, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 20, 1, 70, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 21, 1, 75, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 22, 1, 80, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 23, 1, 85, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 24, 1, 90, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 25, 1, 95, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 26, 1, 100, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 27, 1, 105, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 28, 1, 110, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 29, 1, 115, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 30, 1, 120, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 31, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 32, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 33, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 34, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(3, 35, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 2, 1, 0, 'pool', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 3, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 4, 1, 5, 'pool', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 5, 1, 10, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 9, 1, 15, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 10, 1, 20, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 11, 1, 25, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 12, 1, 30, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 13, 1, 35, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 14, 1, 40, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 15, 1, 45, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 16, 1, 50, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 17, 1, 55, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 18, 1, 60, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 19, 1, 65, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 20, 1, 70, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 21, 1, 75, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 22, 1, 80, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 23, 1, 85, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 24, 1, 90, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 25, 1, 95, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 26, 1, 100, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 27, 1, 105, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 28, 1, 110, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 29, 1, 115, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 30, 1, 120, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 31, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 32, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 33, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 34, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(4, 35, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 4, 0, 5, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 5, 0, 10, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 9, 0, 15, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 10, 0, 20, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 11, 0, 25, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 12, 0, 30, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 13, 0, 35, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 14, 0, 40, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 15, 0, 45, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 16, 0, 50, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 17, 0, 55, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 18, 0, 60, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 19, 0, 65, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 20, 0, 70, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 21, 0, 75, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 22, 0, 80, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 23, 0, 85, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 24, 0, 90, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 25, 0, 95, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 26, 0, 100, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 27, 0, 105, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 28, 0, 110, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 29, 0, 115, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(5, 30, 0, 120, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 4, 0, 5, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 5, 0, 10, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 9, 0, 15, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 10, 0, 20, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 11, 0, 25, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 12, 0, 30, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 13, 0, 35, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 14, 0, 40, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 15, 0, 45, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 16, 0, 50, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 17, 0, 55, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 18, 0, 60, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 19, 0, 65, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 20, 0, 70, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 21, 0, 75, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 22, 0, 80, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 23, 0, 85, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 24, 0, 90, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 25, 0, 95, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 26, 0, 100, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 27, 0, 105, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 28, 0, 110, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 29, 0, 115, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(7, 30, 0, 120, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 1, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 2, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 3, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 4, 0, 5, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 5, 0, 10, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 6, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 7, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 8, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 9, 0, 15, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 10, 0, 20, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 11, 0, 25, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 12, 0, 30, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 13, 0, 35, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 14, 0, 40, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 15, 0, 45, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 16, 0, 50, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 17, 0, 55, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 18, 0, 60, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 19, 0, 65, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 20, 0, 70, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 21, 0, 75, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 22, 0, 80, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 23, 0, 85, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 24, 0, 90, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 25, 0, 95, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 26, 0, 100, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 27, 0, 105, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 28, 0, 110, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 29, 0, 115, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 30, 0, 120, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 31, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 32, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 33, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 34, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(9, 35, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 1, 1, 0, 'tarc', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 2, 1, 0, 'pool', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 3, 1, 0, 'tarc', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 4, 1, 5, 'pool', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 5, 1, 10, 'tarc', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 6, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 7, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 8, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 9, 1, 15, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 10, 1, 20, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 11, 1, 25, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 12, 1, 30, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 13, 1, 35, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 14, 1, 40, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 15, 1, 45, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 16, 1, 50, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 17, 1, 55, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 18, 1, 60, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 19, 1, 65, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 20, 1, 70, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 21, 1, 75, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 22, 1, 80, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 23, 1, 85, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 24, 1, 90, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 25, 1, 95, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 26, 1, 100, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 27, 1, 105, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 28, 1, 110, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 29, 1, 115, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 30, 1, 120, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 31, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 32, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 33, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 34, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(10, 35, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 1, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 2, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 3, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 4, 1, 5, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 5, 0, 10, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 6, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 7, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 8, 1, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 9, 0, 15, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 10, 0, 20, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 11, 0, 25, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 12, 0, 30, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 13, 0, 35, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 14, 0, 40, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 15, 0, 45, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 16, 0, 50, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 17, 0, 55, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 18, 0, 60, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 19, 0, 65, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 20, 0, 70, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 21, 0, 75, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 22, 0, 80, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 23, 0, 85, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 24, 0, 90, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 25, 0, 95, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 26, 0, 100, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 27, 0, 105, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 28, 0, 110, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 29, 0, 115, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 30, 0, 120, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 31, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 32, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 33, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 34, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0),
(11, 35, 0, 0, 'crop', NULL, NULL, 0, 0, 0, 0, NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user_slot_states`
--

CREATE TABLE `user_slot_states` (
  `user_id` int(11) NOT NULL,
  `slot_number` int(11) NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT '',
  `water_interval` int(11) NOT NULL DEFAULT 0,
  `feed_interval` int(11) NOT NULL DEFAULT 0,
  `water_remaining` int(11) NOT NULL DEFAULT 0,
  `feed_remaining` int(11) NOT NULL DEFAULT 0,
  `timer_type` varchar(10) DEFAULT NULL,
  `timer_end` datetime DEFAULT NULL,
  `water_total` int(11) NOT NULL DEFAULT 0,
  `feed_total` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `user_slot_states`
--

INSERT INTO `user_slot_states` (`user_id`, `slot_number`, `image`, `water_interval`, `feed_interval`, `water_remaining`, `feed_remaining`, `timer_type`, `timer_end`, `water_total`, `feed_total`, `updated_at`) VALUES
(1, 1, 'img/strawberry.png', 40, 0, 18, 0, NULL, NULL, 0, 0, NULL),
(1, 2, 'img/strawberry.png', 40, 0, 18, 0, NULL, NULL, 0, 0, NULL),
(1, 3, 'img/strawberry.png', 40, 0, 18, 0, NULL, NULL, 0, 0, NULL),
(1, 4, 'img/strawberry.png', 40, 0, 18, 0, NULL, NULL, 0, 0, NULL),
(1, 5, 'img/strawberry.png', 40, 0, 18, 0, NULL, NULL, 0, 0, NULL),
(2, 1, 'img/cartofi.png', 10, 0, 13, 0, 'water', '2025-09-15 18:51:15', 0, 0, '2025-09-15 15:51:05'),
(2, 2, 'img/cartofi.png', 10, 0, 13, 0, 'water', '2025-09-15 18:51:15', 0, 0, '2025-09-15 15:51:05'),
(2, 3, 'img/cartofi.png', 10, 0, 13, 0, 'water', '2025-09-15 18:51:15', 0, 0, '2025-09-15 15:51:05'),
(2, 4, 'img/cartofi.png', 10, 0, 14, 0, 'water', '2025-09-15 18:51:15', 0, 0, '2025-09-15 15:51:05'),
(2, 5, 'img/cartofi.png', 10, 0, 16, 0, 'water', '2025-09-15 18:50:57', 0, 0, '2025-09-15 15:50:47'),
(2, 11, 'img/strawberry.png', 65, 0, 0, 0, 'harvest', NULL, 0, 0, '2025-09-09 14:40:04'),
(2, 12, 'img/strawberry.png', 65, 0, 0, 0, 'harvest', NULL, 0, 0, '2025-09-09 14:40:11'),
(3, 1, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:33', 0, 0, '2025-09-15 10:23:36'),
(3, 2, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:34', 0, 0, '2025-09-15 10:23:36'),
(3, 3, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:34', 0, 0, '2025-09-15 10:23:36'),
(3, 4, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:35', 0, 0, '2025-09-15 10:23:37'),
(3, 5, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:36', 0, 0, '2025-09-15 10:23:37'),
(3, 6, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:32', 0, 0, '2025-09-15 10:23:37'),
(3, 7, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:32', 0, 0, '2025-09-15 10:23:37'),
(3, 8, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:34', 0, 0, '2025-09-15 10:23:37'),
(3, 9, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:35', 0, 0, '2025-09-15 10:23:37'),
(3, 10, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:36', 0, 0, '2025-09-15 10:23:51'),
(3, 11, 'img/strawberry.png', 40, 0, 9, 0, 'water', '2025-09-17 21:00:33', 0, 0, '2025-09-15 10:23:51'),
(3, 12, 'img/strawberry.png', 40, 0, 10, 0, 'water', '2025-09-17 21:00:33', 0, 0, '2025-09-15 10:22:57'),
(3, 13, 'img/strawberry.png', 40, 0, 10, 0, 'water', '2025-09-17 21:00:35', 0, 0, '2025-09-15 10:22:57'),
(3, 14, 'img/strawberry.png', 40, 0, 10, 0, 'water', '2025-09-17 21:00:35', 0, 0, '2025-09-15 10:22:57'),
(3, 15, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:36', 0, 0, '2025-09-15 10:22:57'),
(3, 16, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:37', 0, 0, '2025-09-15 10:22:57'),
(3, 17, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:37', 0, 0, '2025-09-15 10:22:57'),
(3, 18, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:37', 0, 0, '2025-09-15 10:22:57'),
(3, 19, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:38', 0, 0, '2025-09-15 10:22:57'),
(3, 20, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:38', 0, 0, '2025-09-15 10:22:57'),
(3, 21, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:38', 0, 0, '2025-09-15 10:22:57'),
(3, 22, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:39', 0, 0, '2025-09-15 10:22:57'),
(3, 23, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:40', 0, 0, '2025-09-15 10:22:57'),
(3, 24, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:41', 0, 0, '2025-09-15 10:22:57'),
(3, 25, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:42', 0, 0, '2025-09-15 10:22:57'),
(3, 26, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:39', 0, 0, '2025-09-15 10:22:57'),
(3, 27, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:39', 0, 0, '2025-09-15 10:22:57'),
(3, 28, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:40', 0, 0, '2025-09-15 10:22:57'),
(3, 29, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:41', 0, 0, '2025-09-15 10:22:57'),
(3, 30, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:42', 0, 0, '2025-09-15 10:22:57'),
(3, 31, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:39', 0, 0, '2025-09-15 10:22:57'),
(3, 32, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:40', 0, 0, '2025-09-15 10:22:57'),
(3, 33, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:40', 0, 0, '2025-09-15 10:22:58'),
(3, 34, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:41', 0, 0, '2025-09-15 10:22:58'),
(3, 35, 'img/strawberry.png', 40, 0, 11, 0, 'water', '2025-09-17 21:00:41', 0, 0, '2025-09-15 10:22:58'),
(4, 1, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:18', 0, 0, NULL),
(4, 2, 'img/tridentlord.png', 0, 20, 0, 82, 'feed', '2025-09-15 17:51:09', 0, 0, NULL),
(4, 3, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:19', 0, 0, NULL),
(4, 4, 'img/tridentlord.png', 0, 20, 0, 82, 'feed', '2025-09-15 17:51:09', 0, 0, NULL),
(4, 5, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:20', 0, 0, NULL),
(4, 6, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:20', 0, 0, NULL),
(4, 7, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:21', 0, 0, NULL),
(4, 8, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:21', 0, 0, NULL),
(4, 9, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:21', 0, 0, NULL),
(4, 10, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:22', 0, 0, NULL),
(4, 11, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:22', 0, 0, NULL),
(4, 12, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:23', 0, 0, NULL),
(4, 13, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:24', 0, 0, NULL),
(4, 14, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:24', 0, 0, NULL),
(4, 15, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:24', 0, 0, NULL),
(4, 16, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:26', 0, 0, NULL),
(4, 17, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:23', 0, 0, NULL),
(4, 18, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:23', 0, 0, NULL),
(4, 19, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:25', 0, 0, NULL),
(4, 20, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:25', 0, 0, NULL),
(4, 21, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:26', 0, 0, NULL),
(4, 22, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:26', 0, 0, NULL),
(4, 23, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:28', 0, 0, NULL),
(4, 24, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:29', 0, 0, NULL),
(4, 25, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:30', 0, 0, NULL),
(4, 26, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:27', 0, 0, NULL),
(4, 27, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:27', 0, 0, NULL),
(4, 28, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:28', 0, 0, NULL),
(4, 29, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:29', 0, 0, NULL),
(4, 30, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:30', 0, 0, NULL),
(4, 31, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:27', 0, 0, NULL),
(4, 32, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:27', 0, 0, NULL),
(4, 33, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:28', 0, 0, NULL),
(4, 34, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:29', 0, 0, NULL),
(4, 35, 'img/carrot.png', 30, 0, 12, 0, 'water', '2025-09-15 17:51:30', 0, 0, NULL),
(9, 1, 'img/cartofi.png', 60, 0, 14, 0, NULL, NULL, 0, 0, NULL),
(9, 2, 'img/cartofi.png', 60, 0, 14, 0, NULL, NULL, 0, 0, NULL),
(9, 3, 'img/cartofi.png', 60, 0, 14, 0, NULL, NULL, 0, 0, NULL),
(9, 6, 'img/cartofi.png', 60, 0, 14, 0, NULL, NULL, 0, 0, NULL),
(9, 7, 'img/cartofi.png', 60, 0, 14, 0, NULL, NULL, 0, 0, NULL),
(9, 8, 'img/cartofi.png', 60, 0, 14, 0, NULL, NULL, 0, 0, NULL),
(10, 2, 'img/sirena.png', 0, 20, 0, 94, 'feed', '2025-09-17 21:01:00', 0, 0, NULL),
(10, 4, 'img/tridentlord.png', 0, 20, 0, 94, 'feed', '2025-09-17 21:01:00', 0, 0, NULL),
(10, 6, 'img/rosie.png', 1, 0, 0, 0, 'harvest', NULL, 0, 0, NULL),
(10, 7, 'img/strawberry.png', 5, 0, 0, 0, 'harvest', NULL, 0, 0, NULL),
(10, 8, 'img/rosie.png', 1, 0, 0, 0, 'harvest', NULL, 0, 0, NULL),
(10, 9, 'img/strawberry.png', 5, 0, 0, 0, 'harvest', NULL, 0, 0, NULL),
(10, 10, 'img/strawberry.png', 5, 0, 0, 0, 'harvest', NULL, 0, 0, NULL),
(11, 1, 'img/cartofi.png', 60, 0, 35, 0, 'water', NULL, 0, 0, NULL),
(11, 2, 'img/cartofi.png', 60, 0, 35, 0, 'water', NULL, 0, 0, NULL),
(11, 3, 'img/cartofi.png', 60, 0, 35, 0, 'water', NULL, 0, 0, NULL),
(11, 4, 'img/cartofi.png', 60, 0, 35, 0, 'water', NULL, 0, 0, NULL),
(11, 6, 'img/cartofi.png', 60, 0, 35, 0, 'water', NULL, 0, 0, NULL),
(11, 7, 'img/cartofi.png', 60, 0, 35, 0, 'water', NULL, 0, 0, NULL),
(11, 8, 'img/cartofi.png', 60, 0, 35, 0, 'water', NULL, 0, 0, NULL);

--
-- Indexuri pentru tabele eliminate
--

--
-- Indexuri pentru tabele `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `bank_deposits`
--
ALTER TABLE `bank_deposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexuri pentru tabele `bank_loans`
--
ALTER TABLE `bank_loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexuri pentru tabele `bank_loan_payments`
--
ALTER TABLE `bank_loan_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loan_id` (`loan_id`);

--
-- Indexuri pentru tabele `default_slots`
--
ALTER TABLE `default_slots`
  ADD PRIMARY KEY (`slot_number`);

--
-- Indexuri pentru tabele `farm_items`
--
ALTER TABLE `farm_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pair` (`sender_id`,`receiver_id`),
  ADD KEY `receiver_idx` (`receiver_id`);

--
-- Indexuri pentru tabele `helpers`
--
ALTER TABLE `helpers`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `profile_comments`
--
ALTER TABLE `profile_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_target_created` (`target_id`,`created_at`),
  ADD KEY `fk_profile_comments_author` (`author_id`);

--
-- Indexuri pentru tabele `slot_helpers`
--
ALTER TABLE `slot_helpers`
  ADD PRIMARY KEY (`owner_id`,`slot_number`,`helper_id`);

--
-- Indexuri pentru tabele `typing_status`
--
ALTER TABLE `typing_status`
  ADD PRIMARY KEY (`sender_id`,`receiver_id`);

--
-- Indexuri pentru tabele `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexuri pentru tabele `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`user_id`,`achievement_id`);

--
-- Indexuri pentru tabele `user_barn`
--
ALTER TABLE `user_barn`
  ADD PRIMARY KEY (`user_id`,`slot_number`);

--
-- Indexuri pentru tabele `user_barn_info`
--
ALTER TABLE `user_barn_info`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexuri pentru tabele `user_helpers`
--
ALTER TABLE `user_helpers`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `helper_id` (`helper_id`);

--
-- Indexuri pentru tabele `user_last_helpers`
--
ALTER TABLE `user_last_helpers`
  ADD PRIMARY KEY (`owner_id`);

--
-- Indexuri pentru tabele `user_plants`
--
ALTER TABLE `user_plants`
  ADD PRIMARY KEY (`user_id`,`slot_number`);

--
-- Indexuri pentru tabele `user_slots`
--
ALTER TABLE `user_slots`
  ADD PRIMARY KEY (`user_id`,`slot_number`);

--
-- Indexuri pentru tabele `user_slot_states`
--
ALTER TABLE `user_slot_states`
  ADD PRIMARY KEY (`user_id`,`slot_number`),
  ADD KEY `idx_user_slot` (`user_id`,`slot_number`);

--
-- AUTO_INCREMENT pentru tabele eliminate
--

--
-- AUTO_INCREMENT pentru tabele `bank_deposits`
--
ALTER TABLE `bank_deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT pentru tabele `bank_loans`
--
ALTER TABLE `bank_loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pentru tabele `bank_loan_payments`
--
ALTER TABLE `bank_loan_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pentru tabele `farm_items`
--
ALTER TABLE `farm_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pentru tabele `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pentru tabele `helpers`
--
ALTER TABLE `helpers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pentru tabele `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=640;

--
-- AUTO_INCREMENT pentru tabele `profile_comments`
--
ALTER TABLE `profile_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pentru tabele `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constrângeri pentru tabele eliminate
--

--
-- Constrângeri pentru tabele `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD CONSTRAINT `fr_receiver_fk` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fr_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constrângeri pentru tabele `profile_comments`
--
ALTER TABLE `profile_comments`
  ADD CONSTRAINT `fk_profile_comments_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_profile_comments_target` FOREIGN KEY (`target_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constrângeri pentru tabele `user_slots`
--
ALTER TABLE `user_slots`
  ADD CONSTRAINT `user_slots_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
