<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioAmenitiesBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioAmenity',
			'actions' => [
				'import' => [
					'text' => 'Importa',
					'fa-icon' => 'fas fa-file-import',
					'action' => 'importFromTravio(\'amenities\'); return false',
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
