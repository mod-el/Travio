<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220412150500_StationsServices extends Migration
{
	public function exec()
	{
		$this->createTable('travio_stations_services');
		$this->addColumn('travio_stations_services', 'station', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_stations_services', 'type', ['type' => 'enum(\'departure\',\'arrival\')', 'null' => false]);
		$this->addColumn('travio_stations_services', 'service', ['type' => 'int', 'null' => false]);
		$this->addForeignKey('travio_stations_services', 'travio_stations_services_station', 'station', 'travio_stations', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_stations_services', 'travio_stations_services_service', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);
	}
}
