<?php namespace Model\Travio\Helpers;

use Model\InstantSearch\Base;

class TransferStations extends Base
{
	public function getItem(array|object|null $el): array
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

	public function getItemFromId(?string $id): array
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
					'on' => [
						'links.service' => 'id',
					],
					'where' => [
						'visible' => 1,
					],
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
			'group_by' => 'id',
		]);
		foreach ($q as $station) {
			if (!empty($_POST['subservices_tags'])) {
				$tags = explode(',', $_POST['subservices_tags']);

				$found = false;
				foreach ($station->links as $link) {
					if (!$link['subservice'])
						continue;

					$subservice = $this->model->one('TravioSubservice', $link['subservice']);
					foreach ($subservice->tags as $tag) {
						if (in_array($tag['id'], $tags)) {
							$found = true;
							break;
						}
					}
				}

				if (!$found)
					continue;
			}

			$stations[$station['id']] = $station;
		}

		return array_values($stations);
	}
}
