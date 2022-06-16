<?php namespace Model\Travio\Helpers;

use Model\InstantSearch\Base;

class TransferStations extends Base
{
	public function getItem($el): array
	{
		if ($el !== null) {
			return [
				'id' => $el['id'],
				'text' => $el['name'],
			];
		} else {
			return [
				'id' => null,
				'text' => '',
			];
		}
	}

	public function getItemFromId($id): array
	{
		return $this->getItem($id !== null ? $this->model->one('TravioStation', $id) : null);
	}

	public function getList(string $query, bool $is_popup = false): iterable
	{
		$where = $this->makeQuery($query, ['name']);
		$joins = [];

		if (!empty($_POST['type']) or !empty($_POST['service_type'])) {
			$joins[] = [
				'table' => 'travio_stations_links',
				'alias' => 'links',
				'fields' => [
					'type' => 'link_type',
				],
			];

			if (!empty($_POST['type']))
				$where['link_type'] = $_POST['type'];

			if (!empty($_POST['service_type'])) {
				$joins[] = [
					'table' => 'travio_services',
					'alias' => 'services',
					'full_on' => 'services.id = links.service AND services.visible = 1',
					'fields' => [
						'type' => 'service_type',
					],
				];

				$where['service_type'] = $_POST['service_type'];
			}
		}

		$stations = [];
		$q = $this->model->_ORM->all('TravioStation', $where, [
			'joins' => $joins,
			'order_by' => 'name',
		]);
		foreach ($q as $row)
			$stations[$row['id']] = $row;

		return array_values($stations);
	}
}
