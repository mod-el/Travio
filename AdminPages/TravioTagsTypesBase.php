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
				'R' => false,
				'D' => false,
			],
			'fields' => [
				'name',
			],
			'order_by' => 'name',
		];
	}
}
