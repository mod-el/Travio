<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioPackageBase extends Element
{
	public static $table = 'travio_packages';

	public function init()
	{
		$this->settings['fields']['visible'] = [
			'type' => 'checkbox',
		];

		/*$this->has('tags', [
			'table' => 'travio_services_tags',
			'field' => 'service',
			'order_by' => 'id',
		]);

		$this->has('descriptions', [
			'table' => 'travio_services_descriptions',
			'field' => 'service',
			'order_by' => 'id',
		]);

		$this->has('photos', [
			'table' => 'travio_services_photos',
			'field' => 'service',
			'order_by' => 'id',
		]);

		$this->has('geo', [
			'element' => 'TravioGeo',
			'assoc' => [
				'table' => 'travio_services_geo',
				'parent' => 'service',
				'field' => 'geo',
			],
		]);

		$this->has('amenities', [
			'table' => 'travio_services_amenities',
			'field' => 'service',
		]);

		$this->has('files', [
			'table' => 'travio_services_files',
			'field' => 'service',
		]);

		$this->has('videos', [
			'table' => 'travio_services_videos',
			'field' => 'service',
		]);*/
	}

	public function getMainImg(): ?string
	{
		/*$photos = $this->photos;
		foreach ($photos as $photo) {
			if ($photo['url'])
				return $photo['url'];
		}*/

		return null;
	}

	public function getThumb(): ?string
	{
		$photos = $this->photos;
		foreach ($photos as $photo) {
			if ($photo['thumb'])
				return $photo['thumb'];
		}

		foreach ($photos as $photo) { // Fallback
			if ($photo['url'])
				return $photo['url'];
		}

		return null;
	}
}
