<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220329145200_ServicesAvailability extends Migration
{
	public function exec()
	{
		$this->createTable('travio_services_availability');
		$this->addColumn('travio_services_availability', 'service', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_availability', 'from', ['type' => 'date', 'null' => false]);
		$this->addColumn('travio_services_availability', 'to', ['type' => 'date', 'null' => false]);
		$this->addColumn('travio_services_availability', 'in_monday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'in_tuesday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'in_wednesday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'in_thursday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'in_friday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'in_saturday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'in_sunday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'out_monday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'out_tuesday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'out_wednesday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'out_thursday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'out_friday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'out_saturday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'out_sunday', ['type' => 'tinyint', 'null' => false]);
		$this->addColumn('travio_services_availability', 'min_stay', ['type' => 'tinyint']);
		$this->addColumn('travio_services_availability', 'only_multiples_of', ['type' => 'tinyint']);
		$this->addColumn('travio_services_availability', 'fixed_duration', ['type' => 'tinyint']);
		$this->addForeignKey('travio_services_availability', 'travio_services_availability', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);

		$this->dropTable('travio_services_dates');
	}
}
