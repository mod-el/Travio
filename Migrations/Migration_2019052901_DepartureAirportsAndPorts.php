<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019052901_DepartureAirportsAndPorts extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_airports', 'departure', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_ports', 'departure', ['type' => 'tinyint', 'null' => false]);
	}
}
