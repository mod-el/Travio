<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioLuggageTypesBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioLuggageType',
			'privileges' => [
				'C' => false,
				'R' => file_exists(INCLUDE_PATH . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'TravioAssets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'travio-luggage-types.php'),
				'D' => false,
			],
			'order_by' => 'name',
			'fields' => [
				'name',
				'weight',
				'length',
				'width',
				'height',
			],
		];
	}
}
