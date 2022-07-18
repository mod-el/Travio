<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220718112200_PartenzaDa extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_services', 'departs_from', ['type' => 'int', 'null' => true, 'after' => 'notes']);
		$this->addColumn('travio_packages', 'departs_from', ['type' => 'int', 'null' => true, 'after' => 'geo']);

		$this->addForeignKey('travio_services', 'travio_services_departs_from', 'departs_from', 'travio_geo');
		$this->addForeignKey('travio_packages', 'travio_packages_departs_from', 'departs_from', 'travio_geo');

		$this->query('UPDATE `travio_services` SET `last_update` = NULL');
		$this->query('UPDATE `travio_packages` SET `last_update` = NULL');
	}
}
