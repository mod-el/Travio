<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;
use Model\Db\Db;

class TravioPackagesBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioPackage',
			'privileges' => [
				'C' => false,
				'D' => DEBUG_MODE,
			],
			'order_by' => 'code',
			'fields' => [
				'travio',
				'code',
				'name',
				'geo',
				'departs_from',
				'visible',
				'last_update',
			],
			'actions' => [
				'reset-updates' => [
					'text' => 'Reset updates',
					'specific' => 'list',
					'fa-icon' => 'fas fa-undo',
					'action' => 'resetTravioUpdates("travio-packages")',
				],
			],
		];
	}

	public function resetUpdates(array $payload): array
	{
		$db = Db::getConnection();
		$db->update('travio_packages', [], ['last_update' => null], ['confirm' => true]);

		return [
			'success' => true,
			'message' => 'Updates reset successfully',
		];
	}

	public function customize()
	{
		$adminPath = $this->model->_Admin->getPath() ? DIRECTORY_SEPARATOR . $this->model->_Admin->getPath() : '';
		if (!file_exists(INCLUDE_PATH . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'TravioAssets' . DIRECTORY_SEPARATOR . 'templates' . $adminPath . DIRECTORY_SEPARATOR . 'travio-packages.php')) {
			$this->model->viewOptions['template-module'] = 'Travio';
			$this->model->viewOptions['template'] = 'travio-packages-base';
		}
	}
}
