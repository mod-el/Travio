<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220523182200_PhotosTags extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_services_photos', 'tag', ['type' => 'int', 'null' => true]);
		$this->addColumn('travio_subservices_photos', 'tag', ['type' => 'int', 'null' => true]);
		$this->addColumn('travio_packages_photos', 'tag', ['type' => 'int', 'null' => true]);

		$this->addForeignKey('travio_services_photos', 'travio_services_photos_tag', 'tag', 'travio_tags');
		$this->addForeignKey('travio_subservices_photos', 'travio_subservices_photos_tag', 'tag', 'travio_tags');
		$this->addForeignKey('travio_packages_photos', 'travio_packages_photos_tag', 'tag', 'travio_tags');
	}
}
