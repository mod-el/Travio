<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioPaymentConditionsBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioPaymentCondition',
			'privileges' => [
				'C' => false,
				'R' => true,
				'D' => false,
			],
			'order_by' => 'name',
		];
	}
}
