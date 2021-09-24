<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioPackageBase extends Element
{
	public static ?string $table = 'travio_packages';

	public function init()
	{
		$this->settings['fields']['visible'] = [
			'type' => 'checkbox',
		];

		$this->has('tags', [
			'table' => 'travio_packages_tags',
			'field' => 'package',
			'order_by' => 'id',
		]);

		$this->has('descriptions', [
			'table' => 'travio_packages_descriptions',
			'field' => 'package',
			'order_by' => 'id',
			'fields' => [
				'text' => [
					'type' => 'ckeditor',
				],
			],
		]);

		$this->has('photos', [
			'table' => 'travio_packages_photos',
			'field' => 'package',
			'order_by' => 'id',
		]);

		$this->has('geo', [
			'element' => 'TravioGeo',
			'assoc' => [
				'table' => 'travio_packages_geo',
				'parent' => 'package',
				'field' => 'geo',
			],
		]);

		$this->has('departures', [
			'table' => 'travio_packages_departures',
			'field' => 'package',
			'order_by' => 'date',
		]);

		$this->has('files', [
			'table' => 'travio_packages_files',
			'field' => 'package',
		]);

		$this->has('itinerary', [
			'table' => 'travio_packages_itinerary',
			'field' => 'package',
		]);

		$this->has('hotels', [ // Retrocompatibilità
			'element' => 'TravioService',
			'assoc' => [
				'table' => 'travio_packages_services',
				'parent' => 'package',
				'field' => 'service',
				'where' => ['type' => 2],
			],
		]);

		$this->has('services', [
			'element' => 'TravioService',
			'assoc' => [
				'table' => 'travio_packages_services',
				'parent' => 'package',
				'field' => 'service',
			],
		]);
	}

	public function getMainImg(): ?string
	{
		$photos = $this->photos;
		foreach ($photos as $photo) {
			if ($photo['url'])
				return $photo['url'];
		}

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
