<?php namespace Model\Travio\Helpers;

use Model\Db\Db;
use Model\InstantSearch\Base;
use Model\Multilang\Ml;

class Booking extends Base
{
	public function getItem(array|object|null $el): array
	{
		$fill = [];

		$fillFields = [];
		if (isset($_POST['fill']))
			$fillFields = explode(',', $_POST['fill']) ?: [];

		foreach ($fillFields as $fillField) {
			if (in_array($fillField, $el->getDataKeys()))
				$fill[$fillField] = $el[$fillField];
		}

		switch (get_class($el)) {
			case 'Model\TravioAssets\Elements\TravioGeo':
				$id = 'd' . $el['id'];
				if (!empty($el['parent_name']))
					$plainText = ucwords(mb_strtolower($el['name'])) . ' | ' . ucwords(mb_strtolower($el['parent_name']));
				else
					$plainText = ucwords(mb_strtolower($el['name']));

				$text = '<i class="fas fa-map-marker-alt"></i> ' . entities($plainText);
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
				break;

			default:
				die('Unknown type');
		}

		return [
			'id' => $id,
			'text' => $text,
			'plainText' => $plainText,
			'fill' => $fill,
		];
	}

	public function getItemFromId(?string $id): array
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
		$db = Db::getConnection();

		$show = (isset($_POST['show']) and in_array($_POST['show'], ['geo', 'services', 'both'])) ? $_POST['show'] : 'both';
		$type = isset($_POST['type']) ? explode('-', $_POST['type']) : [null, null];
		if (count($type) === 1)
			$type[] = null;
		if (count($type) > 2)
			return [];

		// Per compatibilità con nuove API
		if ($type[0] === 'hotels') {
			$type[0] = 'service';
			$type[1] = 2;
		}
		if ($type[0] === 'packages')
			$type[0] = 'package';
		/***************************/

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
				$where[] = '(tgp.parent = ' . $db->parseValue($_POST['geo-parent']) . ' OR travio_geo_texts.parent = ' . $db->parseValue($_POST['geo-parent']) . ')';
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

			$destinazioni = $db->selectAll('travio_geo_texts', $where, [
				'order_by' => 'travio_geo_texts.parent, travio_geo_texts.lang!=' . $db->parseValue(Ml::getLang()),
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
				[
					'sub' => [
						['name', 'LIKE', $query . '%'],
						['name', 'LIKE', '% ' . $query . '%'],
					],
					'operator' => 'OR',
				],
				'visible' => 1,
			];
			if ($type[1])
				$where['type'] = $type[1];
			elseif ($type[0] === 'package')
				$where['type'] = 2;

			$joins = [];
			if ($show === 'both') {
				$joins['travio_geo_texts'] = [
					'type' => 'left',
					'on' => ['geo' => 'parent'],
					'fields' => [
						'name' => 'geo_name',
						'parent_name' => 'geo_parent_name',
					],
					'where' => [
						'lang' => Ml::getLang(),
					],
				];

				$where[0]['sub'][] = ['geo_name', 'LIKE', $query . '%'];
				$where[0]['sub'][] = ['geo_name', 'LIKE', '% ' . $query . '%'];
				$where[0]['sub'][] = ['geo_parent_name', 'LIKE', $query . '%'];
				$where[0]['sub'][] = ['geo_parent_name', 'LIKE', '% ' . $query . '%'];
			}
			if (isset($_POST['geo-parent'])) {
				switch ($type[0]) {
					case 'packages':
						$joins['travio_packages_geo'] = [
							'alias' => 'tpg',
							'on' => ['id' => 'package'],
							'fields' => [],
						];

						$where[] = 'tpg.geo = ' . $db->parseValue($_POST['geo-parent']);
						break;
					default:
						$joins['travio_services_geo'] = [
							'alias' => 'tsg',
							'on' => ['id' => 'service'],
							'fields' => [],
						];

						$where[] = 'tsg.geo = ' . $db->parseValue($_POST['geo-parent']);
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
