-- ----------------------------------------------------------------------------
-- MySQL Workbench Migration
-- Migrated Schemata: venuepro_landlord_dump
-- Source Schemata: venuepro_landlord
-- Created: Thu Jul 10 20:36:20 2025
-- Workbench Version: 8.0.38
-- ----------------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- Schema venuepro_landlord_dump
-- ----------------------------------------------------------------------------
DROP SCHEMA IF EXISTS `venuepro_landlord_dump` ;
CREATE SCHEMA IF NOT EXISTS `venuepro_landlord_dump` ;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.access_control
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`access_control` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `group_id` BIGINT UNSIGNED NOT NULL,
  `entity_type` ENUM('room', 'building') NOT NULL,
  `entity_id` BIGINT UNSIGNED NOT NULL,
  `access_level` ENUM('view', 'book', 'manage') NOT NULL DEFAULT 'view',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `access_control_company_id_group_id_entity_type_entity_id_unique` (`company_id` ASC, `group_id` ASC, `entity_type` ASC, `entity_id` ASC) VISIBLE,
  INDEX `access_control_group_id_foreign` (`group_id` ASC) VISIBLE,
  INDEX `access_control_entity_type_entity_id_index` (`entity_type` ASC, `entity_id` ASC) VISIBLE,
  INDEX `access_control_access_level_index` (`access_level` ASC) VISIBLE,
  CONSTRAINT `access_control_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `access_control_group_id_foreign`
    FOREIGN KEY (`group_id`)
    REFERENCES `venuepro_landlord_dump`.`groups` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.amenity
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`amenity` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `name` VARCHAR(191) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `category` VARCHAR(50) NULL DEFAULT NULL,
  `icon` VARCHAR(50) NULL DEFAULT NULL,
  `is_chargeable` TINYINT(1) NOT NULL DEFAULT '0',
  `default_cost` DECIMAL(10,2) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `amenity_company_id_is_active_index` (`company_id` ASC, `is_active` ASC) VISIBLE,
  INDEX `amenity_category_index` (`category` ASC) VISIBLE,
  CONSTRAINT `amenity_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.audit_log
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `action` ENUM('create', 'update', 'delete', 'login', 'logout', 'other') NOT NULL DEFAULT 'other',
  `details` JSON NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `audit_log_user_id_created_at_index` (`user_id` ASC, `created_at` ASC) VISIBLE,
  INDEX `audit_log_entity_type_entity_id_index` (`entity_type` ASC, `entity_id` ASC) VISIBLE,
  INDEX `audit_log_action_index` (`action` ASC) VISIBLE,
  INDEX `audit_log_created_at_index` (`created_at` ASC) VISIBLE,
  INDEX `audit_log_company_id_index` (`company_id` ASC) VISIBLE,
  CONSTRAINT `audit_log_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `audit_log_user_id_foreign`
    FOREIGN KEY (`user_id`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.blocked_date
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`blocked_date` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `building_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `company_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `date` DATE NOT NULL,
  `start_time` TIME NULL DEFAULT NULL,
  `end_time` TIME NULL DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `reason` TEXT NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `is_recurring` TINYINT(1) NOT NULL DEFAULT '0',
  `recurring_pattern` JSON NULL DEFAULT NULL,
  `is_holiday` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `blocked_date_created_by_foreign` (`created_by` ASC) VISIBLE,
  INDEX `blocked_date_room_id_date_index` (`room_id` ASC, `date` ASC) VISIBLE,
  INDEX `blocked_date_building_id_date_index` (`building_id` ASC, `date` ASC) VISIBLE,
  INDEX `blocked_date_company_id_date_index` (`company_id` ASC, `date` ASC) VISIBLE,
  INDEX `blocked_date_is_holiday_index` (`is_holiday` ASC) VISIBLE,
  CONSTRAINT `blocked_date_building_id_foreign`
    FOREIGN KEY (`building_id`)
    REFERENCES `venuepro_landlord_dump`.`building` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `blocked_date_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `blocked_date_created_by_foreign`
    FOREIGN KEY (`created_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`),
  CONSTRAINT `blocked_date_room_id_foreign`
    FOREIGN KEY (`room_id`)
    REFERENCES `venuepro_landlord_dump`.`room` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.booking
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`booking` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `room_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
  `booking_type` ENUM('internal', 'external') NOT NULL DEFAULT 'internal',
  `created_by` BIGINT UNSIGNED NOT NULL,
  `booked_for_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `delegation_type` ENUM('self', 'other') NOT NULL DEFAULT 'self',
  `external_reference` VARCHAR(255) NULL DEFAULT NULL,
  `is_all_day` TINYINT(1) NOT NULL DEFAULT '0',
  `cancellation_reason` TEXT NULL DEFAULT NULL,
  `cancelled_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `cancelled_at` TIMESTAMP NULL DEFAULT NULL,
  `recurring_pattern` JSON NULL DEFAULT NULL,
  `parent_booking_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `invoice_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `has_been_billed` TINYINT(1) NOT NULL DEFAULT '0',
  `check_in_time` TIMESTAMP NULL DEFAULT NULL,
  `check_out_time` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `booking_booked_for_user_id_foreign` (`booked_for_user_id` ASC) VISIBLE,
  INDEX `booking_cancelled_by_foreign` (`cancelled_by` ASC) VISIBLE,
  INDEX `booking_invoice_id_foreign` (`invoice_id` ASC) VISIBLE,
  INDEX `booking_room_id_date_status_index` (`room_id` ASC, `date` ASC, `status` ASC) VISIBLE,
  INDEX `booking_created_by_status_index` (`created_by` ASC, `status` ASC) VISIBLE,
  INDEX `booking_booking_type_index` (`booking_type` ASC) VISIBLE,
  INDEX `booking_parent_booking_id_index` (`parent_booking_id` ASC) VISIBLE,
  INDEX `booking_date_start_time_end_time_index` (`date` ASC, `start_time` ASC, `end_time` ASC) VISIBLE,
  INDEX `booking_company_id_index` (`company_id` ASC) VISIBLE,
  CONSTRAINT `booking_booked_for_user_id_foreign`
    FOREIGN KEY (`booked_for_user_id`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`),
  CONSTRAINT `booking_cancelled_by_foreign`
    FOREIGN KEY (`cancelled_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`),
  CONSTRAINT `booking_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `booking_created_by_foreign`
    FOREIGN KEY (`created_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`),
  CONSTRAINT `booking_invoice_id_foreign`
    FOREIGN KEY (`invoice_id`)
    REFERENCES `venuepro_landlord_dump`.`invoice` (`id`),
  CONSTRAINT `booking_parent_booking_id_foreign`
    FOREIGN KEY (`parent_booking_id`)
    REFERENCES `venuepro_landlord_dump`.`booking` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `booking_room_id_foreign`
    FOREIGN KEY (`room_id`)
    REFERENCES `venuepro_landlord_dump`.`room` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.booking_attendee
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`booking_attendee` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `email` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NULL DEFAULT NULL,
  `attendee_type` ENUM('internal', 'external', 'guest') NOT NULL DEFAULT 'internal',
  `status` ENUM('pending', 'accepted', 'declined', 'maybe') NOT NULL DEFAULT 'pending',
  `response_message` TEXT NULL DEFAULT NULL,
  `is_organizer` TINYINT(1) NOT NULL DEFAULT '0',
  `access_token` VARCHAR(100) NULL DEFAULT NULL,
  `email_sent` TINYINT(1) NOT NULL DEFAULT '0',
  `email_sent_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `booking_attendee_user_id_foreign` (`user_id` ASC) VISIBLE,
  INDEX `booking_attendee_booking_id_status_index` (`booking_id` ASC, `status` ASC) VISIBLE,
  INDEX `booking_attendee_email_index` (`email` ASC) VISIBLE,
  INDEX `booking_attendee_access_token_index` (`access_token` ASC) VISIBLE,
  INDEX `booking_attendee_company_id_index` (`company_id` ASC) VISIBLE,
  CONSTRAINT `booking_attendee_booking_id_foreign`
    FOREIGN KEY (`booking_id`)
    REFERENCES `venuepro_landlord_dump`.`booking` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `booking_attendee_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `booking_attendee_user_id_foreign`
    FOREIGN KEY (`user_id`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.building
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`building` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `address_line1` VARCHAR(255) NULL DEFAULT NULL,
  `address_line2` VARCHAR(255) NULL DEFAULT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `state_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `country_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `postal_code` VARCHAR(20) NULL DEFAULT NULL,
  `timezone` VARCHAR(50) NOT NULL DEFAULT 'UTC',
  `buffer_time_minutes` INT NOT NULL DEFAULT '0',
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `building_state_id_foreign` (`state_id` ASC) VISIBLE,
  INDEX `building_country_id_foreign` (`country_id` ASC) VISIBLE,
  INDEX `building_company_id_is_active_index` (`company_id` ASC, `is_active` ASC) VISIBLE,
  INDEX `building_name_index` (`name` ASC) VISIBLE,
  CONSTRAINT `building_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `building_country_id_foreign`
    FOREIGN KEY (`country_id`)
    REFERENCES `venuepro_landlord_dump`.`countries` (`id`),
  CONSTRAINT `building_state_id_foreign`
    FOREIGN KEY (`state_id`)
    REFERENCES `venuepro_landlord_dump`.`states` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.cache
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.cache_locks
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.companies
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`companies` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(191) NOT NULL,
  `slug` VARCHAR(191) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `logo` VARCHAR(255) NULL DEFAULT NULL,
  `website` VARCHAR(255) NULL DEFAULT NULL,
  `subscription_level` ENUM('trial', 'basic', 'professional', 'enterprise') NOT NULL DEFAULT 'trial',
  `subscription_expires_at` TIMESTAMP NULL DEFAULT NULL,
  `trial_ends_at` TIMESTAMP NULL DEFAULT NULL,
  `max_users` INT NOT NULL DEFAULT '5',
  `max_rooms` INT NOT NULL DEFAULT '10',
  `allow_external_bookings` TINYINT(1) NOT NULL DEFAULT '0',
  `external_booking_domain_whitelist` TEXT NULL DEFAULT NULL,
  `contact_email` VARCHAR(255) NULL DEFAULT NULL,
  `contact_phone` VARCHAR(20) NULL DEFAULT NULL,
  `billing_email` VARCHAR(255) NULL DEFAULT NULL,
  `billing_contact_name` VARCHAR(255) NULL DEFAULT NULL,
  `stripe_customer_id` VARCHAR(255) NULL DEFAULT NULL,
  `payment_method_id` VARCHAR(255) NULL DEFAULT NULL,
  `address_line1` VARCHAR(255) NULL DEFAULT NULL,
  `address_line2` VARCHAR(255) NULL DEFAULT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `state_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `country_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `postal_code` VARCHAR(20) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `companies_slug_unique` (`slug` ASC) VISIBLE,
  INDEX `companies_state_id_foreign` (`state_id` ASC) VISIBLE,
  INDEX `companies_country_id_foreign` (`country_id` ASC) VISIBLE,
  INDEX `companies_slug_index` (`slug` ASC) VISIBLE,
  INDEX `companies_subscription_level_index` (`subscription_level` ASC) VISIBLE,
  INDEX `companies_is_active_index` (`is_active` ASC) VISIBLE,
  INDEX `companies_stripe_customer_id_index` (`stripe_customer_id` ASC) VISIBLE,
  CONSTRAINT `companies_country_id_foreign`
    FOREIGN KEY (`country_id`)
    REFERENCES `venuepro_landlord_dump`.`countries` (`id`),
  CONSTRAINT `companies_state_id_foreign`
    FOREIGN KEY (`state_id`)
    REFERENCES `venuepro_landlord_dump`.`states` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.company_labels
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`company_labels` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `singular_label` VARCHAR(100) NOT NULL,
  `plural_label` VARCHAR(100) NOT NULL,
  `display_label` VARCHAR(100) NULL DEFAULT NULL,
  `short_label` VARCHAR(50) NULL DEFAULT NULL,
  `description_label` VARCHAR(255) NULL DEFAULT NULL,
  `label_category` VARCHAR(50) NULL DEFAULT NULL,
  `is_system_default` TINYINT(1) NOT NULL DEFAULT '0',
  `is_company_default` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `company_labels_company_id_entity_type_is_company_default_unique` (`company_id` ASC, `entity_type` ASC, `is_company_default` ASC) VISIBLE,
  INDEX `company_labels_entity_type_is_system_default_index` (`entity_type` ASC, `is_system_default` ASC) VISIBLE,
  INDEX `company_labels_label_category_index` (`label_category` ASC) VISIBLE,
  CONSTRAINT `company_labels_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.company_module
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`company_module` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `module_id` BIGINT UNSIGNED NOT NULL,
  `subscription_level` ENUM('basic', 'professional', 'enterprise') NOT NULL DEFAULT 'basic',
  `room_quota` INT NULL DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `billing_cycle` ENUM('monthly', 'yearly') NOT NULL DEFAULT 'monthly',
  `starts_at` TIMESTAMP NULL DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `stripe_subscription_id` VARCHAR(255) NULL DEFAULT NULL,
  `usage_count` INT NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `company_module_company_id_module_id_unique` (`company_id` ASC, `module_id` ASC) VISIBLE,
  INDEX `company_module_module_id_foreign` (`module_id` ASC) VISIBLE,
  INDEX `company_module_is_active_index` (`is_active` ASC) VISIBLE,
  INDEX `company_module_expires_at_index` (`expires_at` ASC) VISIBLE,
  CONSTRAINT `company_module_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `company_module_module_id_foreign`
    FOREIGN KEY (`module_id`)
    REFERENCES `venuepro_landlord_dump`.`module` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.content
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`content` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(191) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `type` ENUM('page', 'article', 'faq', 'testimonial', 'help') NOT NULL DEFAULT 'page',
  `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
  `featured_image` VARCHAR(255) NULL DEFAULT NULL,
  `author_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `published_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `content_slug_unique` (`slug` ASC) VISIBLE,
  INDEX `content_author_id_foreign` (`author_id` ASC) VISIBLE,
  INDEX `content_slug_index` (`slug` ASC) VISIBLE,
  INDEX `content_type_status_index` (`type` ASC, `status` ASC) VISIBLE,
  INDEX `content_status_published_at_index` (`status` ASC, `published_at` ASC) VISIBLE,
  CONSTRAINT `content_author_id_foreign`
    FOREIGN KEY (`author_id`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.countries
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`countries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `phone_code` VARCHAR(10) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `countries_code_unique` (`code` ASC) VISIBLE,
  INDEX `countries_code_index` (`code` ASC) VISIBLE,
  INDEX `countries_name_index` (`name` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.daily_break_time
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`daily_break_time` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `building_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `company_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'all') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `daily_break_time_created_by_foreign` (`created_by` ASC) VISIBLE,
  INDEX `daily_break_time_room_id_day_of_week_is_active_index` (`room_id` ASC, `day_of_week` ASC, `is_active` ASC) VISIBLE,
  INDEX `daily_break_time_building_id_day_of_week_is_active_index` (`building_id` ASC, `day_of_week` ASC, `is_active` ASC) VISIBLE,
  INDEX `daily_break_time_company_id_day_of_week_is_active_index` (`company_id` ASC, `day_of_week` ASC, `is_active` ASC) VISIBLE,
  CONSTRAINT `daily_break_time_building_id_foreign`
    FOREIGN KEY (`building_id`)
    REFERENCES `venuepro_landlord_dump`.`building` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `daily_break_time_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `daily_break_time_created_by_foreign`
    FOREIGN KEY (`created_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`),
  CONSTRAINT `daily_break_time_room_id_foreign`
    FOREIGN KEY (`room_id`)
    REFERENCES `venuepro_landlord_dump`.`room` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.email_template
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`email_template` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(191) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body_html` LONGTEXT NOT NULL,
  `body_text` LONGTEXT NULL DEFAULT NULL,
  `variables_json` JSON NULL DEFAULT NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT '0',
  `template_type` ENUM('booking', 'invoice', 'notification', 'welcome', 'other') NOT NULL DEFAULT 'other',
  `company_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `email_template_company_id_foreign` (`company_id` ASC) VISIBLE,
  INDEX `email_template_name_company_id_index` (`name` ASC, `company_id` ASC) VISIBLE,
  INDEX `email_template_template_type_index` (`template_type` ASC) VISIBLE,
  INDEX `email_template_is_system_index` (`is_system` ASC) VISIBLE,
  CONSTRAINT `email_template_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.external_booking_request
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`external_booking_request` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `room_id` BIGINT UNSIGNED NOT NULL,
  `requester_name` VARCHAR(255) NOT NULL,
  `requester_email` VARCHAR(255) NOT NULL,
  `requester_phone` VARCHAR(20) NULL DEFAULT NULL,
  `company_name` VARCHAR(255) NULL DEFAULT NULL,
  `date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `attendee_count` INT NOT NULL DEFAULT '1',
  `status` ENUM('pending', 'approved', 'denied', 'cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `booking_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `token` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `external_booking_request_token_unique` (`token` ASC) VISIBLE,
  INDEX `external_booking_request_approved_by_foreign` (`approved_by` ASC) VISIBLE,
  INDEX `external_booking_request_booking_id_foreign` (`booking_id` ASC) VISIBLE,
  INDEX `external_booking_request_room_id_status_index` (`room_id` ASC, `status` ASC) VISIBLE,
  INDEX `external_booking_request_date_status_index` (`date` ASC, `status` ASC) VISIBLE,
  INDEX `external_booking_request_requester_email_index` (`requester_email` ASC) VISIBLE,
  INDEX `external_booking_request_token_index` (`token` ASC) VISIBLE,
  INDEX `external_booking_request_company_id_index` (`company_id` ASC) VISIBLE,
  CONSTRAINT `external_booking_request_approved_by_foreign`
    FOREIGN KEY (`approved_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`),
  CONSTRAINT `external_booking_request_booking_id_foreign`
    FOREIGN KEY (`booking_id`)
    REFERENCES `venuepro_landlord_dump`.`booking` (`id`),
  CONSTRAINT `external_booking_request_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `external_booking_request_room_id_foreign`
    FOREIGN KEY (`room_id`)
    REFERENCES `venuepro_landlord_dump`.`room` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.failed_jobs
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `failed_jobs_uuid_unique` (`uuid` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.group_member
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`group_member` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `group_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `role` ENUM('member', 'manager', 'admin') NOT NULL DEFAULT 'member',
  `added_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `group_member_company_id_group_id_user_id_unique` (`company_id` ASC, `group_id` ASC, `user_id` ASC) VISIBLE,
  INDEX `group_member_group_id_foreign` (`group_id` ASC) VISIBLE,
  INDEX `group_member_user_id_foreign` (`user_id` ASC) VISIBLE,
  INDEX `group_member_added_by_foreign` (`added_by` ASC) VISIBLE,
  INDEX `group_member_role_index` (`role` ASC) VISIBLE,
  CONSTRAINT `group_member_added_by_foreign`
    FOREIGN KEY (`added_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`),
  CONSTRAINT `group_member_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `group_member_group_id_foreign`
    FOREIGN KEY (`group_id`)
    REFERENCES `venuepro_landlord_dump`.`groups` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `group_member_user_id_foreign`
    FOREIGN KEY (`user_id`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.groups
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`groups` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `logo` VARCHAR(255) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `deactivated_at` TIMESTAMP NULL DEFAULT NULL,
  `deactivated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `groups_company_id_name_unique` (`company_id` ASC, `name` ASC) VISIBLE,
  INDEX `groups_is_active_index` (`is_active` ASC) VISIBLE,
  INDEX `groups_created_by_index` (`created_by` ASC) VISIBLE,
  INDEX `groups_deactivated_by_foreign` (`deactivated_by` ASC) VISIBLE,
  CONSTRAINT `groups_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `groups_created_by_foreign`
    FOREIGN KEY (`created_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `groups_deactivated_by_foreign`
    FOREIGN KEY (`deactivated_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`)
    ON DELETE SET NULL)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.invoice
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`invoice` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `group_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `external_booking_request_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `invoice_company_id_status_index` (`company_id` ASC, `status` ASC) VISIBLE,
  INDEX `invoice_start_date_end_date_index` (`start_date` ASC, `end_date` ASC) VISIBLE,
  INDEX `invoice_group_id_index` (`group_id` ASC) VISIBLE,
  INDEX `invoice_external_booking_request_id_foreign` (`external_booking_request_id` ASC) VISIBLE,
  CONSTRAINT `invoice_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `invoice_external_booking_request_id_foreign`
    FOREIGN KEY (`external_booking_request_id`)
    REFERENCES `venuepro_landlord_dump`.`external_booking_request` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `invoice_group_id_foreign`
    FOREIGN KEY (`group_id`)
    REFERENCES `venuepro_landlord_dump`.`groups` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.invoice_item
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`invoice_item` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `invoice_id` BIGINT UNSIGNED NOT NULL,
  `resource` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `quantity` INT NOT NULL DEFAULT '1',
  `unit_price` DECIMAL(10,2) NOT NULL,
  `reference_table` VARCHAR(50) NULL DEFAULT NULL,
  `reference_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `invoice_item_invoice_id_index` (`invoice_id` ASC) VISIBLE,
  INDEX `invoice_item_reference_table_reference_id_index` (`reference_table` ASC, `reference_id` ASC) VISIBLE,
  INDEX `invoice_item_company_id_index` (`company_id` ASC) VISIBLE,
  CONSTRAINT `invoice_item_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `invoice_item_invoice_id_foreign`
    FOREIGN KEY (`invoice_id`)
    REFERENCES `venuepro_landlord_dump`.`invoice` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.invoice_template
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`invoice_template` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `name` VARCHAR(191) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `header_html` LONGTEXT NULL DEFAULT NULL,
  `footer_html` LONGTEXT NULL DEFAULT NULL,
  `css_styles` LONGTEXT NULL DEFAULT NULL,
  `logo_path` VARCHAR(255) NULL DEFAULT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT '0',
  `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `invoice_template_created_by_foreign` (`created_by` ASC) VISIBLE,
  INDEX `invoice_template_company_id_is_default_index` (`company_id` ASC, `is_default` ASC) VISIBLE,
  CONSTRAINT `invoice_template_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `invoice_template_created_by_foreign`
    FOREIGN KEY (`created_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.job_batches
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL DEFAULT NULL,
  `cancelled_at` INT NULL DEFAULT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.jobs
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL DEFAULT NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `jobs_queue_index` (`queue` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.migrations
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 41
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.model_has_permissions
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`model_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255) NOT NULL,
  `model_id` BIGINT UNSIGNED NOT NULL,
  `team_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`team_id`, `permission_id`, `model_id`, `model_type`),
  INDEX `model_has_permissions_model_id_model_type_index` (`model_id` ASC, `model_type` ASC) VISIBLE,
  INDEX `model_has_permissions_permission_id_foreign` (`permission_id` ASC) VISIBLE,
  INDEX `model_has_permissions_team_foreign_key_index` (`team_id` ASC) VISIBLE,
  CONSTRAINT `model_has_permissions_permission_id_foreign`
    FOREIGN KEY (`permission_id`)
    REFERENCES `venuepro_landlord_dump`.`permissions` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.model_has_roles
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`model_has_roles` (
  `role_id` BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255) NOT NULL,
  `model_id` BIGINT UNSIGNED NOT NULL,
  `team_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`team_id`, `role_id`, `model_id`, `model_type`),
  INDEX `model_has_roles_model_id_model_type_index` (`model_id` ASC, `model_type` ASC) VISIBLE,
  INDEX `model_has_roles_role_id_foreign` (`role_id` ASC) VISIBLE,
  INDEX `model_has_roles_team_foreign_key_index` (`team_id` ASC) VISIBLE,
  CONSTRAINT `model_has_roles_role_id_foreign`
    FOREIGN KEY (`role_id`)
    REFERENCES `venuepro_landlord_dump`.`roles` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.module
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`module` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(191) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `price_monthly` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `price_yearly` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `stripe_monthly_price_id` VARCHAR(255) NULL DEFAULT NULL,
  `stripe_yearly_price_id` VARCHAR(255) NULL DEFAULT NULL,
  `features_json` JSON NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `module_name_index` (`name` ASC) VISIBLE,
  INDEX `module_is_active_index` (`is_active` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.notification
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`notification` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info',
  `link` VARCHAR(255) NULL DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT '0',
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `notification_type` ENUM('booking', 'invoice', 'system', 'user') NOT NULL DEFAULT 'system',
  `entity_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `entity_type` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `notification_user_id_is_read_index` (`user_id` ASC, `is_read` ASC) VISIBLE,
  INDEX `notification_entity_type_entity_id_index` (`entity_type` ASC, `entity_id` ASC) VISIBLE,
  INDEX `notification_notification_type_index` (`notification_type` ASC) VISIBLE,
  INDEX `notification_company_id_index` (`company_id` ASC) VISIBLE,
  CONSTRAINT `notification_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `notification_user_id_foreign`
    FOREIGN KEY (`user_id`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.otp_attempts
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`otp_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `otp_code` VARCHAR(10) NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `is_used` TINYINT(1) NOT NULL DEFAULT '0',
  `attempt_count` INT NOT NULL DEFAULT '0',
  `last_attempt_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `otp_attempts_user_id_is_used_expires_at_index` (`user_id` ASC, `is_used` ASC, `expires_at` ASC) VISIBLE,
  INDEX `otp_attempts_otp_code_index` (`otp_code` ASC) VISIBLE,
  INDEX `otp_attempts_company_id_index` (`company_id` ASC) VISIBLE,
  CONSTRAINT `otp_attempts_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `otp_attempts_user_id_foreign`
    FOREIGN KEY (`user_id`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.payment
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`payment` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `invoice_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'USD',
  `payment_method` ENUM('credit_card', 'bank_transfer', 'paypal', 'stripe', 'other') NOT NULL DEFAULT 'other',
  `payment_reference` VARCHAR(255) NULL DEFAULT NULL,
  `stripe_payment_intent_id` VARCHAR(255) NULL DEFAULT NULL,
  `stripe_charge_id` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `status` ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  `invoice_number` VARCHAR(255) NULL DEFAULT NULL,
  `invoice_url` VARCHAR(255) NULL DEFAULT NULL,
  `payment_date` TIMESTAMP NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `payment_created_by_foreign` (`created_by` ASC) VISIBLE,
  INDEX `payment_company_id_status_index` (`company_id` ASC, `status` ASC) VISIBLE,
  INDEX `payment_invoice_id_index` (`invoice_id` ASC) VISIBLE,
  INDEX `payment_payment_date_index` (`payment_date` ASC) VISIBLE,
  INDEX `payment_stripe_payment_intent_id_index` (`stripe_payment_intent_id` ASC) VISIBLE,
  CONSTRAINT `payment_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `payment_created_by_foreign`
    FOREIGN KEY (`created_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`),
  CONSTRAINT `payment_invoice_id_foreign`
    FOREIGN KEY (`invoice_id`)
    REFERENCES `venuepro_landlord_dump`.`invoice` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.permissions
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `guard_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `permissions_name_guard_name_unique` (`name` ASC, `guard_name` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.role_has_permissions
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`role_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  INDEX `role_has_permissions_role_id_foreign` (`role_id` ASC) VISIBLE,
  CONSTRAINT `role_has_permissions_permission_id_foreign`
    FOREIGN KEY (`permission_id`)
    REFERENCES `venuepro_landlord_dump`.`permissions` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign`
    FOREIGN KEY (`role_id`)
    REFERENCES `venuepro_landlord_dump`.`roles` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.roles
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `guard_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `roles_team_id_name_guard_name_unique` (`team_id` ASC, `name` ASC, `guard_name` ASC) VISIBLE,
  INDEX `roles_team_foreign_key_index` (`team_id` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.room
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`room` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `building_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `room_type` ENUM('meeting_room', 'conference_room', 'board_room', 'training_room', 'event_space', 'other') NOT NULL DEFAULT 'meeting_room',
  `capacity` INT NOT NULL DEFAULT '1',
  `floor` VARCHAR(50) NULL DEFAULT NULL,
  `room_number` VARCHAR(50) NULL DEFAULT NULL,
  `is_private` TINYINT(1) NOT NULL DEFAULT '0',
  `color_scheme` VARCHAR(7) NOT NULL DEFAULT '#3B82F6',
  `module_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `availability_type` ENUM('standard', 'custom', '24/7') NOT NULL DEFAULT 'standard',
  `service_level_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `buffer_time_minutes` INT NULL DEFAULT NULL,
  `min_notice_minutes` INT NULL DEFAULT NULL,
  `max_notice_days` INT NULL DEFAULT NULL,
  `max_hours_per_day` INT NULL DEFAULT NULL,
  `allow_external_booking` TINYINT(1) NOT NULL DEFAULT '0',
  `hourly_rate` DECIMAL(10,2) NULL DEFAULT NULL,
  `daily_rate` DECIMAL(10,2) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `room_module_id_foreign` (`module_id` ASC) VISIBLE,
  INDEX `room_service_level_id_foreign` (`service_level_id` ASC) VISIBLE,
  INDEX `room_building_id_is_active_index` (`building_id` ASC, `is_active` ASC) VISIBLE,
  INDEX `room_room_type_index` (`room_type` ASC) VISIBLE,
  INDEX `room_capacity_index` (`capacity` ASC) VISIBLE,
  INDEX `room_is_private_index` (`is_private` ASC) VISIBLE,
  INDEX `room_company_id_index` (`company_id` ASC) VISIBLE,
  CONSTRAINT `room_building_id_foreign`
    FOREIGN KEY (`building_id`)
    REFERENCES `venuepro_landlord_dump`.`building` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `room_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `room_module_id_foreign`
    FOREIGN KEY (`module_id`)
    REFERENCES `venuepro_landlord_dump`.`module` (`id`),
  CONSTRAINT `room_service_level_id_foreign`
    FOREIGN KEY (`service_level_id`)
    REFERENCES `venuepro_landlord_dump`.`service_level` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.room_amenity
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`room_amenity` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `room_id` BIGINT UNSIGNED NOT NULL,
  `amenity_id` BIGINT UNSIGNED NOT NULL,
  `additional_cost` DECIMAL(10,2) NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `room_amenity_company_id_room_id_amenity_id_unique` (`company_id` ASC, `room_id` ASC, `amenity_id` ASC) VISIBLE,
  INDEX `room_amenity_room_id_foreign` (`room_id` ASC) VISIBLE,
  INDEX `room_amenity_amenity_id_foreign` (`amenity_id` ASC) VISIBLE,
  CONSTRAINT `room_amenity_amenity_id_foreign`
    FOREIGN KEY (`amenity_id`)
    REFERENCES `venuepro_landlord_dump`.`amenity` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `room_amenity_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `room_amenity_room_id_foreign`
    FOREIGN KEY (`room_id`)
    REFERENCES `venuepro_landlord_dump`.`room` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.room_availability
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`room_availability` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `room_id` BIGINT UNSIGNED NOT NULL,
  `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
  `opens_at` TIME NULL DEFAULT NULL,
  `closes_at` TIME NULL DEFAULT NULL,
  `is_closed` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `room_availability_company_id_room_id_day_of_week_unique` (`company_id` ASC, `room_id` ASC, `day_of_week` ASC) VISIBLE,
  INDEX `room_availability_room_id_foreign` (`room_id` ASC) VISIBLE,
  INDEX `room_availability_day_of_week_index` (`day_of_week` ASC) VISIBLE,
  CONSTRAINT `room_availability_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `room_availability_room_id_foreign`
    FOREIGN KEY (`room_id`)
    REFERENCES `venuepro_landlord_dump`.`room` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.room_media
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`room_media` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `room_id` BIGINT UNSIGNED NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(100) NOT NULL,
  `file_size` BIGINT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT '0',
  `is_primary` TINYINT(1) NOT NULL DEFAULT '0',
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `room_media_created_by_foreign` (`created_by` ASC) VISIBLE,
  INDEX `room_media_room_id_is_primary_index` (`room_id` ASC, `is_primary` ASC) VISIBLE,
  INDEX `room_media_sort_order_index` (`sort_order` ASC) VISIBLE,
  INDEX `room_media_company_id_index` (`company_id` ASC) VISIBLE,
  CONSTRAINT `room_media_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `room_media_created_by_foreign`
    FOREIGN KEY (`created_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`),
  CONSTRAINT `room_media_room_id_foreign`
    FOREIGN KEY (`room_id`)
    REFERENCES `venuepro_landlord_dump`.`room` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.service_level
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`service_level` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `buffer_time_minutes` INT NOT NULL DEFAULT '0',
  `min_notice_minutes` INT NOT NULL DEFAULT '0',
  `max_notice_days` INT NOT NULL DEFAULT '365',
  `max_daily_bookings` INT NULL DEFAULT NULL,
  `max_hours_per_day` INT NULL DEFAULT NULL,
  `allow_external_booking` TINYINT(1) NOT NULL DEFAULT '0',
  `default_hourly_rate` DECIMAL(10,2) NULL DEFAULT NULL,
  `default_daily_rate` DECIMAL(10,2) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `service_level_company_id_name_index` (`company_id` ASC, `name` ASC) VISIBLE,
  CONSTRAINT `service_level_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.sessions
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` TEXT NULL DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `sessions_user_id_index` (`user_id` ASC) VISIBLE,
  INDEX `sessions_last_activity_index` (`last_activity` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.states
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`states` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_id` BIGINT UNSIGNED NOT NULL,
  `code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `states_country_id_code_unique` (`country_id` ASC, `code` ASC) VISIBLE,
  INDEX `states_name_index` (`name` ASC) VISIBLE,
  CONSTRAINT `states_country_id_foreign`
    FOREIGN KEY (`country_id`)
    REFERENCES `venuepro_landlord_dump`.`countries` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.subscription_plan
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`subscription_plan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(191) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `module_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `price_monthly` DECIMAL(10,2) NOT NULL,
  `price_yearly` DECIMAL(10,2) NOT NULL,
  `stripe_monthly_price_id` VARCHAR(255) NULL DEFAULT NULL,
  `stripe_yearly_price_id` VARCHAR(255) NULL DEFAULT NULL,
  `room_quota` INT NULL DEFAULT NULL,
  `user_quota` INT NULL DEFAULT NULL,
  `features_json` JSON NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `subscription_plan_module_id_index` (`module_id` ASC) VISIBLE,
  INDEX `subscription_plan_is_active_index` (`is_active` ASC) VISIBLE,
  CONSTRAINT `subscription_plan_module_id_foreign`
    FOREIGN KEY (`module_id`)
    REFERENCES `venuepro_landlord_dump`.`module` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.system_setting
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`system_setting` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(191) NOT NULL,
  `value` TEXT NOT NULL,
  `type` ENUM('string', 'integer', 'boolean', 'json', 'array') NOT NULL DEFAULT 'string',
  `is_public` TINYINT(1) NOT NULL DEFAULT '0',
  `description` TEXT NULL DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `system_setting_key_unique` (`key` ASC) VISIBLE,
  INDEX `system_setting_updated_by_foreign` (`updated_by` ASC) VISIBLE,
  INDEX `system_setting_key_index` (`key` ASC) VISIBLE,
  INDEX `system_setting_is_public_index` (`is_public` ASC) VISIBLE,
  CONSTRAINT `system_setting_updated_by_foreign`
    FOREIGN KEY (`updated_by`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.usage_limit
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`usage_limit` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `group_id` BIGINT UNSIGNED NOT NULL,
  `resource` VARCHAR(100) NOT NULL,
  `limit_value` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `usage_limit_company_id_group_id_resource_unique` (`company_id` ASC, `group_id` ASC, `resource` ASC) VISIBLE,
  INDEX `usage_limit_group_id_foreign` (`group_id` ASC) VISIBLE,
  INDEX `usage_limit_resource_index` (`resource` ASC) VISIBLE,
  CONSTRAINT `usage_limit_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `usage_limit_group_id_foreign`
    FOREIGN KEY (`group_id`)
    REFERENCES `venuepro_landlord_dump`.`groups` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.usage_log
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`usage_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `group_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `resource` VARCHAR(100) NOT NULL,
  `usage_value` INT NOT NULL DEFAULT '1',
  `logged_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `usage_log_user_id_foreign` (`user_id` ASC) VISIBLE,
  INDEX `usage_log_company_id_resource_logged_at_index` (`company_id` ASC, `resource` ASC, `logged_at` ASC) VISIBLE,
  INDEX `usage_log_group_id_resource_index` (`group_id` ASC, `resource` ASC) VISIBLE,
  INDEX `usage_log_logged_at_index` (`logged_at` ASC) VISIBLE,
  CONSTRAINT `usage_log_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `usage_log_group_id_foreign`
    FOREIGN KEY (`group_id`)
    REFERENCES `venuepro_landlord_dump`.`groups` (`id`),
  CONSTRAINT `usage_log_user_id_foreign`
    FOREIGN KEY (`user_id`)
    REFERENCES `venuepro_landlord_dump`.`users` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table venuepro_landlord_dump.users
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `venuepro_landlord_dump`.`users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(191) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  `group_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `company_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'pending',
  `user_type` ENUM('venuepro_admin', 'system_admin', 'hod', 'booking_agent', 'invitee', 'external') NOT NULL DEFAULT 'invitee',
  `auth_method` ENUM('password', 'otp', 'oauth') NOT NULL DEFAULT 'otp',
  `email_verification_token` VARCHAR(100) NULL DEFAULT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `otp_secret` VARCHAR(255) NULL DEFAULT NULL,
  `oauth_providers` JSON NULL DEFAULT NULL,
  `oauth_id` VARCHAR(255) NULL DEFAULT NULL,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `users_email_unique` (`email` ASC) VISIBLE,
  INDEX `users_role_id_foreign` (`role_id` ASC) VISIBLE,
  INDEX `users_email_index` (`email` ASC) VISIBLE,
  INDEX `users_status_index` (`status` ASC) VISIBLE,
  INDEX `users_user_type_index` (`user_type` ASC) VISIBLE,
  INDEX `users_company_id_index` (`company_id` ASC) VISIBLE,
  INDEX `users_group_id_index` (`group_id` ASC) VISIBLE,
  INDEX `users_email_verification_token_index` (`email_verification_token` ASC) VISIBLE,
  CONSTRAINT `users_company_id_foreign`
    FOREIGN KEY (`company_id`)
    REFERENCES `venuepro_landlord_dump`.`companies` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `users_group_id_foreign`
    FOREIGN KEY (`group_id`)
    REFERENCES `venuepro_landlord_dump`.`groups` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `users_role_id_foreign`
    FOREIGN KEY (`role_id`)
    REFERENCES `venuepro_landlord_dump`.`roles` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 1;
