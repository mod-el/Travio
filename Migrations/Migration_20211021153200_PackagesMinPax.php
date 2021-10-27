<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20211021153200_PackagesMinPax extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_packages', 'min_pax', ['type' => 'INT', 'after' => 'duration']);
		$this->query('UPDATE travio_packages SET last_update = NULL');
	}
}
