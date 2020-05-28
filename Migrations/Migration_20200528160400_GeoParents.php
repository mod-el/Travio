<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20200528160400_GeoParents extends Migration
{
	public function exec()
	{
		$this->createTable('travio_geo_parents');
		$this->addColumn('travio_geo_parents', 'geo', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_geo_parents', 'parent', ['type' => 'int', 'null' => false]);
		$this->addIndex('travio_geo_parents', 'travio_geo_parents_idx1', ['geo']);
		$this->addIndex('travio_geo_parents', 'travio_geo_parents_idx2', ['parent']);
		$this->addForeignKey('travio_geo_parents', 'travio_geo_parents_geo', 'geo', 'travio_geo', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_geo_parents', 'travio_geo_parents_parent', 'parent', 'travio_geo', 'id', ['on-delete' => 'CASCADE']);
	}
}
