<?php

declare(strict_types=1);

class Migration_20260409_090000_add_locale_and_timezone_to_portal_access_requests
{
	public function run(): void
	{
		$columns = [];
		$statement = Db::instance()->query("
			SELECT COLUMN_NAME
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = 'portal_access_requests'
		");
		$rows = $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];

		foreach ($rows as $row) {
			$columns[] = (string) ($row['COLUMN_NAME'] ?? '');
		}

		if (!in_array('locale', $columns, true)) {
			Db::instance()->exec("
				ALTER TABLE `portal_access_requests`
				ADD COLUMN `locale` VARCHAR(32) NULL AFTER `email_normalized`
			");
		}

		if (!in_array('timezone', $columns, true)) {
			Db::instance()->exec("
				ALTER TABLE `portal_access_requests`
				ADD COLUMN `timezone` VARCHAR(64) NULL AFTER `locale`
			");
		}
	}
}
