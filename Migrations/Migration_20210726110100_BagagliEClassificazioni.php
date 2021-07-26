<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210726110100_BagagliEClassificazioni extends Migration
{
	public function exec()
	{
		$this->createTable('travio_classifications');
		$this->addColumn('travio_classifications', 'code');
		$this->addColumn('travio_classifications', 'name');
		$this->addColumn('travio_classifications', 'level', ['type' => 'DECIMAL(2,1)']);

		$this->createTable('travio_luggage_types');
		$this->addColumn('travio_luggage_types', 'name');
		$this->addColumn('travio_luggage_types', 'weight', ['type' => 'DECIMAL(10,2)']);
		$this->addColumn('travio_luggage_types', 'length', ['type' => 'DECIMAL(10,2)']);
		$this->addColumn('travio_luggage_types', 'width', ['type' => 'DECIMAL(10,2)']);
		$this->addColumn('travio_luggage_types', 'height', ['type' => 'DECIMAL(10,2)']);
	}
}
