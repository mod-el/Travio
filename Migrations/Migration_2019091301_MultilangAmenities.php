<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;
use Model\Multilang\Ml;

class Migration_2019091301_MultilangAmenities extends Migration
{
	public function exec()
	{
		$this->createTable('travio_amenities_texts');
		$this->addColumn('travio_amenities_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_amenities_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_amenities_texts', 'name', ['null' => false]);
		$this->addIndex('travio_amenities_texts', 'travio_amenities_texts_idx', ['parent']);
		$this->addForeignKey('travio_amenities_texts', 'travio_amenities_texts', 'parent', 'travio_amenities', 'id', ['on-delete' => 'CASCADE']);

		foreach (Ml::getLangs() as $lang)
			$this->query('INSERT INTO travio_amenities_texts(`parent`,`lang`,`name`) SELECT id,\'' . $lang . '\',`name` FROM travio_amenities');

		$this->dropColumn('travio_amenities', 'name');
	}
}
