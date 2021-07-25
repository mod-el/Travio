<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210725134800_SemplificoTabelleCustom extends Migration
{
	public function exec()
	{
		$this->dropForeignKey('travio_geo_custom_texts', 'travio_geo_custom_texts');
		$this->dropColumn('travio_geo_custom_texts', 'parent');
		$this->dropColumn('travio_geo_custom_texts', 'lang');

		$this->dropForeignKey('travio_packages_custom_texts', 'travio_packages_custom_texts');
		$this->dropColumn('travio_packages_custom_texts', 'parent');
		$this->dropColumn('travio_packages_custom_texts', 'lang');

		$this->dropForeignKey('travio_services_custom_texts', 'travio_services_custom_texts');
		$this->dropColumn('travio_services_custom_texts', 'parent');
		$this->dropColumn('travio_services_custom_texts', 'lang');

		$this->dropForeignKey('travio_stations_custom_texts', 'travio_stations_custom_texts');
		$this->dropColumn('travio_stations_custom_texts', 'parent');
		$this->dropColumn('travio_stations_custom_texts', 'lang');

		$this->dropForeignKey('travio_tags_custom_texts', 'travio_tags_custom_texts');
		$this->dropColumn('travio_tags_custom_texts', 'parent');
		$this->dropColumn('travio_tags_custom_texts', 'lang');

		$this->addForeignKey('travio_geo_custom', 'travio_geo_custom', 'id', 'travio_geo', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_geo_custom_texts', 'travio_geo_custom_texts', 'id', 'travio_geo_texts', 'id', ['on-delete' => 'CASCADE']);

		$this->addForeignKey('travio_packages_custom', 'travio_packages_custom', 'id', 'travio_packages', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_packages_custom_texts', 'travio_packages_custom_texts', 'id', 'travio_packages_texts', 'id', ['on-delete' => 'CASCADE']);

		$this->addForeignKey('travio_services_custom', 'travio_services_custom', 'id', 'travio_services', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_services_custom_texts', 'travio_services_custom_texts', 'id', 'travio_services_texts', 'id', ['on-delete' => 'CASCADE']);

		$this->addForeignKey('travio_stations_custom', 'travio_stations_custom', 'id', 'travio_stations', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_stations_custom_texts', 'travio_stations_custom_texts', 'id', 'travio_stations_texts', 'id', ['on-delete' => 'CASCADE']);

		$this->addForeignKey('travio_tags_custom', 'travio_tags_custom', 'id', 'travio_tags', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_tags_custom_texts', 'travio_tags_custom_texts', 'id', 'travio_tags_texts', 'id', ['on-delete' => 'CASCADE']);

		$this->addForeignKey('travio_airports_custom', 'travio_airports_custom', 'id', 'travio_airports', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_master_data_custom', 'travio_master_data_custom', 'id', 'travio_master_data', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_orders_custom', 'travio_orders_custom', 'id', 'travio_orders', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_payment_methods_custom', 'travio_payment_methods_custom', 'id', 'travio_payment_methods', 'id', ['on-delete' => 'CASCADE']);
		$this->addForeignKey('travio_ports_custom', 'travio_ports_custom', 'id', 'travio_ports', 'id', ['on-delete' => 'CASCADE']);
	}
}
