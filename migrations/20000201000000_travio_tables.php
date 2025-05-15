<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class TravioTables extends AbstractMigration
{
	public function change()
	{
		$this->create_travio_airports();

		$this->create_travio_amenities_types();
		$this->create_travio_amenities();
		$this->create_travio_amenities_texts();

		$this->create_travio_classifications();
		$this->create_travio_classifications_custom();

		$this->create_travio_geo();
		$this->create_travio_geo_custom();
		$this->create_travio_geo_custom_texts();
		$this->create_travio_geo_parents();
		$this->create_travio_geo_texts();

		$this->create_travio_luggage_types();

		$this->create_travio_master_data();
		$this->create_travio_master_data_custom();

		$this->create_travio_orders();
		$this->create_travio_orders_custom();

		$this->create_travio_packages();
		$this->create_travio_packages_custom();
		$this->create_travio_packages_custom_texts();
		$this->create_travio_packages_departures();
		$this->create_travio_packages_departures_custom();
		$this->create_travio_packages_departures_routes();
		$this->create_travio_packages_descriptions();
		$this->create_travio_packages_descriptions_texts();
		$this->create_travio_packages_files();
		$this->create_travio_packages_geo();
		$this->create_travio_packages_guides();
		$this->create_travio_packages_itinerary();
		$this->create_travio_packages_itinerary_photos();
		$this->create_travio_packages_itinerary_texts();
		#this->create_travio_packages_photos -> after travio_tags
		#this->create_travio_packages_tags -> after travio_tags
		#this->create_travio_packages_services -> after travio_services
		$this->create_travio_packages_texts();

		$this->create_travio_payment_conditions();
		$this->create_travio_payment_conditions_custom();
		$this->create_travio_payment_methods();
		$this->create_travio_payment_methods_custom();

		$this->create_travio_ports();
		$this->create_travio_ports_custom();

		$this->create_travio_services();
		$this->create_travio_packages_services();
		$this->create_travio_services_amenities();
		$this->create_travio_services_availability();
		$this->create_travio_services_custom();
		$this->create_travio_services_custom_texts();
		$this->create_travio_services_descriptions();
		$this->create_travio_services_descriptions_texts();
		$this->create_travio_services_files();
		$this->create_travio_services_geo();
		#$this->create_travio_services_photos(); -> after travio_tags
		#$this->create_travio_services_tags(); -> after travio_tags
		$this->create_travio_services_texts();
		$this->create_travio_services_videos();

		$this->create_travio_stations();
		$this->create_travio_stations_custom();
		$this->create_travio_stations_custom_texts();
		#$this->create_travio_stations_links(); -> after travio_subservices
		$this->create_travio_stations_texts();

		$this->create_travio_subservices();
		$this->create_travio_stations_links();
		$this->create_travio_subservices_amenities();
		$this->create_travio_subservices_descriptions();
		$this->create_travio_subservices_descriptions_texts();
		$this->create_travio_subservices_files();
		#$this->create_travio_subservices_photos(); -> after travio_tags
		#$this->create_travio_subservices_tags(); -> after travio_tags
		$this->create_travio_subservices_texts();

		$this->create_travio_tags();
		$this->create_travio_packages_photos();
		$this->create_travio_packages_tags();
		$this->create_travio_services_photos();
		$this->create_travio_services_tags();
		$this->create_travio_subservices_photos();
		$this->create_travio_subservices_tags();
		$this->create_travio_tags_custom();
		$this->create_travio_tags_custom_texts();
		$this->create_travio_tags_texts();
	}

	protected function create_travio_geo()
	{
		if (!$this->hasTable('travio_geo')) {
			$this->table('travio_geo', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => true, 'signed' => true])
				->addColumn('has_suppliers', 'boolean', ['null' => false])
				->addColumn('visible', 'boolean', ['null' => false, 'default' => 1])
				->addColumn('last_update', 'datetime', ['null' => true])
				->create();
		}
	}

	protected function create_travio_geo_texts()
	{
		if (!$this->hasTable('travio_geo_texts')) {
			$this->table('travio_geo_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('parent_name', 'string', ['limit' => 255, 'null' => true])
				->addForeignKey('parent', 'travio_geo', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_geo_custom()
	{
		if (!$this->hasTable('travio_geo_custom')) {
			$this->table('travio_geo_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_geo_custom_texts()
	{
		if (!$this->hasTable('travio_geo_custom_texts')) {
			$this->table('travio_geo_custom_texts', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_amenities_types()
	{
		if (!$this->hasTable('travio_amenities_types')) {
			$this->table('travio_amenities_types', ['signed' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->create();
		}
	}

	protected function create_travio_amenities()
	{
		if (!$this->hasTable('travio_amenities')) {
			$this->table('travio_amenities', ['signed' => true])
				->addColumn('type', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('type', 'travio_amenities_types', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services()
	{
		if (!$this->hasTable('travio_services')) {
			$this->table('travio_services', ['signed' => true])
				->addColumn('travio', 'string', ['limit' => 255, 'null' => false])
				->addColumn('code', 'string', ['limit' => 255, 'null' => false])
				->addColumn('type', 'integer', ['null' => false, 'signed' => true])
				->addColumn('typology', 'integer', ['null' => true, 'signed' => true])
				->addColumn('geo', 'integer', ['null' => true, 'signed' => true])
				->addColumn('classification_id', 'integer', ['null' => true, 'signed' => true])
				->addColumn('classification', 'string', ['limit' => 255, 'null' => true])
				->addColumn('classification_level', 'decimal', ['precision' => 2, 'scale' => 1, 'null' => true])
				->addColumn('lat', 'decimal', ['precision' => 10, 'scale' => 7, 'null' => true])
				->addColumn('lng', 'decimal', ['precision' => 10, 'scale' => 7, 'null' => true])
				->addColumn('address', 'string', ['limit' => 255, 'null' => true])
				->addColumn('zip', 'string', ['limit' => 255, 'null' => true])
				->addColumn('tel', 'string', ['limit' => 255, 'null' => true])
				->addColumn('email', 'string', ['limit' => 255, 'null' => true])
				->addColumn('notes', 'text', ['null' => false])
				->addColumn('departs_from', 'integer', ['null' => true, 'signed' => true])
				->addColumn('price', 'decimal', ['precision' => 7, 'scale' => 2, 'null' => true])
				->addColumn('min_date', 'date', ['null' => true])
				->addColumn('max_date', 'date', ['null' => true])
				->addColumn('has_suppliers', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('visible', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 1])
				->addColumn('last_update', 'datetime', ['null' => true])
				->addIndex(['travio'])
				->addForeignKey('classification_id', 'travio_classifications', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
				->addForeignKey('departs_from', 'travio_geo', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->addForeignKey('geo', 'travio_geo', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_texts()
	{
		if (!$this->hasTable('travio_services_texts')) {
			$this->table('travio_services_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('title', 'string', ['limit' => 255, 'null' => true])
				->addColumn('description', 'text', ['null' => true])
				->addColumn('keywords', 'text', ['null' => true])
				->addForeignKey('parent', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_videos()
	{
		if (!$this->hasTable('travio_services_videos')) {
			$this->table('travio_services_videos', ['signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('video', 'string', ['limit' => 255, 'null' => false])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_tags()
	{
		if (!$this->hasTable('travio_services_tags')) {
			$this->table('travio_services_tags', ['signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('tag', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('tag', 'travio_tags', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_amenities()
	{
		if (!$this->hasTable('travio_services_amenities')) {
			$this->table('travio_services_amenities', ['signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('amenity', 'integer', ['null' => true, 'signed' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('tag', 'string', ['limit' => 255, 'null' => true])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('amenity', 'travio_amenities', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_files()
	{
		if (!$this->hasTable('travio_services_files')) {
			$this->table('travio_services_files', ['signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('url', 'string', ['limit' => 255, 'null' => false])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_descriptions()
	{
		if (!$this->hasTable('travio_services_descriptions')) {
			$this->table('travio_services_descriptions', ['signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('tag', 'string', ['limit' => 255, 'null' => true])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_descriptions_texts()
	{
		if (!$this->hasTable('travio_services_descriptions_texts')) {
			$this->table('travio_services_descriptions_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('title', 'string', ['limit' => 255, 'null' => false])
				->addColumn('text', 'text', ['null' => false])
				->addForeignKey('parent', 'travio_services_descriptions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_photos()
	{
		if (!$this->hasTable('travio_services_photos')) {
			$this->table('travio_services_photos', ['signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('url', 'string', ['limit' => 255, 'null' => false])
				->addColumn('thumb', 'string', ['limit' => 255, 'null' => false])
				->addColumn('description', 'string', ['limit' => 255, 'null' => false])
				->addColumn('order', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('tag', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('tag', 'travio_tags', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_geo()
	{
		if (!$this->hasTable('travio_services_geo')) {
			$this->table('travio_services_geo', ['signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('geo', 'integer', ['null' => false, 'signed' => true])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('geo', 'travio_geo', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_custom()
	{
		if (!$this->hasTable('travio_services_custom')) {
			$this->table('travio_services_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_services_custom_texts()
	{
		if (!$this->hasTable('travio_services_custom_texts')) {
			$this->table('travio_services_custom_texts', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_tags()
	{
		if (!$this->hasTable('travio_tags')) {
			$this->table('travio_tags', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('parent', 'travio_tags', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_ports()
	{
		if (!$this->hasTable('travio_ports')) {
			$this->table('travio_ports', ['signed' => true])
				->addColumn('code', 'string', ['limit' => 255, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('departure', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->create();
		}
	}

	protected function create_travio_airports()
	{
		if (!$this->hasTable('travio_airports')) {
			$this->table('travio_airports', ['signed' => true])
				->addColumn('code', 'string', ['limit' => 255, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('departure', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->create();
		}
		if (!$this->hasTable('travio_airports_custom')) {
			$this->table('travio_airports_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_packages()
	{
		if (!$this->hasTable('travio_packages')) {
			$this->table('travio_packages', ['signed' => true])
				->addColumn('travio', 'string', ['limit' => 255, 'null' => false])
				->addColumn('code', 'string', ['limit' => 255, 'null' => false])
				->addColumn('type', 'integer', ['null' => true, 'signed' => true])
				->addColumn('notes', 'text', ['null' => false])
				->addColumn('price', 'decimal', ['precision' => 7, 'scale' => 2, 'null' => true])
				->addColumn('geo', 'integer', ['null' => true, 'signed' => true])
				->addColumn('departs_from', 'integer', ['null' => true, 'signed' => true])
				->addColumn('duration', 'integer', ['null' => false, 'signed' => true])
				->addColumn('min_pax', 'integer', ['null' => true, 'signed' => true])
				->addColumn('visible', 'boolean', ['null' => false, 'default' => 1])
				->addColumn('last_update', 'datetime', ['null' => true])
				->addIndex(['travio'])
				->addForeignKey('geo', 'travio_geo', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->addForeignKey('departs_from', 'travio_geo', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_texts()
	{
		if (!$this->hasTable('travio_packages_texts')) {
			$this->table('travio_packages_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('title', 'string', ['limit' => 255, 'null' => true])
				->addColumn('description', 'text', ['null' => true])
				->addColumn('keywords', 'text', ['null' => true])
				->addForeignKey('parent', 'travio_packages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_custom()
	{
		if (!$this->hasTable('travio_packages_custom')) {
			$this->table('travio_packages_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_packages_custom_texts()
	{
		if (!$this->hasTable('travio_packages_custom_texts')) {
			$this->table('travio_packages_custom_texts', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_packages_descriptions()
	{
		if (!$this->hasTable('travio_packages_descriptions')) {
			$this->table('travio_packages_descriptions', ['signed' => true])
				->addColumn('package', 'integer', ['null' => false, 'signed' => true])
				->addColumn('tag', 'string', ['limit' => 255, 'null' => true])
				->addForeignKey('package', 'travio_packages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_descriptions_texts()
	{
		if (!$this->hasTable('travio_packages_descriptions_texts')) {
			$this->table('travio_packages_descriptions_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('title', 'string', ['limit' => 255, 'null' => false])
				->addColumn('text', 'text', ['null' => false])
				->addForeignKey('parent', 'travio_packages_descriptions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_photos()
	{
		if (!$this->hasTable('travio_packages_photos')) {
			$this->table('travio_packages_photos', ['signed' => true])
				->addColumn('package', 'integer', ['null' => false, 'signed' => true])
				->addColumn('url', 'string', ['limit' => 255, 'null' => false])
				->addColumn('thumb', 'string', ['limit' => 255, 'null' => false])
				->addColumn('description', 'string', ['limit' => 255, 'null' => false])
				->addColumn('order', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('tag', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('package', 'travio_packages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('tag', 'travio_tags', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_geo()
	{
		if (!$this->hasTable('travio_packages_geo')) {
			$this->table('travio_packages_geo', ['signed' => true])
				->addColumn('package', 'integer', ['null' => false, 'signed' => true])
				->addColumn('geo', 'integer', ['null' => false, 'signed' => true])
				->addForeignKey('package', 'travio_packages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('geo', 'travio_geo', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_files()
	{
		if (!$this->hasTable('travio_packages_files')) {
			$this->table('travio_packages_files', ['signed' => true])
				->addColumn('package', 'integer', ['null' => false, 'signed' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('url', 'string', ['limit' => 255, 'null' => false])
				->addForeignKey('package', 'travio_packages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_tags()
	{
		if (!$this->hasTable('travio_packages_tags')) {
			$this->table('travio_packages_tags', ['signed' => true])
				->addColumn('package', 'integer', ['null' => false, 'signed' => true])
				->addColumn('tag', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('package', 'travio_packages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('tag', 'travio_tags', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_departures()
	{
		if (!$this->hasTable('travio_packages_departures')) {
			$this->table('travio_packages_departures', ['signed' => true])
				->addColumn('package', 'integer', ['null' => false, 'signed' => true])
				->addColumn('date', 'date', ['null' => false])
				->addForeignKey('package', 'travio_packages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_services()
	{
		if (!$this->hasTable('travio_packages_services')) {
			$this->table('travio_packages_services', ['signed' => true])
				->addColumn('package', 'integer', ['null' => false, 'signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('type', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_TINY])
				->addForeignKey('package', 'travio_packages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_stations()
	{
		if (!$this->hasTable('travio_stations')) {
			$this->table('travio_stations', ['signed' => true])
				->addColumn('code', 'string', ['limit' => 255, 'null' => false])
				->create();
		}
	}

	protected function create_travio_stations_texts()
	{
		if (!$this->hasTable('travio_stations_texts')) {
			$this->table('travio_stations_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addForeignKey('parent', 'travio_stations', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_amenities_texts()
	{
		if (!$this->hasTable('travio_amenities_texts')) {
			$this->table('travio_amenities_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addForeignKey('parent', 'travio_amenities', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_geo_parents()
	{
		if (!$this->hasTable('travio_geo_parents')) {
			$this->table('travio_geo_parents', ['signed' => true])
				->addColumn('geo', 'integer', ['null' => false, 'signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addForeignKey('geo', 'travio_geo', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('parent', 'travio_geo', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_orders()
	{
		if (!$this->hasTable('travio_orders')) {
			$this->table('travio_orders', ['signed' => true])
				->addColumn('reservation', 'integer', ['null' => false, 'signed' => true])
				->addColumn('reference', 'string', ['limit' => 255, 'null' => false])
				->addColumn('amount', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => false])
				->addColumn('date', 'datetime', ['null' => false])
				->addColumn('gateway', 'string', ['limit' => 255, 'null' => true])
				->addColumn('paid', 'datetime', ['null' => true])
				->addColumn('is_first_payment', 'boolean', ['null' => false, 'default' => 1])
				->addColumn('initial_status', 'boolean', ['null' => true])
				->addColumn('response', 'text', ['null' => true])
				->create();
		}
	}

	protected function create_travio_tags_custom()
	{
		if (!$this->hasTable('travio_tags_custom')) {
			$this->table('travio_tags_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_orders_custom()
	{
		if (!$this->hasTable('travio_orders_custom')) {
			$this->table('travio_orders_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_airports_custom()
	{
		if (!$this->hasTable('travio_airports_custom')) {
			$this->table('travio_airports_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_ports_custom()
	{
		if (!$this->hasTable('travio_ports_custom')) {
			$this->table('travio_ports_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_stations_custom()
	{
		if (!$this->hasTable('travio_stations_custom')) {
			$this->table('travio_stations_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_stations_custom_texts()
	{
		if (!$this->hasTable('travio_stations_custom_texts')) {
			$this->table('travio_stations_custom_texts', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_master_data()
	{
		if (!$this->hasTable('travio_master_data')) {
			$this->table('travio_master_data', ['signed' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => true])
				->addColumn('surname', 'string', ['limit' => 255, 'null' => true])
				->addColumn('business_name', 'string', ['limit' => 255, 'null' => true])
				->addColumn('full_name', 'string', ['limit' => 255, 'null' => true])
				->addColumn('category', 'integer', ['null' => true, 'signed' => true])
				->addColumn('username', 'string', ['limit' => 255, 'null' => true])
				->create();
		}
	}

	protected function create_travio_master_data_custom()
	{
		if (!$this->hasTable('travio_master_data_custom')) {
			$this->table('travio_master_data_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_subservices()
	{
		if (!$this->hasTable('travio_subservices')) {
			$this->table('travio_subservices', ['signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('code', 'string', ['limit' => 255, 'null' => false])
				->addColumn('type', 'integer', ['null' => false, 'signed' => true])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_subservices_texts()
	{
		if (!$this->hasTable('travio_subservices_texts')) {
			$this->table('travio_subservices_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addForeignKey('parent', 'travio_subservices', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_subservices_tags()
	{
		if (!$this->hasTable('travio_subservices_tags')) {
			$this->table('travio_subservices_tags', ['signed' => true])
				->addColumn('subservice', 'integer', ['null' => false, 'signed' => true])
				->addColumn('tag', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('subservice', 'travio_subservices', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('tag', 'travio_tags', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_subservices_amenities()
	{
		if (!$this->hasTable('travio_subservices_amenities')) {
			$this->table('travio_subservices_amenities', ['signed' => true])
				->addColumn('subservice', 'integer', ['null' => false, 'signed' => true])
				->addColumn('amenity', 'integer', ['null' => true, 'signed' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('tag', 'string', ['limit' => 255, 'null' => true])
				->addForeignKey('subservice', 'travio_subservices', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('amenity', 'travio_amenities', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_subservices_files()
	{
		if (!$this->hasTable('travio_subservices_files')) {
			$this->table('travio_subservices_files', ['signed' => true])
				->addColumn('subservice', 'integer', ['null' => false, 'signed' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('url', 'string', ['limit' => 255, 'null' => false])
				->addForeignKey('subservice', 'travio_subservices', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_subservices_descriptions()
	{
		if (!$this->hasTable('travio_subservices_descriptions')) {
			$this->table('travio_subservices_descriptions', ['signed' => true])
				->addColumn('subservice', 'integer', ['null' => false, 'signed' => true])
				->addColumn('tag', 'string', ['limit' => 255, 'null' => true])
				->addForeignKey('subservice', 'travio_subservices', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_subservices_descriptions_texts()
	{
		if (!$this->hasTable('travio_subservices_descriptions_texts')) {
			$this->table('travio_subservices_descriptions_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('title', 'string', ['limit' => 255, 'null' => false])
				->addColumn('text', 'text', ['null' => false])
				->addForeignKey('parent', 'travio_subservices_descriptions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_subservices_photos()
	{
		if (!$this->hasTable('travio_subservices_photos')) {
			$this->table('travio_subservices_photos', ['signed' => true])
				->addColumn('subservice', 'integer', ['null' => false, 'signed' => true])
				->addColumn('url', 'string', ['limit' => 255, 'null' => false])
				->addColumn('thumb', 'string', ['limit' => 255, 'null' => false])
				->addColumn('description', 'string', ['limit' => 255, 'null' => false])
				->addColumn('order', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('tag', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('subservice', 'travio_subservices', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('tag', 'travio_tags', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_tags_texts()
	{
		if (!$this->hasTable('travio_tags_texts')) {
			$this->table('travio_tags_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('full_name', 'string', ['limit' => 255, 'null' => false])
				->addForeignKey('parent', 'travio_tags', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_tags_custom_texts()
	{
		if (!$this->hasTable('travio_tags_custom_texts')) {
			$this->table('travio_tags_custom_texts', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_payment_methods()
	{
		if (!$this->hasTable('travio_payment_methods')) {
			$this->table('travio_payment_methods', ['signed' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('visible', 'boolean', ['null' => false, 'default' => 1])
				->addColumn('gateway', 'string', ['limit' => 255, 'null' => true])
				->create();
		}
	}

	protected function create_travio_payment_methods_custom()
	{
		if (!$this->hasTable('travio_payment_methods_custom')) {
			$this->table('travio_payment_methods_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_classifications()
	{
		if (!$this->hasTable('travio_classifications')) {
			$this->table('travio_classifications', ['signed' => true])
				->addColumn('code', 'string', ['limit' => 255, 'null' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => true])
				->addColumn('level', 'decimal', ['precision' => 2, 'scale' => 1, 'null' => true])
				->create();
		}
	}

	protected function create_travio_luggage_types()
	{
		if (!$this->hasTable('travio_luggage_types')) {
			$this->table('travio_luggage_types', ['signed' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => true])
				->addColumn('weight', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
				->addColumn('length', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
				->addColumn('width', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
				->addColumn('height', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
				->create();
		}
	}

	protected function create_travio_classifications_custom()
	{
		if (!$this->hasTable('travio_classifications_custom')) {
			$this->table('travio_classifications_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_packages_itinerary()
	{
		if (!$this->hasTable('travio_packages_itinerary')) {
			$this->table('travio_packages_itinerary', ['signed' => true])
				->addColumn('package', 'integer', ['null' => false, 'signed' => true])
				->addColumn('day', 'integer', ['null' => true, 'signed' => true])
				->addColumn('geo', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('package', 'travio_packages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('geo', 'travio_geo', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_itinerary_texts()
	{
		if (!$this->hasTable('travio_packages_itinerary_texts')) {
			$this->table('travio_packages_itinerary_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('description', 'text', ['null' => false])
				->addForeignKey('parent', 'travio_packages_itinerary', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_itinerary_photos()
	{
		if (!$this->hasTable('travio_packages_itinerary_photos')) {
			$this->table('travio_packages_itinerary_photos', ['signed' => true])
				->addColumn('itinerary', 'integer', ['null' => false, 'signed' => true])
				->addColumn('url', 'string', ['limit' => 255, 'null' => false])
				->addColumn('thumb', 'string', ['limit' => 255, 'null' => false])
				->addForeignKey('itinerary', 'travio_packages_itinerary', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_payment_conditions()
	{
		if (!$this->hasTable('travio_payment_conditions')) {
			$this->table('travio_payment_conditions', ['signed' => true])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->create();
		}
	}

	protected function create_travio_payment_conditions_custom()
	{
		if (!$this->hasTable('travio_payment_conditions_custom')) {
			$this->table('travio_payment_conditions_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_services_availability()
	{
		if (!$this->hasTable('travio_services_availability')) {
			$this->table('travio_services_availability', ['signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('from', 'date', ['null' => false])
				->addColumn('to', 'date', ['null' => false])
				->addColumn('type', 'string', ['limit' => 255, 'null' => false])
				->addColumn('in_monday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('in_tuesday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('in_wednesday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('in_thursday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('in_friday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('in_saturday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('in_sunday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('out_monday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('out_tuesday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('out_wednesday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('out_thursday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('out_friday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('out_saturday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('out_sunday', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('min_stay', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('only_multiples_of', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_TINY])
				->addColumn('fixed_duration', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_TINY])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_stations_links()
	{
		if (!$this->hasTable('travio_stations_links')) {
			$this->table('travio_stations_links', ['signed' => true])
				->addColumn('station', 'integer', ['null' => false, 'signed' => true])
				->addColumn('type', 'enum', ['values' => ['departure', 'arrival'], 'null' => false])
				->addColumn('service', 'integer', ['null' => true, 'signed' => true])
				->addColumn('subservice', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('station', 'travio_stations', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('subservice', 'travio_subservices', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_departures_custom()
	{
		if (!$this->hasTable('travio_packages_departures_custom')) {
			$this->table('travio_packages_departures_custom', ['signed' => true])
				->create();
		}
	}

	protected function create_travio_packages_guides()
	{
		if (!$this->hasTable('travio_packages_guides')) {
			$this->table('travio_packages_guides', ['signed' => true])
				->addColumn('package', 'integer', ['null' => false, 'signed' => true])
				->addColumn('guide', 'integer', ['null' => false, 'signed' => true])
				->addForeignKey('package', 'travio_packages', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('guide', 'travio_master_data', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_packages_departures_routes()
	{
		if (!$this->hasTable('travio_packages_departures_routes')) {
			$this->table('travio_packages_departures_routes', ['signed' => true])
				->addColumn('departure', 'integer', ['null' => false, 'signed' => true])
				->addColumn('departure_airport', 'integer', ['null' => true, 'signed' => true])
				->addColumn('arrival_airport', 'integer', ['null' => true, 'signed' => true])
				->addColumn('arrival_port', 'integer', ['null' => true, 'signed' => true])
				->addColumn('departure_port', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('departure', 'travio_packages_departures', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}
}
