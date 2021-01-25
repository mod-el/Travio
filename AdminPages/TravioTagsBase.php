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
				'R' => false,
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
