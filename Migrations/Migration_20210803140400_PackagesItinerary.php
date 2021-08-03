<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210803140400_PackagesItinerary extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_packages', 'duration', ['type' => 'INT', 'after' => 'geo', 'null' => false]);

		$this->createTable('travio_packages_itinerary');
		$this->addColumn('travio_packages_itinerary', 'package', ['type' => 'INT', 'null' => false]);
		$this->addColumn('travio_packages_itinerary', 'day', ['type' => 'INT']);
		$this->addColumn('travio_packages_itinerary', 'geo', ['type' => 'INT']);
		$this->addIndex('travio_packages_itinerary', 'travio_packages_itinerary_idx', ['package']);
		$this->addForeignKey('travio_packages_itinerary', 'travio_packages_itinerary', 'package', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);
		$this->addIndex('travio_packages_itinerary', 'travio_packages_itinerary_geo_idx', ['package']);
		$this->addForeignKey('travio_packages_itinerary', 'travio_packages_itinerary_geo', 'geo', 'travio_geo');

		$this->createTable('travio_packages_itinerary_texts');
		$this->addColumn('travio_packages_itinerary_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_itinerary_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_packages_itinerary_texts', 'name', ['null' => false]);
		$this->addColumn('travio_packages_itinerary_texts', 'description', ['type' => 'text', 'null' => false]);
		$this->addIndex('travio_packages_itinerary_texts', 'travio_packages_itinerary_texts_idx', ['parent']);
		$this->addForeignKey('travio_packages_itinerary_texts', 'travio_packages_itinerary_texts', 'parent', 'travio_packages_itinerary', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_packages_itinerary_photos');
		$this->addColumn('travio_packages_itinerary_photos', 'itinerary', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_itinerary_photos', 'url', ['null' => false]);
		$this->addColumn('travio_packages_itinerary_photos', 'thumb', ['null' => false]);
		$this->addIndex('travio_packages_itinerary_photos', 'travio_packages_itinerary_photos_idx', ['itinerary']);
		$this->addForeignKey('travio_packages_itinerary_photos', 'travio_packages_itinerary_photos', 'itinerary', 'travio_packages_itinerary', 'id', ['on-delete' => 'CASCADE']);

		$this->query('UPDATE travio_packages SET last_update = NULL');
	}
}
