<?php

use Phinx\Migration\AbstractMigration;

class TravioServicesDepartures extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_services_departures', ['signed' => true])
			->addColumn('service', 'integer', ['null' => false, 'signed' => true])
			->addColumn('date', 'date', ['null' => false])
			->addColumn('duration', 'integer', ['null' => true])
			->addColumn('guides', 'json')
			->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
			->create();

		$this->table('travio_packages_departures')
			->addColumn('guides', 'json')
			->update();
	}
}
