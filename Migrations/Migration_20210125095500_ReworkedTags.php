<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210125095500_ReworkedTags extends Migration
{
	public function exec()
	{
		$this->query('DELETE FROM `travio_services_tags`');
		$this->query('DELETE FROM `travio_packages_tags`');
		$this->query('UPDATE `travio_tags` SET `type` = NULL');

		$this->query('UPDATE `travio_services` SET last_update = NULL');
		$this->query('UPDATE `travio_packages` SET last_update = NULL');

		$this->createTable('travio_tags_types');
		$this->addColumn('travio_tags_types', 'name', ['null' => false]);

		$this->dropColumn('travio_tags', 'type_name');
		$this->addIndex('travio_tags', 'travio_tags_type', ['type']);
		$this->addForeignKey('travio_tags', 'travio_tags_type', 'type', 'travio_tags_types');

		$this->changeColumn('travio_services_tags', 'tag', ['type' => 'INT', 'null' => true]);
		$this->addIndex('travio_services_tags', 'travio_services_tags_tag', ['tag']);
		$this->addForeignKey('travio_services_tags', 'travio_services_tags_tag', 'tag', 'travio_tags');

		$this->changeColumn('travio_packages_tags', 'tag', ['type' => 'INT', 'null' => true]);
		$this->addIndex('travio_packages_tags', 'travio_packages_tags_tag', ['tag']);
		$this->addForeignKey('travio_packages_tags', 'travio_packages_tags_tag', 'tag', 'travio_tags');
	}
}
