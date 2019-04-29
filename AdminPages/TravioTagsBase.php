<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioTagsBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioTag',
			'actions' => [
				'import' => [
					'text' => 'Importa',
					'fa-icon' => 'fas fa-file-import',
					'action' => 'importFromTravio(\'tags\'); return false',
				],
			],
			'privileges' => [
				'C' => false,
				'R' => false,
				'D' => false,
			],
			'order_by' => 'type_name, name',
		];
	}

	public function visualizerOptions(): array
	{
		return [
			'columns' => [
				'type_name',
				'name',
			],
		];
	}
}
