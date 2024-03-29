<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioGeoBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioGeo',
			'order_by' => 'name',
			'visualizer' => 'Tree',
			'privileges' => [
				'C' => false,
				'R' => file_exists(INCLUDE_PATH . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'TravioAssets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'travio-geo.php'),
				'D' => false,
			],
			'fields' => [
				'name',
			],
		];
	}
}
