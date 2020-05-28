<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioGeoBase extends Element
{
	public static $table = 'travio_geo';

	public function init()
	{
		$this->has('sub', [
			'element' => 'TravioGeo',
			'field' => 'parent',
		]);

		$this->belongsTo('TravioGeo', [
			'field' => 'parent',
			'children' => 'sub',
		]);
	}

	public function getAirports(): array
	{
		$select = $this->model->_Db->select_all('travio_packages_departures', [
			'geo' => $this['id'],
			'departure_airport' => ['!=', null],
			'visible' => 1,
		], [
			'joins' => [
				'travio_packages_geo' => [
					'on' => 'package',
					'join_field' => 'package',
					'fields' => ['geo'],
				],
				'travio_packages' => [
					'on' => 'package',
					'join_field' => 'id',
					'fields' => ['visible'],
				],
			],
		]);

		$ids = [];
		foreach ($select as $row) {
			if ($row['departure_airport'] and !in_array($row['departure_airport'], $ids))
				$ids[] = $row['departure_airport'];
		}

		return $ids ? $this->model->all('TravioAirport', [
			'id' => ['IN', $ids],
		], [
			'order_by' => 'code',
			'stream' => false,
		]) : [];
	}

	public function getPorts(): array
	{
		$select = $this->model->_Db->select_all('travio_packages_departures', [
			'geo' => $this['id'],
			'departure_port' => ['!=', null],
			'visible' => 1,
		], [
			'joins' => [
				'travio_packages_geo' => [
					'on' => 'package',
					'join_field' => 'package',
					'fields' => ['geo'],
				],
				'travio_packages' => [
					'on' => 'package',
					'join_field' => 'id',
					'fields' => ['visible'],
				],
			],
		]);

		$ids = [];
		foreach ($select as $row) {
			if ($row['departure_port'] and !in_array($row['departure_port'], $ids))
				$ids[] = $row['departure_port'];
		}

		return $ids ? $this->model->all('TravioPort', [
			'id' => ['IN', $ids],
		], [
			'order_by' => 'code',
			'stream' => false,
		]) : [];
	}
}
