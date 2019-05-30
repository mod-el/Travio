<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioImport extends AdminPage
{
	public function viewOptions(): array
	{
		return [
			'template-module' => 'Travio',
			'template' => 'travio-import',
		];
	}
}
