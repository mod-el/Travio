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
				'R' => file_exists(INCLUDE_PATH . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'TravioAssets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'travio-amenities.php'),
				'D' => false,
			],
		];
	}
}
