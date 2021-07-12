<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210709170700_NewTags extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_tags', 'parent', ['type' => 'int']);
		$this->dropForeignKey('travio_tags', 'travio_tags_type');
		$this->dropColumn('travio_tags', 'name');
		$this->dropColumn('travio_tags', 'type');
		$this->addIndex('travio_tags', 'travio_tags_parent', ['parent']);
		$this->addForeignKey('travio_tags', 'travio_tags_parent', 'parent', 'travio_tags', 'id', ['on-delete' => 'SET NULL']);

		$this->createTable('travio_tags_texts');
		$this->addColumn('travio_tags_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_tags_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addColumn('travio_tags_texts', 'name', ['null' => false]);
		$this->addIndex('travio_tags_texts', 'travio_tags_texts_idx', ['parent']);
		$this->addForeignKey('travio_tags_texts', 'travio_tags_texts', 'parent', 'travio_tags', 'id', ['on-delete' => 'CASCADE']);

		$this->dropTable('travio_tags_types_custom');
		$this->dropTable('travio_tags_types');

		$this->createTable('travio_tags_custom_texts');
		$this->addColumn('travio_tags_custom_texts', 'parent', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_tags_custom_texts', 'lang', ['type' => 'char(2)', 'null' => false]);
		$this->addIndex('travio_tags_custom_texts', 'travio_tags_custom_texts_idx', ['parent']);
		$this->addForeignKey('travio_tags_custom_texts', 'travio_tags_custom_texts', 'parent', 'travio_tags_custom', 'id', ['on-delete' => 'CASCADE']);
	}
}
