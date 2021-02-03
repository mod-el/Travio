<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioTagsBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioTag',
			'privileges' => [
				'C' => false,
				'R' => file_exists(INCLUDE_PATH . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'TravioAssets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'travio-tags.php'),
				'D' => false,
			],
			'fields' => [
				'type',
				'name',
			],
			'order_by' => 'type, name',
		];
	}
}
