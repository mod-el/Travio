<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioPackageBase extends Element
{
	public static ?string $table = 'travio_packages';

	public function init(): void
	{
		$this->settings['fields']['visible'] = [
			'type' => 'checkbox',
		];

		$this->has('tags', [
			'element' => 'TravioTag',
			'assoc' => [
				'table' => 'travio_packages_tags',
				'parent' => 'package',
				'field' => 'tag',
				'order_by' => 'id',
			],
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
			'order_by' => '`order`, `id`',
			'afterGet' => function (array $photos) {
				if (!defined('DISABLE_LOCAL_TRAVIO_PHOTOS')) {
					foreach ($photos as &$photo) {
						if ($photo['url'])
							$photo['url'] = $this->model->_Travio->checkPhotoCache($photo['url']);
						if ($photo['thumb'])
							$photo['thumb'] = $this->model->_Travio->checkPhotoCache($photo['thumb']);
					}
				}

				return $photos;
			},
		]);

		$this->has('geo', [
			'element' => 'TravioGeo',
			'assoc' => [
				'table' => 'travio_packages_geo',
				'parent' => 'package',
				'field' => 'geo',
			],
		]);

		$this->has('departs_from', [
			'type' => 'single',
			'element' => 'TravioGeo',
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

		$this->has('guides', [
			'element' => 'TravioMasterData',
			'assoc' => [
				'table' => 'travio_packages_guides',
				'parent' => 'package',
				'field' => 'guide',
			],
		]);

		$this->has('itinerary', [
			'table' => 'travio_packages_itinerary',
			'field' => 'package',
			'order_by' => 'day, id',
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

	public function getDescriptionsByTag(string $tag): array
	{
		$descriptions = $this->descriptions;

		$filtered = [];
		foreach ($descriptions as $description) {
			if ($description['tag'] !== $tag)
				continue;

			$filtered[] = $description;
		}

		return $filtered;
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

	public function getMeta(): array
	{
		return [
			'title' => $this['title'] ?? $this['name'],
			'description' => $this['description'],
			'keywords' => $this['keywords'],
		];
	}
}
