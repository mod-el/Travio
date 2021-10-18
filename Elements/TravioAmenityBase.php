<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioAmenityBase extends Element
{
	public static ?string $table = 'travio_amenities';

	public function init()
	{
		$this->has('type', [
			'type' => 'single',
			'table' => 'travio_amenities_types',
		]);
	}
}
