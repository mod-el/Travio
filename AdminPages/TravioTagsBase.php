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
			'order_by' => 'type_name, name',
		];
	}

	public function visualizerOptions(): array
	{
		return [
			'columns' => [
				'type_name',
				'name',
			],
		];
	}
}
