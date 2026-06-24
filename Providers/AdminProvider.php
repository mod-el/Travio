<?php namespace Model\Travio\Providers;

use Model\Admin\AbstractAdminProvider;

class AdminProvider extends AbstractAdminProvider
{
	public static function getAdditionalPages(): array
	{
		return [
			[
				'name' => 'Travio',
				'page' => 'TravioImport',
				'rule' => 'travio-import',
				'sub' => [
					[
						'name' => 'Destinazioni',
						'page' => 'TravioGeo',
						'rule' => 'travio-geo',
					],
					[
						'name' => 'Servizi',
						'page' => 'TravioServices',
						'rule' => 'travio-services',
					],
					[
						'name' => 'Pacchetti',
						'page' => 'TravioPackages',
						'rule' => 'travio-packages',
					],
					[
						'name' => 'Porti',
						'page' => 'TravioPorts',
						'rule' => 'travio-ports',
					],
					[
						'name' => 'Aeroporti',
						'page' => 'TravioAirports',
						'rule' => 'travio-airports',
					],
					[
						'name' => 'Tags',
						'page' => 'TravioTags',
						'rule' => 'travio-tags',
					],
					[
						'name' => 'Amenities',
						'page' => 'TravioAmenities',
						'rule' => 'travio-amenities',
					],
					[
						'name' => 'Tipi amenities',
						'page' => 'TravioAmenitiesTypes',
						'rule' => 'travio-amenities-types',
					],
					[
						'name' => 'Sottotipologie servizi',
						'page' => 'TravioTypologies',
						'rule' => 'travio-services-typologies',
					],
					[
						'name' => 'Classificazioni',
						'page' => 'TravioClassifications',
						'rule' => 'travio-classifications',
					],
					[
						'name' => 'Stazioni transfer',
						'page' => 'TravioStations',
						'rule' => 'travio-stations',
					],
					[
						'name' => 'Metodi di pagamento',
						'page' => 'TravioPaymentMethods',
						'rule' => 'travio-payment-methods',
					],
					[
						'name' => 'Condizioni di pagamento',
						'page' => 'TravioPaymentConditions',
						'rule' => 'travio-payment-conditions',
					],
					[
						'name' => 'Anagrafiche',
						'page' => 'TravioMasterData',
						'rule' => 'travio-master-data',
					],
				],
			],
		];
	}
}
