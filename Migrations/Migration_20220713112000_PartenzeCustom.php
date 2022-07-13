<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220713112000_PartenzeCustom extends Migration
{
	public function exec()
	{
		$this->createTable('travio_packages_departures_custom');

		$this->query('INSERT INTO `travio_packages_departures_custom`(`id`) SELECT `id` FROM `travio_packages_departures`');
	}
}
