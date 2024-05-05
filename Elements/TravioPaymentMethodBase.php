<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioPaymentMethodBase extends Element
{
	public static ?string $table = 'travio_payment_methods';

	public function init(): void
	{
		$this->settings['fields']['visible'] = [
			'type' => 'checkbox',
		];

		$this->settings['fields']['img'] = [
			'type' => 'file',
			'path' => 'app-data/img/payment-methods/[id].png',
			'mime' => 'image/png',
		];
	}
}
