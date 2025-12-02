<?php

use Phinx\Migration\AbstractMigration;

class ServicesDates extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_services_availability')->drop()->save();

		$this->table('travio_services_dates')
			->addColumn('service', 'integer', ['null' => false, 'signed' => true])
			->addColumn('checkin', 'date', ['null' => false])
			->addColumn('time', 'time', ['null' => true, 'default' => null])
			->addColumn('departure', 'string', ['null' => true, 'limit' => 50, 'default' => null])
			->addColumn('arrival', 'string', ['null' => true, 'limit' => 50, 'default' => null])
			->addColumn('checkouts', 'json', ['null' => true, 'default' => null])
			->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
			->addIndex(['service', 'checkin'])
			->addIndex(['service', 'departure'])
			->create();

		$this->execute('UPDATE travio_services SET last_update = NULL');
	}
}
