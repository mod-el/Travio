<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20220308104900_Seo extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_services_texts', 'title');
		$this->addColumn('travio_services_texts', 'description', ['type' => 'text']);
		$this->addColumn('travio_services_texts', 'keywords', ['type' => 'text']);

		$this->addColumn('travio_packages_texts', 'title');
		$this->addColumn('travio_packages_texts', 'description', ['type' => 'text']);
		$this->addColumn('travio_packages_texts', 'keywords', ['type' => 'text']);
	}
}
