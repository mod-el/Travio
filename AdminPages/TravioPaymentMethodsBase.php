<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioPaymentMethodsBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioPaymentMethod',
			'privileges' => [
				'C' => false,
				'R' => true,
				'D' => false,
			],
			'fields' => [
				'name',
				'visible' => ['editable' => true],
			],
			'order_by' => 'name',
		];
	}
}
