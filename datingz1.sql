-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gazdă: 127.0.0.1
-- Timp de generare: aug. 07, 2025 la 10:54 PM
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
(11, 3, 4, 'pending', '2025-08-06 14:46:50', NULL),
(12, 1, 8, 'pending', '2025-08-07 17:45:19', NULL);

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
(375, 1, 4, 'ma apuc de panoul de la shop si cel de la schimbare slot poate le pot face', '2025-08-07 16:44:55', 0);

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
(1, 'quatro', 'serverboost93@gmail.com', '$2y$10$XUP9QK9AU/EgETee1NVvKemxG2xpWWKWzCbg1.AkRIWjmKLbdeLDW', 'Romania', 'Suceava', 31, 'masculin', 'descrieea mea se fura asa ca :)))', 'photo_20250806_144906_20.jpg', '2025-07-21 21:56:14', 'pending', 1, '2025-08-07 23:42:20', 1000000, 100),
(2, 'quatro93', 'quatro93@gmail.com', '$2y$10$D2nwnAS7tkNt7MZTzuB2jOGSt8dWPFXbxHMQy/6hpJT/FRPlce3B2', 'Romania', 'Suceava', 31, 'masculin', 'Miauuuuuuu 😅', '', '2025-07-22 00:47:31', '', 0, '2025-08-07 20:58:54', 0, 0),
(3, 'Catalina', 'albacazapada@gmail.com', '$2y$10$oSYlVjpRUK3.cEJRMinID.DkjFTAzxSVykk0V88SefTegqkIeHsy.', 'Romania', 'suceava', 23, 'feminin', '', 'photo_20250806_144758_94.jpeg,photo_20250806_164731_41.jpeg', '2025-08-04 08:42:45', 'pending,pending', 0, '2025-08-07 16:05:42', 1000000000, 100),
(4, 'Nexus', 'ionut.fidirovici2@gmail.com', '$2y$10$KuZ/MTsqYGSjN9ebtmsfa.6C/ROwsTEcUcWBSpWCGib8p9pjp31t2', 'Romania', 'Radauti', 18, 'masculin', 'Sunt smecher', 'photo_20250806_172705_30.png,photo_20250805_212441_56.png', '2025-08-05 19:24:00', 'pending,pending,pend', 0, '2025-08-07 19:42:01', 1000000000, 200),
(5, 'test', 'test@gmail.com', '$2y$10$VblPlxQqXNqXsbHtHL/Qd.6oIVy8ZcICXbXyl/zWVi4c7uBafmmTW', 'Romania', 'suceava', 31, 'masculin', NULL, NULL, '2025-08-06 09:19:36', 'pending', 0, NULL, 0, 0),
(6, 'test2', 'test2@gmail.com', '$2y$10$UEnt3nQ4YTUCLBXQqd9N1.EBh0qsWtiE9oHyMpU5BBLpwknjyOwR6', 'Romania', 'suceava', 33, 'masculin', NULL, NULL, '2025-08-06 10:08:10', 'pending', 0, '2025-08-06 15:43:54', 0, 0),
(7, 'Test3', 'teat3@gmail.com', '$2y$10$J5YGNEPj1eQ1bu3LRKNTY.JoUa5RrhYuJIOOYqJFxJ0x6XdwHz8fG', 'Romania', 'România', 33, 'masculin', NULL, NULL, '2025-08-06 10:35:38', 'pending', 0, '2025-08-06 15:45:23', 0, 0),
(8, 'Olena', 'frecja@spoko.pl', '$2y$10$aHKKJu9sFIjhWNLQTz80xetYaccbZRjVO3SLCDy0uKjzhFvp9um9.', 'Polska', 'Gdańsk', 25, 'feminin', NULL, NULL, '2025-08-07 17:41:08', 'pending', 0, '2025-08-07 20:43:23', 1000000000, 5000);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user_slots`
--

CREATE TABLE `user_slots` (
  `user_id` int(11) NOT NULL,
  `slot_number` int(11) NOT NULL,
  `unlocked` tinyint(1) NOT NULL DEFAULT 0,
  `required_level` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexuri pentru tabele `user_slots`
--
ALTER TABLE `user_slots`
  ADD PRIMARY KEY (`user_id`,`slot_number`);

--
-- AUTO_INCREMENT pentru tabele eliminate
--

--
-- AUTO_INCREMENT pentru tabele `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pentru tabele `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=376;

--
-- AUTO_INCREMENT pentru tabele `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- Constrângeri pentru tabele `user_slots`
--
ALTER TABLE `user_slots`
  ADD CONSTRAINT `user_slots_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
