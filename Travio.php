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
	 * First initialization of module
	 *
	 * @param array $data
	 * @return bool
	 */
	public function init(?array $data = null): bool
	{
		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_geo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_geo_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `lang` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parent_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_geo_texts_idx` (`parent`),
  CONSTRAINT `travio_geo_texts` FOREIGN KEY (`parent`) REFERENCES `travio_geo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_geo_custom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_geo_custom_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `lang` char(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_geo_custom_texts_idx` (`parent`),
  CONSTRAINT `travio_geo_custom_texts` FOREIGN KEY (`parent`) REFERENCES `travio_geo_custom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_amenities_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_amenities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_amenities_type_idx` (`type`),
  CONSTRAINT `travio_amenities_type` FOREIGN KEY (`type`) REFERENCES `travio_amenities_tags` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `travio` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `typology` int(11) DEFAULT NULL,
  `geo` int(11) DEFAULT NULL,
  `classification` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `classification_level` tinyint(4) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `price` decimal(7,2) DEFAULT NULL,
  `min_date` date DEFAULT NULL,
  `max_date` date DEFAULT NULL,
  `visible` tinyint NOT NULL DEFAULT 1,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travio` (`travio`),
  KEY `travio_services_geo_idx` (`geo`),
  CONSTRAINT `travio_services_geo` FOREIGN KEY (`geo`) REFERENCES `travio_geo` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `lang` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_services_texts_idx` (`parent`),
  CONSTRAINT `travio_services_texts` FOREIGN KEY (`parent`) REFERENCES `travio_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` int(11) NOT NULL,
  `video` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_services_videos_idx` (`service`),
  CONSTRAINT `travio_services_videos` FOREIGN KEY (`service`) REFERENCES `travio_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` int(11) NOT NULL,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_services_tags_idx` (`service`),
  CONSTRAINT `travio_services_tags` FOREIGN KEY (`service`) REFERENCES `travio_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_amenities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` int(11) NOT NULL,
  `amenity` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tag` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_services_amenities_idx` (`service`),
  KEY `travio_services_amenity_idx` (`amenity`),
  CONSTRAINT `travio_services_amenities` FOREIGN KEY (`service`) REFERENCES `travio_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `travio_services_amenity` FOREIGN KEY (`amenity`) REFERENCES `travio_amenities` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_services_files_idx` (`service`),
  CONSTRAINT `travio_services_files` FOREIGN KEY (`service`) REFERENCES `travio_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_descriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` int(11) NOT NULL,
  `tag` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_services_descriptions_idx` (`service`),
  CONSTRAINT `travio_services_descriptions` FOREIGN KEY (`service`) REFERENCES `travio_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_descriptions_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `lang` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_services_descriptions_texts_idx` (`parent`),
  CONSTRAINT `travio_services_descriptions_texts` FOREIGN KEY (`parent`) REFERENCES `travio_services_descriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_photos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `service` INT NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `thumb` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `travio_services_photos_idx` (`service` ASC),
  CONSTRAINT `travio_services_photos`
    FOREIGN KEY (`service`)
    REFERENCES `travio_services` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_geo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` int(11) NOT NULL,
  `geo` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_services_geo_idx` (`service`),
  KEY `travio_services_geo_geo_idx` (`geo`),
  CONSTRAINT `travio_services_geo_geo` FOREIGN KEY (`geo`) REFERENCES `travio_geo` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `travio_services_geo_service` FOREIGN KEY (`service`) REFERENCES `travio_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_custom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_services_custom_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `lang` char(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_services_custom_texts_idx` (`parent`),
  CONSTRAINT `travio_services_custom_texts` FOREIGN KEY (`parent`) REFERENCES `travio_services_custom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) DEFAULT NULL,
  `type_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_ports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_airports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		return true;
	}

	public function postUpdate_0_2_1()
	{
		$this->model->_Db->query('ALTER TABLE `travio_services` 
ADD COLUMN `visible` TINYINT NOT NULL DEFAULT 1 AFTER `max_date`;');
		return true;
	}

	public function postUpdate_0_2_2()
	{
		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_ports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_airports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		return true;
	}

	public function postUpdate_0_2_3()
	{
		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_amenities_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE IF NOT EXISTS `travio_amenities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travio_amenities_type_idx` (`type`),
  CONSTRAINT `travio_amenities_type` FOREIGN KEY (`type`) REFERENCES `travio_amenities_tags` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('ALTER TABLE `travio_services_amenities` 
ADD COLUMN `amenity` INT NULL AFTER `service`,
ADD INDEX `travio_services_amenities_amenity_idx` (`id` ASC);');

		$this->model->_Db->query('ALTER TABLE `travio_services_amenities` 
ADD CONSTRAINT `travio_services_amenities_amenity`
  FOREIGN KEY (`amenity`)
  REFERENCES `travio_amenities` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;');

		return true;
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
