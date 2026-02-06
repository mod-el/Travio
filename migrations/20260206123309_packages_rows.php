<?php

use Phinx\Migration\AbstractMigration;

class PackagesRows extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_packages_services', ['signed' => true])
			->renameColumn('type', 'service_type')
			->addColumn('type', 'string', ['null' => false, 'after' => 'id'])
			->addColumn('from', 'string', ['null' => false, 'after' => 'type'])
			->addColumn('to', 'string', ['null' => false, 'after' => 'from'])
			->addColumn('tag', 'integer', ['null' => true, 'signed' => true, 'after' => 'service'])
			->changeColumn('service', 'integer', ['null' => true, 'signed' => true])
			->addColumn('alternative', 'string', ['null' => true])
			->addForeignKey('tag', 'travio_tags', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
			->update();

		$this->execute('UPDATE travio_packages SET last_update = NULL');
	}
}
