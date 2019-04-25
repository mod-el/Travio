<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioGeoBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioGeo',
			'actions' => [
				'import' => [
					'text' => 'Importa',
					'fa-icon' => 'fas fa-file-import',
					'action' => 'importFromTravio(\'geo\'); return false',
				],
			],
			'privileges' => [
				'C' => false,
				'D' => false,
			],
		];
	}

	public function visualizerOptions(): array
	{
		return [
			'columns' => [
				'name',
				'parent_name',
			],
		];
	}
}
