<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019061201_ServiceNotes extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_services', 'notes', [
			'after' => 'address',
			'type' => 'text',
			'null' => false,
		]);

		$this->query('UPDATE travio_services SET last_update = NULL');
	}
}
