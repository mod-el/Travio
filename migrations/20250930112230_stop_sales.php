<?php

use Phinx\Migration\AbstractMigration;

class StopSales extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_services_stop_sales')
			->addColumn('service', 'integer', ['null' => false, 'signed' => true])
			->addColumn('created', 'date')
			->addColumn('type', 'string')
			->addColumn('from', 'date', ['null' => false])
			->addColumn('to', 'date', ['null' => false])
			->addColumn('notes', 'text')
			->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
			->create();
	}
}
