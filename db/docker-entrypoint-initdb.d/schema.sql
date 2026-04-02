-- MySQL 8 schema for Dinggo PHP challenge
-- Component: DB

CREATE DATABASE IF NOT EXISTS dinggo_challenge
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Application-level DB account (avoid using root in app runtime).
CREATE USER IF NOT EXISTS 'app_admin'@'%' IDENTIFIED BY 'app_admin_pass';
GRANT ALL PRIVILEGES ON dinggo_challenge.* TO 'app_admin'@'%';
FLUSH PRIVILEGES;

USE dinggo_challenge;

SET NAMES utf8mb4;

-- Optional: track each sync execution from the BE service.
CREATE TABLE IF NOT EXISTS sync_run (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  run_type ENUM('cars', 'quotes') NOT NULL,
  status ENUM('started', 'success', 'failed') NOT NULL DEFAULT 'started',
  source_endpoint VARCHAR(255) NOT NULL,
  records_received INT UNSIGNED NOT NULL DEFAULT 0,
  records_inserted INT UNSIGNED NOT NULL DEFAULT 0,
  records_updated INT UNSIGNED NOT NULL DEFAULT 0,
  error_message TEXT NULL,
  started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  finished_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_sync_run_type_status (run_type, status),
  KEY idx_sync_run_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Master list of cars from /phptest/cars.
CREATE TABLE IF NOT EXISTS car (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  license_plate VARCHAR(32) NOT NULL,
  license_state VARCHAR(16) NOT NULL,
  vin VARCHAR(64) NOT NULL,
  manufacture_year SMALLINT UNSIGNED NOT NULL,
  colour VARCHAR(64) NOT NULL,
  make VARCHAR(128) NOT NULL,
  model VARCHAR(128) NOT NULL,
  last_synced_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_car_make_model (make, model),
  KEY idx_car_state (license_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quotes returned from /phptest/quotes for each car.
-- Keep payload for flexibility because challenge response format may evolve.
CREATE TABLE IF NOT EXISTS quote (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  car_id BIGINT UNSIGNED NOT NULL,
  price_in_cent INT UNSIGNED NOT NULL,
  repairer VARCHAR(255) NOT NULL,
  overview_of_work TEXT NOT NULL,
  fetched_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_quote_car_id (car_id),
  KEY idx_quote_fetched_at (fetched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
