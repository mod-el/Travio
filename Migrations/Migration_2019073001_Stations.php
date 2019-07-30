<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019073001_Stations extends Migration
{
	public function exec()
	{
		$this->createTable('travio_stations');
		$this->addColumn('travio_stations', 'code', ['null' => false]);

		$this->createTable('travio_stations_texts');
		$this->addColumn('travio_stations_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_stations_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_stations_texts', 'name', ['null' => false]);
		$this->addIndex('travio_stations_texts', 'travio_stations_texts_idx', ['parent']);
		$this->addForeignKey('travio_stations_texts', 'travio_stations_texts', 'parent', 'travio_stations', 'id', ['on-delete' => 'CASCADE']);
	}
}
