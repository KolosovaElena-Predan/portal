-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Мар 19 2026 г., 07:07
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `lab_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `is_active`) VALUES
(1, 'Контроллеры', 'controllers', 1, 1),
(2, 'Датчики', 'sensors', 2, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `clientsupport`
--

CREATE TABLE `clientsupport` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `support_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `device`
--

CREATE TABLE `device` (
  `id` int(11) NOT NULL,
  `device_type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `device_type`
--

CREATE TABLE `device_type` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT 1,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `price` double NOT NULL,
  `stock` int(11) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `img_url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `device_type`
--

INSERT INTO `device_type` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `is_new`, `img_url`) VALUES
(0, 1, 'ff', '', 0, 0, 0, ''),
(1, 1, 'Контроллер управления системой отопления', 'Устройство предназначено для автоматического регулирования температуры воздуха в административных, производственных и жилых зданиях, что позволяет экономить энергетические ресурсы.\r\n                         \r\nЦЕННСТЬ ДЛЯ ПОТРЕБИТЕЛЯ\r\n - Снижение затрат энергетических ресурсов, снижение углеродного следа (в среднем 35%).\r\n - Уменьшение платы за тепловую энергию конечным потребителем\r\n - Соблюдение требований САНПИН и повышение комфортности пребывания в помещениях в период отопительного сезона (снижение отклонения целевой температуры в помещении до ±3 °С)\r\n - Ускорение и упрощение процесса интеграции и эксплуатации контроллера в существующем технологическом процессе, что приведёт к снижению затрат на привлечение/обучение персонала и уменьшению времени простоев оборудования\r\n - Полностью отечественный продукт, доступное техническое обслуживание и поддержка', 1, 0, 0, 'img/image-2.png'),
(2, 2, 'Датчик температуры', 'Датчик для измерения температуры воздуха.', 350, 100, 80, ''),
(3, 1, 'Датчик', 'Умное что-то', 400, 60, 1, '');

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `datetime`) VALUES
(2, 'Запуск производства контроллера управления отоплением', 'Предприятие начало серийное производство обновлённой модели контроллера с улучшенными характеристиками и расширенным функционалом.\r\nНовая модель полностью совместима с ранее установленным оборудованием и может быть интегрирована в существующие системы без дополнительных затрат.', '2027-01-28 16:31:58'),
(3, 'Участие в международной выставке', 'Наши специалисты представили новейшие разработки в области автоматизации систем отопления на крупнейшей отраслевой выставке.\r\nС 25 по 27 января 2026 года команда ООО МИП \"НПЦ ПИТиА\" приняла участие в международной выставке «Энергоэффективность и Энергосбережение 2026», прошедшей в N.', '2026-02-04 01:36:09'),
(4, '100-тый контроллер установлен и успешно работает', 'Сотый контроллер управления отоплением установлен на объекте заказчика.', '2026-02-25 01:36:09'),
(5, 'Проведён семинар по монтажу и обслуживанию контроллеров', 'Наши инженеры провели обучающий семинар для монтажников и технических специалистов управляющих компаний.', '2026-02-13 01:40:18');

-- --------------------------------------------------------

--
-- Структура таблицы `news_images`
--

