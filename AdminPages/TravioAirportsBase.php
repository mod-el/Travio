<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioAirportsBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioAirport',
			'actions' => [
				'import' => [
					'text' => 'Importa',
					'fa-icon' => 'fas fa-file-import',
					'action' => 'importFromTravio(\'airports\'); return false',
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
