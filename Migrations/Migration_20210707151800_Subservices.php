<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210707151800_Subservices extends Migration
{
	public function exec()
	{
		$this->createTable('travio_subservices');
		$this->addColumn('travio_subservices', 'service', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_subservices', 'code', ['null' => false]);
		$this->addColumn('travio_subservices', 'type', ['type' => 'int', 'null' => false]);
		$this->addIndex('travio_subservices', 'travio_subservices_service_idx', ['service']);
		$this->addForeignKey('travio_subservices', 'travio_subservices_service', 'service', 'travio_services', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_subservices_texts');
		$this->addColumn('travio_subservices_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_subservices_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_subservices_texts', 'name', ['null' => false]);
		$this->addIndex('travio_subservices_texts', 'travio_subservices_texts_idx', ['parent']);
		$this->addForeignKey('travio_subservices_texts', 'travio_subservices_texts', 'parent', 'travio_subservices', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_subservices_tags');
		$this->addColumn('travio_subservices_tags', 'subservice', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_subservices_tags', 'tag', ['type' => 'INT', 'null' => true]);
		$this->addIndex('travio_subservices_tags', 'travio_subservices_tags_idx', ['subservice']);
		$this->addForeignKey('travio_subservices_tags', 'travio_subservices_tags', 'subservice', 'travio_subservices', 'id', ['on-delete' => 'CASCADE']);
		$this->addIndex('travio_subservices_tags', 'travio_subservices_tags_tag', ['tag']);
		$this->addForeignKey('travio_subservices_tags', 'travio_subservices_tags_tag', 'tag', 'travio_tags');

		$this->createTable('travio_subservices_amenities');
		$this->addColumn('travio_subservices_amenities', 'subservice', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_subservices_amenities', 'amenity', ['type' => 'int']);
		$this->addColumn('travio_subservices_amenities', 'name', ['null' => false]);
		$this->addColumn('travio_subservices_amenities', 'tag');
		$this->addIndex('travio_subservices_amenities', 'travio_subservices_amenities_idx', ['subservice']);
		$this->addIndex('travio_subservices_amenities', 'travio_subservices_amenity_idx', ['amenity']);
		$this->addForeignKey('travio_subservices_amenities', 'travio_subservices_amenities', 'subservice', 'travio_subservices', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_subservices_amenities', 'travio_subservices_amenity', 'amenity', 'travio_amenities');

		$this->createTable('travio_subservices_files');
		$this->addColumn('travio_subservices_files', 'subservice', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_subservices_files', 'name', ['null' => false]);
		$this->addColumn('travio_subservices_files', 'url', ['null' => false]);
		$this->addIndex('travio_subservices_files', 'travio_subservices_files_idx', ['subservice']);
		$this->addForeignKey('travio_subservices_files', 'travio_subservices_files', 'subservice', 'travio_subservices', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_subservices_descriptions');
		$this->addColumn('travio_subservices_descriptions', 'subservice', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_subservices_descriptions', 'tag');
		$this->addIndex('travio_subservices_descriptions', 'travio_subservices_descriptions_idx', ['subservice']);
		$this->addForeignKey('travio_subservices_descriptions', 'travio_subservices_descriptions', 'subservice', 'travio_subservices', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_subservices_descriptions_texts');
		$this->addColumn('travio_subservices_descriptions_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_subservices_descriptions_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_subservices_descriptions_texts', 'title', ['null' => false]);
		$this->addColumn('travio_subservices_descriptions_texts', 'text', ['type' => 'text', 'null' => false]);
		$this->addIndex('travio_subservices_descriptions_texts', 'travio_subservices_descriptions_texts_idx', ['parent']);
		$this->addForeignKey('travio_subservices_descriptions_texts', 'travio_subservices_descriptions_texts', 'parent', 'travio_subservices_descriptions', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('travio_subservices_photos');
		$this->addColumn('travio_subservices_photos', 'subservice', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_subservices_photos', 'url', ['null' => false]);
		$this->addColumn('travio_subservices_photos', 'thumb', ['null' => false]);
		$this->addColumn('travio_subservices_photos', 'description', ['null' => false]);
		$this->addIndex('travio_subservices_photos', 'travio_subservices_photos_idx', ['subservice']);
		$this->addForeignKey('travio_subservices_photos', 'travio_subservices_photos', 'subservice', 'travio_subservices', 'id', ['on-delete' => 'CASCADE']);

		$this->query('UPDATE `travio_services` SET last_update = NULL');
	}
}
