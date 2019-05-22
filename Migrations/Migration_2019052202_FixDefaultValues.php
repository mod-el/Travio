<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019052202_FixDefaultValues extends Migration
{
	public function exec()
	{
		$this->changeColumn('travio_services', 'visible', ['type' => 'tinyint', 'null' => false, 'default' => 1]);
		$this->changeColumn('travio_packages', 'visible', ['type' => 'tinyint', 'null' => false, 'default' => 1]);
	}
}
