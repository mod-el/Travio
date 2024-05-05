<?php namespace Model\Travio\Elements;

use Model\Db\Db;
use Model\ORM\Element;

class TravioPortBase extends Element
{
	public static ?string $table = 'travio_ports';

	public function init(): void
	{
		$this->settings['fields']['departure'] = [
			'type' => 'checkbox',
		];
	}

	public function getDestinations(): array
	{
		$select = Db::getConnection()->selectAll('travio_packages_departures', [
			'departure_port' => $this['id'],
			'visible' => 1,
		], [
			'joins' => [
				'travio_packages_geo' => [
					'on' => ['package' => 'package'],
					'fields' => ['geo'],
				],
				'travio_packages' => [
					'on' => ['package' => 'id'],
					'fields' => ['visible'],
				],
			],
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