CREATE TABLE `news_images` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_main` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `news_images`
--

INSERT INTO `news_images` (`id`, `news_id`, `image_url`, `sort_order`, `is_main`) VALUES
(1, 2, 'img/products/device1.jpg', 0, 1),
(2, 4, 'img/news/news1.png', 0, 1),
(3, 2, 'img/news/news1.png', 0, 0),
(4, 3, 'img/news/news1.png', 0, 1),
(5, 5, 'img/news/news1.png', 0, 1),
(7, 2, 'img/products/device1.jpg', 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT 1,
  `name` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `full_description` longtext DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `is_slider` tinyint(1) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `orders_count` int(11) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive','draft') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `short_description`, `full_description`, `base_price`, `stock`, `is_new`, `is_slider`, `views_count`, `orders_count`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Контроллер управления системой отопления\r\n', 'Устройство предназначено для автоматического регулирования температуры воздуха в административных, производственных и жилых зданиях, что позволяет экономить энергетические ресурсы.', 'Устройство предназначено для автоматического регулирования температуры воздуха в административных, производственных и жилых зданиях, что позволяет экономить энергетические ресурсы.\r\n                         \r\nЦЕННСТЬ ДЛЯ ПОТРЕБИТЕЛЯ\r\n - Снижение затрат энергетических ресурсов, снижение углеродного следа (в среднем 35%).\r\n - Уменьшение платы за тепловую энергию конечным потребителем\r\n - Соблюдение требований САНПИН и повышение комфортности пребывания в помещениях в период отопительного сезона (снижение отклонения целевой температуры в помещении до ±3 °С)\r\n - Ускорение и упрощение процесса интеграции и эксплуатации контроллера в существующем технологическом процессе, что приведёт к снижению затрат на привлечение/обучение персонала и уменьшению времени простоев оборудования\r\n - Полностью отечественный продукт, доступное техническое обслуживание и поддержка', 45000.00, 10, 0, 1, 0, 0, 0, 'active', '2026-02-19 17:12:03', '2026-02-26 14:58:30'),
(3, 2, 'Датчик температуры', 'Высокоточный датчик для измерения температуры', 'Полное и подробное описание датчика температуры с диапазоном измерений', 3500.00, 50, 1, 1, 0, 0, 2, 'active', '2026-02-26 14:37:54', '2026-03-12 14:43:41'),
(4, 1, 'Модуль расширения', 'Дополнительный модуль для системы управления', 'Описание модуля расширения с количеством входов/выходов', 12000.00, 20, 0, 1, 0, 0, 3, 'active', '2026-02-26 14:37:54', '2026-02-26 14:37:54');

-- --------------------------------------------------------

--
-- Структура таблицы `product_configurations`
--

CREATE TABLE `product_configurations` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `characteristics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`characteristics`)),
  `price` decimal(10,2) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `product_configurations`
--

