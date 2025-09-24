<?php namespace Model\Travio\Providers;

use Model\LinkedTables\AbstractLinkedTablesProvider;

class LinkedTablesProvider extends AbstractLinkedTablesProvider
{
	public static function tables(\Model\Db\DbConnection $db): array
	{
		return [
			'travio_geo',
			'travio_services',
			'travio_subservices',
			'travio_packages',
			'travio_tags',
			'travio_orders',
			'travio_airports',
			'travio_ports',
			'travio_stations',
			'travio_master_data',
			'travio_payment_methods',
			'travio_payment_conditions',
			'travio_classifications',
			'travio_packages_departures',
			'travio_amenities',
		];
	}
}
