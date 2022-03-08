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
				'D' => DEBUG_MODE,
			],
			'order_by' => 'code',
			'fields' => [
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

	public function customize()
	{
		if (!file_exists(INCLUDE_PATH . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'TravioAssets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'travio-services.php')) {
			$this->model->viewOptions['template-module'] = 'Travio';
			$this->model->viewOptions['template'] = 'travio-services-base';
		}
	}
}
