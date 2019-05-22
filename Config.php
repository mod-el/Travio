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
];
';
		});

		if ($this->model->isLoaded('Multilang')) {
			$this->model->_Multilang->checkAndInsertTable('travio_geo');
			$this->model->_Multilang->checkAndInsertTable('travio_services');
			$this->model->_Multilang->checkAndInsertTable('travio_services_descriptions');
			$this->model->_Multilang->checkAndInsertTable('travio_packages');
		}

		if (!is_dir(INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'travio' . DIRECTORY_SEPARATOR . 'amenities'))
			mkdir(INCLUDE_PATH . 'app-data' . DIRECTORY_SEPARATOR . 'travio' . DIRECTORY_SEPARATOR . 'amenities', 0777, true);

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
	}

	/**
	 * @param string $file
	 * @param string $default
	 */
	private function checkFile(string $file, string $default)
	{
		if (file_exists(INCLUDE_PATH . $file))
			return;

		$dir = pathinfo(INCLUDE_PATH . $file, PATHINFO_DIRNAME);
		if (!is_dir($dir))
			mkdir($dir, 0777, true);

		file_put_contents(INCLUDE_PATH . $file, $default);
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
}
