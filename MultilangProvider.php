<?php namespace Model\Travio;

use Model\Db\DbConnection;
use Model\Multilang\AbstractMultilangProvider;

class MultilangProvider extends AbstractMultilangProvider
{
	public static function tables(DbConnection $db): array
	{
		return [
			'travio_geo',
			'travio_services',
			'travio_services_descriptions',
			'travio_subservices',
			'travio_subservices_descriptions',
			'travio_packages',
			'travio_packages_descriptions',
			'travio_packages_itinerary',
			'travio_stations',
			'travio_amenities',
			'travio_tags',
		];
	}
}
