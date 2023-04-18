<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20230418102300_HasSuppliers extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_geo', 'has_suppliers', ['type' => 'tinyint', 'null' => false, 'after' => 'parent']);
		$this->addColumn('travio_services', 'has_suppliers', ['type' => 'tinyint', 'null' => false, 'after' => 'max_date']);
		$this->query('UPDATE travio_services SET last_update = NULL');
	}
}
