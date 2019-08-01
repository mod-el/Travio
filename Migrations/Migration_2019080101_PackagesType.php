<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019080101_PackagesType extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_packages', 'type', ['type' => 'int', 'after' => 'code']);
		$this->query('UPDATE travio_packages SET last_update = NULL');
	}
}
