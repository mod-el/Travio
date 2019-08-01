<?php namespace Model\Travio\Helpers;

use Model\InstantSearch\Base;

class Booking extends Base
{
	public function getItem($el): array
	{
		switch (get_class($el)) {
			case 'Model\TravioAssets\Elements\TravioGeo':
				$id = 'd' . $el['id'];
				if (!empty($el['parent_name'])) {
					$text = ucwords(mb_strtolower($el['name'])) . ' | ' . ucwords(mb_strtolower($el['parent_name']));
				} else {
					$text = ucwords(mb_strtolower($el['name']));
				}
				$text = '<i class="fas fa-map-marker-alt"></i> ' . $text;
				break;
			case 'Model\TravioAssets\Elements\TravioService':
				$id = 's' . $el['travio'];
				if (!empty($el['geo'])) {
					$destinazione = $this->model->_ORM->one('TravioGeo', $el['geo']);
					$text = ucwords(mb_strtolower($el['name']) . ' | ' . $destinazione['name'] . ($destinazione['parent_name'] ? ' | ' . $destinazione['parent_name'] : ''));
				} else {
					$text = ucwords(mb_strtolower($el['name']));
				}
				$text = '<i class="fas fa-hotel"></i> ' . $text;
				break;
			case 'Model\TravioAssets\Elements\TravioPackage':
				$id = 'p' . $el['travio'];
				if (!empty($el['geo'])) {
					$destinazione = $this->model->_ORM->one('TravioGeo', $el['geo']);
					$text = ucwords(mb_strtolower($el['name']) . ' | ' . $destinazione['name'] . ($destinazione['parent_name'] ? ' | ' . $destinazione['parent_name'] : ''));
				} else {
					$text = ucwords(mb_strtolower($el['name']));
				}
				$text = '<i class="fas fa-plane-departure"></i> ' . $text;
				break;
			default:
				die('Unknown type');
				break;
		}

		return [
			'id' => $id,
			'text' => $text,
		];
	}

	public function getItemFromId($id): array
	{
		if ($id !== null) {
			switch ($id{0}) {
				case 's':
					return $this->getItem($this->model->one('TravioService', ['travio' => substr($id, 1)]));
					break;
				case 'p':
					return $this->getItem($this->model->one('TravioPackage', ['travio' => substr($id, 1)]));
					break;
				case 'd':
					return $this->getItem($this->model->one('TravioGeo', substr($id, 1)));
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

			$joins = [];

			switch ($type[0]) {
				case 'services':
					$joins['travio_services_geo'] = [
						'on' => 'parent',
						'join_field' => 'geo',
						'fields' => [],
					];
					if ($type[1]) {
						$joins['travio_services'] = [
							'full_on' => 'j0.service = j1.id AND j1.visible = 1',
							'fields' => ['type'],
						];
						$where['type'] = $type[1];
					}
					break;
				case 'packages':
					$joins['travio_packages_geo'] = [
						'on' => 'parent',
						'join_field' => 'geo',
						'fields' => [],
					];
					if ($type[1]) {
						$joins['travio_packages'] = [
							'full_on' => 'j0.package = j1.id AND j1.visible = 1',
							'fields' => ['type'],
						];
						$where['type'] = $type[1];
					}
					break;
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

			switch ($type[0]) {
				case 'packages':
					$packages = $this->model->_ORM->all('TravioPackage', $where);
					foreach ($packages as $s)
						$elements[] = $s;
					break;
				default:
					$services = $this->model->_ORM->all('TravioService', $where);
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
