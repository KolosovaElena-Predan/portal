-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Июн 12 2026 г., 14:52
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
-- Структура таблицы `chat_files`
--

CREATE TABLE `chat_files` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` enum('user','support') NOT NULL DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `chat_files`
--

INSERT INTO `chat_files` (`id`, `message_id`, `file_name`, `file_url`, `file_size`, `uploaded_by`, `created_at`) VALUES
(1, 47, 'ER.png', 'uploads/chat/1781265229_87aa71cef81eca4e.png', 125735, 'user', '2026-06-12 20:53:49'),
(2, 48, 'screencapture-localhost-portal-admin-product-edit-php-2026-05-31-23_16_32.png', 'uploads/chat/1781265306_ea8b028861db1b27.png', 773591, 'user', '2026-06-12 20:55:06'),
(3, 49, 'ER.png', '/portal/uploads/chat/1781265440_78f9f5888e30c774.png', 125735, 'user', '2026-06-12 20:57:20'),
(4, 50, 'obyavka1_2026.docx', '/portal/uploads/chat/1781265452_393e2b5130ff064a.docx', 15292, 'user', '2026-06-12 20:57:32'),
(5, 51, '2e741a96218533a2e85730e93aed5b13.jpg', '/portal/uploads/chat/1781267246_ed5537c49cf75601.jpg', 80826, 'support', '2026-06-12 21:27:26');

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
-- Структура таблицы `directions`
--

CREATE TABLE `directions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `directions`
--

INSERT INTO `directions` (`id`, `name`, `description`, `sort_order`, `created_at`) VALUES
(1, 'Возобновляемая энергетика', 'Исследования в области солнечной, ветровой и геотермальной энергетики. Разработка эффективных систем генерации и хранения энергии из возобновляемых источников.', 1, '2026-04-03 04:03:13'),
(2, 'Энергоэффективность', 'Технологии снижения потребления энергоресурсов в промышленности и ЖКХ. Энергетический аудит и оптимизация процессов.', 2, '2026-04-03 04:03:13'),
(3, 'Умные сети', 'Разработка интеллектуальных систем управления энергосетями. Мониторинг, аналитика и автоматизация энергетических процессов.', 3, '2026-04-03 04:03:13'),
(4, 'Накопители энергии', 'Исследования и разработка систем хранения энергии: литий-ионные батареи, суперконденсаторы, водородные технологии.', 4, '2026-04-03 04:03:13'),
(5, 'Электромобильность', 'Разработка зарядной инфраструктуры, систем управления батареями электромобилей, интеграция с энергосетями.', 5, '2026-04-03 04:03:13');

-- --------------------------------------------------------

--
-- Структура таблицы `education`
--

CREATE TABLE `education` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('course','lecture','workshop','internship') DEFAULT 'course',
  `duration` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `img_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `education`
--

INSERT INTO `education` (`id`, `title`, `description`, `type`, `duration`, `start_date`, `img_url`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Курс \"Солнечная энергетика\"', 'Основы фотоэлектричества, типы солнечных панелей, проектирование СЭС, расчет окупаемости. Практические занятия на оборудовании лаборатории.', 'course', '36 часов', '2024-03-01', NULL, 1, 1, '2026-04-03 04:03:13'),
(2, 'Семинар \"Энергоаудит\"', 'Методы проведения энергоаудита, работа с тепловизором и анализаторами, составление отчетов и рекомендаций.', 'workshop', '16 часов', '2024-04-15', NULL, 1, 2, '2026-04-03 04:03:13'),
(3, 'Лекция \"Ветроэнергетика\"', 'Принципы работы ветрогенераторов, типы конструкций, выбор места установки, экономические аспекты.', 'lecture', '4 часа', '2024-03-20', NULL, 1, 3, '2026-04-03 04:03:13'),
(4, 'Стажировка \"Проектирование СЭС\"', 'Интенсивная практическая подготовка по проектированию солнечных электростанций с использованием специализированного ПО.', 'internship', '72 часа', '2024-05-01', NULL, 1, 4, '2026-04-03 04:03:13');

-- --------------------------------------------------------

--
-- Структура таблицы `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `img_url` varchar(500) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `description`, `manufacturer`, `model`, `year`, `img_url`, `is_available`, `sort_order`, `created_at`) VALUES
(1, 'Солнечный симулятор', 'Установка для тестирования солнечных панелей и фотоэлектрических модулей. Мощность освещения до 1000 Вт/м²', 'SolarTech', 'STS-3000', 2021, NULL, 1, 1, '2026-04-03 04:03:13'),
(2, 'Тепловизор', 'Профессиональный тепловизор для диагностики зданий и электроустановок. Разрешение 640x480', 'FLIR', 'E8-XT', 2022, NULL, 1, 2, '2026-04-03 04:03:13'),
(3, 'Анализатор качества электроэнергии', 'Многоканальный прибор для измерения параметров электрической сети, гармоник, провалов напряжения', 'Fluke', '435-II', 2020, NULL, 1, 3, '2026-04-03 04:03:13'),
(4, 'Батарейный тестер', 'Стенд для тестирования литий-ионных аккумуляторов. Диапазон токов 0-100А, напряжений 0-100В', 'Arbin', 'BT2000', 2021, NULL, 1, 4, '2026-04-03 04:03:13'),
(5, 'Ветроизмерительная мачта', 'Мачта высотой 10м с анемометрами и датчиками направления ветра для исследования ветрового потенциала', 'Vaisala', 'WMT700', 2019, NULL, 1, 5, '2026-04-03 04:03:13'),
(6, 'Инвертор гибридный', 'Гибридный инвертор мощностью 10 кВт для тестирования систем накопления энергии', 'SMA', 'Sunny Island', 2022, NULL, 1, 6, '2026-04-03 04:03:13');

-- --------------------------------------------------------

--
-- Структура таблицы `lab_projects`
--

CREATE TABLE `lab_projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `full_description` text DEFAULT NULL,
  `img_url` varchar(500) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','completed','planned') DEFAULT 'active',
  `budget` decimal(15,2) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `lab_projects`
--

INSERT INTO `lab_projects` (`id`, `name`, `short_description`, `full_description`, `img_url`, `start_date`, `end_date`, `status`, `budget`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Создание производственной линии мелкосерийного производства электронных устройств', 'Проект заключается в проработке технологии создания на территории Забайкальского края производственных мощностей для проектирования и производства электронных устройств, в том числе программируемых и используемых технологий искусственного интеллекта', 'Проект заключается в проработке технологии создания на территории Забайкальского края производственных мощностей для проектирования и производства электронных устройств, в том числе программируемых и используемых технологий искусственного интеллекта', 'img/projects/project_69ec484236480.jpg', NULL, NULL, 'active', NULL, 1, '2026-04-03 04:03:13', '2026-05-12 23:56:53'),
(2, 'Контроллер управления системой отопления', 'Система автоматического управления энергопотреблением', 'Разработка интеллектуальной системы управления энергопотреблением в жилых зданиях. Система анализирует поведение пользователей, погодные условия и тарифы на электроэнергию для оптимизации расходов.', 'img/projects/project_69ec5062b2301.png', NULL, NULL, 'active', NULL, 2, '2026-04-03 04:03:13', '2026-05-13 00:01:55');

-- --------------------------------------------------------

--
-- Структура таблицы `lab_services`
--

CREATE TABLE `lab_services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `lab_services`
--

