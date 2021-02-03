<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioMasterDataBase extends AdminPage
{
	public function options(): array
	{
		return [
			'element' => 'TravioMasterData',
			'privileges' => [
				'C' => false,
				'R' => false,
				'D' => false,
			],
		];
	}
}
