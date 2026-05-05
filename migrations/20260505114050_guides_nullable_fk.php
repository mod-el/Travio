<?php

use Phinx\Migration\AbstractMigration;

class GuidesNullableFk extends AbstractMigration
{
	public function change()
	{
		$table = $this->table('travio_packages_guides', ['signed' => true]);

		if ($table->hasForeignKey('guide')) {
			$table->dropForeignKey('guide')
				->update();
		}

		$this->table('travio_packages_guides', ['signed' => true])
			->changeColumn('guide', 'integer', ['null' => true, 'signed' => true])
			->addForeignKey('guide', 'travio_master_data', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
			->update();
	}
}
