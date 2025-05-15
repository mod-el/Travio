<?php namespace Model\Travio\Providers;

use Model\Db\AbstractDbProvider;

class DbProvider extends AbstractDbProvider
{
	public static function getMigrationsPaths(): array
	{
		return [
			[
				'path' => 'model/Travio/migrations',
			],
		];
	}
}
