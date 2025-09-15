<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioAmenitiesBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioAmenity',
			'privileges' => [
				'C' => false,
				'D' => false,
			],
			'order_by' => 'type, name',
		];
	}
}
