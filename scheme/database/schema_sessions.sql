-- Sessions table compatible with both LavaLust's Lauth and Database session handler
-- This table includes columns expected by both subsystems:
-- - `session_id` (INT) for Lauth's counting and primary key
-- - `id` (VARCHAR) for PHP session id (used by Database_session_handler)
-- - `user_id`, `browser`, `ip`, `session_data` used by Lauth
-- - `ip_address`, `user_agent`, `timestamp`, `data` used by Database_session_handler

CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id` VARCHAR(128) DEFAULT NULL,
  `user_id` INT DEFAULT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `browser` VARCHAR(255) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `session_data` VARCHAR(255) DEFAULT NULL,
  `data` LONGTEXT,
  `timestamp` INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `session_unique_id` (`id`),
  KEY `idx_user_browser_sessiondata` (`user_id`, `browser`, `session_data`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- End of file
