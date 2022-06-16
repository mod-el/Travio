<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220616111200_StationsSubservices extends Migration
{
	public function exec()
	{
		$this->changeColumn('travio_stations_services', 'service', ['type' => 'int', 'null' => true]);
		$this->addColumn('travio_stations_services', 'subservice', ['type' => 'int', 'null' => true, 'after' => 'service']);
		$this->renameTable('travio_stations_services', 'travio_stations_links');
		$this->addForeignKey('travio_stations_links', 'travio_stations_links_subservice', 'subservice', 'travio_subservices', 'id', ['on-delete' => 'CASCADE']);
	}
}
