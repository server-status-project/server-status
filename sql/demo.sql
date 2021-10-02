-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 02, 2021 at 07:08 PM
-- Server version: 10.5.12-MariaDB-1:10.5.12+maria~focal
-- PHP Version: 7.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_status_beta`
--

-- --------------------------------------------------------

--
-- Table structure for table `queue_notify`
--

CREATE TABLE `queue_notify` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `retries` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `queue_task`
--

CREATE TABLE `queue_task` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `template_data1` text COLLATE utf8_czech_ci DEFAULT NULL,
  `template_data2` text COLLATE utf8_czech_ci DEFAULT NULL,
  `created_time` int(11) NOT NULL,
  `completed_time` int(11) DEFAULT NULL,
  `num_errors` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `group_id`) VALUES
(1, 'Web', 'The main web server', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `services_groups`
--

CREATE TABLE `services_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visibility` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services_status`
--

CREATE TABLE `services_status` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Dumping data for table `services_status`
--

INSERT INTO `services_status` (`id`, `service_id`, `status_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `services_subscriber`
--

CREATE TABLE `services_subscriber` (
  `comboID` int(11) NOT NULL,
  `subscriberIDFK` int(11) NOT NULL,
  `serviceIDFK` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting`, `value`) VALUES
('cron_server_ip', ''),
('dbConfigVersion', 'Version2Beta7'),
('google_recaptcha', 'no'),
('google_recaptcha_secret', ''),
('google_recaptcha_sitekey', ''),
('mailer', 'Server Status DEV'),
('mailer_email', 'sysadmin@example.com'),
('name', 'Server Status DEV'),
('notifyUpdates', 'yes'),
('php_mailer', 'no'),
('php_mailer_host', ''),
('php_mailer_pass', ''),
('php_mailer_path', ''),
('php_mailer_port', ''),
('php_mailer_secure', 'no'),
('php_mailer_smtp', 'no'),
('php_mailer_user', ''),
('subscribe_email', 'no'),
('subscribe_telegram', 'no'),
('tg_bot_api_token', ''),
('tg_bot_username', ''),
('title', 'Server Status DEV'),
('url', 'http://server-status.localhost');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `title` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `text` text COLLATE utf8_czech_ci NOT NULL,
  `time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`id`, `type`, `title`, `text`, `time`, `end_time`, `user_id`) VALUES
(1, 0, 'Power shut down', 'We are having problems with the power grid. The issue is under investigation, should be up and running soon.', 1633114722, 0, 1),
(2, 1, 'Network problems', 'Network routers are still having a rough time after the power loss, hopefully they will be up and running soon.', 1633164722, 0, 1),
(3, 3, 'We are UP!', 'Everything is up and running again!', 1633194722, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `subscriberID` int(11) NOT NULL,
  `typeID` tinyint(1) NOT NULL,
  `userID` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `token` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `user` int(11) NOT NULL,
  `expire` int(11) NOT NULL,
  `data` varchar(80) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `username` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `surname` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `password_hash` char(64) COLLATE utf8_czech_ci NOT NULL,
  `password_salt` char(64) COLLATE utf8_czech_ci NOT NULL,
  `permission` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `name`, `surname`, `password_hash`, `password_salt`, `permission`, `active`) VALUES
(1, 'sysadmin@example.com', 'sysadmin', 'Sysadmin', 'DEV', '94977f202777689254e899f3846116b76e4d0652e2a03e9b2c3ef28891936e74', '1700003474615891853019f5.99130930', 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `queue_notify`
--
ALTER TABLE `queue_notify`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `queue_task`
--
ALTER TABLE `queue_task`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services_groups`
--
ALTER TABLE `services_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services_status`
--
ALTER TABLE `services_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `services_subscriber`
--
ALTER TABLE `services_subscriber`
  ADD PRIMARY KEY (`comboID`),
  ADD UNIQUE KEY `unique_subscription` (`subscriberIDFK`,`serviceIDFK`),
  ADD KEY `serviceIDFK` (`serviceIDFK`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD UNIQUE KEY `setting` (`setting`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`subscriberID`),
  ADD UNIQUE KEY `userID` (`userID`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`token`),
  ADD KEY `user` (`user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `queue_notify`
--
ALTER TABLE `queue_notify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `queue_task`
--
ALTER TABLE `queue_task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services_groups`
--
ALTER TABLE `services_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services_status`
--
ALTER TABLE `services_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services_subscriber`
--
ALTER TABLE `services_subscriber`
  MODIFY `comboID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `subscriberID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `services_status`
--
ALTER TABLE `services_status`
  ADD CONSTRAINT `service_id` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `status_id` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`);

--
-- Constraints for table `services_subscriber`
--
ALTER TABLE `services_subscriber`
  ADD CONSTRAINT `services_subscriber_ibfk_1` FOREIGN KEY (`subscriberIDFK`) REFERENCES `subscribers` (`subscriberID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `services_subscriber_ibfk_2` FOREIGN KEY (`serviceIDFK`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `status`
--
ALTER TABLE `status`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tokens`
--
ALTER TABLE `tokens`
  ADD CONSTRAINT `user` FOREIGN KEY (`user`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
