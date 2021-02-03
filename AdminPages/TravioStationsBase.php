<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioStationsBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioStation',
			'privileges' => [
				'C' => false,
				'R' => file_exists(INCLUDE_PATH . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'TravioAssets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'travio-stations.php'),
				'D' => false,
			],
			'fields' => [
				'code',
				'name',
			],
		];
	}
}
