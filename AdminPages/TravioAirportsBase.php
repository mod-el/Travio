<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioAirportsBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioAirport',
			'privileges' => [
				'C' => false,
				'R' => false,
				'D' => false,
			],
		];
	}
}
