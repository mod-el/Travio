<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;
use Model\Db\Db;

class TravioImport extends AdminPage
{
	private const STORAGE_URL = 'https://storage.travio.it/';
	private const CACHE_PATH = 'app-data/travio/cache/';

	private const PHOTO_SOURCES = [
		[
			'table' => 'travio_services_photos',
			'parent' => 'service',
			'type' => 'service',
		],
		[
			'table' => 'travio_packages_photos',
			'parent' => 'package',
			'type' => 'package',
		],
		[
			'table' => 'travio_subservices_photos',
			'parent' => 'subservice',
			'type' => 'subservice',
		],
		[
			'table' => 'travio_services_itinerary_photos',
			'parent' => 'itinerary',
			'type' => 'service-itinerary',
		],
		[
			'table' => 'travio_packages_itinerary_photos',
			'parent' => 'itinerary',
			'type' => 'package-itinerary',
		],
	];

	public function customize()
	{
		$this->model->viewOptions['template-module'] = 'Travio';
		$this->model->viewOptions['template'] = 'travio-import';
	}

	/**
	 * Given a local cached photo path (or a storage.travio.it url), looks for the service or the package it belongs to
	 *
	 * @param array $payload
	 * @return array
	 */
	public function findPhoto(array $payload): array
	{
		$url = $this->normalizePhotoUrl($payload['path'] ?? '');

		$results = $this->searchPhoto($url, false);
		if (count($results) === 0)
			$results = $this->searchPhoto($url, true);

		return [
			'success' => true,
			'url' => $url,
			'results' => $results,
		];
	}

	/**
	 * Converts a local cached path (as built by Travio::checkPhotoCache) back to the original storage url
	 *
	 * @param string $path
	 * @return string
	 */
	private function normalizePhotoUrl(string $path): string
	{
		$path = str_replace('\\', '/', trim($path));
		$path = explode('?', $path)[0];
		$path = explode('#', $path)[0];
		$path = rawurldecode(trim($path));

		$cachePosition = strpos($path, self::CACHE_PATH);
		if ($cachePosition !== false)
			$path = substr($path, $cachePosition + strlen(self::CACHE_PATH));
		elseif (str_starts_with($path, self::STORAGE_URL))
			$path = substr($path, strlen(self::STORAGE_URL));

		$path = ltrim($path, '/');
		if (!$path)
			throw new \Exception('Percorso non riconosciuto', 400);

		return self::STORAGE_URL . $path;
	}

	/**
	 * Looks for the given url in every photos table; if $fuzzy is true, only the file name is matched
	 *
	 * @param string $url
	 * @param bool $fuzzy
	 * @return array
	 */
	private function searchPhoto(string $url, bool $fuzzy): array
	{
		$db = Db::getConnection();

		if ($fuzzy) {
			$filename = basename($url);
			if (!$filename)
				return [];

			$where = [
				'OR' => [
					['url', 'LIKE', '%/' . $filename],
					['thumb', 'LIKE', '%/' . $filename],
				],
			];
		} else {
			$where = [
				'OR' => [
					['url', $url],
					['thumb', $url],
				],
			];
		}

		$results = [];

		foreach (self::PHOTO_SOURCES as $source) {
			$photos = $db->selectAll($source['table'], $where, ['stream' => false]);
			foreach ($photos as $photo) {
				$result = $this->buildPhotoResult($source, $photo, $url);
				if ($result === null)
					continue;

				$result['exact'] = !$fuzzy;
				$results[] = $result;
			}
		}

		return $results;
	}

	/**
	 * Resolves a photo row to the service or the package it belongs to
	 *
	 * @param array $source
	 * @param array $photo
	 * @param string $url
	 * @return array|null
	 */
	private function buildPhotoResult(array $source, array $photo, string $url): ?array
	{
		$db = Db::getConnection();

		$parentId = $photo[$source['parent']] ?? null;
		if (!$parentId)
			return null;

		$via = null;

		switch ($source['type']) {
			case 'service':
			case 'package':
				$type = $source['type'];
				$elementId = $parentId;
				break;

			case 'subservice':
				$subservice = $this->model->one('TravioSubservice', $parentId);
				if (!$subservice)
					return null;

				$type = 'service';
				$elementId = $subservice['service'];
				$via = 'Sottoservizio ' . ($subservice['name'] ? '"' . $subservice['name'] . '"' : '#' . $parentId);
				break;

			case 'service-itinerary':
			case 'package-itinerary':
				$isService = $source['type'] === 'service-itinerary';

				$itinerary = $db->select($isService ? 'travio_services_itinerary' : 'travio_packages_itinerary', $parentId);
				if (!$itinerary)
					return null;

				$type = $isService ? 'service' : 'package';
				$elementId = $itinerary[$isService ? 'service' : 'package'];
				$via = 'Itinerario' . ($itinerary['day'] ? ', giorno ' . $itinerary['day'] : '');
				break;

			default:
				return null;
		}

		if (!$elementId)
			return null;

		$element = $this->model->one($type === 'service' ? 'TravioService' : 'TravioPackage', $elementId);
		if (!$element)
			return null;

		return [
			'type' => $type,
			'rule' => $type === 'service' ? 'travio-services' : 'travio-packages',
			'id' => $elementId,
			'name' => (string)($element['name'] ?? ''),
			'code' => (string)($element['code'] ?? ''),
			'travio_id' => $element['travio'] ?? null,
			'photo_id' => $photo['id'],
			'field' => $this->getMatchedField($photo, $url),
			'description' => (string)($photo['description'] ?? ''),
			'via' => $via,
		];
	}

	/**
	 * Tells which of the two columns of a photo row matched the searched url
	 *
	 * @param array $photo
	 * @param string $url
	 * @return string
	 */
	private function getMatchedField(array $photo, string $url): string
	{
		if (($photo['url'] ?? null) === $url)
			return 'url';
		if (($photo['thumb'] ?? null) === $url)
			return 'thumb';

		$filename = basename($url);
		if ($filename and isset($photo['url']) and str_ends_with($photo['url'], '/' . $filename))
			return 'url';

		return 'thumb';
	}
}
