<?php

use Phinx\Migration\AbstractMigration;

class ServicesSupplier extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_services')
			->addColumn('supplier', 'integer', ['null' => true, 'signed' => false, 'after' => 'typology'])
			->update();
	}
}
