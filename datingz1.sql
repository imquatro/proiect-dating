-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gazdă: 127.0.0.1
-- Timp de generare: aug. 06, 2025 la 10:33 PM
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
(2, 1, 3, 'accepted', '2025-08-04 09:36:33', '2025-08-04 09:37:21'),
(3, 3, 2, 'accepted', '2025-08-04 13:41:44', '2025-08-04 13:43:37'),
(4, 4, 1, 'accepted', '2025-08-05 19:33:14', '2025-08-05 19:33:29'),
(5, 4, 2, 'accepted', '2025-08-05 19:33:25', '2025-08-06 13:23:44'),
(6, 6, 1, 'accepted', '2025-08-06 12:06:37', '2025-08-06 12:42:14'),
(7, 6, 3, 'accepted', '2025-08-06 12:06:38', '2025-08-06 14:48:07'),
(8, 6, 4, 'pending', '2025-08-06 12:06:39', NULL),
(9, 6, 7, 'accepted', '2025-08-06 12:06:39', '2025-08-06 12:44:42'),
(10, 2, 1, 'accepted', '2025-08-06 13:25:39', '2025-08-06 13:27:07'),
(11, 3, 4, 'pending', '2025-08-06 14:46:50', NULL);

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
(199, 2, 4, 'Bau', '2025-08-06 15:47:58', 0),
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
(212, 1, 3, 'bine uite pe acasa tu ?', '2025-08-06 20:05:33', 1);

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
  `money` int(11) NOT NULL DEFAULT 0,
  `gold` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `country`, `city`, `age`, `gender`, `description`, `gallery`, `created_at`, `gallery_status`, `is_admin`, `last_active`, `money`, `gold`) VALUES
(1, 'quatro', 'serverboost93@gmail.com', '$2y$10$XUP9QK9AU/EgETee1NVvKemxG2xpWWKWzCbg1.AkRIWjmKLbdeLDW', 'Romania', 'Suceava', 31, 'masculin', 'descrieea mea se fura asa ca :)))', 'photo_20250806_144906_20.jpg', '2025-07-21 21:56:14', 'pending', 1, '2025-08-06 23:25:02', 0, 0),
(2, 'quatro93', 'quatro93@gmail.com', '$2y$10$D2nwnAS7tkNt7MZTzuB2jOGSt8dWPFXbxHMQy/6hpJT/FRPlce3B2', 'Romania', 'Suceava', 31, 'masculin', 'Miauuuuuuu 😅', '', '2025-07-22 00:47:31', '', 0, '2025-08-06 19:11:14', 0, 0),
(3, 'Catalina', 'albacazapada@gmail.com', '$2y$10$oSYlVjpRUK3.cEJRMinID.DkjFTAzxSVykk0V88SefTegqkIeHsy.', 'Romania', 'suceava', 23, 'feminin', '', 'photo_20250806_164731_41.jpeg,photo_20250806_144758_94.jpeg', '2025-08-04 08:42:45', 'pending,pending', 0, '2025-08-06 23:06:01', 0, 0),
(4, 'Nexus', 'ionut.fidirovici2@gmail.com', '$2y$10$KuZ/MTsqYGSjN9ebtmsfa.6C/ROwsTEcUcWBSpWCGib8p9pjp31t2', 'Romania', 'Radauti', 18, 'masculin', 'Sunt smecher', 'photo_20250806_172705_30.png,photo_20250805_212441_56.png', '2025-08-05 19:24:00', 'pending,pending,pend', 0, '2025-08-06 18:48:34', 0, 0),
(5, 'test', 'test@gmail.com', '$2y$10$VblPlxQqXNqXsbHtHL/Qd.6oIVy8ZcICXbXyl/zWVi4c7uBafmmTW', 'Romania', 'suceava', 31, 'masculin', NULL, NULL, '2025-08-06 09:19:36', 'pending', 0, NULL, 0, 0),
(6, 'test2', 'test2@gmail.com', '$2y$10$UEnt3nQ4YTUCLBXQqd9N1.EBh0qsWtiE9oHyMpU5BBLpwknjyOwR6', 'Romania', 'suceava', 33, 'masculin', NULL, NULL, '2025-08-06 10:08:10', 'pending', 0, '2025-08-06 15:43:54', 0, 0),
(7, 'Test3', 'teat3@gmail.com', '$2y$10$J5YGNEPj1eQ1bu3LRKNTY.JoUa5RrhYuJIOOYqJFxJ0x6XdwHz8fG', 'Romania', 'România', 33, 'masculin', NULL, NULL, '2025-08-06 10:35:38', 'pending', 0, '2025-08-06 15:45:23', 0, 0);

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
-- AUTO_INCREMENT pentru tabele eliminate
--

--
-- AUTO_INCREMENT pentru tabele `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pentru tabele `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- AUTO_INCREMENT pentru tabele `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
