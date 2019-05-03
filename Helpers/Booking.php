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
					$text = ucwords(strtolower($el['name'])) . ' | ' . ucwords(strtolower($el['parent_name']));
				} else {
					$text = ucwords(strtolower($el['name']));
				}
				break;
			case 'Model\TravioAssets\Elements\TravioService':
				$id = 's' . $el['travio'];
				if (!empty($el['geo'])) {
					$destinazione = $this->model->_ORM->one('TravioGeo', $el['geo']);
					$text = ucwords(strtolower($el['name']) . ' | ' . $destinazione['nome'] . ($destinazione['parent_name'] ? ' | ' . $destinazione['parent_name'] : ''));
				} else {
					$text = ucwords(strtolower($el['name']));
				}
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
		$destinazioni = $this->model->_ORM->all('TravioGeo', $where);

		$where = [
			[
				'sub' => [
					['name', 'LIKE', $query . '%'],
					['name', 'LIKE', '% ' . $query . '%'],
				],
				'operator' => 'OR',
			],
		];
		$servizi = $this->model->_ORM->all('TravioService', $where);

		$elements = [];
		foreach ($destinazioni as $d)
			$elements[] = $d;
		foreach ($servizi as $s)
			$elements[] = $s;

		usort($elements, function ($a, $b) use ($query) {
			$nomeA = strtolower($a['name']);
			$nomeB = strtolower($b['name']);
			$parentA = strtolower($a['parent_name'] ?? '');
			$parentB = strtolower($b['parent_name'] ?? '');

			if ($nomeA == $query) $va = 0;
			elseif ($parentA == $query) $va = 1;
			elseif (stripos($nomeA, $query) !== false) $va = 2;
			elseif (stripos($nomeA, $query) === 0) $va = 3;
			elseif (stripos($parentA, $query) === 0) $va = 4;
			elseif (stripos($parentA, $query) !== false) $va = 5;
			else $va = 6;

			if ($nomeB == $query) $vb = 0;
			elseif ($parentB == $query) $vb = 1;
			elseif (stripos($nomeB, $query) !== false) $vb = 2;
			elseif (stripos($nomeB, $query) === 0) $vb = 3;
			elseif (stripos($parentB, $query) === 0) $vb = 4;
			elseif (stripos($parentB, $query) !== false) $vb = 5;
			else $vb = 6;

			return $va <=> $vb ?: strlen($nomeA) <=> strlen($nomeB);
		});

		return $elements;
	}
}
