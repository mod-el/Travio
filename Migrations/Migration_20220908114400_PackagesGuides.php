<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220908114400_PackagesGuides extends Migration
{
	public function exec()
	{
		$this->createTable('travio_packages_guides');
		$this->addColumn('travio_packages_guides', 'package', ['type' => 'int', 'null' => false]);
		$this->addColumn('travio_packages_guides', 'guide', ['type' => 'int', 'null' => false]);
		$this->addForeignKey('travio_packages_guides', 'travio_packages_guides', 'package', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_packages_guides', 'travio_packages_guides_master_data', 'guide', 'travio_master_data');

		$this->query('UPDATE `travio_packages` SET `last_update` = NULL');
	}
}
