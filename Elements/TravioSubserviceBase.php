<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioSubserviceBase extends Element
{
	public static ?string $table = 'travio_subservices';

	public function init()
	{
		$this->settings['fields']['type'] = [
			'type' => 'select',
			'options' => [
				'' => '',
				1 => 'Sistemazione',
				2 => 'Trattamento',
				3 => 'Supplemento',
				4 => 'Riduzione',
				5 => 'Tratta disp',
			],
		];

		$this->belongsTo('TravioService', [
			'field' => 'service',
			'children' => 'subservices',
		]);

		$this->has('tags', [
			'table' => 'travio_subservices_tags',
			'field' => 'subservice',
			'order_by' => 'id',
		]);

		$this->has('descriptions', [
			'table' => 'travio_subservices_descriptions',
			'field' => 'subservice',
			'order_by' => 'id',
			'fields' => [
				'text' => [
					'type' => 'ckeditor',
				],
			],
		]);

		$this->has('photos', [
			'table' => 'travio_subservices_photos',
			'field' => 'subservice',
			'order_by' => '`order`, `id`',
		]);

		$this->has('amenities', [
			'table' => 'travio_subservices_amenities',
			'field' => 'subservice',
		]);

		$this->has('files', [
			'table' => 'travio_subservices_files',
			'field' => 'subservice',
		]);
	}
}
