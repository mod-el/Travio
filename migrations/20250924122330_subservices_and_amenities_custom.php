<?php

use Phinx\Migration\AbstractMigration;

class SubservicesAndAmenitiesCustom extends AbstractMigration
{
	public function change()
	{
		$this->table('travio_subservices_custom', ['signed' => true])
			->create();

		$this->table('travio_amenities_custom', ['signed' => true])
			->create();
	}
}
