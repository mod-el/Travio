<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210924172900_PackagesServices extends Migration
{
	public function exec()
	{
		$this->changeColumn('travio_packages_hotels', 'hotel', ['name' => 'service', 'type' => 'INT', 'null' => false]);
		$this->addColumn('travio_packages_hotels', 'type', ['type' => 'tinyint']);
		$this->renameTable('travio_packages_hotels', 'travio_packages_services');

		$this->query('UPDATE travio_packages SET last_update = NULL');
	}
}
