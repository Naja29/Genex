-- GENEX DATABASE SCHEMA

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = '+05:30';

-- DATABASE
CREATE DATABASE IF NOT EXISTS `genex_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `genex_db`;

-- TABLES 

DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `product_specs`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `admin_users`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `newsletter_subscribers`;

CREATE TABLE `categories` (
  `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)     NOT NULL,
  `slug`        VARCHAR(100)     NOT NULL,
  `icon`        VARCHAR(100)     NOT NULL DEFAULT 'fas fa-box',
  `description` VARCHAR(255)     DEFAULT NULL,
  `parent_id`   INT UNSIGNED     DEFAULT NULL,
  `sort_order`  INT              NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`),
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(255)   NOT NULL,
  `slug`              VARCHAR(255)   NOT NULL,
  `category_id`       INT UNSIGNED   NOT NULL,
  `brand`             VARCHAR(100)   DEFAULT NULL,
  `sku`               VARCHAR(100)   DEFAULT NULL,
  `price`             DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `old_price`         DECIMAL(12,2)  DEFAULT NULL,
  `short_description` VARCHAR(500)   DEFAULT NULL,
  `description`       TEXT           DEFAULT NULL,
  `stock_qty`         INT            NOT NULL DEFAULT 0,
  `in_stock`          TINYINT(1)     NOT NULL DEFAULT 1,
  `badge`             VARCHAR(50)    DEFAULT NULL,
  `rating`            DECIMAL(3,1)   NOT NULL DEFAULT 0.0,
  `review_count`      INT            NOT NULL DEFAULT 0,
  `is_featured`       TINYINT(1)     NOT NULL DEFAULT 0,
  `is_active`         TINYINT(1)     NOT NULL DEFAULT 1,
  `thumbnail`         VARCHAR(255)   DEFAULT NULL,
  `created_at`        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`),
  UNIQUE KEY `uq_sku`  (`sku`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_images` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED  NOT NULL,
  `image_path` VARCHAR(255)  NOT NULL,
  `sort_order` INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_specs` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED  NOT NULL,
  `spec_key`   VARCHAR(100)  NOT NULL,
  `spec_value` VARCHAR(500)  NOT NULL,
  `sort_order` INT           NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `order_number`     VARCHAR(30)   NOT NULL,
  `customer_name`    VARCHAR(150)  NOT NULL,
  `customer_phone`   VARCHAR(20)   NOT NULL,
  `customer_email`   VARCHAR(150)  DEFAULT NULL,
  `customer_address` TEXT          DEFAULT NULL,
  `items_json`       JSON          NOT NULL,
  `subtotal`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `delivery_charge`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total`            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status`           ENUM('pending','confirmed','processing','dispatched','delivered','cancelled')
                                   NOT NULL DEFAULT 'pending',
  `source`           ENUM('whatsapp','website','instore')
                                   NOT NULL DEFAULT 'whatsapp',
  `notes`            TEXT          DEFAULT NULL,
  `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `order_id`     INT UNSIGNED  NOT NULL,
  `product_id`   INT UNSIGNED  DEFAULT NULL,
  `product_name` VARCHAR(255)  NOT NULL,
  `product_sku`  VARCHAR(100)  DEFAULT NULL,
  `price`        DECIMAL(12,2) NOT NULL,
  `quantity`     INT           NOT NULL DEFAULT 1,
  `subtotal`     DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `admin_users` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)  NOT NULL,
  `username`    VARCHAR(100)  NOT NULL,
  `email`       VARCHAR(150)  NOT NULL,
  `password`    VARCHAR(255)  NOT NULL,
  `role`        ENUM('superadmin','admin','staff') NOT NULL DEFAULT 'admin',
  `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
  `last_login`  TIMESTAMP     NULL DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email`    (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customers` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(150)  NOT NULL,
  `phone`       VARCHAR(20)   NOT NULL,
  `email`       VARCHAR(150)  DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `setting_key`   VARCHAR(100)  NOT NULL,
  `setting_value` TEXT          DEFAULT NULL,
  `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `newsletter_subscribers` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`         VARCHAR(150)  NOT NULL,
  `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
  `subscribed_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contact_messages` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150)  NOT NULL,
  `phone`      VARCHAR(30)   DEFAULT NULL,
  `email`      VARCHAR(150)  NOT NULL,
  `subject`    VARCHAR(100)  DEFAULT NULL,
  `message`    TEXT          NOT NULL,
  `is_read`    TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Default login: username = admin | password = admin123
INSERT INTO `admin_users` (`name`, `username`, `email`, `password`, `role`) VALUES
('Admin', 'admin', 'admin@genex.lk', '$2y$10$u27LwClh1mwBLSYAguXhYuBW2rir.JQWjI2bWY.8F/m6w82G45vI2', 'superadmin');

-- SEED: categories 
INSERT INTO `categories` (`name`, `slug`, `icon`, `description`, `sort_order`) VALUES
('Processors',         'processors',         'fas fa-microchip',     'CPUs from Intel & AMD',                       1),
('Motherboards',       'motherboards',       'fas fa-server',        'Motherboards for all socket types',           2),
('RAM',                'ram',                'fas fa-memory',        'DDR4 & DDR5 memory modules',                  3),
('Storage',            'storage',            'fas fa-hdd',           'SSDs, HDDs and NVMe drives',                  4),
('Graphics Cards',     'graphics-cards',     'fas fa-tv',            'GPUs from NVIDIA and AMD',                    5),
('Monitors',           'monitors',           'fas fa-desktop',       'FHD, QHD and 4K monitors',                    6),
('Keyboards & Mice',   'keyboards-mice',     'fas fa-keyboard',      'Mechanical keyboards and gaming mice',         7),
('Networking',         'networking',         'fas fa-wifi',          'Routers, switches and network adapters',       8),
('Mobile Accessories', 'mobile-accessories', 'fas fa-mobile-alt',    'Displays, batteries and phone parts',          9),
('Power Supplies',     'power-supplies',     'fas fa-bolt',          'ATX and SFX power supply units',              10),
('PC Cases',           'pc-cases',           'fas fa-cube',          'ATX, mATX and ITX PC cases',                  11),
('Cooling',            'cooling',            'fas fa-wind',          'CPU coolers, case fans and thermal paste',    12),
('Laptops',            'laptops',            'fas fa-laptop',        'Laptops and notebooks',                       13),
('Headsets & Audio',   'headsets-audio',     'fas fa-headphones',    'Headsets, speakers and audio gear',           14);

-- SEED: products (10 samples across key categories) 
INSERT INTO `products`
  (`name`, `slug`, `category_id`, `brand`, `sku`, `price`, `old_price`, `short_description`, `stock_qty`, `in_stock`, `badge`, `rating`, `review_count`, `is_featured`)
VALUES
-- Processors
('AMD Ryzen 5 5600X',
 'amd-ryzen-5-5600x', 1, 'AMD', 'AMD-R5-5600X',
 38900.00, 43000.00,
 '6-core 12-thread Zen 3 processor with unlocked multiplier. Best value gaming CPU.',
 10, 1, 'HOT', 4.9, 98, 1),

('AMD Ryzen 7 7700X',
 'amd-ryzen-7-7700x', 1, 'AMD', 'AMD-R7-7700X',
 72000.00, 78000.00,
 '8-core 16-thread Zen 4 processor. AM5 socket with DDR5 support.',
 6, 1, 'NEW', 4.8, 31, 1),

-- Motherboards
('MSI MAG Z790 Tomahawk DDR5',
 'msi-mag-z790-tomahawk', 2, 'MSI', 'MSI-Z790-TMH',
 68000.00, 72000.00,
 'ATX Intel Z790 DDR5 motherboard. Supports 12th and 13th Gen Intel processors.',
 5, 1, 'NEW', 4.8, 22, 1),

-- RAM
('Corsair Vengeance LPX 32GB DDR4',
 'corsair-vengeance-lpx-32gb-ddr4', 3, 'Corsair', 'COR-VLP-32G-D4',
 22000.00, 25000.00,
 '32GB (2×16GB) DDR4 3200MHz CL16 low-profile RAM kit. Ideal for gaming and workstations.',
 12, 1, 'HOT', 4.8, 53, 1),

-- Storage
('Samsung 980 Pro 1TB NVMe SSD',
 'samsung-980-pro-1tb', 4, 'Samsung', 'SAM-980PRO-1TB',
 28000.00, 32000.00,
 'PCIe 4.0 NVMe SSD. Up to 7,000MB/s read. Perfect for gaming and creative workloads.',
 18, 1, 'NEW', 4.9, 89, 1),

-- Graphics Cards
('GIGABYTE RTX 4060 Gaming OC 8GB',
 'gigabyte-rtx-4060-gaming-oc', 5, 'GIGABYTE', 'GBT-RTX4060-OC',
 115000.00, 120000.00,
 'NVIDIA RTX 4060 8GB GDDR6 with DLSS 3, Ray Tracing and triple-fan cooling.',
 6, 1, 'HOT', 4.8, 77, 1),

-- Monitors
('LG 27" FHD IPS 144Hz Gaming Monitor',
 'lg-27-fhd-ips-144hz', 6, 'LG', 'LG-27MK600M',
 42000.00, 46000.00,
 '27-inch Full HD 1920×1080 IPS panel. 144Hz refresh rate with AMD FreeSync.',
 8, 1, 'HOT', 4.7, 56, 1),

-- Keyboards & Mice
('Logitech G305 LIGHTSPEED Wireless Mouse',
 'logitech-g305-wireless', 7, 'Logitech', 'LGT-G305',
 8200.00, 9500.00,
 'LIGHTSPEED wireless gaming mouse with HERO 12K sensor. Up to 250 hours battery life.',
 22, 1, NULL, 4.8, 94, 0),

-- Power Supplies
('Corsair CV550 550W 80+ Bronze PSU',
 'corsair-cv550-550w', 10, 'Corsair', 'COR-CV550',
 12500.00, 14000.00,
 '550W ATX PSU with 80 PLUS Bronze certification and single +12V rail.',
 18, 1, NULL, 4.6, 29, 0),

-- Cooling
('Noctua NH-D15 Dual-Tower CPU Cooler',
 'noctua-nh-d15', 12, 'Noctua', 'NOC-NHD15',
 18500.00, NULL,
 'Flagship dual-tower air cooler with two NF-A15 fans. Near-silent, industry-leading performance.',
 7, 1, 'NEW', 5.0, 12, 1);

-- SEED: product_specs (AMD Ryzen 5 5600X - product_id = 1) 
INSERT INTO `product_specs` (`product_id`, `spec_key`, `spec_value`, `sort_order`) VALUES
(1, 'Socket',          'AM4',             1),
(1, 'Cores / Threads', '6C / 12T',        2),
(1, 'Base Clock',      '3.7 GHz',         3),
(1, 'Boost Clock',     '4.6 GHz',         4),
(1, 'Cache',           '35MB Total',      5),
(1, 'TDP',             '65W',             6),
(1, 'Memory Support',  'DDR4-3200',       7),
(1, 'PCIe Version',    'PCIe 4.0',        8);

-- SEED: settings 
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name',        'Genex - Global Xperience'),
('store_tagline',     'Sri Lanka''s Premier Computer & Electronics Store'),
('store_phone',       '+94 77 723 7962'),
('store_whatsapp',    '94777237962'),
('store_email',       'genecoretech@gmail.com'),
('store_address',     'Lenabatuwa, Kamburupitiya, Matara District, Sri Lanka - 81100'),
('store_hours',       'Mon-Sat: 8:00 AM - 7:00 PM | Sunday: Closed'),
('store_map_embed',   ''),
('store_map_link',    ''),
('facebook_url',      'https://web.facebook.com/genecoretech'),
('instagram_url',     ''),
('youtube_url',       ''),
('tiktok_url',        ''),
('currency_symbol',   'Rs.'),
('currency_code',     'LKR'),
('free_delivery_min', '50000'),
('meta_description',  'Genex - Sri Lanka''s trusted source for genuine computer parts, electronics and accessories. Wholesale & Retail from Kamburupitiya.');
