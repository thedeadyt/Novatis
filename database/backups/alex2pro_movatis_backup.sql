-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql-alex2pro.alwaysdata.net
-- Generation Time: Oct 04, 2025 at 09:15 PM
-- Server version: 10.11.14-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alex2pro_movatis`
--
CREATE DATABASE IF NOT EXISTS `alex2pro_movatis` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `alex2pro_movatis`;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `section` varchar(50) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `created_at`) VALUES
(1, 'Développement Web', 'dev-web', '💻', '2025-09-22 20:00:07'),
(2, 'Design Graphique', 'design', '🎨', '2025-09-22 20:00:07'),
(3, 'Rédaction', 'redaction', '✍️', '2025-09-22 20:00:07'),
(4, 'Marketing', 'marketing', '📈', '2025-09-22 20:00:07'),
(5, 'Traduction', 'traduction', '🌍', '2025-09-22 20:00:07');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('order','message','system','payment','service','review') DEFAULT 'system',
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `frequency` varchar(20) DEFAULT 'immediate',
  `channels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '["email"]' CHECK (json_valid(`channels`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `status` enum('pending','in_progress','delivered','completed','cancelled') DEFAULT 'pending',
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `portfolio`
--

CREATE TABLE `portfolio` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `delivery_days` int(11) DEFAULT 3,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','paused','draft') DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `orders_count` int(11) DEFAULT 0,
  `rating` decimal(2,1) DEFAULT 0.0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `pseudo` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `phone_verified` tinyint(1) DEFAULT 0,
  `account_status` enum('active','suspended','deleted') DEFAULT 'active',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `pseudo`, `email`, `password`, `role`, `avatar`, `bio`, `phone`, `rating`, `is_verified`, `created_at`, `last_login`, `email_verified`, `phone_verified`, `account_status`, `updated_at`, `two_factor_enabled`, `two_factor_secret`) VALUES
(1, 'Alexis Rodrigues Dos Reis', 'thedead', 'contact.alex2.dev@gmail.com', '$2y$10$4mv70By1IPza0/zM3GUkWOHNWayM6spTJOtg8bz1LfNOWd3qA2ImO', 'user', NULL, NULL, NULL, 0.0, 0, '2025-09-30 18:49:31', NULL, 0, 0, 'active', '2025-09-30 18:49:31', 0, NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `tr_user_login_history` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.last_login != OLD.last_login OR OLD.last_login IS NULL THEN
        INSERT INTO login_history (user_id, ip_address, user_agent, login_time)
        VALUES (NEW.id, @user_ip, @user_agent, NEW.last_login);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_display`
--

CREATE TABLE `user_display` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `theme` varchar(20) DEFAULT 'light',
  `sidebar_collapsed` tinyint(1) DEFAULT 0,
  `dashboard_layout` varchar(20) DEFAULT 'grid',
  `items_per_page` int(11) DEFAULT 10,
  `show_tutorials` tinyint(1) DEFAULT 1,
  `custom_css` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_integrations`
--

CREATE TABLE `user_integrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_name` varchar(50) NOT NULL,
  `is_connected` tinyint(1) DEFAULT 0,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `token_expires_at` timestamp NULL DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 0,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `dark_mode` tinyint(1) DEFAULT 0,
  `language` varchar(10) DEFAULT 'fr',
  `timezone` varchar(50) DEFAULT 'Europe/Paris',
  `currency` varchar(3) DEFAULT 'EUR',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_privacy`
--

CREATE TABLE `user_privacy` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_visibility` varchar(20) DEFAULT 'public',
  `show_email` tinyint(1) DEFAULT 0,
  `show_phone` tinyint(1) DEFAULT 0,
  `allow_search_engines` tinyint(1) DEFAULT 1,
  `data_sharing` tinyint(1) DEFAULT 0,
  `analytics_tracking` tinyint(1) DEFAULT 1,
  `marketing_emails` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_security`
--

CREATE TABLE `user_security` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `backup_codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`backup_codes`)),
  `last_password_change` timestamp NULL DEFAULT current_timestamp(),
  `password_expires_at` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `security_questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`security_questions`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `device_info` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_activity` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_settings_summary`
-- (See below for the actual view)
--
CREATE TABLE `user_settings_summary` (
`user_id` int(11)
,`email_notifications` tinyint(1)
,`push_notifications` tinyint(1)
,`sms_notifications` tinyint(1)
,`dark_mode` tinyint(1)
,`language` varchar(10)
,`timezone` varchar(50)
,`two_factor_enabled` tinyint(1)
,`last_password_change` timestamp
,`theme` varchar(20)
,`sidebar_collapsed` tinyint(1)
,`profile_visibility` varchar(20)
,`data_sharing` tinyint(1)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_login_time` (`login_time`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `idx_messages_order` (`order_id`),
  ADD KEY `idx_messages_read` (`is_read`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_notifications` (`user_id`,`is_read`,`created_at`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_type` (`user_id`,`notification_type`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `idx_orders_buyer` (`buyer_id`),
  ADD KEY `idx_orders_seller` (`seller_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Indexes for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewee_id` (`reviewee_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_services_user` (`user_id`),
  ADD KEY `idx_services_category` (`category_id`),
  ADD KEY `idx_services_status` (`status`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_support_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `pseudo` (`pseudo`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_status` (`account_status`),
  ADD KEY `idx_users_last_login` (`last_login`);

--
-- Indexes for table `user_display`
--
ALTER TABLE `user_display`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_integrations`
--
ALTER TABLE `user_integrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_service` (`user_id`,`service_name`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Indexes for table `user_privacy`
--
ALTER TABLE `user_privacy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_security`
--
ALTER TABLE `user_security`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_token` (`session_token`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_display`
--
ALTER TABLE `user_display`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_integrations`
--
ALTER TABLE `user_integrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_privacy`
--
ALTER TABLE `user_privacy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_security`
--
ALTER TABLE `user_security`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `user_settings_summary`
--
DROP TABLE IF EXISTS `user_settings_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`alex2pro_alex`@`%` SQL SECURITY DEFINER VIEW `user_settings_summary`  AS SELECT `up`.`user_id` AS `user_id`, `up`.`email_notifications` AS `email_notifications`, `up`.`push_notifications` AS `push_notifications`, `up`.`sms_notifications` AS `sms_notifications`, `up`.`dark_mode` AS `dark_mode`, `up`.`language` AS `language`, `up`.`timezone` AS `timezone`, `us`.`two_factor_enabled` AS `two_factor_enabled`, `us`.`last_password_change` AS `last_password_change`, `ud`.`theme` AS `theme`, `ud`.`sidebar_collapsed` AS `sidebar_collapsed`, `upr`.`profile_visibility` AS `profile_visibility`, `upr`.`data_sharing` AS `data_sharing` FROM (((`user_preferences` `up` left join `user_security` `us` on(`up`.`user_id` = `us`.`user_id`)) left join `user_display` `ud` on(`up`.`user_id` = `ud`.`user_id`)) left join `user_privacy` `upr` on(`up`.`user_id` = `upr`.`user_id`)) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD CONSTRAINT `portfolio_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `portfolio_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
--
-- Database: `alex2pro_site`
--
CREATE DATABASE IF NOT EXISTS `alex2pro_site` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `alex2pro_site`;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('À faire','En cours','Terminé') DEFAULT 'À faire',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deadline` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_notes`
--

CREATE TABLE `project_notes` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projets`
--

CREATE TABLE `projets` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `annee` varchar(10) NOT NULL,
  `type` varchar(100) NOT NULL,
  `image` text NOT NULL,
  `description_courte` text NOT NULL,
  `description_detaillee` text NOT NULL,
  `lien` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projets`
--

INSERT INTO `projets` (`id`, `nom`, `annee`, `type`, `image`, `description_courte`, `description_detaillee`, `lien`) VALUES
(16, 'Site web toptt', '2025', 'Développement Web, Sécurité, Base de donnée', '/asset/img/projets/68af9cc2ee685_LogoTOPTT.png', 'Refonte complète d’un site depuis zéro avec une interface moderne, une base de données optimisée et des mécanismes de sécurité permettant une gestion autonome par les administrateurs.', 'On a réalisé la refonte complète d’un site web depuis zéro, en concevant une interface moderne, ergonomique et responsive pour offrir une expérience utilisateur optimale. La base de données a été repensée et optimisée afin de garantir une gestion efficace et fiable des informations. Des mécanismes de sécurité renforcés ont été mis en place, incluant l’authentification des utilisateurs et le contrôle des accès. Le site permet désormais une gestion autonome par les administrateurs, tout en offrant une application web plus performante, sécurisée et évolutive, adaptée aux besoins actuels des utilisateurs.', 'https://www.toptt.fr'),
(18, 'MovieMi', '2025', 'Développement Web, Base de donnée', 'data:image/png;base64,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', 'MovieMi est un site web où les utilisateurs peuvent voter pour leur top 3 de films. Les votes sont enregistrés en base de données et utilisés pour créer un classement global.', 'MovieMi est un projet réalisé en cours, développé avec PHP et une base de données MySQL. Il permet aux utilisateurs de voter pour leurs 3 films préférés, et enregistre ces votes pour générer un classement dynamique.\r\n\r\nLes informations sur les films (titre, résumé, image) sont affichées depuis une base de données, et l’interface a été conçue pour être claire, responsive et facile à utiliser. Ce projet nous a permis de travailler la gestion des données, l’interaction utilisateur et la logique de vote dans un environnement complet.', 'https://bouvy.alwaysdata.net/index.php');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `line1` varchar(255) DEFAULT NULL,
  `line2` varchar(255) DEFAULT NULL,
  `line3` varchar(255) DEFAULT NULL,
  `line4` varchar(255) DEFAULT NULL,
  `line5` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `line1`, `line2`, `line3`, `line4`, `line5`) VALUES
(1, 'site_vitrine_simple', '<Alex²/> ➜ run site_vitrine', '> Création de site vitrine simple', '> Responsive, rapide, moderne', '> Adapté à votre activité', '> Statut : DISPONIBLE'),
(2, 'site_vitrine_personnalise', '<Alex²/> ➜ run site_vitrine_personnalise', '> Création de site vitrine personnalisé', '> Design sur mesure & fonctionnalités avancées', '> Accompagnement UX/UI', '> Statut : DISPONIBLE'),
(3, 'site_portfolio', '<Alex²/> ➜ run site_portfolio', '> Création de site personnel ou portfolio', '> Présentation claire de vos compétences', '> Intégration LinkedIn, contact', '> Statut : DISPONIBLE'),
(4, 'maintenance_mensuelle', '<Alex²/> ➜ run maintenance_mensuel', '> Maintenance mensuelle', '> Sauvegardes, mises à jour, support', '> Monitoring performance & sécurité', '> Statut : DISPONIBLE'),
(5, 'seo_ponctuel', '<Alex²/> ➜ run seo_ponctuel', '> Audit SEO ponctuel', '> Optimisation technique & sémantique', '> Amélioration des balises & contenus', '> Statut : DISPONIBLE'),
(6, 'seo_mensuel', '<Alex²/> ➜ run seo_mensuel', '> Suivi SEO mensuel', '> Analyse de performance & recommandations', '> Reporting & ajustements continus', '> Statut : DISPONIBLE'),
(7, 'conseil_ux_ui', '<Alex²/> ➜ run conseil_ux_ui', '> Conseil en UX/UI ou stratégie digitale', '> Recommandations ergonomiques', '> Accompagnement sur l’expérience utilisateur', '> Statut : DISPONIBLE'),
(8, 'identite_visuelle', '<Alex²/> ➜ run identite_visuelle', '> Création d’identité visuelle', '> Logo, palette de couleurs, typographie', '> Kit complet pour le web & print', '> Statut : DISPONIBLE');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'AlexisRodrigues', 'rodriguesdosreisalexis.pro@gmail.com', '$2y$10$6MRhyF5EUxlN1lC2B4rNG.NgWFB.vugtC1B7rYh9IZcKi/0PSLn62', '2025-08-21 13:06:39'),
(2, 'AlexB', 'alexbouvy11122005@gmail.com', '$2y$10$WbLihO5D07gGgxUy4JMj4OLCXKcrUcagYrcFJlHfycQIrZD8FRWnu', '2025-08-27 09:19:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `project_notes`
--
ALTER TABLE `project_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projets`
--
ALTER TABLE `projets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
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
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `project_notes`
--
ALTER TABLE `project_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `projets`
--
ALTER TABLE `projets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_notes`
--
ALTER TABLE `project_notes`
  ADD CONSTRAINT `project_notes_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
