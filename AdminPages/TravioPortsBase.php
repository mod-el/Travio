<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioPortsBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioPort',
			'privileges' => [
				'C' => false,
				'R' => false,
				'D' => false,
			],
		];
	}
}
