<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210125105700_AllTablesCustom extends Migration
{
	public function exec()
	{
		$this->createTable('travio_tags_custom');
		$this->createTable('travio_tags_types_custom');
		$this->createTable('travio_orders_custom');
		$this->createTable('travio_airports_custom');
		$this->createTable('travio_ports_custom');
		$this->createTable('travio_stations_custom');

		$this->createTable('travio_stations_custom_texts');
		$this->addColumn('travio_stations_custom_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_stations_custom_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addIndex('travio_stations_custom_texts', 'travio_stations_custom_texts_idx', ['parent']);
		$this->addForeignKey('travio_stations_custom_texts', 'travio_stations_custom_texts', 'parent', 'travio_stations_custom', 'id', ['on-delete' => 'CASCADE']);
	}
}
