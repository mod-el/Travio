<?php namespace Model\Travio;

use Model\Core\Module_Config;

class Config extends Module_Config
{
	/**
	 */
	protected function assetsList()
	{
		$this->addAsset('config', 'config.php', function () {
			return '<?php
$config = [
	\'license\' => null,
	\'key\' => null,
	\'target-types\' => [
		[
			\'search\' => \'service\',
			\'type\' => 2,
		],
	],
	\'dev\' => true,
	\'import\' => [
		\'geo\' => [
			\'import\' => true,
		],
		\'services\' => [
			\'import\' => true,
			\'override\' => [
				\'name\' => true,
				\'price\' => true,
				\'classification\' => true,
				\'classification_level\' => true,
				\'min_date\' => true,
				\'max_date\' => true,
				\'lat\' => true,
				\'lng\' => true,
			],
		],
		\'subservices\' => [
			\'import\' => false,
		],
		\'packages\' => [
			\'import\' => true,
			\'override\' => [
				\'name\' => true,
				\'price\' => true,
			],
		],
		\'tags\' => [
			\'import\' => true,
		],
		\'amenities\' => [
			\'import\' => true,
		],
		\'airports\' => [
			\'import\' => true,
			\'override\' => [
				\'name\' => true,
				\'departure\' => true,
			],
		],
		\'ports\' => [
			\'import\' => true,
			\'override\' => [
				\'name\' => true,
				\'departure\' => true,
			],
		],
		\'stations\' => [
			\'import\' => true,
			\'override\' => [
				\'name\' => true,
			],
		],
		\'master-data\' => [
			\'import\' => false,
			\'filters\' => [],
		],
	],
];
';
		});

		if ($this->model->moduleExists('Multilang')) {
			$this->model->_Multilang->checkAndInsertTable('travio_geo');
			$this->model->_Multilang->checkAndInsertTable('travio_services');
			$this->model->_Multilang->checkAndInsertTable('travio_services_descriptions');
			$this->model->_Multilang->checkAndInsertTable('travio_subservices');
			$this->model->_Multilang->checkAndInsertTable('travio_subservices_descriptions');
			$this->model->_Multilang->checkAndInsertTable('travio_packages');
			$this->model->_Multilang->checkAndInsertTable('travio_packages_descriptions');
			$this->model->_Multilang->checkAndInsertTable('travio_stations');
			$this->model->_Multilang->checkAndInsertTable('travio_amenities');
			$this->model->_Multilang->checkAndInsertTable('travio_tags');
		}

		if (!is_dir(INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'travio' . DIRECTORY_SEPARATOR . 'amenities'))
			mkdir(INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'travio' . DIRECTORY_SEPARATOR . 'amenities', 0777, true);

		$this->checkFile('app/modules/TravioAssets/TravioAssets.php', '<?php namespace Model\\TravioAssets;

use Model\\Travio\\TravioAssetsBase;

class TravioAssets extends TravioAssetsBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioGeo.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioGeoBase;

class TravioGeo extends TravioGeoBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioGeo.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioGeoBase;

class TravioGeo extends TravioGeoBase
{
}
');

		$this->checkFile('app/modules/TravioAssets/Elements/TravioTag.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioTagBase;

class TravioTag extends TravioTagBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioTags.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioTagsBase;

class TravioTags extends TravioTagsBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioService.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioServiceBase;

class TravioService extends TravioServiceBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioServices.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioServicesBase;

class TravioServices extends TravioServicesBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioSubservice.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioSubserviceBase;

class TravioSubservice extends TravioSubserviceBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioPackage.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioPackageBase;

class TravioPackage extends TravioPackageBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioPackages.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioPackagesBase;

class TravioPackages extends TravioPackagesBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioPort.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioPortBase;

class TravioPort extends TravioPortBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioPorts.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioPortsBase;

class TravioPorts extends TravioPortsBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioAirport.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioAirportBase;

class TravioAirport extends TravioAirportBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioAirports.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioAirportsBase;

class TravioAirports extends TravioAirportsBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioAmenity.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioAmenityBase;

class TravioAmenity extends TravioAmenityBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioAmenities.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioAmenitiesBase;

class TravioAmenities extends TravioAmenitiesBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioStation.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioStationBase;

class TravioStation extends TravioStationBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioStations.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioStationsBase;

class TravioStations extends TravioStationsBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioMasterData.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioMasterDataBase;

class TravioMasterData extends TravioMasterDataBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioMasterData.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioMasterDataBase;

class TravioMasterData extends TravioMasterDataBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioOrder.php', file_get_contents(INCLUDE_PATH . 'model' . DIRECTORY_SEPARATOR . 'Travio' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'TravioOrderBaseContent.php'));

		if (file_exists(INCLUDE_PATH . 'app/modules/TravioAssets/Elements/TravioTagType.php'))
			unlink(INCLUDE_PATH . 'app/modules/TravioAssets/Elements/TravioTagType.php');
		if (file_exists(INCLUDE_PATH . 'app/modules/TravioAssets/AdminPages/TravioTagsTypes.php'))
			unlink(INCLUDE_PATH . 'app/modules/TravioAssets/AdminPages/TravioTagsTypes.php');
	}

	/**
	 * @return array
	 */
	public function getRules(): array
	{
		return [
			'rules' => [
				'import' => 'import-from-travio',
			],
			'controllers' => [
				'ImportFromTravio',
			],
		];
	}

	/**
	 * @return bool
	 */
	public function makeCache(): bool
	{
		$config = $this->retrieveConfig();
		if ($config and !isset($config['import'])) {
			$config['import'] = [
				'geo' => [
					'import' => true,
				],
				'services' => [
					'import' => true,
					'override' => $config['override-on-import']['services'],
				],
				'subservices' => [
					'import' => false,
				],
				'packages' => [
					'import' => true,
					'override' => $config['override-on-import']['packages'],
				],
				'tags' => [
					'import' => true,
				],
				'amenities' => [
					'import' => true,
				],
				'ports' => [
					'import' => true,
					'override' => $config['override-on-import']['ports'],
				],
				'airports' => [
					'import' => true,
					'override' => $config['override-on-import']['airports'],
				],
				'stations' => [
					'import' => true,
					'override' => $config['override-on-import']['stations'],
				],
				'master-data' => [
					'import' => false,
					'filters' => [],
				],
			];

			unset($config['override-on-import']);
			$this->saveConfig('config', $config);
		}

		if ($config and !isset($config['import']['subservices'])) {
			$config['import']['subservices'] = ['import' => false];
			$this->saveConfig('config', $config);
		}

		return true;
	}
}
