<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220317132700_ServicesDates extends Migration
{
	public function exec()
	{
		$this->createTable('travio_services_dates');
		$this->addColumn('travio_services_dates', 'service', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_dates', 'date', ['type' => 'date', 'null' => false]);
		$this->addColumn('travio_services_dates', 'min_out', ['type' => 'date', 'null' => true]);
		$this->addForeignKey('travio_services_dates', 'travio_services_dates', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);
	}
}
