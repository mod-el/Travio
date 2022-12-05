<?php namespace Model\Travio\Helpers;

use Model\InstantSearch\Base;
use Model\Multilang\Ml;

class Booking extends Base
{
	public function getItem($el): array
	{
		$fill = [];
		$dates = [];

		switch (get_class($el)) {
			case 'Model\TravioAssets\Elements\TravioGeo':
				$id = 'd' . $el['id'];
				if (!empty($el['parent_name']))
					$plainText = ucwords(mb_strtolower($el['name'])) . ' | ' . ucwords(mb_strtolower($el['parent_name']));
				else
					$plainText = ucwords(mb_strtolower($el['name']));

				$text = '<i class="fas fa-map-marker-alt"></i> ' . entities($plainText);

				$services = $this->model->select_all('travio_services', ['join_geo' => $el['id']], [
					'joins' => [
						'travio_services_geo' => [
							'on' => ['id' => 'service'],
							'fields' => ['geo' => 'join_geo'],
						],
					],
				]);

				$today = date_create();
				$totalMinDate = null;
				$totalMaxDate = null;

				foreach ($services as $service) {
					if ($service['min_date']) {
						$minDate = date_create($service['min_date']);
						if ($minDate < $today)
							$minDate = $today;

						if ($totalMinDate === null or $minDate < $totalMinDate)
							$totalMinDate = $minDate;

						if ($service['max_date']) {
							$maxDate = date_create($service['max_date']);
							if ($totalMaxDate === null or $maxDate > $totalMaxDate)
								$totalMaxDate = $maxDate;
						}
					}
				}

				if ($totalMinDate and $totalMaxDate) {
					$dates['min'] = $totalMinDate->format('Y-m-d');
					$dates['max'] = $totalMaxDate->format('Y-m-d');
				}

				if (isset($_POST['departures'])) {
					$airports = $this->model->_Db->query('SELECT a.id, a.code FROM travio_packages_departures d INNER JOIN travio_packages_geo g ON g.package = d.package INNER JOIN travio_packages p ON p.id = d.package AND p.visible = 1 INNER JOIN travio_airports a ON a.id = d.departure_airport WHERE g.geo = ' . $el['id'] . ' GROUP BY d.departure_airport ORDER BY a.code')->fetchAll();
					$ports = $this->model->_Db->query('SELECT a.id, a.code FROM travio_packages_departures d INNER JOIN travio_packages_geo g ON g.package = d.package INNER JOIN travio_packages p ON p.id = d.package AND p.visible = 1 INNER JOIN travio_ports a ON a.id = d.departure_port WHERE g.geo = ' . $el['id'] . ' GROUP BY d.departure_port ORDER BY a.code')->fetchAll();

					$fill['travioAirports'] = json_encode($airports);
					$fill['travioPorts'] = json_encode($ports);
				}
				break;
			case 'Model\TravioAssets\Elements\TravioService':
				$id = 's' . $el['travio'];
				if (!empty($el['geo'])) {
					$destinazione = $this->model->_ORM->one('TravioGeo', $el['geo']);
					$plainText = ucwords(mb_strtolower($el['name']) . ' | ' . $destinazione['name'] . ($destinazione['parent_name'] ? ' | ' . $destinazione['parent_name'] : ''));
				} else {
					$plainText = ucwords(mb_strtolower($el['name']));
				}
				$text = '<i class="fas fa-hotel"></i> ' . entities($plainText);

				if ($el['min_date']) {
					$today = date_create();
					$minDate = date_create($el['min_date']);
					if ($minDate < $today)
						$minDate = $today;

					$dates['min'] = $minDate->format('Y-m-d');

					if ($el['max_date']) {
						$maxDate = date_create($el['max_date']);
						if ($maxDate >= $minDate)
							$dates['max'] = $maxDate->format('Y-m-d');
					}
				}

				$checkin_dates = $el->getCheckinDates();
				if (count($checkin_dates) > 0)
					$dates['list'] = $checkin_dates;

				$fill['travioWebsiteServiceId'] = $el['id'];

				if (isset($_POST['departures'])) {
					$airports = $this->model->_Db->query('SELECT a.id, a.code FROM travio_packages_departures d INNER JOIN travio_packages_services s ON s.package = d.package INNER JOIN travio_packages p ON p.id = d.package AND p.visible = 1 INNER JOIN travio_airports a ON a.id = d.departure_airport WHERE s.service = ' . $el['id'] . ' GROUP BY d.departure_airport ORDER BY a.code')->fetchAll();
					$ports = $this->model->_Db->query('SELECT a.id, a.code FROM travio_packages_departures d INNER JOIN travio_packages_services s ON s.package = d.package INNER JOIN travio_packages p ON p.id = d.package AND p.visible = 1 INNER JOIN travio_ports a ON a.id = d.departure_port WHERE s.service = ' . $el['id'] . ' GROUP BY d.departure_port ORDER BY a.code')->fetchAll();

					$fill['travioAirports'] = json_encode($airports);
					$fill['travioPorts'] = json_encode($ports);
				}
				break;
			case 'Model\TravioAssets\Elements\TravioPackage':
				$id = 'p' . $el['travio'];
				if (!empty($el['geo'])) {
					$destinazione = $this->model->_ORM->one('TravioGeo', $el['geo']);
					$plainText = ucwords(mb_strtolower($el['name']) . ' | ' . $destinazione['name'] . ($destinazione['parent_name'] ? ' | ' . $destinazione['parent_name'] : ''));
				} else {
					$plainText = ucwords(mb_strtolower($el['name']));
				}
				$text = '<i class="fas fa-plane-departure"></i> ' . entities($plainText);
				break;
			case 'Model\TravioAssets\Elements\TravioTag':
				$id = 't' . $el['id'];
				$plainText = ucwords(mb_strtolower($el['name']));
				$text = '<i class="fas fa-tag"></i> ' . entities($plainText);

				$services = $this->model->select_all('travio_services', ['tag' => $el['id']], [
					'joins' => [
						'travio_services_tags' => [
							'on' => ['id' => 'service'],
							'fields' => ['tag'],
						],
					],
				]);

				$today = date_create();
				$totalMinDate = null;
				$totalMaxDate = null;

				foreach ($services as $service) {
					if ($service['min_date']) {
						$minDate = date_create($service['min_date']);
						if ($minDate < $today)
							$minDate = $today;

						if ($totalMinDate === null or $minDate < $totalMinDate)
							$totalMinDate = $minDate;

						if ($service['max_date']) {
							$maxDate = date_create($service['max_date']);
							if ($totalMaxDate === null or $maxDate > $totalMaxDate)
								$totalMaxDate = $maxDate;
						}
					}
				}

				if ($totalMinDate and $totalMaxDate) {
					$dates['min'] = $totalMinDate->format('Y-m-d');
					$dates['max'] = $totalMaxDate->format('Y-m-d');
				}
				break;
			default:
				die('Unknown type');
		}

		if ($dates)
			$fill['travioDates'] = json_encode($dates);

		return [
			'id' => $id,
			'text' => $text,
			'plainText' => $plainText,
			'fill' => $fill,
		];
	}

