<?php namespace Model\Travio\Elements;

use Model\Cache\Cache;
use Model\ORM\Element;

class TravioServiceBase extends Element
{
	public static ?string $table = 'travio_services';

	public function init(): void
	{
		$this->settings['fields']['type'] = [
			'type' => 'select',
			'options' => [
				'' => '',
				1 => 'Volo',
				2 => 'Hotel',
				3 => 'Trasferimento',
				4 => 'Tour',
				5 => 'Autonoleggio',
				6 => 'Crociera',
				7 => 'Traghetto (con rotazioni fisse)',
				14 => 'Traghetto',
				8 => 'Quota automatica',
				9 => 'Assicurazione',
				10 => 'Penale',
				11 => 'Escursioni',
				12 => 'Quota Partecipazione',
				13 => 'Altro',
			],
		];
		$this->settings['fields']['visible'] = [
			'type' => 'checkbox',
		];

		$this->has('subservices', [
			'element' => 'TravioSubservice',
			'field' => 'service',
			'order_by' => 'type, code',
		]);

		$this->has('tags', [
			'element' => 'TravioTag',
			'assoc' => [
				'table' => 'travio_services_tags',
				'parent' => 'service',
				'field' => 'tag',
				'order_by' => 'id',
			],
		]);

		$this->has('descriptions', [
			'table' => 'travio_services_descriptions',
			'field' => 'service',
			'order_by' => 'id',
			'fields' => [
				'text' => [
					'type' => 'ckeditor',
				],
			],
		]);

		$this->has('photos', [
			'table' => 'travio_services_photos',
			'field' => 'service',
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
				'table' => 'travio_services_geo',
				'parent' => 'service',
				'field' => 'geo',
			],
		]);

		$this->has('amenities', [
			'element' => 'TravioAmenity',
			'assoc' => [
				'table' => 'travio_services_amenities',
				'parent' => 'service',
				'field' => 'amenity',
				'order_by' => 'id',
			],
		]);

		$this->has('files', [
			'table' => 'travio_services_files',
			'field' => 'service',
		]);

		$this->has('videos', [
			'table' => 'travio_services_videos',
			'field' => 'service',
		]);

		$this->has('stop_sales', [
			'table' => 'travio_services_stop_sales',
			'field' => 'service',
			'order_by' => '`from`',
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

	public function getCheckinDates(): array
	{
		$cache = Cache::getCacheAdapter();

		$cacheKey = 's' . $this['id'];
		return $cache->get('travio.dates.' . $cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) {
			$item->expiresAfter(3600 * 24 * 7);
			$item->tag('travio.dates');
			$item->tag('travio.dates.' . $this['id']);

			$q = $this->model->select_all('travio_services_dates', [
				'service' => $this['id'],
				'checkin' => ['>=' => date('Y-m-d')],
			], [
				'order_by' => 'checkin',
			]);

			$dates = [];
			foreach ($q as $d)
				$dates[] = $d['checkin'];

			return $dates;
		});
	}

	public function getCheckoutDates(\DateTime $in): array
	{
		$q = $this->model->select('travio_services_dates', [
			'service' => $this['id'],
			'checkin' => $in->format('Y-m-d'),
		]);

		return $q ? array_column($q, 'date') : [];
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
