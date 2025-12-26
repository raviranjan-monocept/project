-- Create categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created` DATETIME DEFAULT NULL,
  `modified` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add category_id to policies (nullable)
ALTER TABLE `policies`
  ADD COLUMN IF NOT EXISTS `category_id` INT(11) NULL AFTER `categories`;

-- Optional: foreign key
-- ALTER TABLE `policies`
--   ADD CONSTRAINT `fk_policies_categories` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Notes:
-- 1) Run this SQL in your database (phpMyAdmin or mysql client).
-- 2) You may want to migrate existing `policies.categories` values into `categories` and set `policies.category_id` accordingly.
