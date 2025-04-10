<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20250410121900_AvailabilityTypes extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_services_availability', 'type', ['null' => false, 'after' => 'total']);
	}
}
