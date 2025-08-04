<?php namespace Model\Travio\Elements;

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
				foreach ($photos as &$photo) {
					if ($photo['url'])
						$photo['url'] = $this->model->_Travio->checkPhotoCache($photo['url']);
					if ($photo['thumb'])
						$photo['thumb'] = $this->model->_Travio->checkPhotoCache($photo['thumb']);
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

		$this->has('availability', [
			'table' => 'travio_services_availability',
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
		$today = date_create(date('Y-m-d'));

		$weekdays = [
			'sunday',
			'monday',
			'tuesday',
			'wednesday',
			'thursday',
			'friday',
			'saturday',
		];

		$dates = [];
		foreach ($this->availability as $availability) {
			if ($availability['type'] === 'closed')
				continue;
			if (date_create($availability['to']) < $today)
				continue;

			$day = date_create($availability['from']);
			$to = date_create($availability['to']);

			for (; $day <= $to; $day->modify('+1 day')) {
				if ($day < $today)
					continue;

				$weekday = $weekdays[(int)$day->format('w')];
				if (!$availability['in_' . $weekday])
					continue;

				$dates[] = $day->format('Y-m-d');
			}
		}
		return $dates;
	}

	public function getCheckoutDates(\DateTime $in): array
	{
		$config = \Model\Config\Config::get('travio');

		$inAvailability = null;
		$lastAvailability = null;
		foreach ($this->availability as $availability) {
			if ($availability['type'] === 'closed')
				continue;
			if ($in >= date_create($availability['from']) and $in <= date_create($availability['to']))
				$inAvailability = $availability;

			$lastAvailability = $availability['to'];
		}

		if (!$inAvailability)
			return [];

		$weekdays = [
			'sunday',
			'monday',
			'tuesday',
			'wednesday',
			'thursday',
			'friday',
			'saturday',
		];

		$list = [];

		$lastAvailability = date_create($lastAvailability);
		$lastAvailability->modify('+1 day');

		$day = clone $in;

		for (; $day <= $lastAvailability; $day->modify('+1 day')) {
			$duration = date_diff($day, $in, true)->days;
			if ($day < $in or $duration > 60)
				continue;

			if ($inAvailability['only_multiples_of'] and $duration % $inAvailability['only_multiples_of'] > 0)
				continue;

			if ($inAvailability['fixed_duration'] and $duration !== ($inAvailability['fixed_duration'] - 1))
				continue;

			$outAvailability = null;
			if ($config['availability_dates']['min_stay_from'] === 'out' or $config['availability_dates']['out_weekdays_from'] === 'out') {
				foreach ($this->availability as $availability) {
					if ($availability['type'] === 'closed' and $day->format('Y-m-d') !== $availability['from'])
						continue;
					if ($day >= date_create($availability['from']) and $day <= date_create($availability['to'])) {
						$outAvailability = $availability;
						break;
					}
				}
			}

			$weekday = $weekdays[$day->format('w')];
			if ($config['availability_dates']['out_weekdays_from'] === 'in' and !$inAvailability['out_' . $weekday])
				continue;

			if ($config['availability_dates']['out_weekdays_from'] === 'out' and (!$outAvailability or !$outAvailability['out_' . $weekday]))
				continue;

			$minStay = $config['availability_dates']['min_stay_from'] === 'in' ? $inAvailability['min_stay'] : ($outAvailability ? $outAvailability['min_stay'] : null);
			if ($minStay and $duration < $minStay)
				continue;

			$list[] = $day->format('Y-m-d');
		}

		return $list;
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
