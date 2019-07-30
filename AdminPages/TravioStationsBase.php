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
				'R' => false,
				'D' => false,
			],
		];
	}

	public function visualizerOptions(): array
	{
		return [
			'columns' => [
				'code',
				'name',
			],
		];
	}
}
