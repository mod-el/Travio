<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioPackagesBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioPackage',
			'privileges' => [
				'C' => false,
				'R' => false,
				'D' => DEBUG_MODE,
			],
			'order_by' => 'code',
		];
	}

	public function visualizerOptions(): array
	{
		return [
			'columns' => [
				'travio',
				'code',
				'name',
				'geo',
				'visible' => ['editable' => true],
				'last_update',
			],
		];
	}
}
