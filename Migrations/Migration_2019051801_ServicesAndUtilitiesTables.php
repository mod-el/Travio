<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019051801_ServicesAndUtilitiesTables extends Migration
{
	public function exec()
	{
		$this->createTable('travio_geo');
		$this->addColumn('travio_geo', 'parent', ['type' => 'int']);

		$this->createTable('travio_geo_texts');
		$this->addColumn('travio_geo_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_geo_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_geo_texts', 'name', ['null' => false]);
		$this->addColumn('travio_geo_texts', 'parent_name');
		$this->addIndex('travio_geo_texts', 'travio_geo_texts_idx', ['parent']);
		$this->addForeignKey('travio_geo_texts', 'travio_geo_texts', 'parent', 'travio_geo', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_geo_custom');

		$this->createTable('travio_geo_custom_texts');
		$this->addColumn('travio_geo_custom_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_geo_custom_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addIndex('travio_geo_custom_texts', 'travio_geo_custom_texts_idx', ['parent']);
		$this->addForeignKey('travio_geo_custom_texts', 'travio_geo_custom_texts', 'parent', 'travio_geo_custom', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_amenities_types');
		$this->addColumn('travio_amenities_types', 'name', ['null' => false]);

		$this->createTable('travio_amenities');
		$this->addColumn('travio_amenities', 'name', ['null' => false]);
		$this->addColumn('travio_amenities', 'type', ['type' => 'int']);
		$this->addIndex('travio_amenities', 'travio_amenities_type_idx', ['type']);
		$this->addForeignKey('travio_amenities', 'travio_amenities_type', 'type', 'travio_amenities_types');

		$this->createTable('travio_services');
		$this->addColumn('travio_services', 'travio', ['null' => false]);
		$this->addColumn('travio_services', 'code', ['null' => false]);
		$this->addColumn('travio_services', 'type', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services', 'type', ['type' => 'int']);
		$this->addColumn('travio_services', 'geo', ['type' => 'int']);
		$this->addColumn('travio_services', 'classification');
		$this->addColumn('travio_services', 'classification_level', ['type' => 'tinyint']);
		$this->addColumn('travio_services', 'lat', ['type' => 'decimal(10,7)']);
		$this->addColumn('travio_services', 'lng', ['type' => 'decimal(10,7)']);
		$this->addColumn('travio_services', 'address');
		$this->addColumn('travio_services', 'price', ['type' => 'decimal(7,2)']);
		$this->addColumn('travio_services', 'min_date', ['type' => 'date']);
		$this->addColumn('travio_services', 'max_date', ['type' => 'date']);
		$this->addColumn('travio_services', 'visible', ['type' => 'tinyint', 'null' => false, 'default' => 1]);
		$this->addColumn('travio_services', 'last_update', ['type' => 'datetime']);
		$this->addIndex('travio_services', 'travio', ['travio']);
		$this->addIndex('travio_services', 'travio_services_geo_idx', ['geo']);
		$this->addForeignKey('travio_services', 'travio_services_geo', 'geo', 'travio_geo');

		$this->createTable('travio_services_texts');
		$this->addColumn('travio_services_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_services_texts', 'name', ['null' => false]);
		$this->addIndex('travio_services_texts', 'travio_services_texts_idx', ['parent']);
		$this->addForeignKey('travio_services_texts', 'travio_services_texts', 'parent', 'travio_services', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_services_videos');
		$this->addColumn('travio_services_videos', 'service', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_videos', 'video', ['null' => false]);
		$this->addIndex('travio_services_videos', 'travio_services_videos_idx', ['service']);
		$this->addForeignKey('travio_services_videos', 'travio_services_videos', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_services_tags');
		$this->addColumn('travio_services_tags', 'service', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_tags', 'tag', ['null' => false]);
		$this->addIndex('travio_services_tags', 'travio_services_tags_idx', ['service']);
		$this->addForeignKey('travio_services_tags', 'travio_services_tags', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_services_amenities');
		$this->addColumn('travio_services_amenities', 'service', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_amenities', 'amenity', ['type' => 'int']);
		$this->addColumn('travio_services_amenities', 'name', ['null' => false]);
		$this->addColumn('travio_services_amenities', 'tag');
		$this->addIndex('travio_services_amenities', 'travio_services_amenities_idx', ['service']);
		$this->addIndex('travio_services_amenities', 'travio_services_amenity_idx', ['amenity']);
		$this->addForeignKey('travio_services_amenities', 'travio_services_amenities', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_services_amenities', 'travio_services_amenity', 'amenity', 'travio_amenities');

		$this->createTable('travio_services_files');
		$this->addColumn('travio_services_files', 'service', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_files', 'name', ['null' => false]);
		$this->addColumn('travio_services_files', 'url', ['null' => false]);
		$this->addIndex('travio_services_files', 'travio_services_files_idx', ['service']);
		$this->addForeignKey('travio_services_files', 'travio_services_files', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_services_descriptions');
		$this->addColumn('travio_services_descriptions', 'service', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_descriptions', 'tag');
		$this->addIndex('travio_services_descriptions', 'travio_services_descriptions_idx', ['service']);
		$this->addForeignKey('travio_services_descriptions', 'travio_services_descriptions', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_services_descriptions_texts');
		$this->addColumn('travio_services_descriptions_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_descriptions_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_services_descriptions_texts', 'title', ['null' => false]);
		$this->addColumn('travio_services_descriptions_texts', 'text', ['type' => 'text', 'null' => false]);
		$this->addIndex('travio_services_descriptions_texts', 'travio_services_descriptions_texts_idx', ['parent']);
		$this->addForeignKey('travio_services_descriptions_texts', 'travio_services_descriptions_texts', 'parent', 'travio_services_descriptions', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_services_photos');
		$this->addColumn('travio_services_photos', 'service', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_photos', 'url', ['null' => false]);
		$this->addColumn('travio_services_photos', 'thumb', ['null' => false]);
		$this->addColumn('travio_services_photos', 'description', ['null' => false]);
		$this->addIndex('travio_services_photos', 'travio_services_photos_idx', ['service']);
		$this->addForeignKey('travio_services_photos', 'travio_services_photos', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_services_geo');
		$this->addColumn('travio_services_geo', 'service', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_geo', 'geo', ['type' => 'int', 'null' => false]);
		$this->addIndex('travio_services_geo', 'travio_services_geo_idx', ['service']);
		$this->addIndex('travio_services_geo', 'travio_services_geo_geo_idx', ['geo']);
		$this->addForeignKey('travio_services_geo', 'travio_services_geo_service', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_services_geo', 'travio_services_geo_geo', 'geo', 'travio_geo');

		$this->createTable('travio_services_custom');

		$this->createTable('travio_services_custom_texts');
		$this->addColumn('travio_services_custom_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_services_custom_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addIndex('travio_services_custom_texts', 'travio_services_custom_texts_idx', ['parent']);
		$this->addForeignKey('travio_services_custom_texts', 'travio_services_custom_texts', 'parent', 'travio_services_custom', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_tags');
		$this->addColumn('travio_tags', 'name', ['null' => false]);
		$this->addColumn('travio_tags', 'type', ['type' => 'int']);
		$this->addColumn('travio_tags', 'type_name');

		$this->createTable('travio_ports');
		$this->addColumn('travio_ports', 'code', ['null' => false]);
		$this->addColumn('travio_ports', 'name', ['null' => false]);

		$this->createTable('travio_airports');
		$this->addColumn('travio_airports', 'code', ['null' => false]);
		$this->addColumn('travio_airports', 'name', ['null' => false]);
	}

	public function check(): bool
	{
		return $this->tableExists('travio_services');
	}
}
