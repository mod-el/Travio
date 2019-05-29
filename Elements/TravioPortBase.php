<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioPortBase extends Element
{
	public static $table = 'travio_ports';

	public function init()
	{
		$this->settings['fields']['departure'] = [
			'type' => 'checkbox',
		];
	}

	public function getDestinations(): array
	{
		$select = $this->model->_Db->select_all('travio_packages_departures', [
			'departure_port' => $this['id'],
		], [
			'joins' => [
				'travio_packages_geo' => [
					'on' => 'package',
					'join_field' => 'package',
					'fields' => ['geo'],
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
			'order_by' => 'name',
			'stream' => false,
		]) : [];
	}
}