INSERT INTO `lab_services` (`id`, `name`, `description`, `price`, `duration`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Энергоаудит', 'Комплексное обследование объекта с выявлением возможностей повышения энергоэффективности. Включает тепловизионное обследование, анализ потребления, рекомендации по модернизации.в ыфвфы вфывфыв', 25000.00, '5-10 дней', 1, 1, '2026-04-03 04:03:13'),
(2, 'Проектирование СЭС', 'Разработка проектной документации для солнечных электростанций любой мощности. Подбор оборудования, расчет окупаемости, согласование.', 50000.00, '14-30 дней', 1, 2, '2026-04-03 04:03:13'),
(3, 'Монтаж оборудования', 'Профессиональный монтаж солнечных панелей, ветрогенераторов, систем накопления энергии. Гарантия на работы 3 года.', NULL, 'по договору', 1, 3, '2026-04-03 04:03:13'),
(4, 'Консультации', 'Экспертные консультации по вопросам энергетики, выбора оборудования, оптимизации энергопотребления.', 5000.00, '1-2 часа', 1, 4, '2026-04-03 04:03:13'),
(5, 'Обучение', 'Проведение обучающих семинаров и тренингов для специалистов предприятий по вопросам энергоэффективности и ВИЭ.', 30000.00, '3 дня', 1, 5, '2026-04-03 04:03:13');

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `section` enum('lab','mip') NOT NULL DEFAULT 'lab',
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `section`, `datetime`) VALUES
(2, 'Запуск производства контроллера управления отоплением', 'Предприятие начало производство обновлённой модели контроллера с улучшенными характеристиками и расширенным функционалом.\r\nНовая модель полностью совместима с ранее установленным оборудованием и может быть интегрирована в существующие системы без дополнительных затрат.', 'mip', '2027-01-28 16:31:58'),
(3, 'Участие в международной выставке', 'Наши специалисты представили новейшие разработки в области автоматизации систем отопления на крупнейшей отраслевой выставке.\r\nС 25 по 27 января 2026 года команда ООО МИП \"НПЦ ПИТиА\" приняла участие в международной выставке «Энергоэффективность и Энергосбережение 2026», прошедшей в N.', 'mip', '2026-02-04 01:36:09'),
(5, 'Проведён семинар по монтажу и обслуживанию контроллеров', 'Наши инженеры провели обучающий семинар для монтажников и технических специалистов управляющих компаний.', 'mip', '2026-02-13 01:40:18');

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
(4, 3, 'img/news/news1.png', 0, 1),
(18, 5, 'img/news/temp_69ec55755b9e8.webp', 0, 1),
(20, 5, 'img/news/temp_69ec5a8af19a7.webp', 1, 0),
(21, 2, 'img/news/temp_6a0333473b552.png', 0, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('status_change','new_message','order_created','service_ordered','stock_available') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #71', '/mip/lk_user.php', 1, '2026-04-23 21:40:25'),
(2, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #71', '/portal/mip/lk_user.php', 1, '2026-04-23 21:40:34'),
(3, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #71', '/portal/mip/lk_user.php', 1, '2026-04-23 21:40:38'),
(4, 12, 'status_change', 'Статус заявки #71 изменён', 'Статус вашей заявки изменён на: Закрыт\nКомментарий: Надоел', '/portal/mip/lk_user.php', 1, '2026-04-23 21:40:50'),
(5, 12, 'status_change', 'Статус заявки #71 изменён', 'Статус вашей заявки изменён на: В обработке\nКомментарий: А неееет', '/portal/mip/lk_user.php#request-71', 1, '2026-04-23 22:56:04'),
(6, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #71', '/portal/mip/lk_user.php#request-71', 1, '2026-04-23 22:56:14'),
(7, 12, 'status_change', 'Статус заявки #71 изменён', 'Статус вашей заявки изменён на: В обработке\nКомментарий: sdfxgchvjbknlm;,', '/portal/mip/lk_user.php#request-71', 1, '2026-04-23 23:09:17'),
(8, 12, 'status_change', 'Статус заявки #71 изменён', 'Статус вашей заявки изменён на: В обработке\nКомментарий: явыявыявыяв', '/portal/mip/lk_user.php#request-71', 1, '2026-04-23 23:10:59'),
(9, 12, 'status_change', 'Статус заявки #71 изменён', 'Статус вашей заявки изменён на: Закрыт\nКомментарий: явчасрпмоилтдьжбэю', '/portal/mip/lk_user.php#request-71-status', 1, '2026-04-23 23:31:35'),
(10, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #71', '/portal/mip/lk_user.php#request-71-chat', 1, '2026-04-23 23:31:40'),
(11, 5, 'new_message', 'Новое сообщение в чате', 'Пользователь оставил сообщение в заявке #71', '/portal/mip/lk_support.php#request-71-chat', 0, '2026-04-24 03:43:06'),
(12, 5, 'new_message', 'Новое сообщение в чате', 'Пользователь оставил сообщение в заявке #71', '/portal/mip/lk_support.php#request-71-chat', 0, '2026-04-24 03:45:55'),
(13, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #71', '/portal/mip/lk_user.php#request-71-chat', 1, '2026-04-24 03:51:04'),
(14, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #71', '/portal/mip/lk_user.php#request-71-chat', 1, '2026-04-24 04:06:13'),
(15, 12, 'status_change', 'Статус заявки #71 изменён', 'Статус вашей заявки изменён на: Новый\nКомментарий: dgsgsdgsd', '/portal/mip/lk_user.php#request-71-status', 1, '2026-04-24 11:07:30'),
(16, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #71', '/portal/mip/lk_user.php#request-71-chat', 1, '2026-04-24 13:23:57'),
(17, 5, 'new_message', 'Новое сообщение в чате', 'Пользователь оставил сообщение в заявке #77', '/portal/mip/lk_support.php#request-77-chat', 0, '2026-04-25 03:30:36'),
(18, 12, 'status_change', 'Статус заявки #77 изменён', 'Статус вашей заявки изменён на: Отменён', '/portal/mip/lk_user.php#request-77-status', 1, '2026-04-25 10:27:56'),
(19, 12, 'status_change', 'Статус заявки #77 изменён', 'Статус вашей заявки изменён на: Отменён', '/portal/mip/lk_user.php#request-77-status', 1, '2026-04-25 11:29:42'),
(20, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #77', '/portal/mip/lk_user.php#request-77-chat', 1, '2026-04-25 11:31:19'),
(21, 12, 'status_change', 'Статус заявки #77 изменён', 'Статус вашей заявки изменён на: Отклонён', '/portal/mip/lk_user.php#request-77-status', 1, '2026-04-25 11:32:50'),
(22, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #78', '/portal/mip/lk_user.php#request-78-chat', 1, '2026-04-25 15:49:29'),
(23, 5, 'new_message', 'Новое сообщение в чате', 'Пользователь оставил сообщение в заявке #81', '/portal/mip/lk_support.php#request-81-chat', 0, '2026-05-12 23:36:18'),
(24, 43, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки ответил на ваше обращение #82', '/portal/mip/lk_user.php#request-82-chat', 1, '2026-05-14 00:08:34'),
(25, 43, 'status_change', 'Статус заявки #82 изменён', 'Статус вашей заявки изменён на: В обработке', '/portal/mip/lk_user.php#request-82-status', 1, '2026-05-14 00:08:42'),
(26, 43, 'status_change', 'Статус заявки #82 изменён', 'Статус вашей заявки изменён на: Закрыт', '/portal/mip/lk_user.php#request-82-status', 0, '2026-05-14 00:11:54'),
(27, 43, 'status_change', 'Статус заявки #82 изменён', 'Статус вашей заявки изменён на: В обработке\nКомментарий: Собираем заказ', '/portal/mip/lk_user.php#request-82-status', 0, '2026-05-14 00:13:15'),
(28, 12, 'status_change', 'Статус заявки #81 изменён', 'Статус вашей заявки изменён на: В обработке\nКомментарий: В сборке', '/portal/mip/lk_user.php#request-81-status', 1, '2026-05-20 02:31:37'),
(29, 12, 'status_change', 'Товар добавлен в лист ожидания', 'Товар \"Контроллер управления системой отопления\" добавлен в лист ожидания. Мы уведомим вас, когда товар появится на складе.', '/portal/mip/lk_user.php#request-92', 1, '2026-06-11 16:40:02'),
(30, 12, 'status_change', 'Товар добавлен в лист ожидания', 'Товар \"Контроллер управления системой отопления\" добавлен в лист ожидания. Мы уведомим вас, когда товар появится на складе.', '/portal/mip/lk_user.php#request-93', 1, '2026-06-11 16:45:47'),
(31, 12, 'status_change', 'Товар добавлен в лист ожидания', 'Товар \"Контроллер управления системой отопления\" добавлен в лист ожидания. Мы уведомим вас, когда товар появится на складе.', '/portal/mip/lk_user.php#request-94', 1, '2026-06-11 16:50:26'),
(32, 12, 'status_change', 'Товар добавлен в лист ожидания', 'Товар \"Контроллер управления системой отопления\" добавлен в лист ожидания. Мы уведомим вас, когда товар появится на складе.', '/portal/mip/lk_user.php#request-95', 1, '2026-06-11 16:53:38'),
(33, 12, 'status_change', 'Товар удалён из листа ожидания', 'Вы отменили ожидание товара.', '/portal/mip/lk_user.php', 1, '2026-06-11 17:03:20'),
(34, 12, 'status_change', 'Товар удалён из листа ожидания', 'Вы отменили ожидание товара.', '/portal/mip/lk_user.php', 1, '2026-06-11 17:03:22'),
(35, 12, 'status_change', 'Товар добавлен в лист ожидания', 'Товар \"Контроллер управления системой отопления\" в количестве 1 шт. добавлен в лист ожидания. Мы уведомим вас, когда товар появится на складе.', '/portal/mip/lk_user.php#request-96', 1, '2026-06-11 17:04:04'),
(36, 12, 'status_change', 'Товар удалён из листа ожидания', 'Вы отменили ожидание товара.', '/portal/mip/lk_user.php', 1, '2026-06-11 17:21:31'),
(37, 12, 'status_change', 'Товар добавлен в лист ожидания', 'Товар \"Контроллер управления системой отопления\" в количестве 10 шт. добавлен в лист ожидания. Мы уведомим вас, когда товар появится на складе.', '/portal/mip/lk_user.php#request-97', 1, '2026-06-11 17:22:05'),
(38, 12, 'status_change', 'Товар добавлен в лист ожидания', 'Товар \"Контроллер управления системой отопления\" в количестве 16 шт. добавлен в лист ожидания. Мы уведомим вас, когда товар появится на складе.', '/portal/mip/lk_user.php#request-98', 1, '2026-06-11 18:04:44'),
(39, 12, 'status_change', 'Товар удалён из листа ожидания', 'Вы отменили ожидание товара.', '/portal/mip/lk_user.php', 1, '2026-06-11 18:13:12'),
(40, 12, 'status_change', 'Товар удалён из листа ожидания', 'Вы отменили ожидание товара.', '/portal/mip/lk_user.php', 1, '2026-06-11 18:13:14'),
(41, 12, 'status_change', 'Товар удалён из листа ожидания', 'Вы отменили ожидание товара.', '/portal/mip/lk_user.php', 1, '2026-06-11 18:37:53'),
(42, 12, 'status_change', 'Товар удалён из листа ожидания', 'Вы отменили ожидание товара.', '/portal/mip/lk_user.php', 1, '2026-06-11 19:31:11'),
(43, 12, 'stock_available', 'Товар \'Контроллер управления системой отопления\' поступил в наличие!', 'Запрошенное вами количество (7 шт.) теперь доступно для заказа в каталоге.', '/mip/product.php?id=1', 1, '2026-06-12 00:34:57'),
(44, 12, 'stock_available', 'Товар \'Контроллер управления системой отопления\' поступил в наличие!', 'Запрошенное вами количество (4 шт.) теперь доступно для заказа в каталоге.', '/mip/product.php?id=1', 1, '2026-06-12 00:34:57'),
(45, 12, 'new_message', 'Новое сообщение в чате', 'Специалист поддержки отправил файл в заявке #110', '/portal/mip/lk_user.php#request-110-chat', 0, '2026-06-12 21:27:26');

-- --------------------------------------------------------

--
-- Структура таблицы `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `status` enum('active','draft','inactive') DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `is_made_to_order` tinyint(1) DEFAULT 0,
  `lead_time` varchar(100) DEFAULT '14-21 дней',
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

INSERT INTO `products` (`id`, `category_id`, `name`, `short_description`, `full_description`, `base_price`, `stock`, `is_made_to_order`, `lead_time`, `is_new`, `is_slider`, `views_count`, `orders_count`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Контроллер управления системой отопления', 'Устройство предназначено для автоматического регулирования температуры воздуха в административных, производственных и жилых зданиях, что позволяет экономить энергетические ресурсы.', 'Устройство предназначено для автоматического регулирования температуры воздуха в административных, производственных и жилых зданиях, что позволяет экономить энергетические ресурсы.\r\n                         \r\nЦЕННСТЬ ДЛЯ ПОТРЕБИТЕЛЯ\r\n - Снижение затрат энергетических ресурсов, снижение углеродного следа (в среднем 35%).\r\n - Уменьшение платы за тепловую энергию конечным потребителем\r\n - Соблюдение требований САНПИН и повышение комфортности пребывания в помещениях в период отопительного сезона (снижение отклонения целевой температуры в помещении до ±3 °С)\r\n - Ускорение и упрощение процесса интеграции и эксплуатации контроллера в существующем технологическом процессе, что приведёт к снижению затрат на привлечение/обучение персонала и уменьшению времени простоев оборудования\r\n - Полностью отечественный продукт, доступное техническое обслуживание и поддержка', 45000.00, 10, 0, '14-21 дней', 0, 1, 0, 0, 0, 'active', '2026-02-19 17:12:03', '2026-06-11 15:34:57'),
(3, 2, 'Датчик температуры', 'Высокоточный датчик для измерения температуры', 'Полное и подробное описание датчика температуры с диапазоном измерений', 3500.00, 48, 0, '14-21 дней', 1, 1, 0, 0, 2, 'active', '2026-02-26 14:37:54', '2026-06-12 12:38:33'),
(4, 1, 'Модуль расширения', 'Дополнительный модуль для системы управления', 'Описание модуля расширения с количеством входов/выходов', 12000.00, 20, 0, '14-21 дней', 0, 1, 0, 0, 3, 'active', '2026-02-26 14:37:54', '2026-06-11 05:39:28');

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
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_made_to_order` tinyint(1) DEFAULT 0,
  `lead_time` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_main` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `product_configurations`
--

INSERT INTO `product_configurations` (`id`, `product_id`, `name`, `characteristics`, `price`, `stock`, `is_made_to_order`, `lead_time`, `sort_order`, `is_main`, `created_at`, `updated_at`) VALUES
(1, 1, 'Базовая', '{\"Точность\":\"0.5%\",\"Диапазон\":\"-50..+200°C\",\"stock\":60,\"made_to_order\":false}', 45000.00, 0, 0, NULL, 0, 1, '2026-03-27 01:21:36', '2026-06-12 00:34:57'),
(2, 1, 'Расширенная', '{\"Точность\":\"0.1%\",\"Диапазон\":\"-100..+300°C\",\"stock\":75,\"made_to_order\":false}', 65000.00, 0, 0, NULL, 0, 0, '2026-03-27 01:21:36', '2026-06-12 00:34:57');

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
(3, 3, 'img/products/device2.png', 0, 1),
(4, 4, 'img/products/device3.png', 0, 1),
(35, 1, 'img/products/temp_6a033218befda.png', 0, 1),
(36, 1, 'img/products/temp_6a033218bf36d.png', 1, 0),
(37, 1, 'img/products/temp_6a033232147a2.png', 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `product_modifications`
--

CREATE TABLE `product_modifications` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `product_modifications`
--

INSERT INTO `product_modifications` (`id`, `product_id`, `group_name`, `options`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Количество входов', '[{\"name\":\"Один\",\"price\":100,\"description\":\"\",\"properties\":[]},{\"name\":\"два\",\"price\":200,\"description\":\"\",\"properties\":[]}]', 0, '2026-03-27 01:21:37', '2026-05-12 22:57:22');

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

-- --------------------------------------------------------

--
-- Структура таблицы `product_services`
--

CREATE TABLE `product_services` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `product_services`
--

INSERT INTO `product_services` (`id`, `product_id`, `service_id`, `is_active`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 3, 2, 1),
(5, 3, 3, 1);

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
  `status` enum('new','processed','closed','cancelled','waiting') NOT NULL DEFAULT 'new',
  `is_notified` tinyint(1) DEFAULT 0 COMMENT 'Отправлено ли уведомление о поступлении',
  `datetime` datetime NOT NULL,
  `type` enum('q','r','s','wl') NOT NULL DEFAULT 'q',
  `backorder_option` enum('partial','full_wait','cancel') DEFAULT NULL,
  `requested_quantity` int(11) DEFAULT 1,
  `shipped_quantity` int(11) DEFAULT 0,
  `is_backorder` tinyint(1) DEFAULT 0,
  `is_waiting_list` tinyint(1) DEFAULT 0,
  `waiting_list_option` varchar(50) DEFAULT NULL,
  `user_clientsupport_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `request`
--

INSERT INTO `request` (`id`, `user_id`, `device_type_id`, `product_id`, `device_id`, `message`, `status`, `is_notified`, `datetime`, `type`, `backorder_option`, `requested_quantity`, `shipped_quantity`, `is_backorder`, `is_waiting_list`, `waiting_list_option`, `user_clientsupport_id`, `latitude`, `longitude`) VALUES
(18, 2, 1, 1, NULL, '{\"quantity\":1,\"address\":\"г. Пример, ул. Пример, д. 1, кв. 1\",\"device_name\":\"Контроллер управления системой отопления\"}', 'new', 0, '2025-12-12 01:17:46', 'r', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(22, 2, 1, 1, NULL, '{\"quantity\":1,\"address\":\"ул. Ква, дом 5, кв 232\",\"device_name\":\"Контроллер управления системой отопления\"}', 'new', 0, '2025-12-16 12:45:53', 'r', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(34, 12, NULL, NULL, NULL, '{\"type\":\"service\",\"service_id\":3,\"service_name\":\"Консультация инженера\",\"price\":0,\"ordered_at\":\"2026-02-28 02:28:42\"}', 'new', 0, '2026-02-28 10:28:42', 's', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(81, 12, NULL, 1, NULL, '{\"quantity\":1,\"address\":\"г. Чита, ул. Новозоводская, д.X\",\"product_name\":\"Контроллер управления системой отопления\",\"product_id\":1,\"configuration\":{\"id\":\"1\",\"price\":45000},\"configuration_name\":\"Базовая\",\"modifications\":[],\"unit_price\":45000,\"line_total\":45000}', 'processed', 0, '2026-05-12 23:35:23', 'r', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(82, 43, NULL, NULL, NULL, '{\"type\":\"service\",\"service_id\":1,\"service_name\":\"Монтаж оборудования\",\"ordered_at\":\"2026-05-13 17:07:08\"}', 'processed', 0, '2026-05-14 00:07:08', 's', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(89, 12, NULL, 1, NULL, '{\"quantity\":1,\"address\":\"\",\"delivery_method\":\"pickup\",\"product_name\":\"Контроллер управления системой отопления\",\"product_id\":1,\"configuration\":{\"id\":\"2\",\"price\":65000},\"configuration_name\":\"Расширенная\",\"modifications\":[{\"group\":\"Количество входов\",\"variant\":{\"name\":\"два\",\"price\":200},\"property\":null}],\"unit_price\":65200,\"line_total\":65200}', 'new', 0, '2026-06-11 14:26:43', 'r', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(90, 12, NULL, NULL, NULL, '{\"type\":\"service\",\"service_id\":\"1\",\"service_name\":\"Монтаж оборудования\",\"price\":15000,\"product_id\":\"1\",\"delivery_method\":\"pickup\",\"ordered_at\":\"2026-06-11 07:26:43\"}', 'new', 0, '2026-06-11 14:26:43', 's', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(91, 12, NULL, NULL, NULL, '{\"type\":\"service\",\"service_id\":\"2\",\"service_name\":\"Техническое обслуживание\",\"price\":5000,\"product_id\":\"1\",\"delivery_method\":\"pickup\",\"ordered_at\":\"2026-06-11 07:26:43\"}', 'new', 0, '2026-06-11 14:26:43', 's', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(92, 12, NULL, 1, NULL, '{\"quantity\":1,\"product_name\":\"Контроллер управления системой отопления\",\"product_id\":1,\"configuration\":{\"id\":\"1\",\"price\":45000},\"configuration_name\":\"Базовая\",\"modifications\":[],\"unit_price\":45000,\"waiting_list_option\":\"notify_all\",\"stock_at_add\":10,\"is_made_to_order\":false,\"lead_time\":null}', '', 0, '2026-06-11 16:40:02', '', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(93, 12, NULL, 1, NULL, '{\"quantity\":10,\"product_name\":\"Контроллер управления системой отопления\",\"product_id\":1,\"configuration\":{\"id\":\"1\",\"price\":45000},\"configuration_name\":\"Базовая\",\"modifications\":[],\"unit_price\":45000,\"waiting_list_option\":\"notify_all\",\"stock_at_add\":10,\"is_made_to_order\":false,\"lead_time\":null}', '', 0, '2026-06-11 16:45:47', '', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL),
(102, 12, NULL, 1, NULL, '{\"quantity\":23,\"available_to_buy\":10,\"waiting_quantity\":13,\"address\":\"\",\"delivery_method\":\"pickup\",\"product_name\":\"Контроллер управления системой отопления\",\"product_id\":1,\"configuration\":{\"id\":\"1\",\"price\":45000},\"configuration_name\":\"Базовая\",\"modifications\":[],\"unit_price\":45000,\"line_total\":450000,\"is_made_to_order\":false,\"lead_time\":null,\"stock_at_order\":10}', 'processed', 0, '2026-06-11 19:31:00', 'r', NULL, 23, 10, 0, 0, NULL, NULL, NULL, NULL),
(107, 12, NULL, 1, NULL, '{\"quantity\":5,\"address\":\"\",\"delivery_method\":\"pickup\",\"product_name\":null,\"product_id\":\"1\",\"configuration\":{\"id\":\"1\",\"price\":45000},\"configuration_name\":\"Базовая\",\"modifications\":[],\"unit_price\":45000,\"line_total\":225000,\"is_made_to_order\":false,\"lead_time\":\"\",\"from_waiting_list\":true,\"waiting_list_request_id\":105}', 'new', 0, '2026-06-11 20:10:30', 'r', NULL, 5, 5, 0, 0, NULL, NULL, NULL, NULL),
(108, 12, NULL, 3, NULL, '{\"quantity\":30,\"product_name\":\"Датчик температуры\",\"product_id\":3,\"configuration\":null,\"configuration_name\":\"\",\"modifications\":[],\"unit_price\":3500,\"waiting_list_option\":\"notify_all\",\"stock_at_add\":50,\"is_made_to_order\":false,\"lead_time\":null}', 'waiting', 0, '2026-06-11 20:22:40', 'wl', NULL, 30, 0, 0, 0, 'notify_all', NULL, NULL, NULL),
(109, 12, NULL, 3, NULL, '{\"quantity\":50,\"address\":\"\",\"delivery_method\":\"pickup\",\"product_name\":\"Датчик температуры\",\"product_id\":\"3\",\"configuration\":null,\"configuration_name\":\"\",\"modifications\":[],\"unit_price\":3500,\"line_total\":175000,\"is_made_to_order\":false,\"lead_time\":\"\",\"from_waiting_list\":true,\"waiting_list_request_id\":108}', 'new', 0, '2026-06-11 20:23:03', 'r', NULL, 50, 50, 0, 0, NULL, NULL, NULL, NULL),
(110, 12, NULL, 3, NULL, '{\"quantity\":42,\"address\":\"\",\"delivery_method\":\"pickup\",\"product_name\":\"Датчик температуры\",\"product_id\":\"3\",\"configuration\":null,\"configuration_name\":\"\",\"modifications\":[],\"unit_price\":3500,\"line_total\":147000,\"is_made_to_order\":false,\"lead_time\":\"\",\"from_waiting_list\":true,\"waiting_list_request_id\":108}', 'new', 0, '2026-06-11 20:23:44', 'r', NULL, 42, 42, 0, 0, NULL, NULL, NULL, NULL),
(111, 12, NULL, 1, NULL, '{\"quantity\":6,\"product_name\":\"Контроллер управления системой отопления\",\"product_id\":1,\"configuration\":{\"id\":\"1\",\"price\":45000},\"configuration_name\":\"Базовая\",\"modifications\":[],\"unit_price\":45000,\"waiting_list_option\":\"notify_all\",\"stock_at_add\":5,\"is_made_to_order\":false,\"lead_time\":null}', 'waiting', 1, '2026-06-12 00:18:51', 'wl', NULL, 6, 0, 0, 0, 'notify_all', NULL, NULL, NULL),
(112, 12, NULL, 1, NULL, '{\"quantity\":4,\"product_name\":\"Контроллер управления системой отопления\",\"product_id\":1,\"configuration\":{\"id\":\"2\",\"price\":65000},\"configuration_name\":\"Расширенная\",\"modifications\":[],\"unit_price\":65000,\"waiting_list_option\":\"notify_all\",\"stock_at_add\":3,\"is_made_to_order\":false,\"lead_time\":null}', 'waiting', 1, '2026-06-12 00:19:54', 'wl', NULL, 4, 0, 0, 0, 'notify_all', NULL, NULL, NULL),
(113, 12, NULL, 3, NULL, '{\"quantity\":1,\"available_to_buy\":1,\"waiting_quantity\":0,\"address\":\"\",\"delivery_method\":\"pickup\",\"product_name\":\"Датчик температуры\",\"product_id\":3,\"configuration\":null,\"configuration_name\":\"\",\"modifications\":[],\"unit_price\":3500,\"line_total\":3500,\"is_made_to_order\":false,\"lead_time\":null,\"stock_at_order\":50,\"new_stock\":49}', 'new', 0, '2026-06-12 21:28:36', 'r', NULL, 1, 1, 0, 0, NULL, NULL, NULL, NULL),
(114, 12, NULL, 3, NULL, '{\"quantity\":100,\"product_name\":\"Датчик температуры\",\"product_id\":3,\"configuration\":null,\"configuration_name\":\"\",\"modifications\":[],\"unit_price\":3500,\"waiting_list_option\":\"notify_all\",\"stock_at_add\":49,\"is_made_to_order\":false,\"lead_time\":null}', 'waiting', 0, '2026-06-12 21:30:09', 'wl', NULL, 100, 0, 0, 0, 'notify_all', NULL, NULL, NULL),
(115, 12, NULL, 3, NULL, '{\"quantity\":1,\"available_to_buy\":1,\"waiting_quantity\":0,\"address\":\"\",\"delivery_method\":\"pickup\",\"product_name\":\"Датчик температуры\",\"product_id\":3,\"configuration\":null,\"configuration_name\":\"\",\"modifications\":[],\"unit_price\":3500,\"line_total\":3500,\"is_made_to_order\":false,\"lead_time\":null,\"stock_at_order\":49,\"new_stock\":48}', 'new', 0, '2026-06-12 21:38:33', 'r', NULL, 1, 1, 0, 0, NULL, NULL, NULL, NULL),
(116, 12, NULL, NULL, NULL, '{\"name\":\"\\u0418\\u0432\\u0430\\u043d\\u043e\\u0432 \\u0418\\u0432\\u0430\\u043d \\u0418\\u0432\\u0430\\u043d\\u043e\\u0432\\u0438\\u0447\",\"email\":\"user@gmail.com\",\"question\":\"\\u044b\\u0432\\u0447\\u0430\\u0441\\u043c\\u043f\\u0438\\u0440\\u043e\\u0442\\u043b\\u044c\\u0434\\u0431\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/149.0.0.0 Safari\\/537.36\",\"ip\":\"::1\"}', 'new', 0, '2026-06-12 21:42:06', 'q', NULL, 1, 0, 0, 0, NULL, NULL, NULL, NULL);

--
-- Триггеры `request`
--
DELIMITER $$
CREATE TRIGGER `after_request_status_update` AFTER UPDATE ON `request` FOR EACH ROW BEGIN
  -- Если статус изменился, добавляем запись в историю
  IF OLD.status != NEW.status THEN
    INSERT INTO `request_status_history` (`request_id`, `status`, `comment`, `created_at`)
    VALUES (NEW.id, NEW.status, CONCAT('Статус изменён с "', OLD.status, '" на "', NEW.status, '"'), NOW());
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `request_messages`
--

CREATE TABLE `request_messages` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `sender_type` enum('user','support') NOT NULL COMMENT 'Кто отправил',
  `sender_id` int(11) DEFAULT NULL COMMENT 'ID пользователя или поддержки',
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0 COMMENT 'Прочитано ли',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `request_messages`
--

INSERT INTO `request_messages` (`id`, `request_id`, `sender_type`, `sender_id`, `message`, `is_read`, `created_at`) VALUES
(1, 18, 'support', 5, 'Здравствуйте! Ваш заказ принят в работу.', 0, '2025-12-12 09:00:00'),
(2, 18, 'user', 2, 'Спасибо! Когда ожидать доставку?', 0, '2025-12-12 14:30:00'),
(3, 18, 'support', 5, 'Доставка запланирована на 15.12.2025', 0, '2025-12-12 15:00:00'),
(42, 81, 'user', 12, 'Вопрос', 0, '2026-05-12 23:36:17'),
(43, 82, 'support', 17, 'Вы когда-нибудь получите свой товар', 0, '2026-05-14 00:08:34'),
(44, 110, 'user', 12, 'ппппп', 0, '2026-06-12 18:21:47'),
(45, 109, 'user', 12, 'пппп', 0, '2026-06-12 18:44:28'),
(46, 110, 'user', 12, 'ааа', 0, '2026-06-12 20:51:20'),
(47, 110, 'user', NULL, '', 0, '2026-06-12 20:53:49'),
(48, 110, 'user', NULL, '', 0, '2026-06-12 20:55:06'),
(49, 110, 'user', NULL, '', 0, '2026-06-12 20:57:20'),
(50, 110, 'user', NULL, '', 0, '2026-06-12 20:57:32'),
(51, 110, 'support', 17, '', 0, '2026-06-12 21:27:26');

-- --------------------------------------------------------

--
-- Структура таблицы `request_status_history`
--

CREATE TABLE `request_status_history` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `status` enum('new','processed','closed','cancelled') NOT NULL,
  `comment` text DEFAULT NULL COMMENT 'Комментарий от поддержки',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL COMMENT 'ID сотрудника поддержки'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `request_status_history`
--

INSERT INTO `request_status_history` (`id`, `request_id`, `status`, `comment`, `created_at`, `created_by`) VALUES
(1, 18, 'new', 'Заказ создан', '2025-12-12 01:17:46', NULL),
(2, 18, 'processed', 'Заказ передан в доставку', '2025-12-13 10:30:00', NULL),
(56, 82, 'processed', 'Статус изменён с \"new\" на \"processed\"', '2026-05-14 00:08:42', NULL),
(57, 82, 'processed', '', '2026-05-14 00:08:42', 17),
(58, 82, 'closed', 'Статус изменён с \"processed\" на \"closed\"', '2026-05-14 00:11:54', NULL),
(59, 82, 'closed', '', '2026-05-14 00:11:54', 17),
(60, 82, 'processed', 'Статус изменён с \"closed\" на \"processed\"', '2026-05-14 00:13:15', NULL),
(61, 82, 'processed', 'Собираем заказ', '2026-05-14 00:13:15', 17),
(62, 81, 'processed', 'Статус изменён с \"new\" на \"processed\"', '2026-05-20 02:31:37', NULL),
(63, 81, 'processed', 'В сборке', '2026-05-20 02:31:37', 17);

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
(1, 'Монтаж оборудования', '', '', 15000.00, 'img/serv/s0.png', '1-3 дня', 1, 1, '2026-02-26 14:13:47', '2026-04-04 02:26:42', '1'),
(2, 'Техническое обслуживание', 'Ежегодное сервисное обслуживание контроллеров', NULL, 5000.00, 'img/serv/s2.png', 'по графику', 1, 2, '2026-02-26 14:13:47', '2026-02-27 16:19:05', '2'),
(3, 'Консультация инженера', 'Выезд специалиста для аудита системы', NULL, 3000.00, 'img/serv/s1.png', '2-4 часа', 1, 3, '2026-02-26 14:13:47', '2026-02-27 16:19:27', '');

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL COMMENT 'Уникальный ключ настройки',
  `value` text DEFAULT NULL COMMENT 'Значение настройки',
  `type` enum('text','textarea','number','checkbox','select','file') DEFAULT 'text' COMMENT 'Тип поля в форме',
  `options` text DEFAULT NULL COMMENT 'Опции для select (JSON)',
  `label` varchar(255) DEFAULT NULL COMMENT 'Подпись поля в форме',
  `description` text DEFAULT NULL COMMENT 'Подсказка',
  `section` varchar(100) DEFAULT 'general' COMMENT 'Группа настроек',
  `sort_order` int(11) DEFAULT 0 COMMENT 'Порядок в форме',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `settings`
--

INSERT INTO `settings` (`key`, `value`, `type`, `options`, `label`, `description`, `section`, `sort_order`, `updated_at`) VALUES
('contact_address', 'г. Чита, ул. Баргузинская, 49', 'textarea', NULL, 'Адрес организации', 'Отображается в футере', 'contacts', 10, '2026-04-04 01:16:56'),
('contact_email', 'mip@zabgu.ru', 'text', NULL, 'Email для связи', 'Для формы обратной связи и футера', 'contacts', 12, '2026-04-04 01:16:56'),
('contact_phone', '+7 (3022) XX-XX-XX', 'text', NULL, 'Телефон', 'Формат для ссылок: tel:+73022XXXXXX', 'contacts', 11, '2026-04-04 01:16:56'),
('copyright_text', '© {year} ООО МИП «НПЦ ПИТиА». Все права защищены.', 'text', NULL, 'Копирайт', 'Используйте {year} для текущего года', 'general', 4, '2026-04-04 01:16:56'),
('menu_catalog_url', 'catalog.php', 'text', NULL, 'Ссылка на Каталог', '', 'links', 20, '2026-04-04 01:16:56'),
('menu_news_url', 'news.php', 'text', NULL, 'Ссылка на Новости', '', 'links', 22, '2026-04-04 01:16:56'),
('menu_services_url', 'services_catalog.php', 'text', NULL, 'Ссылка на Услуги', '', 'links', 21, '2026-04-04 01:16:56'),
('menu_support_url', 'question.php', 'text', NULL, 'Ссылка на Поддержку', '', 'links', 23, '2026-04-04 01:16:56'),
('site_description', 'Коммерциализация научных разработок', 'text', NULL, 'Слоган / Описание', 'Краткое описание из футера', 'general', 3, '2026-04-04 01:16:56'),
('site_name', 'ООО МИП «НПЦ ПИТиА»', 'text', NULL, 'Название организации (МИП)', 'Главное название в футере и заголовках', 'general', 1, '2026-04-04 01:16:56'),
('site_name_lab', 'Лаборатория перспективных энергетических технологий', 'text', NULL, 'Название лаборатории', 'Для шапки раздела Лаборатории', 'general', 2, '2026-04-04 01:16:56');

-- --------------------------------------------------------

--
-- Структура таблицы `team`
--

CREATE TABLE `team` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `education` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `team`
--

INSERT INTO `team` (`id`, `name`, `position`, `photo_url`, `email`, `phone`, `bio`, `education`, `sort_order`, `created_at`) VALUES
(1, 'Палкин Георгий Александрович', 'Руководитель лаборатории', 'img/team/PGA.jpg', '', NULL, '', 'Программное обеспечение ВТ и АС, специалитет\r\n', 1, '2026-04-03 04:03:13'),
(2, 'Иванова Анастасия Андреевна\r\n', 'Специалист по системам искусственного интеллекта\r\n', 'img/team/IAA.jpg', '', NULL, '', 'Программное обеспечение ВТ и АС, магистратура ', 2, '2026-04-03 04:03:13'),
(3, 'Долгих Роман Сергеевич', 'Специалист по программному обеспечению\r\n', 'img/team/DRS.jpg', '', NULL, '', 'Программное обеспечение ВТ и АС, специалитет\r\n', 3, '2026-04-03 04:03:13');

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
  `role` enum('client','admin','support_specialist','guest') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `email`, `name`, `login`, `password`, `is_verified`, `verification_token`, `token_expires_at`, `role`, `phone`, `address`) VALUES
(2, 'ex2@ex.com', 'Иванов Иван Иванович', 'ex2', '$2y$10$q4olpAwvBOPQkK9tIMJj7O.DnmbV9RQiRvj1s1dP7H098J1Vkdd1C', 0, NULL, NULL, 'guest', NULL, NULL),
(5, 'sup@sup.ex', 'sup', 'supp3', '$2y$10$H0vcikedd.GrQjFLFJdIBe20b7nhgVF3LIyes6LyZJtpU/vwYzoou', 0, NULL, NULL, 'support_specialist', NULL, NULL),
(10, 'admin@ex.com', 'admin1', 'admin1', '$2y$10$RJORs1nPkX9mgRdCYsO3ze3WJZIyFB7xJ2UpOzaTcuk92HlrYpQQG', 1, NULL, NULL, 'admin', NULL, NULL),
(11, 'sup@ex2.ex', 'Васильев Василий Васильевич', 'supp2', '$2y$10$SbcptdZJpiNEitZrwmAE6.HMO3eUrWfD5lSwXE2OKemCUWjLUGsuC', 0, NULL, NULL, 'support_specialist', NULL, NULL),
(12, 'user@gmail.com', 'Иванов Иван Иванович', 'log3', '$2y$10$xhb.OYlnWqKpDFi6EIVlJOJIPwcPgvt.UWqNJ3.0tlNk.k0w0sWPm', 1, NULL, NULL, 'client', '+79141338704', '{\"city\":\"Чита\",\"street\":\"Новозйавдская\",\"house\":\"46\\/6\"}'),
(16, 'nik@nik.com', 'Николь', 'nik', '$2y$10$KRmNzM7r6312K2dFE4CQsuznVMPE3vrC2rJwR15uX6ik8cHZNbb22', 1, NULL, NULL, 'client', NULL, NULL),
(17, 'sup@sup.com', 'Аркадий', 'support_a', '$2y$10$JptkZ0y6VgPLbR1D36ikqeq0RhM8kVhoXg4VLVcOjccMVwU86i4Uq', 1, NULL, NULL, 'support_specialist', NULL, NULL),
(43, 'elena.kolosova.04@mail.ru', 'Елена', 'lena', '$2y$10$fJsXLseH8oKfYJnStJGkqeBwOhSbj9X/PJqI1YSoIm69tdqocC1ee', 1, NULL, NULL, 'client', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `visits`
--

CREATE TABLE `visits` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `device_type` enum('desktop','tablet','mobile') DEFAULT 'desktop',
  `browser` varchar(50) DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `referer` varchar(500) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_bot` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `visits`
--

INSERT INTO `visits` (`id`, `ip_address`, `user_agent`, `country`, `city`, `device_type`, `browser`, `page_url`, `referer`, `user_id`, `is_bot`, `created_at`) VALUES
(7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', NULL, '/portal/mip/mip.php', NULL, NULL, 0, '2026-04-04 00:11:28'),
(8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', NULL, '/portal/mip/catalog.php', NULL, NULL, 0, '2026-04-04 00:11:30'),
(9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', NULL, '/portal/mip/services_catalog.php', NULL, NULL, 0, '2026-04-04 00:11:30'),
(10, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', NULL, '/portal/mip/question.php', NULL, NULL, 0, '2026-04-04 00:11:47'),
(11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', NULL, '/portal/lab/projects.php', NULL, NULL, 0, '2026-04-04 00:15:41'),
(12, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', NULL, '/portal/lab/services.php', NULL, NULL, 0, '2026-04-04 00:15:43'),
(13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', NULL, '/portal/lab/news.php', NULL, NULL, 0, '2026-04-04 00:15:43'),
(14, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', NULL, '/portal/lab/question.php', NULL, NULL, 0, '2026-04-04 00:15:43'),
(15, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', NULL, '/portal/lab/about.php', NULL, NULL, 0, '2026-04-04 00:15:58'),
(16, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', NULL, '/portal/lab/main_lab.php', NULL, NULL, 0, '2026-04-04 00:15:59'),
(17, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-04 00:26:46'),
(18, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-04 00:26:49'),
(19, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-04 00:26:51'),
(20, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-04-04 00:26:52'),
(21, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-04-04 00:26:52'),
(22, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', NULL, 0, '2026-04-04 00:26:53'),
(23, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 10, 0, '2026-04-04 01:21:29'),
(24, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-04 01:23:41'),
(25, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', 10, 0, '2026-04-04 01:24:13'),
(26, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-04-04 02:20:57'),
(27, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-04-04 02:20:57'),
(28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-04 02:20:58'),
(29, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-04 02:20:59'),
(30, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-04-04 02:37:15'),
(31, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-04 02:37:21'),
(32, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-04 02:37:24'),
(33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-04 02:37:30'),
(34, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-04-04 02:53:02'),
(35, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-04 02:53:05'),
(36, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-04 03:01:22'),
(37, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-04 03:01:30'),
(38, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', NULL, 0, '2026-04-04 03:01:34'),
(39, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-04 03:01:37'),
(40, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', NULL, 0, '2026-04-04 03:01:41'),
(41, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-04-04 03:01:44'),
(42, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-04-04 10:05:52'),
(43, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-04 10:08:00'),
(44, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-04 10:09:30'),
(45, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-04 10:09:32'),
(46, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-04 10:21:11'),
(47, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-04 10:22:16'),
(48, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-04 10:28:50'),
(49, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-04 10:28:55'),
(50, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-04 10:28:56'),
(51, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-04 10:28:59'),
(52, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-04 10:29:09'),
(53, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-04 10:29:11'),
(54, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-04 10:29:14'),
(55, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-04 10:36:58'),
(56, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-04 10:36:59'),
(57, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/admin/product_edit.php?id=1', 10, 0, '2026-04-04 11:32:24'),
(58, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', 10, 0, '2026-04-04 11:34:52'),
(59, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', 10, 0, '2026-04-04 11:36:02'),
(60, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/news.php', 10, 0, '2026-04-04 11:36:08'),
(61, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', 10, 0, '2026-04-04 11:36:18'),
(62, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 10, 0, '2026-04-04 11:36:23'),
(63, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 10, 0, '2026-04-04 11:36:43'),
(64, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-04 11:40:09'),
(65, '1.102.253.191', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/auth/login', NULL, 2, 0, '2026-04-01 12:12:24'),
(66, '199.231.48.58', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/blog', NULL, NULL, 0, '2026-04-02 13:19:24'),
(67, '189.235.97.36', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/blog', NULL, 3, 0, '2026-04-01 13:47:24'),
(68, '147.191.0.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/cart', NULL, NULL, 0, '2026-04-01 19:58:24'),
(69, '121.199.120.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/blog/post-1', NULL, 2, 0, '2026-04-03 12:50:24'),
(70, '88.14.61.8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/product/12', NULL, NULL, 0, '2026-04-02 14:36:24'),
(71, '138.240.15.127', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/services', NULL, 7, 0, '2026-04-04 21:38:24'),
(72, '175.183.132.112', 'Mozilla/5.0 (Linux; Android 14) Mobile Safari/537.36', NULL, NULL, 'desktop', NULL, '/blog/post-1', NULL, 2, 0, '2026-03-30 12:41:24'),
(73, '238.175.165.43', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/cart', NULL, 3, 0, '2026-04-01 13:00:24'),
(74, '237.130.196.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/products', NULL, NULL, 0, '2026-04-02 14:50:24'),
(75, '138.24.217.245', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/services', NULL, 7, 0, '2026-04-04 18:29:24'),
(76, '77.172.117.72', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/', NULL, 3, 0, '2026-04-01 22:57:24'),
(77, '38.40.90.73', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/contacts', NULL, 7, 0, '2026-03-29 21:07:24'),
(78, '195.148.157.82', 'Mozilla/5.0 (Linux; Android 14) Mobile Safari/537.36', NULL, NULL, 'desktop', NULL, '/auth/register', NULL, NULL, 0, '2026-04-03 18:14:24'),
(79, '63.130.202.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/auth/register', NULL, NULL, 0, '2026-04-04 17:27:24'),
(80, '132.75.238.197', 'Mozilla/5.0 (Linux; Android 14) Mobile Safari/537.36', NULL, NULL, 'desktop', NULL, '/', NULL, 1, 0, '2026-03-30 23:18:24'),
(81, '224.143.45.49', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/product/12', NULL, 3, 0, '2026-03-30 12:52:24'),
(82, '84.78.142.219', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/blog', NULL, 1, 0, '2026-03-31 23:56:24'),
(83, '191.8.236.132', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/auth/register', NULL, 2, 0, '2026-04-04 13:01:24'),
(84, '31.82.64.75', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/auth/login', NULL, NULL, 0, '2026-04-02 19:17:24'),
(85, '104.31.100.149', 'Mozilla/5.0 (Linux; Android 14) Mobile Safari/537.36', NULL, NULL, 'desktop', NULL, '/auth/login', NULL, NULL, 0, '2026-03-30 20:48:24'),
(86, '154.166.111.57', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/auth/register', NULL, 1, 0, '2026-03-30 20:22:24'),
(87, '175.51.242.32', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/auth/register', NULL, 1, 0, '2026-04-01 23:10:24'),
(88, '168.157.24.164', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/cart', NULL, 7, 0, '2026-04-01 01:50:24'),
(89, '131.147.87.252', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/cart', NULL, 3, 0, '2026-04-04 18:01:24'),
(90, '251.188.187.113', 'Mozilla/5.0 (Linux; Android 14) Mobile Safari/537.36', NULL, NULL, 'desktop', NULL, '/', NULL, NULL, 0, '2026-04-05 00:41:24'),
(91, '109.18.20.48', 'Mozilla/5.0 (Linux; Android 14) Mobile Safari/537.36', NULL, NULL, 'desktop', NULL, '/auth/login', NULL, 1, 0, '2026-04-01 23:02:24'),
(92, '45.241.44.9', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/blog/post-1', NULL, NULL, 0, '2026-04-03 19:35:24'),
(93, '150.199.31.72', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/', NULL, 5, 0, '2026-03-31 02:00:24'),
(94, '101.98.186.123', 'Mozilla/5.0 (Linux; Android 14) Mobile Safari/537.36', NULL, NULL, 'desktop', NULL, '/services', NULL, 2, 0, '2026-04-05 02:24:24'),
(95, '43.63.187.236', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/product/12', NULL, 2, 0, '2026-04-02 20:41:24'),
(96, '78.33.191.89', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/product/45', NULL, NULL, 0, '2026-03-30 21:36:24'),
(97, '59.194.23.46', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/blog/post-1', NULL, 2, 0, '2026-04-02 21:09:24'),
(98, '252.124.121.235', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/products', NULL, NULL, 0, '2026-04-05 00:44:24'),
(99, '197.228.37.14', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/auth/register', NULL, 3, 0, '2026-03-30 17:52:24'),
(100, '252.204.10.207', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/profile', NULL, 1, 0, '2026-04-03 00:12:24'),
(101, '2.179.123.77', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/', NULL, 5, 0, '2026-03-29 21:06:24'),
(102, '214.135.36.30', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/products', NULL, 7, 0, '2026-03-29 12:17:24'),
(103, '105.235.90.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/', NULL, 5, 0, '2026-04-03 12:13:24'),
(104, '251.193.209.214', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/auth/login', NULL, 1, 0, '2026-04-02 16:22:24'),
(105, '241.237.206.64', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/auth/register', NULL, NULL, 0, '2026-04-01 19:38:24'),
(106, '54.205.97.125', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/services', NULL, NULL, 0, '2026-04-02 23:29:24'),
(107, '140.97.63.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/auth/register', NULL, 1, 0, '2026-04-01 21:55:24'),
(108, '23.248.146.246', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/catalog', NULL, 5, 0, '2026-03-31 21:02:24'),
(109, '120.159.178.161', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', NULL, NULL, 'desktop', NULL, '/', NULL, 2, 0, '2026-03-31 02:08:24'),
(110, '226.23.205.186', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/services', NULL, NULL, 0, '2026-04-01 15:32:24'),
(111, '198.226.25.217', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', NULL, NULL, 'desktop', NULL, '/profile', NULL, NULL, 0, '2026-03-29 12:09:24'),
(112, '0.142.197.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/blog/post-1', NULL, NULL, 0, '2026-03-29 12:12:24'),
(113, '66.156.69.135', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Firefox/121.0', NULL, NULL, 'desktop', NULL, '/auth/register', NULL, NULL, 0, '2026-04-03 01:19:24'),
(114, '52.39.37.71', 'Mozilla/5.0 (Linux; Android 14) Mobile Safari/537.36', NULL, NULL, 'desktop', NULL, '/profile', NULL, NULL, 0, '2026-03-29 22:57:24'),
(128, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-04-04 12:01:02'),
(129, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-04 12:01:07'),
(130, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', 12, 0, '2026-04-04 12:05:37'),
(131, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-04 12:05:40'),
(132, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 12, 0, '2026-04-04 12:09:56'),
(133, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 17, 0, '2026-04-04 12:16:01'),
(134, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', 12, 0, '2026-04-04 12:21:54'),
(135, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-04 12:21:59'),
(136, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-04-04 12:22:06'),
(137, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-04 12:22:10'),
(138, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-04 12:22:12'),
(139, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-04 12:22:13'),
(140, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-04 13:41:19'),
(141, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-04 13:41:23'),
(142, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-04 14:21:02'),
(143, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-04 16:14:14'),
(144, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-04 16:14:16'),
(145, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-04 16:14:32'),
(146, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-04 16:14:36'),
(147, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-04 16:14:38'),
(148, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-04-04 16:14:41'),
(149, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-04 16:14:42'),
(150, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-04 16:14:43'),
(151, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-04 16:22:35'),
(152, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-04 16:22:38'),
(153, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-04 16:23:11'),
(154, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-04 16:23:35'),
(155, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-04 16:25:08'),
(156, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=5', 'http://localhost/portal/admin/news.php', 10, 0, '2026-04-06 12:19:42'),
(157, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=12', 'http://localhost/portal/admin/news_add.php', 10, 0, '2026-04-06 13:05:37'),
(158, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/news_view.php?id=12', 10, 0, '2026-04-06 13:06:43'),
(159, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-06 13:06:50'),
(160, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 10, 0, '2026-04-06 13:06:52'),
(161, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-06 13:07:07'),
(162, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/news.php', 10, 0, '2026-04-06 13:07:19'),
(163, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-23 15:36:02'),
(164, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-23 15:36:27'),
(165, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-04-23 15:36:34'),
(166, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-04-23 15:36:39'),
(167, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', NULL, 0, '2026-04-23 15:36:42'),
(168, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-23 15:37:07'),
(169, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-23 15:54:18'),
(170, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', NULL, 0, '2026-04-23 15:54:19'),
(171, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-04-23 15:54:19'),
(172, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-04-23 15:54:20'),
(173, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-04-23 15:54:21'),
(174, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-23 15:54:22'),
(175, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-23 15:54:24'),
(176, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-23 15:54:43'),
(177, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-23 16:01:07'),
(178, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 16:01:22'),
(179, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-23 16:19:06'),
(180, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=mip', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 16:20:22'),
(181, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=lab', 'http://localhost/portal/mip/question.php?context=mip', NULL, 0, '2026-04-23 16:20:23'),
(182, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=mip', 'http://localhost/portal/mip/question.php?context=lab', NULL, 0, '2026-04-23 16:34:15'),
(183, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=lab', 'http://localhost/portal/mip/question.php?context=mip', NULL, 0, '2026-04-23 16:34:20'),
(184, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=lab', 'http://localhost/portal/mip/question.php?context=mip', NULL, 0, '2026-04-23 16:53:34'),
(185, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=mip', 'http://localhost/portal/mip/question.php?context=lab', NULL, 0, '2026-04-23 16:53:39'),
(186, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=mip', 'http://localhost/portal/mip/question.php?context=mip', NULL, 0, '2026-04-23 17:08:49'),
(187, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=lab', 'http://localhost/portal/mip/question.php?context=mip', NULL, 0, '2026-04-23 17:08:50'),
(188, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-23 17:10:50'),
(189, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=lab', 'http://localhost/portal/mip/question.php?context=mip', NULL, 0, '2026-04-23 17:24:57'),
(190, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=mip', 'http://localhost/portal/mip/question.php?context=lab', NULL, 0, '2026-04-23 17:25:20'),
(191, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 37, 0, '2026-04-23 17:28:40'),
(192, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/mip.php', 38, 0, '2026-04-23 17:34:10'),
(193, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php?context=mip', 'http://localhost/portal/mip/question.php?context=lab', 40, 0, '2026-04-23 17:39:00'),
(194, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/verify_email.php?token=722ff8adc03b7fad98cb743c142c35b30e06b0d52ca98485dd986542357d59b7', 41, 0, '2026-04-23 17:44:44'),
(195, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-23 17:45:04'),
(196, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 17:46:51'),
(197, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 17:46:59'),
(198, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-23 17:55:12'),
(199, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-23 18:07:52'),
(200, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 18:08:21'),
(201, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-23 20:33:42'),
(202, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-23 20:38:13'),
(203, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 20:38:17'),
(204, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-23 20:44:53'),
(205, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-23 20:48:38'),
(206, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 20:48:42'),
(207, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-23 20:50:30'),
(208, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 20:53:43'),
(209, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 20:53:47'),
(210, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 20:53:49'),
(211, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 20:53:50'),
(212, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/question.php', NULL, 0, '2026-04-23 20:53:52'),
(213, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 20:58:39'),
(214, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 21:01:48'),
(215, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 21:01:49'),
(216, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 21:09:26'),
(217, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 21:09:29'),
(218, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 21:09:37'),
(219, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-23 21:10:25'),
(220, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-23 21:10:28'),
(221, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 21:10:36'),
(222, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 21:10:42'),
(223, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-23 21:12:54'),
(224, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-23 21:13:04'),
(225, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-04-23 21:13:12'),
(226, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-23 21:13:14'),
(227, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-23 21:13:19'),
(228, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-23 21:13:21'),
(229, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-23 21:15:45');
INSERT INTO `visits` (`id`, `ip_address`, `user_agent`, `country`, `city`, `device_type`, `browser`, `page_url`, `referer`, `user_id`, `is_bot`, `created_at`) VALUES
(230, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 21:20:39'),
(231, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-23 21:24:41'),
(232, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 21:25:55'),
(233, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 21:27:17'),
(234, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 21:33:22'),
(235, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 21:33:26'),
(236, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 21:40:56'),
(237, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 12, 0, '2026-04-23 21:45:42'),
(238, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 21:46:52'),
(239, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-23 21:51:28'),
(240, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-23 21:51:32'),
(241, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-23 21:52:03'),
(242, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-23 21:52:04'),
(243, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-23 21:52:06'),
(244, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-23 21:52:08'),
(245, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 21:52:17'),
(246, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-23 21:52:19'),
(247, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-23 21:52:20'),
(248, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 21:52:22'),
(249, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 21:57:42'),
(250, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 22:05:09'),
(251, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 22:10:27'),
(252, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-23 22:13:49'),
(253, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-23 22:13:51'),
(254, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 22:20:12'),
(255, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-23 22:22:32'),
(256, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-23 22:24:43'),
(257, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-23 22:27:43'),
(258, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 22:27:46'),
(259, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-23 22:27:48'),
(260, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-23 22:27:51'),
(261, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-23 22:27:58'),
(262, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 22:28:02'),
(263, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-23 22:34:19'),
(264, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 22:34:23'),
(265, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 22:36:26'),
(266, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-23 22:36:28'),
(267, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-23 22:37:30'),
(268, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=3', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-23 22:37:32'),
(269, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news_view.php?id=3', 12, 0, '2026-04-23 22:37:35'),
(270, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-23 22:37:39'),
(271, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-23 22:37:49'),
(272, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-23 22:39:01'),
(273, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-23 22:39:35'),
(274, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-04-23 22:39:58'),
(275, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-23 22:41:02'),
(276, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-23 22:41:04'),
(277, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-23 22:41:31'),
(278, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-04-23 22:41:45'),
(279, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 22:42:18'),
(280, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-23 22:42:20'),
(281, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 22:48:47'),
(282, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 22:51:55'),
(283, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-04-23 22:58:00'),
(284, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 22:58:54'),
(285, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-23 23:00:36'),
(286, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 23:00:40'),
(287, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-23 23:09:23'),
(288, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 23:09:29'),
(289, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 23:17:42'),
(290, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 23:25:03'),
(291, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 23:31:50'),
(292, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-23 23:31:55'),
(293, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 23:35:34'),
(294, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 23:39:09'),
(295, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-23 23:57:52'),
(296, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 01:47:20'),
(297, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 01:52:29'),
(298, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 01:58:21'),
(299, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 02:04:49'),
(300, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 02:13:21'),
(301, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=12', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 02:15:59'),
(302, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 02:16:16'),
(303, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:18:31'),
(304, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 02:21:49'),
(305, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 02:27:13'),
(306, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?search=%D0%BF%D0%BF%D0%BF', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:27:34'),
(307, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?search=%D0%BF%D0%BE', 'http://localhost/portal/mip/catalog.php?search=%D0%BF%D0%BF%D0%BF', 12, 0, '2026-04-24 02:28:32'),
(308, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?search=', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:30:52'),
(309, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?search=ccc', 'http://localhost/portal/mip/catalog.php?search=', 12, 0, '2026-04-24 02:30:55'),
(310, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?search=%D0%B4%D0%B0', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:31:02'),
(311, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=2', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:31:09'),
(312, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=1', 'http://localhost/portal/mip/catalog.php?category=2', 12, 0, '2026-04-24 02:31:11'),
(313, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:31:19'),
(314, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:31:27'),
(315, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-24 02:36:50'),
(316, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 02:38:19'),
(317, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:38:21'),
(318, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:38:39'),
(319, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 02:43:08'),
(320, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 02:45:33'),
(321, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 02:46:17'),
(322, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:48:29'),
(323, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:48:35'),
(324, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:53:45'),
(325, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-24 02:53:53'),
(326, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:53:55'),
(327, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 02:59:15'),
(328, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-24 02:59:32'),
(329, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 03:02:46'),
(330, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-24 03:02:52'),
(331, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-24 03:05:17'),
(332, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 03:07:58'),
(333, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-24 03:10:29'),
(334, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 03:12:04'),
(335, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-24 03:12:36'),
(336, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-24 03:12:37'),
(337, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-04-24 03:12:40'),
(338, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 03:18:16'),
(339, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-24 03:19:19'),
(340, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 03:20:11'),
(341, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 03:20:20'),
(342, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 03:24:30'),
(343, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 03:24:53'),
(344, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=12', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 03:27:36'),
(345, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/news_view.php?id=12', 12, 0, '2026-04-24 03:27:40'),
(346, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 03:27:43'),
(347, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 03:28:25'),
(348, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 03:28:35'),
(349, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-24 03:31:40'),
(350, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 03:32:18'),
(351, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 03:35:06'),
(352, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-24 03:37:21'),
(353, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 03:37:25'),
(354, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 03:37:26'),
(355, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=4', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 03:38:05'),
(356, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 03:40:15'),
(357, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-24 03:40:51'),
(358, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 03:43:09'),
(359, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 03:47:33'),
(360, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 03:51:36'),
(361, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-04-24 03:56:04'),
(362, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 03:57:05'),
(363, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 04:05:43'),
(364, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/notifications.php', 12, 0, '2026-04-24 04:05:48'),
(365, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 04:06:03'),
(366, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 04:11:58'),
(367, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 04:12:03'),
(368, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-04-24 04:13:56'),
(369, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-04-24 04:14:17'),
(370, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 04:14:57'),
(371, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-24 04:15:03'),
(372, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-24 04:15:06'),
(373, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-24 04:15:08'),
(374, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-24 04:15:10'),
(375, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-24 10:01:45'),
(376, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-24 10:11:40'),
(377, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/search_portal.php?q=%D0%B4%D0%B0%D1%82%D1%87%D0%B8%D0%BA', NULL, 0, '2026-04-24 10:12:13'),
(378, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/search_portal.php?q=%D0%BA%D0%BE%D0%BD%D1%82%D1%80%D0%BE%D0%BB%D0%BB%D0%B5%D1%80', NULL, 0, '2026-04-24 10:13:59'),
(379, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php?id=2', 'http://localhost/portal/mip/search_portal.php?q=%D0%BA%D0%BE%D0%BD%D1%82%D1%80%D0%BE%D0%BB%D0%BB%D0%B5%D1%80', NULL, 0, '2026-04-24 10:14:03'),
(380, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/search_portal.php?q=%D0%BA%D0%BE%D0%BD%D1%82%D1%80%D0%BE%D0%BB%D0%BB%D0%B5%D1%80', NULL, 0, '2026-04-24 10:14:13'),
(381, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-24 10:15:16'),
(382, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/question.php', NULL, 0, '2026-04-24 10:15:17'),
(383, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-24 10:15:20'),
(384, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/question.php', NULL, 0, '2026-04-24 10:15:25'),
(385, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-04-24 10:15:27'),
(386, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-24 10:18:07'),
(387, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-04-24 10:20:43'),
(388, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-24 10:20:45'),
(389, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-04-24 10:20:46'),
(390, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 10:24:33'),
(391, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 10:33:47'),
(392, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php?id=1', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 10:37:19'),
(393, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/services.php?id=1', 12, 0, '2026-04-24 10:49:12'),
(394, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 10:49:14'),
(395, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 10:49:26'),
(396, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-24 10:49:37'),
(397, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-24 10:50:33'),
(398, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-24 10:50:36'),
(399, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-04-24 10:50:38'),
(400, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-24 10:50:40'),
(401, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-24 10:59:46'),
(402, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 11:00:27'),
(403, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-24 11:00:31'),
(404, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-24 11:00:32'),
(405, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 11:00:34'),
(406, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-24 11:03:11'),
(407, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-04-24 11:07:37'),
(408, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-24 11:07:40'),
(409, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 11:07:58'),
(410, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 11:08:15'),
(411, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-04-24 11:18:32'),
(412, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 11:18:36'),
(413, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 11:24:44'),
(414, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 11:29:59'),
(415, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-04-24 11:30:00'),
(416, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 11:36:10'),
(417, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-04-24 11:42:35'),
(418, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 11:42:40');
INSERT INTO `visits` (`id`, `ip_address`, `user_agent`, `country`, `city`, `device_type`, `browser`, `page_url`, `referer`, `user_id`, `is_bot`, `created_at`) VALUES
(419, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 11:44:15'),
(420, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-24 13:16:20'),
(421, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 13:16:25'),
(422, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 13:16:26'),
(423, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 13:16:29'),
(424, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 13:16:32'),
(425, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-24 13:16:36'),
(426, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-24 13:16:43'),
(427, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-24 13:16:47'),
(428, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 13:17:00'),
(429, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=2', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 13:20:44'),
(430, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=1', 'http://localhost/portal/mip/catalog.php?category=2', 12, 0, '2026-04-24 13:20:45'),
(431, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-24 13:20:53'),
(432, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-24 13:21:40'),
(433, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-24 13:21:43'),
(434, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 13:21:50'),
(435, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-24 13:22:00'),
(436, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/product.php?id=3', 12, 0, '2026-04-24 13:22:18'),
(437, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=3', 12, 0, '2026-04-24 13:22:23'),
(438, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-24 13:22:24'),
(439, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-24 13:22:43'),
(440, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 13:23:04'),
(441, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-24 13:23:06'),
(442, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-24 13:23:11'),
(443, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-04-24 13:23:15'),
(444, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-24 13:23:17'),
(445, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-24 13:23:19'),
(446, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-24 13:23:22'),
(447, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-04-24 13:24:08'),
(448, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 17, 0, '2026-04-24 13:40:59'),
(449, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 17, 0, '2026-04-24 13:41:02'),
(450, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 10, 0, '2026-04-24 13:52:59'),
(451, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/services_catalog.php', 17, 0, '2026-04-24 14:07:37'),
(452, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 17, 0, '2026-04-24 14:23:49'),
(453, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 17, 0, '2026-04-24 14:23:54'),
(454, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-24 14:24:55'),
(455, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-24 14:25:04'),
(456, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-24 14:28:50'),
(457, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-24 14:28:57'),
(458, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-24 14:29:45'),
(459, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-24 14:31:50'),
(460, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php?id=1', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-24 14:33:53'),
(461, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/services.php?id=1', NULL, 0, '2026-04-24 14:34:08'),
(462, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/question.php', NULL, 0, '2026-04-24 14:37:17'),
(463, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-25 00:49:12'),
(464, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 00:54:07'),
(465, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 00:54:55'),
(466, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 01:00:01'),
(467, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-04-25 01:08:56'),
(468, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 01:13:50'),
(469, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-25 01:13:59'),
(470, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 01:14:07'),
(471, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 01:14:27'),
(472, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/news_view.php?id=2', 12, 0, '2026-04-25 01:14:30'),
(473, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-25 01:24:23'),
(474, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 01:25:11'),
(475, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 01:25:13'),
(476, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 01:25:18'),
(477, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-25 01:30:20'),
(478, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 01:30:33'),
(479, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-25 01:39:15'),
(480, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-04-25 01:47:47'),
(481, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 01:47:51'),
(482, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', '', 10, 0, '2026-04-25 01:55:41'),
(483, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', '', 10, 0, '2026-04-25 02:02:16'),
(484, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 02:08:37'),
(485, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?search=13213', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-25 02:08:42'),
(486, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/catalog.php?search=13213', 12, 0, '2026-04-25 02:15:40'),
(487, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=4', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-25 02:17:57'),
(488, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-25 02:18:03'),
(489, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-25 02:18:39'),
(490, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php?search=%D1%82%D0%B5%D1%85', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-25 02:18:50'),
(491, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=2', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-25 02:19:14'),
(492, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=1', 'http://localhost/portal/mip/catalog.php?category=2', 12, 0, '2026-04-25 02:19:14'),
(493, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-25 02:21:25'),
(494, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 02:21:27'),
(495, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-25 02:21:32'),
(496, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 02:31:25'),
(497, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/service_view.php?id=1', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-25 02:34:05'),
(498, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/service_view.php?id=2', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-25 02:34:16'),
(499, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/service_view.php?id=2', 12, 0, '2026-04-25 02:37:45'),
(500, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/service_view.php?id=2', 12, 0, '2026-04-25 02:44:56'),
(501, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-25 02:50:14'),
(502, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 02:50:21'),
(503, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', '', 12, 0, '2026-04-25 02:55:55'),
(504, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-25 02:56:25'),
(505, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', NULL, 0, '2026-04-25 02:56:26'),
(506, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-25 03:05:49'),
(507, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-25 03:10:57'),
(508, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-04-25 03:11:12'),
(509, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-04-25 03:11:25'),
(510, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 03:11:38'),
(511, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-25 03:12:28'),
(512, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news_view.php?id=2', 12, 0, '2026-04-25 03:12:40'),
(513, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-25 03:12:52'),
(514, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-25 03:12:56'),
(515, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-25 03:13:02'),
(516, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-04-25 03:18:35'),
(517, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 10, 0, '2026-04-25 03:19:05'),
(518, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-25 03:19:22'),
(519, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/services_catalog.php', 10, 0, '2026-04-25 03:19:25'),
(520, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/question.php', 10, 0, '2026-04-25 03:19:26'),
(521, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=4', 'http://localhost/portal/mip/catalog.php', 10, 0, '2026-04-25 03:19:31'),
(522, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-25 03:19:51'),
(523, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=4', 12, 0, '2026-04-25 03:19:58'),
(524, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-25 03:21:29'),
(525, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-25 03:22:47'),
(526, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-25 03:29:54'),
(527, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 03:30:54'),
(528, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 03:34:08'),
(529, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-25 03:34:13'),
(530, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-25 03:34:18'),
(531, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 03:35:00'),
(532, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 03:39:56'),
(533, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-04-25 03:40:11'),
(534, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 03:40:37'),
(535, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 03:40:41'),
(536, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 03:46:02'),
(537, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 03:46:03'),
(538, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 03:46:34'),
(539, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-25 03:53:35'),
(540, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-04-25 03:53:40'),
(541, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 03:56:21'),
(542, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-25 03:56:30'),
(543, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-04-25 03:56:44'),
(544, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-04-25 04:06:57'),
(545, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-25 04:07:00'),
(546, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-04-25 04:07:03'),
(547, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 04:07:08'),
(548, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/project_detail.php?id=1', 12, 0, '2026-04-25 04:08:18'),
(549, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 04:09:59'),
(550, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 04:10:00'),
(551, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-25 04:10:06'),
(552, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-25 04:13:13'),
(553, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php?id=12', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 04:13:18'),
(554, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=3', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-25 04:14:12'),
(555, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-04-25 04:22:21'),
(556, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php?id=12', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 04:22:26'),
(557, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/news.php?id=12', 12, 0, '2026-04-25 04:22:34'),
(558, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=12', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-25 04:22:36'),
(559, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-25 04:22:41'),
(560, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-25 04:23:37'),
(561, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-04-25 04:23:40'),
(562, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-25 04:23:41'),
(563, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-25 04:23:48'),
(564, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 04:23:56'),
(565, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/project_detail.php?id=1', 12, 0, '2026-04-25 04:28:59'),
(566, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-25 04:29:07'),
(567, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-25 04:30:18'),
(568, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-04-25 04:30:25'),
(569, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-25 04:30:27'),
(570, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-04-25 04:30:34'),
(571, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-04-25 04:31:34'),
(572, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/services.php', 12, 0, '2026-04-25 04:31:36'),
(573, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-04-25 04:31:40'),
(574, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-25 09:07:12'),
(575, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 09:08:13'),
(576, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 09:08:18'),
(577, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-04-25 09:08:20'),
(578, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/services.php', NULL, 0, '2026-04-25 09:08:22'),
(579, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 09:08:24'),
(580, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 09:15:08'),
(581, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 09:16:56'),
(582, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 09:26:14'),
(583, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 09:26:36'),
(584, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=13', 'http://localhost/portal/admin/news_add.php', 10, 0, '2026-04-25 09:36:27'),
(585, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/news_view.php?id=13', 10, 0, '2026-04-25 09:36:36'),
(586, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=15', 'http://localhost/portal/admin/news_add.php', 10, 0, '2026-04-25 09:38:02'),
(587, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/news.php', 10, 0, '2026-04-25 09:38:15'),
(588, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', 10, 0, '2026-04-25 09:38:20'),
(589, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 09:39:37'),
(590, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-25 09:43:45'),
(591, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/news_view.php?id=15', 10, 0, '2026-04-25 09:46:19'),
(592, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=15', 'http://localhost/portal/admin/news_add.php', 10, 0, '2026-04-25 09:46:20'),
(593, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=17', 'http://localhost/portal/admin/news_add.php', 10, 0, '2026-04-25 09:46:40'),
(594, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', 10, 0, '2026-04-25 09:46:51'),
(595, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/news.php', 10, 0, '2026-04-25 09:46:54'),
(596, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=18', 'http://localhost/portal/admin/news_add.php', 10, 0, '2026-04-25 09:47:15'),
(597, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=20', 'http://localhost/portal/admin/news_add.php', 10, 0, '2026-04-25 09:56:48'),
(598, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/news_view.php?id=20', 10, 0, '2026-04-25 09:57:04'),
(599, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-25 09:57:06'),
(600, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', 10, 0, '2026-04-25 09:57:08'),
(601, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/news.php', 10, 0, '2026-04-25 09:59:00'),
(602, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 10:06:42'),
(603, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', '', 17, 0, '2026-04-25 10:09:25'),
(604, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=', 'http://localhost/portal/lk_support.php', 17, 0, '2026-04-25 10:09:38'),
(605, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=services&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=orders&sort=desc&search=', 17, 0, '2026-04-25 10:09:39'),
(606, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=questions&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=services&sort=desc&search=', 17, 0, '2026-04-25 10:09:39'),
(607, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-04-25 10:12:26');
INSERT INTO `visits` (`id`, `ip_address`, `user_agent`, `country`, `city`, `device_type`, `browser`, `page_url`, `referer`, `user_id`, `is_bot`, `created_at`) VALUES
(608, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/lab/main_lab.php', 17, 0, '2026-04-25 10:14:38'),
(609, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lk_support.php', 17, 0, '2026-04-25 10:14:47'),
(610, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', 17, 0, '2026-04-25 10:15:48'),
(611, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', 17, 0, '2026-04-25 10:16:13'),
(612, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=20', 'http://localhost/portal/mip/news.php', 17, 0, '2026-04-25 10:16:48'),
(613, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-04-25 10:21:11'),
(614, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/news.php', 10, 0, '2026-04-25 10:23:15'),
(615, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-04-25 10:26:51'),
(616, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/authorization.php', 17, 0, '2026-04-25 10:31:35'),
(617, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=', 'http://localhost/portal/lk_support.php', 17, 0, '2026-04-25 10:35:40'),
(618, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=services&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=orders&sort=desc&search=', 17, 0, '2026-04-25 10:35:41'),
(619, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=questions&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=services&sort=desc&search=', 17, 0, '2026-04-25 10:35:41'),
(620, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-04-25 10:35:45'),
(621, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-04-25 10:46:38'),
(622, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/lk_support.php?filter=all&sort=desc&search=', 17, 0, '2026-04-25 10:48:52'),
(623, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/lab/main_lab.php', 17, 0, '2026-04-25 10:48:58'),
(624, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-04-25 10:49:13'),
(625, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 10:49:17'),
(626, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/lk_lab.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 10:49:21'),
(627, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lk_support.php', 17, 0, '2026-04-25 10:54:52'),
(628, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/lab/news_view.php?id=5', 17, 0, '2026-04-25 10:56:07'),
(629, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/lab/news_view.php?id=5', 17, 0, '2026-04-25 11:01:54'),
(630, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-04-25 11:04:51'),
(631, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 11:04:58'),
(632, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/lk_lab.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 11:04:59'),
(633, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 11:05:01'),
(634, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 11:06:30'),
(635, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=questions&sort=desc&search=', 'http://localhost/portal/lk_support.php', 17, 0, '2026-04-25 11:06:38'),
(636, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-25 11:14:18'),
(637, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-04-25 11:14:31'),
(638, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 11:14:34'),
(639, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 11:14:35'),
(640, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=questions&sort=desc&search=', 'http://localhost/portal/lk_support.php', 17, 0, '2026-04-25 11:15:48'),
(641, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=questions&sort=desc&search=', 'http://localhost/portal/lk_support.php', 17, 0, '2026-04-25 11:23:53'),
(642, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?search=80', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-04-25 11:24:44'),
(643, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=questions&sort=desc&search=80', 'http://localhost/portal/lk_support.php?search=80', 17, 0, '2026-04-25 11:25:24'),
(644, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=80', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=80', 17, 0, '2026-04-25 11:26:09'),
(645, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?search=', 'http://localhost/portal/lk_support.php?filter=all&sort=desc&search=80', 17, 0, '2026-04-25 11:26:14'),
(646, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-04-25 11:31:52'),
(647, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 11:31:56'),
(648, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?search=', 'http://localhost/portal/lk_support.php?search=', 17, 0, '2026-04-25 11:32:52'),
(649, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 11:36:08'),
(650, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=questions&sort=desc&search=', 'http://localhost/portal/lk_support.php?search=', 17, 0, '2026-04-25 11:37:25'),
(651, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-04-25 11:37:29'),
(652, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=all&sort=desc&search=', 17, 0, '2026-04-25 11:37:42'),
(653, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=all&sort=desc&search=', 17, 0, '2026-04-25 11:42:53'),
(654, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=all&sort=desc&search=', 17, 0, '2026-04-25 11:52:27'),
(655, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/admin/news.php', 'http://localhost/portal/admin/services.php', 10, 0, '2026-04-25 11:58:42'),
(656, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=all&sort=desc&search=', 17, 0, '2026-04-25 12:00:47'),
(657, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lk_support.php?filter=orders&sort=desc&search=', 17, 0, '2026-04-25 12:01:45'),
(658, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lk_support.php?filter=orders&sort=desc&search=', 17, 0, '2026-04-25 12:42:08'),
(659, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 17, 0, '2026-04-25 12:42:10'),
(660, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 17, 0, '2026-04-25 12:59:28'),
(661, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 17, 0, '2026-04-25 13:10:51'),
(662, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 17, 0, '2026-04-25 13:10:57'),
(663, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/mip.php', 17, 0, '2026-04-25 13:11:03'),
(664, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 17, 0, '2026-04-25 13:16:30'),
(665, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/mip.php', 17, 0, '2026-04-25 13:16:36'),
(666, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/news_view.php?id=2', 17, 0, '2026-04-25 13:17:20'),
(667, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/news_view.php?id=2', 17, 0, '2026-04-25 13:19:00'),
(668, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/mip/news_view.php?id=2', 17, 0, '2026-04-25 13:19:04'),
(669, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/news.php', 17, 0, '2026-04-25 13:19:14'),
(670, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', 17, 0, '2026-04-25 13:19:15'),
(671, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lab/news.php', 17, 0, '2026-04-25 13:19:21'),
(672, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/news.php', 17, 0, '2026-04-25 13:19:27'),
(673, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', 17, 0, '2026-04-25 13:19:31'),
(674, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/projects.php', 17, 0, '2026-04-25 13:19:35'),
(675, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/services.php', 'http://localhost/portal/lab/projects.php', 17, 0, '2026-04-25 13:21:55'),
(676, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 17, 0, '2026-04-25 13:23:38'),
(677, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/question.php', 17, 0, '2026-04-25 13:23:52'),
(678, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/projects.php', 17, 0, '2026-04-25 13:24:30'),
(679, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 17, 0, '2026-04-25 13:24:42'),
(680, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 17, 0, '2026-04-25 13:25:44'),
(681, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/projects.php', 17, 0, '2026-04-25 13:25:46'),
(682, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/project_detail.php?id=1', 17, 0, '2026-04-25 13:25:59'),
(683, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/mip/mip.php', 17, 0, '2026-04-25 13:34:59'),
(684, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 13:49:38'),
(685, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-25 13:51:25'),
(686, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 10, 0, '2026-04-25 13:51:26'),
(687, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/projects.php', 10, 0, '2026-04-25 13:51:29'),
(688, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=2', 'http://localhost/portal/lab/projects.php', 10, 0, '2026-04-25 13:51:43'),
(689, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/projects.php', 10, 0, '2026-04-25 13:51:54'),
(690, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/news.php', 10, 0, '2026-04-25 14:00:02'),
(691, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/projects.php', 17, 0, '2026-04-25 14:17:39'),
(692, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 17, 0, '2026-04-25 14:17:44'),
(693, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/lab/main_lab.php', 17, 0, '2026-04-25 14:19:48'),
(694, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 14:26:41'),
(695, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 14:26:42'),
(696, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-04-25 14:26:45'),
(697, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-04-25 14:28:38'),
(698, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-04-25 14:31:16'),
(699, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 14:31:51'),
(700, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-04-25 14:34:07'),
(701, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 14:34:08'),
(702, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 14:36:42'),
(703, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 14:39:08'),
(704, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 14:39:26'),
(705, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 14:39:26'),
(706, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', NULL, 0, '2026-04-25 14:39:40'),
(707, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/news.php', NULL, 0, '2026-04-25 14:40:44'),
(708, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 14:45:06'),
(709, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 14:46:56'),
(710, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 14:46:57'),
(711, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', NULL, 0, '2026-04-25 14:46:58'),
(712, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/news_view.php?id=2', NULL, 0, '2026-04-25 14:48:35'),
(713, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 14:48:40'),
(714, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 14:50:41'),
(715, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 14:53:12'),
(716, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 14:53:12'),
(717, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', NULL, 0, '2026-04-25 14:53:16'),
(718, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/news.php', NULL, 0, '2026-04-25 14:54:16'),
(719, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 14:54:19'),
(720, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/news_view.php?id=5', NULL, 0, '2026-04-25 14:54:57'),
(721, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-04-25 14:55:00'),
(722, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-04-25 14:55:04'),
(723, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/news_view.php?id=5', NULL, 0, '2026-04-25 15:04:01'),
(724, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 15:04:03'),
(725, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/lab/news_view.php?id=5', NULL, 0, '2026-04-25 15:04:06'),
(726, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 15:06:38'),
(727, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 15:09:26'),
(728, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 15:09:27'),
(729, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 15:09:31'),
(730, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-04-25 15:09:37'),
(731, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-04-25 15:10:03'),
(732, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-04-25 15:12:45'),
(733, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/news_view.php?id=5', 10, 0, '2026-04-25 15:15:19'),
(734, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-25 15:15:28'),
(735, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/news.php', 10, 0, '2026-04-25 15:15:32'),
(736, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-04-25 15:16:04'),
(737, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/authorization.php', 17, 0, '2026-04-25 15:17:48'),
(738, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/authorization.php', 17, 0, '2026-04-25 15:45:52'),
(739, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news_view.php?id=5', 'http://localhost/portal/lk_support.php', 17, 0, '2026-04-25 15:46:03'),
(740, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/lab/news_view.php?id=5', 17, 0, '2026-04-25 15:46:12'),
(741, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=', 'http://localhost/portal/lk_support.php', 17, 0, '2026-04-25 15:48:38'),
(742, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-04-25 15:51:12'),
(743, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 16:34:58'),
(744, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 16:35:00'),
(745, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/lk_lab.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 16:35:09'),
(746, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-04-25 16:39:02'),
(747, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-04-25 16:39:05'),
(748, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-04-25 16:43:28'),
(749, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-04-25 16:43:31'),
(750, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-04-25 16:43:34'),
(751, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-04-25 16:44:29'),
(752, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-04-25 16:46:26'),
(753, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-04-25 16:50:18'),
(754, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-05-12 22:16:48'),
(755, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-05-12 22:22:32'),
(756, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-05-12 22:22:42'),
(757, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 10, 0, '2026-05-12 22:45:14'),
(758, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 10, 0, '2026-05-12 22:51:02'),
(759, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-05-12 22:51:13'),
(760, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 10, 0, '2026-05-12 22:51:17'),
(761, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-05-12 22:56:32'),
(762, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 10, 0, '2026-05-12 22:56:37'),
(763, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/product.php?id=1', 10, 0, '2026-05-12 22:59:26'),
(764, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/product.php?id=1', 10, 0, '2026-05-12 23:01:12'),
(765, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 10, 0, '2026-05-12 23:01:31'),
(766, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 10, 0, '2026-05-12 23:01:43'),
(767, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/question.php', 10, 0, '2026-05-12 23:01:57'),
(768, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=1', 'http://localhost/portal/mip/catalog.php', 10, 0, '2026-05-12 23:02:14'),
(769, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?search=%D0%9A%D0%BE%D0%BD%D1%82%D1%80%D0%BE%D0%BB%D0%BB%D0%B5%D1%80', 'http://localhost/portal/mip/catalog.php?category=1', 10, 0, '2026-05-12 23:02:25'),
(770, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/admin/news_edit.php?id=2', 10, 0, '2026-05-12 23:04:20'),
(771, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/news_view.php?id=2', 10, 0, '2026-05-12 23:04:52'),
(772, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 10, 0, '2026-05-12 23:08:00'),
(773, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/news.php', 10, 0, '2026-05-12 23:09:41'),
(774, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 10, 0, '2026-05-12 23:09:43'),
(775, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 10, 0, '2026-05-12 23:09:49'),
(776, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/question.php', 10, 0, '2026-05-12 23:09:57'),
(777, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/main_lab.php', 10, 0, '2026-05-12 23:09:58'),
(778, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/question.php', 10, 0, '2026-05-12 23:12:39'),
(779, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 10, 0, '2026-05-12 23:13:56'),
(780, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', 10, 0, '2026-05-12 23:19:19'),
(781, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/projects.php', 10, 0, '2026-05-12 23:19:30'),
(782, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 10, 0, '2026-05-12 23:19:32'),
(783, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/question.php', 10, 0, '2026-05-12 23:19:34'),
(784, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/about.php', 10, 0, '2026-05-12 23:19:39'),
(785, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-05-12 23:22:49'),
(786, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', NULL, 0, '2026-05-12 23:23:47'),
(787, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', NULL, 0, '2026-05-12 23:26:25'),
(788, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news_view.php?id=2', NULL, 0, '2026-05-12 23:26:57'),
(789, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-05-12 23:27:13'),
(790, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-05-12 23:27:21'),
(791, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-05-12 23:29:09'),
(792, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-05-12 23:29:11'),
(793, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-05-12 23:29:13');
INSERT INTO `visits` (`id`, `ip_address`, `user_agent`, `country`, `city`, `device_type`, `browser`, `page_url`, `referer`, `user_id`, `is_bot`, `created_at`) VALUES
(794, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-05-12 23:29:23'),
(795, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-05-12 23:30:08'),
(796, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-05-12 23:30:13'),
(797, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-05-12 23:34:51'),
(798, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-05-12 23:34:54'),
(799, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-05-12 23:37:58'),
(800, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-05-12 23:38:33'),
(801, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-05-12 23:38:42'),
(802, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-05-12 23:39:10'),
(803, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-05-12 23:39:20'),
(804, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-05-12 23:39:22'),
(805, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-05-12 23:39:35'),
(806, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-05-12 23:45:48'),
(807, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-05-12 23:56:10'),
(808, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 12, 0, '2026-05-12 23:57:41'),
(809, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-05-12 23:57:44'),
(810, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-05-12 23:58:46'),
(811, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-05-13 00:02:05'),
(812, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-05-13 00:03:44'),
(813, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-05-13 00:03:50'),
(814, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-05-13 00:04:29'),
(815, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-05-13 00:04:54'),
(816, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-05-13 00:04:55'),
(817, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-05-13 00:05:16'),
(818, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-05-13 00:05:59'),
(819, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/authorization.php', 17, 0, '2026-05-13 00:06:15'),
(820, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=', 'http://localhost/portal/lk_support.php', 17, 0, '2026-05-13 00:07:40'),
(821, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=services&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=orders&sort=desc&search=', 17, 0, '2026-05-13 00:07:41'),
(822, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=questions&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=services&sort=desc&search=', 17, 0, '2026-05-13 00:07:43'),
(823, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-05-13 00:07:45'),
(824, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', 10, 0, '2026-05-13 00:14:11'),
(825, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-05-13 00:15:26'),
(826, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=1', 'http://localhost/portal/mip/catalog.php', 10, 0, '2026-05-13 00:15:49'),
(827, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-05-13 22:05:44'),
(828, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 10, 0, '2026-05-13 22:10:56'),
(829, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 10, 0, '2026-05-13 22:11:00'),
(830, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/projects.php', 10, 0, '2026-05-13 22:11:05'),
(831, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/question.php', 10, 0, '2026-05-13 22:11:26'),
(832, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/verify_email.php?token=320931253680cc4199bced7a8bdf51258d3d128b5fee0ba223a50531cc3df94e', 43, 0, '2026-05-13 23:01:02'),
(833, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 43, 0, '2026-05-14 00:07:02'),
(834, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', 43, 0, '2026-05-14 00:07:05'),
(835, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/services_catalog.php', 43, 0, '2026-05-14 00:07:13'),
(836, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 43, 0, '2026-05-14 00:07:27'),
(837, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 43, 0, '2026-05-14 00:07:29'),
(838, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/product.php?id=1', 43, 0, '2026-05-14 00:07:41'),
(839, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/authorization.php', 17, 0, '2026-05-14 00:08:06'),
(840, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/mip.php', 43, 0, '2026-05-14 00:09:13'),
(841, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/lk_support.php', 17, 0, '2026-05-14 00:13:20'),
(842, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'https://e.mail.ru/', 43, 0, '2026-05-14 00:14:06'),
(843, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-05-18 14:55:54'),
(844, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', 10, 0, '2026-05-18 18:07:34'),
(845, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 10, 0, '2026-05-18 18:07:40'),
(846, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-05-20 02:10:21'),
(847, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-05-20 02:10:41'),
(848, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-05-20 02:10:44'),
(849, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/question.php', NULL, 0, '2026-05-20 02:10:48'),
(850, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-05-20 02:10:52'),
(851, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-05-20 02:29:54'),
(852, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-05-20 02:29:58'),
(853, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/authorization.php', 17, 0, '2026-05-20 02:30:53'),
(854, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=', 'http://localhost/portal/lk_support.php', 17, 0, '2026-05-20 02:31:03'),
(855, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-05-20 02:33:05'),
(856, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-05-20 02:40:38'),
(857, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-05-20 02:52:21'),
(858, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/lk_lab.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-05-20 02:57:35'),
(859, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/lk_lab.php', 12, 0, '2026-05-20 02:58:06'),
(860, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-05-20 02:58:07'),
(861, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-05-20 03:03:24'),
(862, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-10 21:14:46'),
(863, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-10 21:14:51'),
(864, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-10 21:33:45'),
(865, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-10 21:33:49'),
(866, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-10 21:35:49'),
(867, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-10 21:36:08'),
(868, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/product.php?id=1', NULL, 0, '2026-06-10 21:36:14'),
(869, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', NULL, 0, '2026-06-10 21:36:31'),
(870, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', NULL, 0, '2026-06-10 21:36:39'),
(871, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-10 21:36:49'),
(872, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/question.php', NULL, 0, '2026-06-10 21:36:51'),
(873, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-06-10 21:36:54'),
(874, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-06-10 21:36:57'),
(875, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-11 09:16:10'),
(876, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-11 09:16:16'),
(877, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-11 09:16:24'),
(878, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-06-11 09:16:59'),
(879, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=2', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-06-11 09:17:10'),
(880, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/project_detail.php?id=2', NULL, 0, '2026-06-11 09:17:15'),
(881, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-06-11 09:17:20'),
(882, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-11 09:17:55'),
(883, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-11 09:17:57'),
(884, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-11 09:18:02'),
(885, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-06-11 09:45:39'),
(886, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-11 09:45:44'),
(887, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 09:45:46'),
(888, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 09:45:56'),
(889, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 09:50:38'),
(890, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-11 09:53:20'),
(891, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=3', 12, 0, '2026-06-11 09:56:55'),
(892, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 10:02:18'),
(893, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-11 10:02:24'),
(894, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-06-11 10:02:26'),
(895, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-11 10:13:25'),
(896, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-11 10:28:25'),
(897, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 10:31:12'),
(898, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=4', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 10:31:15'),
(899, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 10:31:18'),
(900, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 10:34:39'),
(901, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 10:43:03'),
(902, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 10:43:08'),
(903, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 10:43:18'),
(904, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 10:43:22'),
(905, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-06-11 10:43:23'),
(906, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 10:43:54'),
(907, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 10:46:26'),
(908, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 10:48:04'),
(909, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 10:48:25'),
(910, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-11 10:48:27'),
(911, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 11:01:05'),
(912, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 11:08:21'),
(913, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 11:16:46'),
(914, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 11:23:07'),
(915, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 11:25:11'),
(916, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 11:26:00'),
(917, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/authorization.php', 17, 0, '2026-06-11 11:26:36'),
(918, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=questions&sort=desc&search=', 'http://localhost/portal/lk_support.php', 17, 0, '2026-06-11 11:29:31'),
(919, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-06-11 11:29:32'),
(920, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 11:30:56'),
(921, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 11:36:29'),
(922, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 11:36:32'),
(923, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 11:36:57'),
(924, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-06-11 11:37:05'),
(925, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/lk_support.php?filter=all&sort=desc&search=', 17, 0, '2026-06-11 11:43:15'),
(926, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/product.php?id=3', 12, 0, '2026-06-11 11:48:29'),
(927, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-11 11:48:35'),
(928, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-11 11:49:10'),
(929, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-06-11 11:49:12'),
(930, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/project_detail.php?id=1', 12, 0, '2026-06-11 11:49:19'),
(931, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-06-11 11:49:21'),
(932, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/lk_lab.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-06-11 11:49:24'),
(933, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-11 11:49:32'),
(934, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-11 11:57:38'),
(935, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 11:57:40'),
(936, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 11:57:42'),
(937, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lk_support.php', 17, 0, '2026-06-11 12:01:44'),
(938, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 12:08:37'),
(939, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 12:13:23'),
(940, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 12:25:29'),
(941, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 12:25:49'),
(942, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 12:25:52'),
(943, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-11 12:26:28'),
(944, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-11 12:26:29'),
(945, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 12:27:05'),
(946, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 14:12:36'),
(947, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=2', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 14:12:40'),
(948, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=1', 'http://localhost/portal/mip/catalog.php?category=2', 12, 0, '2026-06-11 14:12:41'),
(949, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 14:12:44'),
(950, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-11 14:12:46'),
(951, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-06-11 14:12:49'),
(952, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-11 14:13:01'),
(953, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-11 14:13:03'),
(954, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-06-11 14:13:03'),
(955, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-06-11 14:13:04'),
(956, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-06-11 14:13:05'),
(957, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/lk_support.php', 17, 0, '2026-06-11 14:18:30'),
(958, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/about.php', 12, 0, '2026-06-11 14:23:36'),
(959, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-11 14:23:37'),
(960, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 14:23:52'),
(961, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/authorization.php', 17, 0, '2026-06-11 14:24:20'),
(962, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 14:26:27'),
(963, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 14:26:29'),
(964, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 14:26:37'),
(965, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 14:27:14'),
(966, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-11 14:27:14'),
(967, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-11 14:29:09'),
(968, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-11 14:29:13'),
(969, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 14:48:19'),
(970, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 14:55:12'),
(971, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 15:09:16'),
(972, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 15:09:18'),
(973, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 15:15:01'),
(974, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lk_support.php', 17, 0, '2026-06-11 15:17:35'),
(975, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', 17, 0, '2026-06-11 15:17:41'),
(976, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/projects.php', 17, 0, '2026-06-11 15:17:43'),
(977, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/question.php', 17, 0, '2026-06-11 15:17:43'),
(978, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/about.php', 17, 0, '2026-06-11 15:17:58'),
(979, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 15:23:35'),
(980, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 15:23:37'),
(981, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 15:23:44'),
(982, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 15:44:48');
INSERT INTO `visits` (`id`, `ip_address`, `user_agent`, `country`, `city`, `device_type`, `browser`, `page_url`, `referer`, `user_id`, `is_bot`, `created_at`) VALUES
(983, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 15:47:18'),
(984, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=4', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 15:47:21'),
(985, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 15:47:48'),
(986, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 15:54:56'),
(987, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:13:49'),
(988, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:14:07'),
(989, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 16:14:09'),
(990, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:16:00'),
(991, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-11 16:16:03'),
(992, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-11 16:16:05'),
(993, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=3', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-11 16:16:12'),
(994, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 16:16:55'),
(995, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 16:16:58'),
(996, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-11 16:17:02'),
(997, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:22:28'),
(998, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:29:02'),
(999, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 16:29:05'),
(1000, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 16:29:11'),
(1001, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 16:29:13'),
(1002, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:39:38'),
(1003, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:39:41'),
(1004, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 16:39:44'),
(1005, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:40:06'),
(1006, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 16:40:08'),
(1007, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:44:57'),
(1008, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:45:50'),
(1009, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 16:45:52'),
(1010, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 16:45:53'),
(1011, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 16:45:55'),
(1012, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 16:50:18'),
(1013, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 16:52:26'),
(1014, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 16:52:45'),
(1015, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:53:20'),
(1016, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 16:53:42'),
(1017, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 17:03:20'),
(1018, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 17:03:24'),
(1019, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 17:03:29'),
(1020, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 17:03:31'),
(1021, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 17:03:35'),
(1022, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 17:21:31'),
(1023, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 17:21:45'),
(1024, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 17:21:47'),
(1025, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 17:21:53'),
(1026, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 17:22:24'),
(1027, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 18:04:15'),
(1028, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-11 18:04:17'),
(1029, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-11 18:04:21'),
(1030, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 18:04:35'),
(1031, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 18:04:47'),
(1032, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 18:04:48'),
(1033, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 18:05:29'),
(1034, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 18:12:50'),
(1035, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 18:13:06'),
(1036, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 18:13:07'),
(1037, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 18:14:01'),
(1038, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 18:14:04'),
(1039, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 18:37:53'),
(1040, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 18:37:56'),
(1041, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 18:37:58'),
(1042, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=3', 12, 0, '2026-06-11 18:38:00'),
(1043, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 18:38:04'),
(1044, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 18:40:06'),
(1045, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 19:00:22'),
(1046, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 19:00:28'),
(1047, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 19:00:30'),
(1048, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 19:06:59'),
(1049, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 19:16:59'),
(1050, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 19:29:45'),
(1051, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 19:30:24'),
(1052, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 19:30:26'),
(1053, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 19:31:02'),
(1054, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 19:31:06'),
(1055, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 19:40:03'),
(1056, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 19:41:16'),
(1057, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 19:41:17'),
(1058, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=3', 12, 0, '2026-06-11 19:41:20'),
(1059, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 19:41:44'),
(1060, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 19:45:12'),
(1061, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 19:56:41'),
(1062, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 19:56:44'),
(1063, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 19:58:34'),
(1064, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 19:58:38'),
(1065, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 20:10:16'),
(1066, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 20:10:49'),
(1067, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 20:14:54'),
(1068, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 20:22:03'),
(1069, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 20:22:12'),
(1070, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 20:22:14'),
(1071, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 20:22:23'),
(1072, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 20:22:25'),
(1073, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 20:30:37'),
(1074, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-11 20:49:07'),
(1075, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 20:49:48'),
(1076, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-11 20:50:39'),
(1077, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 21:06:40'),
(1078, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-11 21:06:41'),
(1079, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 21:06:44'),
(1080, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-11 21:13:30'),
(1081, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-11 21:25:25'),
(1082, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', NULL, 0, '2026-06-11 21:25:27'),
(1083, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-06-11 23:33:07'),
(1084, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-11 23:33:12'),
(1085, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 23:33:14'),
(1086, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 23:33:28'),
(1087, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=3', 12, 0, '2026-06-11 23:33:30'),
(1088, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-11 23:34:00'),
(1089, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/admin/product_edit.php?id=1', 10, 0, '2026-06-12 00:18:03'),
(1090, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=1', 12, 0, '2026-06-12 00:18:38'),
(1091, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 00:18:58'),
(1092, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 00:20:00'),
(1093, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 00:20:02'),
(1094, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-12 00:35:20'),
(1095, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 01:01:19'),
(1096, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 01:06:19'),
(1097, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 01:06:38'),
(1098, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 01:11:57'),
(1099, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 01:20:26'),
(1100, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-06-12 01:21:22'),
(1101, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/projects.php', NULL, 0, '2026-06-12 01:21:59'),
(1102, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', NULL, 0, '2026-06-12 01:22:13'),
(1103, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/question.php', NULL, 0, '2026-06-12 01:23:43'),
(1104, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 01:25:09'),
(1105, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-12 01:25:25'),
(1106, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/product.php?id=1', NULL, 0, '2026-06-12 01:26:49'),
(1107, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', NULL, 0, '2026-06-12 01:27:11'),
(1108, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', NULL, 0, '2026-06-12 01:27:31'),
(1109, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-12 01:28:08'),
(1110, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 01:29:03'),
(1111, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-06-12 01:29:35'),
(1112, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/lk_user.php', NULL, 0, '2026-06-12 01:30:07'),
(1113, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/authorization.php', 17, 0, '2026-06-12 01:30:23'),
(1114, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lk_support.php?filter=all&sort=asc&search=', 'http://localhost/portal/lk_support.php', 17, 0, '2026-06-12 01:30:35'),
(1115, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=all&sort=asc&search=', 17, 0, '2026-06-12 01:30:36'),
(1116, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lk_support.php?filter=orders&sort=asc&search=', 'http://localhost/portal/lk_support.php?filter=all&sort=asc&search=', 17, 0, '2026-06-12 01:30:58'),
(1117, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lk_support.php?filter=services&sort=asc&search=', 'http://localhost/portal/lk_support.php?filter=orders&sort=asc&search=', 17, 0, '2026-06-12 01:31:04'),
(1118, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lk_support.php?filter=questions&sort=asc&search=', 'http://localhost/portal/lk_support.php?filter=services&sort=asc&search=', 17, 0, '2026-06-12 01:31:09'),
(1119, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/', NULL, 0, '2026-06-12 01:34:30'),
(1120, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 01:35:43'),
(1121, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 01:43:52'),
(1122, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 01:45:07'),
(1123, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 01:50:11'),
(1124, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 02:00:33'),
(1125, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 02:02:39'),
(1126, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 02:06:07'),
(1127, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 02:07:48'),
(1128, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 02:09:22'),
(1129, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-12 02:09:29'),
(1130, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 10:52:53'),
(1131, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 10:59:08'),
(1132, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 11:01:39'),
(1133, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-06-12 11:01:43'),
(1134, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/authorization.php', 12, 0, '2026-06-12 11:02:00'),
(1135, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-12 11:04:40'),
(1136, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-12 11:09:15'),
(1137, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 11:10:07'),
(1138, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-06-12 11:17:12'),
(1139, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 11:18:10'),
(1140, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 11:18:39'),
(1141, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 11:23:50'),
(1142, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/lab/about.php', NULL, 0, '2026-06-12 11:24:47'),
(1143, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-12 11:25:18'),
(1144, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 11:25:19'),
(1145, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 11:30:29'),
(1146, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 11:31:57'),
(1147, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 11:36:15'),
(1148, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 11:38:07'),
(1149, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 11:44:48'),
(1150, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', NULL, NULL, 'desktop', 'Edge', '/portal/mip/mip.php', '', NULL, 0, '2026-06-12 11:50:28'),
(1151, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 11:50:51'),
(1152, '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36', NULL, NULL, 'mobile', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 11:59:20'),
(1153, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 12:04:30'),
(1154, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', '', NULL, 0, '2026-06-12 12:10:19'),
(1155, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 12:19:05'),
(1156, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 12:25:05'),
(1157, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 12:25:58'),
(1158, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 12:26:02'),
(1159, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 12:26:15'),
(1160, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-12 12:30:56'),
(1161, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 12:33:10'),
(1162, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 12:33:27'),
(1163, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 12:33:35'),
(1164, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-12 12:36:29'),
(1165, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', NULL, 0, '2026-06-12 13:03:50'),
(1166, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 13:04:01'),
(1167, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=2', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-12 13:07:29'),
(1168, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?category=1', 'http://localhost/portal/mip/catalog.php?category=2', NULL, 0, '2026-06-12 13:07:31'),
(1169, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/catalog.php?category=2', NULL, 0, '2026-06-12 13:11:51'),
(1170, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/catalog.php?category=2', NULL, 0, '2026-06-12 17:11:27');
INSERT INTO `visits` (`id`, `ip_address`, `user_agent`, `country`, `city`, `device_type`, `browser`, `page_url`, `referer`, `user_id`, `is_bot`, `created_at`) VALUES
(1171, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/catalog.php?category=2', NULL, 0, '2026-06-12 17:16:41'),
(1172, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 17:17:30'),
(1173, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 17:17:57'),
(1174, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-12 17:22:43'),
(1175, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 17:22:44'),
(1176, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 17:22:46'),
(1177, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 17:31:31'),
(1178, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 17:33:58'),
(1179, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 17:34:28'),
(1180, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 17:34:36'),
(1181, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 17:36:05'),
(1182, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 17:37:29'),
(1183, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 17:38:58'),
(1184, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 17:42:24'),
(1185, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 17:42:31'),
(1186, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 17:42:58'),
(1187, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=4', 'http://localhost/portal/mip/catalog.php', NULL, 0, '2026-06-12 17:44:03'),
(1188, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/mip.php', 12, 0, '2026-06-12 17:45:01'),
(1189, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 17:48:41'),
(1190, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 17:54:15'),
(1191, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 17:57:57'),
(1192, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 18:02:10'),
(1193, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 18:02:13'),
(1194, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 18:02:18'),
(1195, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 18:02:41'),
(1196, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-12 18:02:42'),
(1197, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 18:05:21'),
(1198, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 18:10:48'),
(1199, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 18:13:00'),
(1200, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 18:15:51'),
(1201, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 18:20:30'),
(1202, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 18:24:31'),
(1203, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 18:34:05'),
(1204, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 18:35:27'),
(1205, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 18:39:29'),
(1206, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 18:42:45'),
(1207, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 18:44:12'),
(1208, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 18:52:12'),
(1209, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 18:53:20'),
(1210, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 18:54:11'),
(1211, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php?search=%D1%80%D1%80', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 18:57:54'),
(1212, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 18:58:12'),
(1213, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php?search=%D1%80%D1%80', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 18:58:15'),
(1214, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 18:59:58'),
(1215, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 19:03:41'),
(1216, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 19:07:05'),
(1217, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 19:09:16'),
(1218, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 19:09:17'),
(1219, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 19:09:44'),
(1220, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 19:11:21'),
(1221, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 19:16:36'),
(1222, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 19:16:53'),
(1223, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 19:17:50'),
(1224, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 19:19:17'),
(1225, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 19:19:22'),
(1226, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 19:24:59'),
(1227, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 19:26:42'),
(1228, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 19:30:00'),
(1229, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 19:30:01'),
(1230, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 19:30:06'),
(1231, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-12 19:31:05'),
(1232, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/news_view.php?id=2', 12, 0, '2026-06-12 19:31:56'),
(1233, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 19:32:13'),
(1234, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 19:39:15'),
(1235, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 19:39:16'),
(1236, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 19:39:51'),
(1237, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 19:39:52'),
(1238, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 19:39:52'),
(1239, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-12 19:40:59'),
(1240, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/news_view.php?id=2', 12, 0, '2026-06-12 19:48:39'),
(1241, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=2', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-12 19:48:45'),
(1242, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news_view.php?id=5', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-12 19:48:54'),
(1243, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-12 19:49:02'),
(1244, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-06-12 19:51:11'),
(1245, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 19:53:05'),
(1246, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/index.php', NULL, 0, '2026-06-12 19:53:36'),
(1247, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/main_lab.php', NULL, 0, '2026-06-12 19:53:39'),
(1248, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/mip.php', NULL, 0, '2026-06-12 19:53:47'),
(1249, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', NULL, 0, '2026-06-12 19:53:49'),
(1250, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 19:54:11'),
(1251, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 19:54:13'),
(1252, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 19:56:28'),
(1253, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/question.php', 12, 0, '2026-06-12 19:58:04'),
(1254, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 19:58:06'),
(1255, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-12 19:59:22'),
(1256, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-12 20:01:28'),
(1257, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-06-12 20:07:34'),
(1258, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 20:07:37'),
(1259, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-12 20:08:57'),
(1260, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 20:09:37'),
(1261, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 20:09:40'),
(1262, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 20:12:50'),
(1263, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 20:19:47'),
(1264, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-06-12 20:20:08'),
(1265, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-06-12 20:20:24'),
(1266, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 20:21:03'),
(1267, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 20:21:04'),
(1268, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 20:21:04'),
(1269, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-12 20:21:05'),
(1270, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 20:21:46'),
(1271, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/main_lab.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 20:22:23'),
(1272, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/main_lab.php', 12, 0, '2026-06-12 20:22:26'),
(1273, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/project_detail.php?id=1', 'http://localhost/portal/lab/projects.php', 12, 0, '2026-06-12 20:22:28'),
(1274, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/project_detail.php?id=1', 12, 0, '2026-06-12 20:22:31'),
(1275, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lab/news.php', 12, 0, '2026-06-12 20:22:33'),
(1276, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/mip.php', 'http://localhost/portal/lab/question.php', 12, 0, '2026-06-12 20:22:38'),
(1277, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 20:25:58'),
(1278, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 20:31:16'),
(1279, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 20:36:25'),
(1280, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-06-12 20:36:26'),
(1281, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/services_catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 20:37:09'),
(1282, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/news.php', 'http://localhost/portal/mip/services_catalog.php', 12, 0, '2026-06-12 20:37:12'),
(1283, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/news.php', 12, 0, '2026-06-12 20:37:14'),
(1284, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/notifications.php', 12, 0, '2026-06-12 20:38:49'),
(1285, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/waiting_list.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 20:38:59'),
(1286, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/notifications.php', 'http://localhost/portal/mip/waiting_list.php', 12, 0, '2026-06-12 20:42:59'),
(1287, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 20:51:05'),
(1288, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/authorization.php', 17, 0, '2026-06-12 20:58:31'),
(1289, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/question.php', 'http://localhost/portal/lk_support.php', 17, 0, '2026-06-12 20:58:55'),
(1290, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/news.php', 'http://localhost/portal/lab/question.php', 17, 0, '2026-06-12 20:58:56'),
(1291, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/projects.php', 'http://localhost/portal/lab/news.php', 17, 0, '2026-06-12 20:58:56'),
(1292, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lab/about.php', 'http://localhost/portal/lab/projects.php', 17, 0, '2026-06-12 20:58:57'),
(1293, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php', 'http://localhost/portal/lab/about.php', 17, 0, '2026-06-12 21:24:48'),
(1294, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?search=81', 'http://localhost/portal/lk_support.php', 17, 0, '2026-06-12 21:26:34'),
(1295, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=81', 'http://localhost/portal/lk_support.php?search=81', 17, 0, '2026-06-12 21:27:08'),
(1296, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=81', 'http://localhost/portal/lk_support.php?filter=orders&sort=desc&search=81', 17, 0, '2026-06-12 21:27:10'),
(1297, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?search=110', 'http://localhost/portal/lk_support.php?filter=all&sort=desc&search=81', 17, 0, '2026-06-12 21:27:13'),
(1298, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 21:28:01'),
(1299, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 21:28:05'),
(1300, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=3', 12, 0, '2026-06-12 21:28:11'),
(1301, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 21:28:37'),
(1302, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=1', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 21:28:49'),
(1303, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?search=', 'http://localhost/portal/lk_support.php?search=110', 17, 0, '2026-06-12 21:31:13'),
(1304, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/catalog.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 21:38:24'),
(1305, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/product.php?id=3', 'http://localhost/portal/mip/catalog.php', 12, 0, '2026-06-12 21:38:27'),
(1306, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/cart.php', 'http://localhost/portal/mip/product.php?id=3', 12, 0, '2026-06-12 21:38:28'),
(1307, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/lk_user.php', 'http://localhost/portal/mip/cart.php', 12, 0, '2026-06-12 21:38:34'),
(1308, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?search=', 'http://localhost/portal/lk_support.php?search=110', 17, 0, '2026-06-12 21:40:19'),
(1309, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=orders&sort=desc&search=', 'http://localhost/portal/lk_support.php?search=', 17, 0, '2026-06-12 21:41:53'),
(1310, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=questions&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=orders&sort=desc&search=', 17, 0, '2026-06-12 21:41:55'),
(1311, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/mip/question.php', 'http://localhost/portal/mip/lk_user.php', 12, 0, '2026-06-12 21:42:01'),
(1312, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', NULL, NULL, 'desktop', 'Chrome', '/portal/lk_support.php?filter=all&sort=desc&search=', 'http://localhost/portal/lk_support.php?filter=questions&sort=desc&search=', 17, 0, '2026-06-12 21:44:19');

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
-- Индексы таблицы `chat_files`
--
ALTER TABLE `chat_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

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
-- Индексы таблицы `directions`
--
ALTER TABLE `directions`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `lab_projects`
--
ALTER TABLE `lab_projects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `lab_services`
--
ALTER TABLE `lab_services`
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
-- Индексы таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индексы таблицы `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

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
-- Индексы таблицы `product_services`
--
ALTER TABLE `product_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_product_service` (`product_id`,`service_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `user_clientsupport_id` (`user_clientsupport_id`),
  ADD KEY `device_type_id` (`device_type_id`) USING BTREE,
  ADD KEY `fk_request_device` (`device_id`),
  ADD KEY `idx_backorder` (`is_backorder`,`status`);

--
-- Индексы таблицы `request_messages`
--
ALTER TABLE `request_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_id` (`request_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_sender_type` (`sender_type`);

--
-- Индексы таблицы `request_status_history`
--
ALTER TABLE `request_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_id` (`request_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индексы таблицы `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active_order` (`is_active`,`sort_order`,`created_at`);

--
-- Индексы таблицы `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Индексы таблицы `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_page_url` (`page_url`(100)),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_country` (`country`),
  ADD KEY `idx_device` (`device_type`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `chat_files`
--
ALTER TABLE `chat_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- AUTO_INCREMENT для таблицы `directions`
--
ALTER TABLE `directions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `lab_projects`
--
ALTER TABLE `lab_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `lab_services`
--
ALTER TABLE `lab_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `news_images`
--
ALTER TABLE `news_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT для таблицы `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT для таблицы `product_configurations`
--
ALTER TABLE `product_configurations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `product_files`
--
ALTER TABLE `product_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT для таблицы `product_modifications`
--
ALTER TABLE `product_modifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `product_schemes`
--
ALTER TABLE `product_schemes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `product_services`
--
ALTER TABLE `product_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `request`
--
ALTER TABLE `request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT для таблицы `request_messages`
--
ALTER TABLE `request_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT для таблицы `request_status_history`
--
ALTER TABLE `request_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT для таблицы `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `team`
--
ALTER TABLE `team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT для таблицы `visits`
--
ALTER TABLE `visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1313;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `chat_files`
--
ALTER TABLE `chat_files`
  ADD CONSTRAINT `chat_files_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `request_messages` (`id`) ON DELETE CASCADE;

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
-- Ограничения внешнего ключа таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

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
-- Ограничения внешнего ключа таблицы `product_services`
--
ALTER TABLE `product_services`
  ADD CONSTRAINT `fk_product_services_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_services_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `fk_request_device` FOREIGN KEY (`device_id`) REFERENCES `device` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_request_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_ibfk_3` FOREIGN KEY (`user_clientsupport_id`) REFERENCES `clientsupport` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `request_messages`
--
ALTER TABLE `request_messages`
  ADD CONSTRAINT `fk_messages_request` FOREIGN KEY (`request_id`) REFERENCES `request` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `request_status_history`
--
ALTER TABLE `request_status_history`
  ADD CONSTRAINT `fk_status_history_request` FOREIGN KEY (`request_id`) REFERENCES `request` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
