<?php namespace model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20241023105000_DeparturesRoutes2 extends Migration
{
	public function exec()
	{
		$this->dropColumn('travio_packages_departures', 'departure_airport');
		$this->dropColumn('travio_packages_departures', 'arrival_airport');
		$this->dropColumn('travio_packages_departures', 'arrival_port');
		$this->dropColumn('travio_packages_departures', 'departure_port');
	}
}
