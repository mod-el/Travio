<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioAmenityBase extends Element
{
	public static ?string $table = 'travio_amenities';
	public static array $fields = [
		'img' => [
			'type' => 'file',
			'path' => 'app-data/travio/single-amenities/[id].png',
			'mime' => 'image/png',
		],
	];

	public function init(): void
	{
		$this->has('type', [
			'type' => 'single',
			'table' => 'travio_amenities_types',
		]);
	}
}
