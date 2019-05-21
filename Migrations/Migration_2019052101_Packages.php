<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019052101_Packages extends Migration
{
	public function exec()
	{
		$this->createTable('travio_packages');
		$this->addColumn('travio_packages', 'travio', ['null' => false]);
		$this->addColumn('travio_packages', 'code', ['null' => false]);
		$this->addColumn('travio_packages', 'notes', ['type' => 'text', 'null' => false]);
		$this->addColumn('travio_packages', 'price', ['type' => 'decimal(7,2)']);

		$this->createTable('travio_packages_texts');
		$this->addColumn('travio_packages_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_packages_texts', 'name', ['null' => false]);
		$this->addIndex('travio_packages_texts', 'travio_packages_texts_idx', ['parent']);
		$this->addForeignKey('travio_packages_texts', 'travio_packages_texts', 'parent', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_packages_custom');

		$this->createTable('travio_packages_custom_texts');
		$this->addColumn('travio_packages_custom_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_custom_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addIndex('travio_packages_custom_texts', 'travio_packages_custom_texts_idx', ['parent']);
		$this->addForeignKey('travio_packages_custom_texts', 'travio_packages_custom_texts', 'parent', 'travio_packages_custom', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_packages_descriptions');
		$this->addColumn('travio_packages_descriptions', 'package', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_descriptions', 'tag');
		$this->addIndex('travio_packages_descriptions', 'travio_packages_descriptions_idx', ['package']);
		$this->addForeignKey('travio_packages_descriptions', 'travio_packages_descriptions', 'package', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_packages_descriptions_texts');
		$this->addColumn('travio_packages_descriptions_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_descriptions_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_packages_descriptions_texts', 'title', ['null' => false]);
		$this->addColumn('travio_packages_descriptions_texts', 'text', ['type' => 'text', 'null' => false]);
		$this->addIndex('travio_packages_descriptions_texts', 'travio_packages_descriptions_texts_idx', ['parent']);
		$this->addForeignKey('travio_packages_descriptions_texts', 'travio_packages_descriptions_texts', 'parent', 'travio_packages_descriptions', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_packages_photos');
		$this->addColumn('travio_packages_photos', 'package', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_photos', 'url', ['null' => false]);
		$this->addColumn('travio_packages_photos', 'thumb', ['null' => false]);
		$this->addColumn('travio_packages_photos', 'description', ['null' => false]);
		$this->addIndex('travio_packages_photos', 'travio_packages_photos_idx', ['package']);
		$this->addForeignKey('travio_packages_photos', 'travio_packages_photos', 'package', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_packages_geo');
		$this->addColumn('travio_packages_geo', 'package', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_geo', 'geo', ['type' => 'int', 'null' => false]);
		$this->addIndex('travio_packages_geo', 'travio_packages_geo_idx', ['package']);
		$this->addIndex('travio_packages_geo', 'travio_packages_geo_geo_idx', ['geo']);
		$this->addForeignKey('travio_packages_geo', 'travio_packages_geo_package', 'package', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_packages_geo', 'travio_packages_geo_geo', 'geo', 'travio_geo');

		$this->createTable('travio_packages_files');
		$this->addColumn('travio_packages_files', 'package', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_files', 'name', ['null' => false]);
		$this->addColumn('travio_packages_files', 'url', ['null' => false]);
		$this->addIndex('travio_packages_files', 'travio_packages_files_idx', ['package']);
		$this->addForeignKey('travio_packages_files', 'travio_packages_files', 'package', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_packages_tags');
		$this->addColumn('travio_packages_tags', 'package', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_tags', 'tag', ['null' => false]);
		$this->addIndex('travio_packages_tags', 'travio_packages_tags_idx', ['package']);
		$this->addForeignKey('travio_packages_tags', 'travio_packages_tags', 'package', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_packages_departures');
		$this->addColumn('travio_packages_departures', 'package', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_departures', 'date', ['type' => 'date', 'null' => false]);
		$this->addColumn('travio_packages_departures', 'departure_airport', ['type' => 'int']);
		$this->addColumn('travio_packages_departures', 'arrival_airport', ['type' => 'int']);
		$this->addColumn('travio_packages_departures', 'arrival_port', ['type' => 'int']);
		$this->addColumn('travio_packages_departures', 'departure_port', ['type' => 'int']);
		$this->addIndex('travio_packages_departures', 'travio_packages_departures_idx', ['package']);
		$this->addForeignKey('travio_packages_departures', 'travio_packages_departures', 'package', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);
	}

	public function check(): bool
	{
		return $this->tableExists('travio_packages');
	}
}
