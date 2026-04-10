-- Migration 1.1.0: restructure schema to separate header_highlights (parent) from header_highlights_image (child).
--
-- TheliaMain.sql already contains the final schema. This migration only needs
-- to handle upgrades from 1.0.x where header_highlights_image was the main entity.
-- All statements are idempotent so this file is safe to re-run on any schema state.

SET FOREIGN_KEY_CHECKS = 0;

-- Create parent table if it does not exist yet (fresh installs already have it via TheliaMain.sql)
CREATE TABLE IF NOT EXISTS `header_highlights`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `category_id` INTEGER,
    `image_block` VARCHAR(255),
    `display_type` VARCHAR(255),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `fi_header_highlights_category` (`category_id`)
) ENGINE=InnoDB;

-- Create parent i18n table if it does not exist yet
CREATE TABLE IF NOT EXISTS `header_highlights_i18n`
(
    `id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT 'en_US' NOT NULL,
    `title` VARCHAR(255),
    `call_to_action` VARCHAR(255),
    `url` VARCHAR(255),
    PRIMARY KEY (`id`,`locale`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
