<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20241020113800_DeparturesRoutes extends Migration
{
	public function exec()
	{
		$this->createTable('travio_packages_departures_routes');
		$this->addColumn('travio_packages_departures_routes', 'departure', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_departures_routes', 'departure_airport', ['type' => 'int']);
		$this->addColumn('travio_packages_departures_routes', 'arrival_airport', ['type' => 'int']);
		$this->addColumn('travio_packages_departures_routes', 'arrival_port', ['type' => 'int']);
		$this->addColumn('travio_packages_departures_routes', 'departure_port', ['type' => 'int']);
		$this->addIndex('travio_packages_departures_routes', 'travio_packages_departures_routes_idx', ['departure']);
		$this->addForeignKey('travio_packages_departures_routes', 'travio_packages_departures_routes', 'departure', 'travio_packages_departures', 'id', ['on-delete' => 'CASCADE']);
	}
}
