<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioStationBase extends Element
{
	public static ?string $table = 'travio_stations';

	public function init(): void
	{
		$this->has('links', [
			'table' => 'travio_stations_links',
			'field' => 'station',
		]);
	}
}
