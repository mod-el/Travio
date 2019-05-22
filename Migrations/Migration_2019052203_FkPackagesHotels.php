<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_2019052203_FkPackagesHotels extends Migration
{
	public function exec()
	{
		$this->addIndex('travio_packages_hotels', 'travio_packages_hotels_hotel_idx', ['hotel']);
		$this->addForeignKey('travio_packages_hotels', 'travio_packages_hotels_geo', 'hotel', 'travio_services');
	}
}
