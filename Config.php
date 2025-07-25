<?php namespace Model\Travio;

use Model\Core\Module_Config;

class Config extends Module_Config
{
	/**
	 */
	protected function assetsList(): void
	{
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
		$this->checkFile('app/modules/TravioAssets/Elements/TravioPaymentMethod.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioPaymentMethodBase;

class TravioPaymentMethod extends TravioPaymentMethodBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioPaymentMethods.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioPaymentMethodsBase;

class TravioPaymentMethods extends TravioPaymentMethodsBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioPaymentCondition.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioPaymentConditionBase;

class TravioPaymentCondition extends TravioPaymentConditionBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioPaymentConditions.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioPaymentConditionsBase;

class TravioPaymentConditions extends TravioPaymentConditionsBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioLuggageType.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioLuggageTypeBase;

class TravioLuggageType extends TravioLuggageTypeBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioLuggageTypes.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioLuggageTypesBase;

class TravioLuggageTypes extends TravioLuggageTypesBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/Elements/TravioClassification.php', '<?php namespace Model\\TravioAssets\\Elements;

use Model\\Travio\\Elements\\TravioClassificationBase;

class TravioClassification extends TravioClassificationBase
{
}
');
		$this->checkFile('app/modules/TravioAssets/AdminPages/TravioClassifications.php', '<?php namespace Model\\TravioAssets\\AdminPages;

use Model\\Travio\\AdminPages\\TravioClassificationsBase;

class TravioClassifications extends TravioClassificationsBase
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
				'dates' => 'travio-dates',
			],
			'controllers' => [
				'ImportFromTravio',
				'GetDates',
			],
		];
	}

	public function retrieveConfig(): array
	{
		return \Model\Config\Config::get('travio');
	}

	public function getConfigData(): ?array
	{
		return [];
	}
}
