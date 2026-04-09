<?php

declare(strict_types=1);

class Migration_20260408_150000_create_portal_access_requests
{
	public function run(): void
	{
		Db::instance()->exec("CREATE TABLE IF NOT EXISTS `portal_access_requests` (
			`request_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			`email` VARCHAR(320) NOT NULL,
			`email_normalized` VARCHAR(320) NOT NULL,
			`wants_updates` TINYINT(1) NOT NULL DEFAULT 0,
			`status` ENUM('pending_confirmation', 'confirmed') NOT NULL DEFAULT 'pending_confirmation',
			`confirmation_token_hash` VARCHAR(64) NULL,
			`confirmation_expires_at` DATETIME NULL,
			`confirmed_at` DATETIME NULL,
			`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`request_id`),
			UNIQUE KEY `uq_portal_access_requests_email_normalized` (`email_normalized`),
			KEY `idx_portal_access_requests_status` (`status`, `created_at`),
			KEY `idx_portal_access_requests_token_hash` (`confirmation_token_hash`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='__noaudit'");
	}
}
