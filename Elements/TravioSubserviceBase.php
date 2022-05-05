<?php namespace Model\Travio\Elements;

use Model\ORM\Element;
use Model\Travio\TravioCacheTrait;

class TravioSubserviceBase extends Element
{
	use TravioCacheTrait;

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
			'afterGet' => function (array $photos) {
				foreach ($photos as &$photo) {
					if ($photo['url'])
						$photo['url'] = $this->checkTravioPhotoCache($photo['url']);
					if ($photo['thumb'])
						$photo['thumb'] = $this->checkTravioPhotoCache($photo['thumb']);
				}

				return $photos;
			},
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
