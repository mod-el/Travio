<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioAmenitiesTypes extends AdminPage
{
	public function options(): array
	{
		return [
			'table' => 'travio_amenities_types',
			'privileges' => [
				'C' => false,
				'D' => false,
			],
		];
	}

	public function customize()
	{
		$this->model->_Admin->field('img', [
			'type' => 'file',
			'path' => 'app-data/travio/amenities/[id].png',
			'mime' => 'image/png',
		]);
	}
}
