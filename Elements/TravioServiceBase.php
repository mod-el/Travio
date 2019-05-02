<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioServiceBase extends Element
{
	public static $table = 'travio_services';

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

		$this->has('tags', [
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
			'table' => 'travio_services_geo',
			'field' => 'service',
			'order_by' => 'id',
		]);
	}
}