	public function getItemFromId($id): array
	{
		if ($id !== null) {
			switch ($id[0]) {
				case 's':
					return $this->getItem($this->model->one('TravioService', ['travio' => substr($id, 1)]));

				case 'p':
					return $this->getItem($this->model->one('TravioPackage', ['travio' => substr($id, 1)]));

				case 'd':
					return $this->getItem($this->model->one('TravioGeo', (int)substr($id, 1)));

				case 't':
					return $this->getItem($this->model->one('TravioTag', (int)substr($id, 1)));
			}
		}

		return [
			'id' => null,
			'text' => '',
		];
	}

	public function getList(string $query, bool $is_popup = false): iterable
	{
		$show = (isset($_POST['show']) and in_array($_POST['show'], ['geo', 'services', 'both'])) ? $_POST['show'] : 'both';
		$type = isset($_POST['type']) ? explode('-', $_POST['type']) : [null, null];
		if (count($type) === 1)
			$type[] = null;
		if (count($type) > 2)
			return [];

		$query = trim($query);

		$elements = [];

		if (in_array($show, ['geo', 'both'])) {
			$where = [
				'visible' => 1,
				[
					'sub' => [
						['name', 'LIKE', $query . '%'],
						['parent_name', 'LIKE', $query . '%'],
						['name', 'LIKE', '% ' . $query . '%'],
						['parent_name', 'LIKE', '% ' . $query . '%'],
					],
					'operator' => 'OR',
				],
			];

			$joins = [
				'travio_geo' => [
					'visible',
				],
			];

			switch ($type[0]) {
				case 'services':
					$joins['travio_services_geo'] = [
						'alias' => 'tsg',
						'on' => ['parent' => 'geo'],
						'fields' => [],
					];

					if ($type[1]) {
						$joins['travio_services'] = [
							'alias' => 'ts',
							'on' => ['tsg.service' => 'id'],
							'where' => ['visible' => 1],
							'fields' => ['type'],
						];
						$where['type'] = $type[1];
					}
					break;
				case 'packages':
					$joins['travio_packages_geo'] = [
						'alias' => 'tpg',
						'on' => ['parent' => 'geo'],
						'fields' => [],
					];

					if ($type[1]) {
						$joins['travio_packages'] = [
							'alias' => 'tp',
							'on' => ['tpg.package' => 'id'],
							'where' => ['visible' => 1],
							'fields' => ['type'],
						];
						$where['type'] = $type[1];
					}
					break;
			}

			if (isset($_POST['geo-parent'])) {
				$joins['travio_geo_parents'] = [
					'type' => 'LEFT',
					'alias' => 'tgp',
					'on' => ['parent' => 'geo'],
					'fields' => [],
				];
				$where[] = '(tgp.parent = ' . $this->model->_Db->quote($_POST['geo-parent']) . ' OR travio_geo_texts.parent = ' . $this->model->_Db->quote($_POST['geo-parent']) . ')';
			}

			if (!empty($_POST['filters'])) {
				$joins['travio_geo_custom'] = [
					'alias' => 'main_custom',
					'on' => ['parent' => 'id'],
					'fields' => [],
				];

				$filters = json_decode($_POST['filters'], true, 512, JSON_THROW_ON_ERROR);
				foreach ($filters as $k => $v) {
					if (is_numeric($k) or is_array($v))
						throw new \Exception('Invalid filters');
					$where[$k] = $v;
					$joins['travio_geo_custom']['fields'][] = $k;
				}
			}

			$destinazioni = $this->model->_Db->select_all('travio_geo_texts', $where, [
				'order_by' => 'travio_geo_texts.parent, travio_geo_texts.lang!=' . $this->model->_Db->quote(Ml::getLang()),
				'joins' => $joins,
			]);

			foreach ($destinazioni as $d) {
				if (!isset($elements['d' . $d['parent']]))
					$elements['d' . $d['parent']] = $this->model->one('TravioGeo', $d['parent']);
			}
			$elements = array_values($elements);
		}

		if (in_array($show, ['services', 'both'])) {
			$where = [
				'visible' => 1,
				[
					'sub' => [
						['name', 'LIKE', $query . '%'],
						['name', 'LIKE', '% ' . $query . '%'],
					],
					'operator' => 'OR',
				],
			];
			if ($type[1])
				$where['type'] = $type[1];

			$joins = [];
			if (isset($_POST['geo-parent'])) {
				switch ($type[0]) {
					case 'packages':
						$joins['travio_packages_geo'] = [
							'alias' => 'tpg',
							'on' => ['id' => 'package'],
							'fields' => [],
						];

						$where[] = 'tpg.geo = ' . $this->model->_Db->quote($_POST['geo-parent']);
						break;
					default:
						$joins['travio_services_geo'] = [
							'alias' => 'tsg',
							'on' => ['id' => 'service'],
							'fields' => [],
						];

						$where[] = 'tsg.geo = ' . $this->model->_Db->quote($_POST['geo-parent']);
						break;
				}
			}

			switch ($type[0]) {
				case 'packages':
					$packages = $this->model->_ORM->all('TravioPackage', $where, ['joins' => $joins]);
					foreach ($packages as $s)
						$elements[] = $s;
					break;
				default:
					$services = $this->model->_ORM->all('TravioService', $where, ['joins' => $joins]);
					foreach ($services as $s)
						$elements[] = $s;
					break;
			}
		}

		usort($elements, function ($a, $b) use ($query) {
			$nomeA = mb_strtolower($a['name']);
			$nomeB = mb_strtolower($b['name']);
			$parentA = mb_strtolower($a['parent_name'] ?? '');
			$parentB = mb_strtolower($b['parent_name'] ?? '');

			if ($query) {
				if ($nomeA === $query) $va = 0;
				elseif ($parentA === $query) $va = 1;
				elseif (mb_stripos($nomeA, $query) !== false) $va = 2;
				elseif (mb_stripos($nomeA, $query) === 0) $va = 3;
				elseif (mb_stripos($parentA, $query) === 0) $va = 4;
				elseif (mb_stripos($parentA, $query) !== false) $va = 5;
				else $va = 6;

				if ($nomeB === $query) $vb = 0;
				elseif ($parentB === $query) $vb = 1;
				elseif (mb_stripos($nomeB, $query) !== false) $vb = 2;
				elseif (mb_stripos($nomeB, $query) === 0) $vb = 3;
				elseif (mb_stripos($parentB, $query) === 0) $vb = 4;
				elseif (mb_stripos($parentB, $query) !== false) $vb = 5;
				else $vb = 6;
			} else {
				$va = 0;
				$vb = 0;
			}

			return $va <=> $vb ?: mb_strlen($nomeA) <=> mb_strlen($nomeB);
		});

		return $elements;
	}
}
