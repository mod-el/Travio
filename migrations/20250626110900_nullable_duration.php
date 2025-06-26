<?php

use Phinx\Migration\AbstractMigration;

class NullableDuration extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_packages')
			->changeColumn('duration', 'integer', ['null' => true, 'signed' => true])
			->update();
	}
}
