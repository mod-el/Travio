<?php namespace Model\Travio\Elements;

use Model\Db\Db;
use Model\ORM\Element;

class TravioAirportBase extends Element
{
	public static ?string $table = 'travio_airports';

	public function init(): void
	{
		$this->settings['fields']['departure'] = [
			'type' => 'checkbox',
		];
	}

	public function getDestinations(): array
	{
		$select = Db::getConnection()->selectAll('travio_packages_departures', [
			'departure_airport' => $this['id'],
			'visible' => 1,
		], [
			'joins' => [
				'travio_packages_departures_routes' => [
					'on' => ['departure' => 'id'],
					'fields' => ['departure_airport'],
				],
				'travio_packages_geo' => [
					'on' => ['package' => 'package'],
					'fields' => ['geo'],
				],
				'travio_packages' => [
					'on' => 'package',
					'fields' => ['visible'],
				],
			],
			'group_by' => 'geo',
		]);

		$ids = [];
		foreach ($select as $row) {
			if ($row['geo'] and !in_array($row['geo'], $ids))
				$ids[] = $row['geo'];
		}

		return $ids ? $this->model->all('TravioGeo', [
			'id' => ['IN', $ids],
		], [
			'order_by' => ['name'],
			'stream' => false,
		]) : [];
	}
}