INSERT INTO `product_configurations` (`id`, `product_id`, `name`, `characteristics`, `price`, `sort_order`) VALUES
(1, 1, 'Базовая', '{\"Точность\": \"0.5%\", \"Диапазон\": \"-50..+200°C\"}', 45000.00, 0),
(2, 1, 'Расширенная', '{\"Точность\": \"0.1%\", \"Диапазон\": \"-100..+300°C\"}', 65000.00, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `product_files`
--

CREATE TABLE `product_files` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `product_files`
--

INSERT INTO `product_files` (`id`, `product_id`, `group_name`, `file_name`, `file_url`, `file_size`, `sort_order`) VALUES
(1, 1, 'Инструкции', 'Инструкция по эксплуатации', 'i.docx', NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_main` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `sort_order`, `is_main`) VALUES
(1, 1, 'img/products/device1.jpg', 0, 1),
(2, 1, 'img/products/device1_2.jpg', 0, 0),
(3, 3, 'img/products/device2.png', 0, 1),
(4, 4, 'img/products/device3.png', 0, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `product_modifications`
--

CREATE TABLE `product_modifications` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `product_modifications`
--

INSERT INTO `product_modifications` (`id`, `product_id`, `group_name`, `options`, `sort_order`) VALUES
(1, 1, 'Количество входов', '[\r\n  {\r\n    \"name\": \"Один\",\r\n    \"price\": 100,\r\n    \"properties\": [\r\n      {\"name\": \"Крутой\", \"price\": 50},\r\n      {\"name\": \"Очень крутой\", \"price\": 100}\r\n    ]\r\n  },\r\n  {\r\n    \"name\": \"два\",\r\n    \"price\": 200,\r\n    \"properties\": [\r\n      {\"name\": \"Крутой\", \"price\": 100},\r\n      {\"name\": \"Очень крутой\", \"price\": 200}\r\n    ]\r\n  }\r\n]', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `product_schemes`
--

CREATE TABLE `product_schemes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `product_schemes`
--

INSERT INTO `product_schemes` (`id`, `product_id`, `title`, `description`, `image_url`, `sort_order`) VALUES
(1, 1, 'Схема подключения первая', 'Утное описание схемы', 'img/products/aa.png', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `request`
--

CREATE TABLE `request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_type_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `device_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','processed','closed') NOT NULL,
  `datetime` datetime NOT NULL,
  `type` enum('q','r','s') NOT NULL,
  `user_clientsupport_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `request`
--

INSERT INTO `request` (`id`, `user_id`, `device_type_id`, `product_id`, `device_id`, `message`, `status`, `datetime`, `type`, `user_clientsupport_id`) VALUES
(18, 2, 1, 1, NULL, '{\"quantity\":1,\"address\":\"г. Пример, ул. Пример, д. 1, кв. 1\",\"device_name\":\"Контроллер управления системой отопления\"}', 'new', '2025-12-12 01:17:46', 'r', NULL),
(21, 12, 1, 1, NULL, '{\"quantity\":1,\"address\":\"г. Пример, ул. Пример, д. 1, кв. 2\",\"device_name\":\"Контроллер управления системой отопления\"}', 'processed', '2025-12-12 19:55:23', 'r', NULL),
(22, 2, 1, 1, NULL, '{\"quantity\":1,\"address\":\"ул. Ква, дом 5, кв 232\",\"device_name\":\"Контроллер управления системой отопления\"}', 'new', '2025-12-16 12:45:53', 'r', NULL),
(23, 12, 3, 3, NULL, '{\"quantity\":1,\"address\":\"ул. А, дом ААА, квартира ААААААА\",\"product_name\":\"Датчик температуры\"}', 'new', '2026-02-27 10:48:24', 'r', NULL),
(26, 16, 3, 3, NULL, '{\"quantity\":1,\"price\":\"3500.00\",\"address\":\"fffff\",\"product_name\":\"Датчик температуры\",\"user_name\":\"Николь\",\"user_email\":\"\"}', 'new', '2026-02-28 03:13:29', 'r', NULL),
(27, 12, NULL, NULL, NULL, 'вапролдж', 'new', '2026-02-28 04:59:38', 'q', NULL),
(30, 12, NULL, NULL, NULL, '{\"type\":\"service\",\"service_id\":1,\"service_name\":\"Монтаж оборудования\",\"price\":0,\"ordered_at\":\"2026-02-27 21:26:28\"}', 'new', '2026-02-28 05:26:28', 's', NULL),
(34, 12, NULL, NULL, NULL, '{\"type\":\"service\",\"service_id\":3,\"service_name\":\"Консультация инженера\",\"price\":0,\"ordered_at\":\"2026-02-28 02:28:42\"}', 'new', '2026-02-28 10:28:42', 's', NULL),
(46, 12, NULL, NULL, NULL, 'квеапиротл', 'processed', '2026-02-28 12:33:03', 'q', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Название услуги',
  `short_description` text DEFAULT NULL COMMENT 'Краткое описание для превью',
  `full_description` longtext DEFAULT NULL COMMENT 'Полное описание',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Стоимость',
  `img_url` varchar(500) DEFAULT '' COMMENT 'Изображение услуги',
  `duration` varchar(50) DEFAULT NULL COMMENT 'Срок оказания (опционально)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Отображать на сайте',
  `sort_order` int(11) DEFAULT 0 COMMENT 'Порядок сортировки',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `services`
--

INSERT INTO `services` (`id`, `name`, `short_description`, `full_description`, `price`, `img_url`, `duration`, `is_active`, `sort_order`, `created_at`, `updated_at`, `status`) VALUES
(1, 'Монтаж оборудования', 'Профессиональная установка и настройка систем отопления', NULL, 15000.00, 'img/serv/s0.png', '1-3 дня', 1, 1, '2026-02-26 14:13:47', '2026-02-27 16:18:42', '1'),
(2, 'Техническое обслуживание', 'Ежегодное сервисное обслуживание контроллеров', NULL, 5000.00, 'img/serv/s2.png', 'по графику', 1, 2, '2026-02-26 14:13:47', '2026-02-27 16:19:05', '2'),
(3, 'Консультация инженера', 'Выезд специалиста для аудита системы', NULL, 3000.00, 'img/serv/s1.png', '2-4 часа', 1, 3, '2026-02-26 14:13:47', '2026-02-27 16:19:27', '');

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `name` text NOT NULL,
  `login` text DEFAULT NULL,
  `password` text DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `role` enum('client','admin','support_specialist','guest') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `email`, `name`, `login`, `password`, `is_verified`, `verification_token`, `token_expires_at`, `role`) VALUES
(2, 'ex2@ex.com', 'Иванов Иван Иванович', 'ex2', '$2y$10$q4olpAwvBOPQkK9tIMJj7O.DnmbV9RQiRvj1s1dP7H098J1Vkdd1C', 0, NULL, NULL, 'client'),
(5, 'sup@sup.ex', 'sup', 'supp3', '$2y$10$H0vcikedd.GrQjFLFJdIBe20b7nhgVF3LIyes6LyZJtpU/vwYzoou', 0, NULL, NULL, 'support_specialist'),
(10, 'admin@ex.com', 'admin1', 'admin1', '$2y$10$RJORs1nPkX9mgRdCYsO3ze3WJZIyFB7xJ2UpOzaTcuk92HlrYpQQG', 0, NULL, NULL, 'admin'),
(11, 'sup@ex2.ex', 'Васильев Василий Васильевич', 'supp2', '$2y$10$SbcptdZJpiNEitZrwmAE6.HMO3eUrWfD5lSwXE2OKemCUWjLUGsuC', 0, NULL, NULL, 'support_specialist'),
(12, 'user@gmail.com', 'Иванов Иван', 'log3', '$2y$10$xhb.OYlnWqKpDFi6EIVlJOJIPwcPgvt.UWqNJ3.0tlNk.k0w0sWPm', 0, NULL, NULL, 'client'),
(16, 'nik@nik.com', 'Николь', 'nik', '$2y$10$hdCESDe1UzxCdpLRVf2rAOeE6cZ1PUeY59ZTZptjZQHXU6a6XyiBS', 0, NULL, NULL, 'client');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_slug` (`slug`);

--
-- Индексы таблицы `clientsupport`
--
ALTER TABLE `clientsupport`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_client_support` (`client_id`,`support_id`),
  ADD KEY `support_id` (`support_id`);

--
-- Индексы таблицы `device`
--
ALTER TABLE `device`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`) USING BTREE,
  ADD KEY `fk_userdevice_type` (`device_type_id`);

--
-- Индексы таблицы `device_type`
--
ALTER TABLE `device_type`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `news_images`
--
ALTER TABLE `news_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news` (`news_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_catalog` (`status`,`is_new`,`stock`,`created_at`),
  ADD KEY `idx_slider` (`is_slider`,`sort_order`),
  ADD KEY `idx_category` (`category_id`);

--
-- Индексы таблицы `product_configurations`
--
ALTER TABLE `product_configurations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `product_files`
--
ALTER TABLE `product_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `product_modifications`
--
ALTER TABLE `product_modifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `product_schemes`
--
ALTER TABLE `product_schemes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `user_clientsupport_id` (`user_clientsupport_id`),
  ADD KEY `device_type_id` (`device_type_id`) USING BTREE,
  ADD KEY `fk_request_device` (`device_id`);

--
-- Индексы таблицы `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active_order` (`is_active`,`sort_order`,`created_at`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `clientsupport`
--
ALTER TABLE `clientsupport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `device`
--
ALTER TABLE `device`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `news_images`
--
ALTER TABLE `news_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `product_configurations`
--
ALTER TABLE `product_configurations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `product_files`
--
ALTER TABLE `product_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `product_modifications`
--
ALTER TABLE `product_modifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `product_schemes`
--
ALTER TABLE `product_schemes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `request`
--
ALTER TABLE `request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT для таблицы `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `clientsupport`
--
ALTER TABLE `clientsupport`
  ADD CONSTRAINT `clientsupport_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `clientsupport_ibfk_2` FOREIGN KEY (`support_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `device`
--
ALTER TABLE `device`
  ADD CONSTRAINT `fk_userdevice_type` FOREIGN KEY (`device_type_id`) REFERENCES `device_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_userdevice_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `news_images`
--
ALTER TABLE `news_images`
  ADD CONSTRAINT `fk_news_images` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_configurations`
--
ALTER TABLE `product_configurations`
  ADD CONSTRAINT `product_configurations_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_files`
--
ALTER TABLE `product_files`
  ADD CONSTRAINT `product_files_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_modifications`
--
ALTER TABLE `product_modifications`
  ADD CONSTRAINT `product_modifications_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_schemes`
--
ALTER TABLE `product_schemes`
  ADD CONSTRAINT `product_schemes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `fk_request_device` FOREIGN KEY (`device_id`) REFERENCES `device` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_request_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_ibfk_3` FOREIGN KEY (`user_clientsupport_id`) REFERENCES `clientsupport` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
