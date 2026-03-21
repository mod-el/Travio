<?php

use Phinx\Migration\AbstractMigration;

class DropLuggageTypes extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_luggage_types')->drop()->save();
	}
}
