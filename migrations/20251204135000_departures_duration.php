<?php

use Phinx\Migration\AbstractMigration;

class DeparturesDuration extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_packages_departures')
			->addColumn('duration', 'integer', ['null' => true])
			->update();

		$this->execute('UPDATE travio_packages SET last_update = NULL');
	}
}
