<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioPortsBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioPort',
			'actions' => [
				'import' => [
					'text' => 'Importa',
					'fa-icon' => 'fas fa-file-import',
					'action' => 'importFromTravio(\'ports\'); return false',
				],
			],
			'privileges' => [
				'C' => false,
				'R' => false,
				'D' => false,
			],
		];
	}
}
