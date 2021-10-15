<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioServiceBase extends Element
{
	public static ?string $table = 'travio_services';

	public function init()
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
}
