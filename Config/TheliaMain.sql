
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- header_highlights
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `header_highlights`;

CREATE TABLE `header_highlights`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `category_id` INTEGER,
    `image_block` VARCHAR(255),
    `display_type` VARCHAR(255),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `fi_header_highlights_category` (`category_id`),
    CONSTRAINT `fk_header_highlights_category`
        FOREIGN KEY (`category_id`)
        REFERENCES `category` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- header_highlights_image
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `header_highlights_image`;

CREATE TABLE `header_highlights_image`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `header_highlights_id` INTEGER,
    `file` VARCHAR(255) NOT NULL,
    `visible` TINYINT DEFAULT 1 NOT NULL,
    `position` INTEGER,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `fi_header_highlights_image_parent` (`header_highlights_id`),
    CONSTRAINT `fk_header_highlights_image_parent`
        FOREIGN KEY (`header_highlights_id`)
        REFERENCES `header_highlights` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- header_highlights_i18n
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `header_highlights_i18n`;

CREATE TABLE `header_highlights_i18n`
(
    `id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT 'en_US' NOT NULL,
    `title` VARCHAR(255),
    `call_to_action` VARCHAR(255),
    `url` VARCHAR(255),
    `catchphrase` VARCHAR(255),
    PRIMARY KEY (`id`,`locale`),
    CONSTRAINT `header_highlights_i18n_fk_2edfcb`
        FOREIGN KEY (`id`)
        REFERENCES `header_highlights` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- header_highlights_image_i18n
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `header_highlights_image_i18n`;

CREATE TABLE `header_highlights_image_i18n`
(
    `id` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT 'en_US' NOT NULL,
    `title` VARCHAR(255),
    `description` LONGTEXT,
    `chapo` TEXT,
    `postscriptum` TEXT,
    PRIMARY KEY (`id`,`locale`),
    CONSTRAINT `header_highlights_image_i18n_fk_7068c6`
        FOREIGN KEY (`id`)
        REFERENCES `header_highlights_image` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
