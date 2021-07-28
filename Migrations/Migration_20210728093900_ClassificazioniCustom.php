<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210728093900_ClassificazioniCustom extends Migration
{
	public function exec()
	{
		$this->createTable('travio_classifications_custom');

		$this->query('INSERT INTO `travio_classifications_custom`(`id`) SELECT `id` FROM `travio_classifications`');
	}
}
