<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioTypologiesBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioTypology',
			'privileges' => [
				'C' => false,
				'R' => file_exists(INCLUDE_PATH . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'TravioAssets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'travio-typologies.php'),
				'D' => false,
			],
			'order_by' => 'code',
			'fields' => [
				'code',
				'name',
				'type',
			],
		];
	}
}
