<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220113173600_PhotosOrder extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_services_photos', 'order', ['type' => 'TINYINT', 'null' => false]);
		$this->addColumn('travio_subservices_photos', 'order', ['type' => 'TINYINT', 'null' => false]);
		$this->addColumn('travio_packages_photos', 'order', ['type' => 'TINYINT', 'null' => false]);
	}
}
