<?php

use Phinx\Migration\AbstractMigration;

class TravioServicesTypologies extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_services_typologies', ['signed' => true])
			->addColumn('code', 'string', ['null' => false])
			->addColumn('name', 'string', ['null' => false])
			->addColumn('type', 'integer', ['null' => false, 'signed' => true])
			->create();
	}
}
