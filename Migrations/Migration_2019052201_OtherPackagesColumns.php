<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019052201_OtherPackagesColumns extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_packages', 'geo', ['type' => 'int']);
		$this->addColumn('travio_packages', 'visible', ['type' => 'tinyint', 'null' => false, 'default' => 1]);
		$this->addColumn('travio_packages', 'last_update', ['type' => 'date']);
		$this->addIndex('travio_packages', 'travio_packages_geo_idx', ['geo']);
		$this->addIndex('travio_packages', 'travio', ['travio']);
		$this->addForeignKey('travio_packages', 'travio_packages_geo', 'geo', 'travio_geo');

		$this->createTable('travio_packages_hotels');
		$this->addColumn('travio_packages_hotels', 'package', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_hotels', 'hotel', ['type' => 'int', 'null' => false]);
		$this->addIndex('travio_packages_hotels', 'travio_packages_hotels_idx', ['package']);
		$this->addForeignKey('travio_packages_hotels', 'travio_packages_hotels', 'package', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);
	}
}
