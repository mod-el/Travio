<?php namespace Model\Travio\Helpers;

use Model\InstantSearch\Base;

class Booking extends Base
{
	public function getItem($el): array
	{
		$fill = [];
		$dates = [];

		switch (get_class($el)) {
			case 'Model\TravioAssets\Elements\TravioGeo':
				$id = 'd' . $el['id'];
				if (!empty($el['parent_name'])) {
					$plainText = ucwords(mb_strtolower($el['name'])) . ' | ' . ucwords(mb_strtolower($el['parent_name']));
				} else {
					$plainText = ucwords(mb_strtolower($el['name']));
				}
				$text = '<i class="fas fa-map-marker-alt"></i> ' . entities($plainText);

				$services = $this->model->select_all('travio_services', ['join_geo' => $el['id']], [
					'joins' => [
						'travio_services_geo' => [
							'on' => 'id',
							'join_field' => 'service',
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

				$services = $this->model->select_all('travio_services', ['tag' => ['LIKE', $el['name']]], [
					'joins' => [
						'travio_services_tags' => [
							'on' => 'id',
							'join_field' => 'service',
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
				break;
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
					break;
				case 'p':
					return $this->getItem($this->model->one('TravioPackage', ['travio' => substr($id, 1)]));
					break;
				case 'd':
					return $this->getItem($this->model->one('TravioGeo', substr($id, 1)));
					break;
				case 't':
					return $this->getItem($this->model->one('TravioTag', substr($id, 1)));
					break;
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
		if (count($type) !== 2)
			return [];

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
						'on' => 'parent',
						'join_field' => 'geo',
						'fields' => [],
					];

					if ($type[1]) {
						$joins['travio_services'] = [
							'alias' => 'ts',
							'full_on' => 'tsg.service = ts.id AND ts.visible = 1',
							'fields' => ['type'],
						];
						$where['type'] = $type[1];
					}
					break;
				case 'packages':
					$joins['travio_packages_geo'] = [
						'alias' => 'tpg',
						'on' => 'parent',
						'join_field' => 'geo',
						'fields' => [],
					];

					if ($type[1]) {
						$joins['travio_packages'] = [
							'alias' => 'tp',
							'full_on' => 'tpg.package = tp.id AND tp.visible = 1',
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
					'on' => 'parent',
					'join_field' => 'geo',
					'fields' => [],
				];
				$where[] = '(tgp.parent = ' . $this->model->_Db->quote($_POST['geo-parent']) . ' OR t.parent = ' . $this->model->_Db->quote($_POST['geo-parent']) . ')';
			}

			$destinazioni = $this->model->_Db->select_all('travio_geo_texts', $where, [
				'order_by' => 'parent, lang!=' . $this->model->_Db->quote($this->model->_Multilang->lang),
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
							'on' => 'id',
							'join_field' => 'package',
							'fields' => [],
						];

						$where[] = 'tpg.geo = ' . $this->model->_Db->quote($_POST['geo-parent']);
						break;
					default:
						$joins['travio_services_geo'] = [
							'alias' => 'tsg',
							'on' => 'id',
							'join_field' => 'service',
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

			if ($nomeA == $query) $va = 0;
			elseif ($parentA == $query) $va = 1;
			elseif (mb_stripos($nomeA, $query) !== false) $va = 2;
			elseif (mb_stripos($nomeA, $query) === 0) $va = 3;
			elseif (mb_stripos($parentA, $query) === 0) $va = 4;
			elseif (mb_stripos($parentA, $query) !== false) $va = 5;
			else $va = 6;

			if ($nomeB == $query) $vb = 0;
			elseif ($parentB == $query) $vb = 1;
			elseif (mb_stripos($nomeB, $query) !== false) $vb = 2;
			elseif (mb_stripos($nomeB, $query) === 0) $vb = 3;
			elseif (mb_stripos($parentB, $query) === 0) $vb = 4;
			elseif (mb_stripos($parentB, $query) !== false) $vb = 5;
			else $vb = 6;

			return $va <=> $vb ?: mb_strlen($nomeA) <=> mb_strlen($nomeB);
		});

		return $elements;
	}
}
