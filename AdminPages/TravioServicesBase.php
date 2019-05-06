<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioServicesBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioService',
			'actions' => [
				'import' => [
					'text' => 'Importa',
					'fa-icon' => 'fas fa-file-import',
					'action' => 'importFromTravio(\'services\'); return false',
				],
			],
			'privileges' => [
				'C' => false,
				'R' => false,
				'D' => false,
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
