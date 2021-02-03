<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioTagsTypesBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioTagType',
			'privileges' => [
				'C' => false,
				'R' => file_exists(INCLUDE_PATH . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'TravioAssets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'travio-tags-types.php'),
				'D' => false,
			],
			'fields' => [
				'name',
			],
			'order_by' => 'name',
		];
	}
}
