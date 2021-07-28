<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210728093900_ClassificazioniCustom extends Migration
{
	public function exec()
	{
		$this->createTable('travio_classifications_custom');
	}
}
