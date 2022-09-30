<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220930100500_DeletableTags extends Migration
{
	public function exec()
	{
		$this->dropForeignKey('travio_services_tags', 'travio_services_tags_tag');
		$this->addForeignKey('travio_services_tags', 'travio_services_tags_tag', 'tag', 'travio_tags', 'id', ['on-delete' => 'CASCADE']);

		$this->dropForeignKey('travio_subservices_tags', 'travio_subservices_tags_tag');
		$this->addForeignKey('travio_subservices_tags', 'travio_subservices_tags_tag', 'tag', 'travio_tags', 'id', ['on-delete' => 'CASCADE']);

		$this->dropForeignKey('travio_packages_tags', 'travio_packages_tags_tag');
		$this->addForeignKey('travio_packages_tags', 'travio_packages_tags_tag', 'tag', 'travio_tags', 'id', ['on-delete' => 'CASCADE']);
	}
}
