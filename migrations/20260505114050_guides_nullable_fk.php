<?php

use Phinx\Migration\AbstractMigration;

class GuidesNullableFk extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_packages_guides', ['signed' => true])
			->dropForeignKey('guide')
			->addForeignKey('guide', 'travio_master_data', 'id', ['delete' => 'SET NULL', 'update' => 'CASCADE'])
			->update();
	}
}
