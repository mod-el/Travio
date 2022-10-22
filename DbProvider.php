<?php namespace Model\Travio;

use Model\Db\AbstractDbProvider;

class DbProvider extends AbstractDbProvider
{
	public static function linkedTables(): array
	{
		return [
			'primary' => [
				'travio_geo',
				'travio_services',
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
			],
		];
	}
}
