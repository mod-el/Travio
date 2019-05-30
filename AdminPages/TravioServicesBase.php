<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioServicesBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioService',
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
				'type',
				'geo',
				'visible' => ['editable' => true],
				'last_update',
			],
		];
	}
}
