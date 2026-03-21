<?php

use Phinx\Migration\AbstractMigration;

class ServicesItinerary extends AbstractMigration
{
	public function change()
	{
		$this->create_travio_services_itinerary();
    $this->create_travio_services_itinerary_texts();
    $this->create_travio_services_itinerary_photos();

    $this->execute('UPDATE travio_services SET last_update = NULL');
	}

	protected function create_travio_services_itinerary()
	{
		if (!$this->hasTable('travio_services_itinerary')) {
			$this->table('travio_services_itinerary', ['signed' => true])
				->addColumn('service', 'integer', ['null' => false, 'signed' => true])
				->addColumn('day', 'integer', ['null' => true, 'signed' => true])
				->addColumn('geo', 'integer', ['null' => true, 'signed' => true])
				->addForeignKey('service', 'travio_services', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->addForeignKey('geo', 'travio_geo', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_itinerary_texts()
	{
		if (!$this->hasTable('travio_services_itinerary_texts')) {
			$this->table('travio_services_itinerary_texts', ['signed' => true])
				->addColumn('parent', 'integer', ['null' => false, 'signed' => true])
				->addColumn('lang', 'char', ['limit' => 2, 'null' => false])
				->addColumn('name', 'string', ['limit' => 255, 'null' => false])
				->addColumn('description', 'text', ['null' => false])
				->addForeignKey('parent', 'travio_services_itinerary', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}

	protected function create_travio_services_itinerary_photos()
	{
		if (!$this->hasTable('travio_services_itinerary_photos')) {
			$this->table('travio_services_itinerary_photos', ['signed' => true])
				->addColumn('itinerary', 'integer', ['null' => false, 'signed' => true])
				->addColumn('url', 'string', ['limit' => 255, 'null' => false])
				->addColumn('thumb', 'string', ['limit' => 255, 'null' => false])
				->addForeignKey('itinerary', 'travio_services_itinerary', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
				->create();
		}
	}
}
