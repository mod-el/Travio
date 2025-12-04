<?php

use Phinx\Migration\AbstractMigration;

class DeparturesDuration extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_packages_departures')
			->addColumn('duration', 'integer', ['null' => true])
			->update();
	}
}
