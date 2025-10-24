<?php namespace Model\Travio\Providers;

use Model\Router\AbstractRouterProvider;

class RouterProvider extends AbstractRouterProvider
{
	public static function getRoutes(): array
	{
		return [
			[
				'pattern' => '/import-from-travio',
				'controller' => 'ImportFromTravio',
			],
			[
				'pattern' => '/travio-dates',
				'controller' => 'GetDates',
			],
		];
	}
}
