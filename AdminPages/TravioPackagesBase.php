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
				'R' => file_exists(INCLUDE_PATH . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'TravioAssets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'travio-packages.php'),
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
