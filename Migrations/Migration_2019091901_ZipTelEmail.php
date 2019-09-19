<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019091901_ZipTelEmail extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_services', 'zip', ['after' => 'address']);
		$this->addColumn('travio_services', 'tel', ['after' => 'zip']);
		$this->addColumn('travio_services', 'email', ['after' => 'tel']);
		$this->query('UPDATE travio_services SET last_update = NULL');
	}
}
