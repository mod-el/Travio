<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019061101_ReupdateAllPackages extends Migration
{
	public function exec()
	{
		$this->query('UPDATE travio_packages SET last_update = NULL');
	}
}
