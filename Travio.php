<?php namespace Model\Travio;

use Model\Assets\Assets;
use Model\Cache\Cache;
use Model\Core\Globals;
use Model\Core\Module;
use Model\Db\Db;
use Model\Multilang\Ml;
use Model\TravioAssets\Elements\TravioOrder;
use Model\TravioAssets\Elements\TravioService;

class Travio extends Module
{
	/** @var array */
	private array $cartCache = [];

	/**
	 * @param array $options
	 */
	public function init(array $options)
	{
		if (!$this->model->isLoaded('Multilang')) // La presenza di Multilang fra i moduli caricati è requisito fondamentale per il buon funzionamento di questo modulo
			$this->model->load('Multilang');

		if (!isset(Globals::$data['adminAdditionalPages']))
			Globals::$data['adminAdditionalPages'] = [];

		Globals::$data['adminAdditionalPages'][] = [
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
					'name' => 'Tipi  bagaglio',
					'page' => 'TravioLuggageTypes',
					'rule' => 'travio-luggage-types',
				],
				[
					'name' => 'Anagrafiche',
					'page' => 'TravioMasterData',
					'rule' => 'travio-master-data',
				],
			],
		];

		Assets::add('model/Travio/files/admin.js', ['withTags' => 'module-AdminFront']);
		Assets::add('model/Travio/files/admin.css', ['withTags' => 'module-AdminFront']);
	}

	/**
	 * @param string $request
	 * @param array $payload
	 * @param int|null $searchId
	 * @return array
	 */
	public function request(string $request, array $payload = [], ?int $searchId = null): array
	{
		$get = [];

		if ($request !== 'get-session-id')
			$get['SessionId'] = $this->getSessionId();
		if ($searchId !== null)
			$get['SearchId'] = $searchId;

		if (DEBUG_MODE) {
			$get['debug'] = '';
			if (isset($_COOKIE['XDEBUG_SESSION']))
				$get['XDEBUG_SESSION_START'] = $_COOKIE['XDEBUG_SESSION'];
		}

		if (!isset($payload['lang']))
			$payload['lang'] = Ml::getLang();

		$url = $this->makeUrl($request, $get);

		$c = curl_init($url);

		$body = json_encode($payload);
		curl_setopt($c, CURLOPT_HTTPHEADER, [
			'Content-Type: text/json',
			'Content-length: ' . strlen($body),
			'Connection: close',
		]);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, $body);
		$data = curl_exec($c);

		if (curl_errno($c))
			throw new \Exception('Errore cURL: ' . curl_error($c));

		curl_close($c);

		$decoded = json_decode($data, true);
		if ($decoded === null)
			throw new \Exception('Errore nella decodifica dei dati: ' . $data);

		if (isset($decoded['err']))
			throw new \Exception($decoded['err']);

		return $decoded;
	}

	public function retrieveConfig(): array
	{
		return \Model\Config\Config::get('travio');
	}

	/**
	 * @param string $request
	 * @param array $get
	 * @return string
	 */
	private function makeUrl(string $request, array $get = []): string
	{
		$config = $this->retrieveConfig();

		if (DEBUG_MODE and $config['dev'])
			$url = 'https://old.dev.travio.it';
		else
			$url = 'https://old.travio.it';

		$url .= '/api-' . $config['auth']['id'] . '/' . $request;

		$get['Key'] = $config['auth']['key'];
		$get = http_build_query($get);
		if ($get)
			$url .= '?' . $get;

		return $url;
	}

	/**
	 * @return string
	 */
	public function getSessionId(): string
	{
		if (!isset($_SESSION['sessionId'])) {
			$response = $this->request('get-session-id');
			$_SESSION['sessionId'] = $response['SessionId'];
		}

		return $_SESSION['sessionId'];
	}

	/**
	 * @param string $code
	 */
	public function setSessionId(string $code): void
	{
		$_SESSION['sessionId'] = $code;
	}

	/**
	 * @param array $result
	 * @return TravioService
	 * @deprecated
	 */
	public function getServiceFromResult(array $result): TravioService
	{
		$service = $this->model->one('TravioService', ['travio' => $result['id']]);
		if (!$service) {
			$service = $this->model->create('TravioService');
			// TODO: riempire servizio fittizio con i dati da $result
		}

		return $service;
	}

	public function getServiceFromId(string $id): ?TravioService
	{
		$db = Db::getConnection();

		$check = $db->select('travio_services', ['travio' => $id]);
		if (!$check or !$check['visible']) {
			try {
				$this->importService($id);
				$check = $db->select('travio_services', ['travio' => $id]);
			} catch (\Throwable $e) {
				return null;
			}
		}

		if (!$check or !$check['visible'])
			return null;

		return $this->model->one('TravioService', $check['id']);
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return array
	 */
	public function login(string $username, string $password): array
	{
		$this->clearLoginCache();
		$this->emptyCartCache();

		return $this->request('login', [
			'username' => $username,
			'password' => $password,
		]);
	}

	/**
	 * @return array|null
	 */
	public function logged(): ?array
	{
		if (isset($_SESSION) and !array_key_exists('travio-login-cache', $_SESSION)) {
			$req = $this->request('logged');
			if ($req and $req['user'])
				$_SESSION['travio-login-cache'] = $req['user'];
			else
				$_SESSION['travio-login-cache'] = null;
		}

		return $_SESSION['travio-login-cache'];
	}

	/**
	 * @return bool
	 */
	public function logout(): bool
	{
		$this->clearLoginCache();
		$this->emptyCartCache();

		return $this->request('logout')['status'];
	}

	/**
	 *
	 */
	public function clearLoginCache(): void
	{
		if (array_key_exists('travio-login-cache', $_SESSION))
			unset($_SESSION['travio-login-cache']);
	}

	/**
	 * @param array $data
	 * @param array $options
	 * @return array
	 */
	public function reg(array $data, array $options = []): array
	{
		$options = array_merge([
			'private' => true,
			'enabled' => false,
		], $options);

		$this->checkPassword($data);

		$options['data'] = $data;
		$response = $this->request('reg', $options);
		$this->clearLoginCache();

		return $response;
	}

	/**
	 * @param array $data
	 */
	public function editProfile(array $data): void
	{
		$this->checkPassword($data);
		$this->request('edit-profile', ['data' => $data]);
		$this->clearLoginCache();
	}

	/**
	 * @param array $data
	 */
	private function checkPassword(array &$data): void
	{
		if (!empty($data['password']) and isset($data['repassword'])) {
			if ($data['password'] !== $data['repassword'])
				throw new \Exception('Password don\'t match', 400);
			unset($data['repassword']);
		}
	}

	/**
	 * @param int $searchId
	 * @return array
	 */
	public function addToCart(int $searchId): array
	{
		$this->emptyCartCache();
		return $this->request('add-to-cart', [], $searchId);
	}

	/**
	 *
	 */
	public function emptyCartCache(): void
	{
		$this->cartCache = [];
		if (isset($_SESSION['travio-cart-cache']))
			unset($_SESSION['travio-cart-cache']);
	}

	/**
	 * @param string $idx
	 * @return array
	 */
	public function removeFromCart(string $idx): array
	{
		$this->emptyCartCache();
		return $this->request('remove-from-cart', [
			'element' => $idx,
		]);
	}

	/**
	 * @return array
	 */
	public function emptyCart(): array
	{
		$this->emptyCartCache();
		return $this->request('empty-cart');
	}

	/**
	 * @param string $index
	 * @param bool $add
	 * @return array
	 */
	public function optionalService(string $index, bool $add): array
	{
		$this->emptyCartCache();
		return $this->request('optional-service', [
			'index' => $index,
			'add' => $add,
		]);
	}

	/**
	 * @return array
	 */
	public function getCart(bool $availability = true, array $simulate_payment_conditions = []): array
	{
		$k = (int)$availability;
		if (!isset($this->cartCache[$k]) or $simulate_payment_conditions) {
			if (isset($_SESSION['travio-cart-cache'][$k]) and !$simulate_payment_conditions) {
				$this->cartCache[$k] = $_SESSION['travio-cart-cache'][$k];
			} else {
				$this->cartCache[$k] = $this->request('view-cart', ['availability' => $availability, 'simulate_payment_conditions' => $simulate_payment_conditions]);
				$_SESSION['travio-cart-cache'][$k] = $this->cartCache[$k];
			}
		}
		return $this->cartCache[$k];
	}

	/**
	 * @param array $pax
	 * @param bool $instantConfirmation
	 * @param array $options
	 * @return array
	 */
	public function book(array $pax, bool $instantConfirmation = false, array $options = []): array
	{
		$this->emptyCartCache();
		$ordine = $this->request('book', array_merge($options, [
			'pax' => $pax,
			'instant-confirmation' => $instantConfirmation,
		]));

		if ($ordine['status'] === 'check')
			$this->model->error('Attenzione: controllare eventuali variazioni di prezzo da parte dei fornitori.');
		if ($ordine['status'] !== 'ok')
			$this->model->error('Errore durante la comunicazione API col sistema.');

		return $ordine;
	}

	/**
	 * @param array $pax
	 * @param bool $instantConfirmation
	 * @param string|null $gateway
	 * @param array $options
	 * @return TravioOrder
	 */
	public function placeOrder(array $pax, bool $instantConfirmation = false, ?string $gateway = null, array $options = []): TravioOrder
	{
		$data = $this->book($pax, $instantConfirmation, $options);

		$order = $this->model->_ORM->create('TravioOrder');
		$order->save([
			'reservation' => $data['id'],
			'reference' => $data['reference'],
			'initial_status' => (int)$data['booking-status'],
			'amount' => (float)$data['amount'],
			'date' => date('Y-m-d H:i:s'),
			'gateway' => $gateway,
			'response' => json_encode($data),
		]);

		return $order;
	}

	/**
	 * @param string $reference
	 * @return array
	 */
	public function confirmOrder(string $reference, ?float $paid = null, ?string $payment_reference = null): array
	{
		$payload = [
			'reference' => $reference,
		];
		if ($paid !== null) {
			$payload['paid'] = $paid;

			if ($payment_reference !== null)
				$payload['payment_reference'] = $payment_reference;
		}

		return $this->request('confirm', $payload);
	}

	/**
	 * @param array $request
	 * @param string $rule
	 * @return array
	 */
	public function getController(array $request, string $rule): ?array
	{
		return [
			'controller' => match ($rule) {
				'import' => 'ImportFromTravio',
				'dates' => 'GetDates',
				default => throw new \Exception('Invalid rule: ' . $rule),
			},
		];
	}

	/**
	 * @param int $geoId
	 * @param string $search_type
	 * @param array|null $poi
	 * @return array
	 */
	public function getDatesFromGeo(int $geoId, string $search_type, ?array $poi = null): array
	{
		$cache = Cache::getCacheAdapter();

		$cacheKey = 'd' . $geoId . '-' . $search_type . '-' . date('Y-m-d');
		[$dates, $airports, $ports] = $cache->get('travio.dates.' . $cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($geoId, $search_type, $poi) {
			$item->expiresAfter(3600 * 24);
			$item->tag('travio.dates');

			$db = Db::getConnection();

			$el = $this->model->one('TravioGeo', $geoId);

			$dates = ['min' => date('Y-m-d')];
			if ($search_type === 'packages') {
				$where = [
					'date' => ['>=', date('Y-m-d')],
					'join_geo' => $el['id'],
				];

				if ($poi)
					$where['departure_' . ($poi['type'] === 'IATA' ? 'airport' : 'port')] = $poi['id'];

				$datesQ = $this->model->select_all('travio_packages_departures', $where, [
					'joins' => [
						'travio_packages_geo' => [
							'on' => ['package' => 'package'],
							'fields' => ['geo' => 'join_geo'],
						],
					],
					'group_by' => 'date',
				]);

				$dates['list'] = [];
				foreach ($datesQ as $row)
					$dates['list'][] = $row['date'];
			} else {
				$services = $this->model->all('TravioService', [
					'join_geo' => $el['id'],
					'max_date' => ['>=', date('Y-m-d')],
				], [
					'joins' => [
						'travio_services_geo' => [
							'on' => ['id' => 'service'],
							'fields' => ['geo' => 'join_geo'],
						],
					],
				]);

				$today = date_create();
				$totalMinDate = null;
				$totalMaxDate = null;
				$list = [];

				if (!$el['has_suppliers']) {
					foreach ($services as $service) {
						if ($service['min_date']) {
							$minDate = date_create($service['min_date']);
							if ($minDate < $today)
								$minDate = $today;

							if ($totalMinDate === null or $minDate < $totalMinDate)
								$totalMinDate = $minDate;

							if ($service['max_date']) {
								$maxDate = date_create($service['max_date']);
								if ($totalMaxDate === null or $maxDate > $totalMaxDate)
									$totalMaxDate = $maxDate;
							}
						}

						$checkin_dates = $service->getCheckinDates();
						if (count($checkin_dates) > 0)
							$list = array_merge($list, $checkin_dates);
					}

					if ($totalMinDate and $totalMaxDate) {
						$dates['min'] = $totalMinDate->format('Y-m-d');
						$dates['max'] = $totalMaxDate->format('Y-m-d');
					}

					if ($list)
						$dates['list'] = array_values(array_unique($list));
				}
			}

			$airports = $db->query('SELECT a.id, a.code, a.name FROM travio_packages_departures d INNER JOIN travio_packages_departures_routes r ON r.departure = d.id INNER JOIN travio_packages_geo g ON g.package = d.package INNER JOIN travio_packages p ON p.id = d.package AND p.visible = 1 INNER JOIN travio_airports a ON a.id = r.departure_airport WHERE g.geo = ' . $geoId . ' AND d.`date`>\'' . date('Y-m-d') . '\' GROUP BY r.departure_airport ORDER BY a.code')->fetchAll();
			$ports = $db->query('SELECT a.id, a.code, a.name FROM travio_packages_departures d INNER JOIN travio_packages_departures_routes r ON r.departure = d.id INNER JOIN travio_packages_geo g ON g.package = d.package INNER JOIN travio_packages p ON p.id = d.package AND p.visible = 1 INNER JOIN travio_ports a ON a.id = r.departure_port WHERE g.geo = ' . $geoId . ' AND d.`date`>\'' . date('Y-m-d') . '\' GROUP BY r.departure_port ORDER BY a.code')->fetchAll();

			return [$dates, $airports, $ports];
		});

		return [
			'dates' => $dates,
			'airports' => $airports,
			'ports' => $ports,
		];
	}

	/**
	 * @param int $serviceId
	 * @return array
	 */
	public function getDatesFromService(int $serviceId): array
	{
		$cache = Cache::getCacheAdapter();

		$cacheKey = 's' . $serviceId . '-' . date('Y-m-d');
		[$dates, $airports, $ports] = $cache->get('travio.dates.' . $cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($serviceId) {
			$item->expiresAfter(3600 * 24);
			$item->tag('travio.dates');

			$db = Db::getConnection();

			$el = $this->model->one('TravioService', $serviceId);

			$dates = ['min' => date('Y-m-d')];
			if (!$el['has_suppliers']) {
				if ($el['min_date']) {
					$today = date_create();
					$minDate = date_create($el['min_date']);
					if ($minDate < $today)
						$minDate = $today;

					$dates['min'] = $minDate->format('Y-m-d');

					if ($el['max_date']) {
						$maxDate = date_create($el['max_date']);
						if ($maxDate >= $minDate)
							$dates['max'] = $maxDate->format('Y-m-d');
					}
				}

				$checkin_dates = $el->getCheckinDates();
				if (count($checkin_dates) > 0)
					$dates['list'] = $checkin_dates;
			}

			$airports = $db->query('SELECT a.id, a.code, a.name FROM travio_packages_departures d INNER JOIN travio_packages_departures_routes r ON r.departure = d.id INNER JOIN travio_packages_services s ON s.package = d.package INNER JOIN travio_packages p ON p.id = d.package AND p.visible = 1 INNER JOIN travio_airports a ON a.id = r.departure_airport WHERE s.service = ' . $serviceId . ' AND d.`date`>\'' . date('Y-m-d') . '\' GROUP BY r.departure_airport ORDER BY a.code')->fetchAll();
			$ports = $db->query('SELECT a.id, a.code, a.name FROM travio_packages_departures d INNER JOIN travio_packages_departures_routes r ON r.departure = d.id INNER JOIN travio_packages_services s ON s.package = d.package INNER JOIN travio_packages p ON p.id = d.package AND p.visible = 1 INNER JOIN travio_ports a ON a.id = r.departure_port WHERE s.service = ' . $serviceId . ' AND d.`date`>\'' . date('Y-m-d') . '\' GROUP BY r.departure_port ORDER BY a.code')->fetchAll();

			return [$dates, $airports, $ports];
		});

		return [
			'dates' => $dates,
			'airports' => $airports,
			'ports' => $ports,
		];
	}

	/**
	 * @param int $packageId
	 * @return array
	 */
	public function getDatesFromPackage(int $packageId): array
	{
		$cache = Cache::getCacheAdapter();

		$cacheKey = 'p' . $packageId . '-' . date('Y-m-d');
		[$dates, $airports, $ports] = $cache->get('travio.dates.' . $cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($packageId) {
			$item->expiresAfter(3600 * 24);
			$item->tag('travio.dates');

			$db = Db::getConnection();

			$q = $db->query('SELECT d.`date`,r.`departure_airport`,r.`departure_port`,a.code AS airport_code, a.name AS airport_name,p.code AS port_code, p.name AS port_name FROM travio_packages_departures d INNER JOIN travio_packages_departures_routes r ON r.departure = d.id LEFT JOIN travio_airports a ON a.id = r.departure_airport LEFT JOIN travio_ports p ON p.id = r.departure_port WHERE d.package = ' . $packageId . ' AND d.`date`>\'' . date('Y-m-d') . '\'')->fetchAll();

			$dates = ['list' => []];
			foreach ($q as $departure) {
				$date = date_create($departure['date']);
				if (!in_array($date->format('Y-m-d'), $dates['list']))
					$dates['list'][] = $date->format('Y-m-d');

				if ($departure['departure_airport'] and !isset($airports[$departure['departure_airport']])) {
					$airports[$departure['departure_airport']] = [
						'id' => $departure['departure_airport'],
						'code' => $departure['airport_code'],
						'name' => $departure['airport_name'],
					];
				}

				if ($departure['departure_port'] and !isset($ports[$departure['departure_port']])) {
					$ports[$departure['departure_port']] = [
						'id' => $departure['departure_port'],
						'code' => $departure['port_code'],
						'name' => $departure['port_name'],
					];
				}
			}

			return [$dates, array_values($airports), array_values($ports)];
		});

		return [
			'dates' => $dates,
			'airports' => $airports,
			'ports' => $ports,
		];
	}

	/**
	 * @param int $tagId
	 * @return array
	 */
	public function getDatesFromTag(int $tagId): array
	{
		$cache = Cache::getCacheAdapter();

		$cacheKey = 't' . $tagId . '-' . date('Y-m-d');
		[$dates, $airports, $ports] = $cache->get('travio.dates.' . $cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($tagId) {
			$item->expiresAfter(3600 * 24);
			$item->tag('travio.dates');

			$db = Db::getConnection();

			$services = $db->selectAll('travio_services', ['tag' => $tagId], [
				'joins' => [
					'travio_services_tags' => [
						'on' => ['id' => 'service'],
						'fields' => ['tag'],
					],
				],
			]);

			$today = date_create();
			$totalMinDate = null;
			$totalMaxDate = null;

			$dates = [];
			foreach ($services as $service) {
				if ($service['min_date']) {
					$minDate = date_create($service['min_date']);
					if ($minDate < $today)
						$minDate = $today;

					if ($totalMinDate === null or $minDate < $totalMinDate)
						$totalMinDate = $minDate;

					if ($service['max_date']) {
						$maxDate = date_create($service['max_date']);
						if ($totalMaxDate === null or $maxDate > $totalMaxDate)
							$totalMaxDate = $maxDate;
					}
				}
			}

			if ($totalMinDate and $totalMaxDate) {
				$dates['min'] = $totalMinDate->format('Y-m-d');
				$dates['max'] = $totalMaxDate->format('Y-m-d');
			}

			return [$dates, [], []];
		});

		return [
			'dates' => $dates,
			'airports' => $airports,
			'ports' => $ports,
		];
	}

	/* Photo caching methods */

	public function checkPhotoCache(string $url): string
	{
		if (in_array(\Model\Config\Config::getEnv(), ['staging', 'production']) and str_starts_with($url, 'https://storage.travio.it/'))
			return $this->getPhotoFromCache($url);
		else
			return $url;
	}

	private function getPhotoFromCache(string $url): string
	{
		$path = $this->convertUrlToCachePath($url);

		if (!file_exists(INCLUDE_PATH . $path) or filesize(INCLUDE_PATH . $path) === 0) {
			$dir = pathinfo(INCLUDE_PATH . $path, PATHINFO_DIRNAME);
			if (!is_dir($dir))
				mkdir($dir, 0777, true);

			$url = explode('/', $url);
			$filename = rawurlencode(array_pop($url));
			$url[] = $filename;
			$url = implode('/', $url);

			file_put_contents($path, file_get_contents($url));
		}

		return PATH . $path;
	}

	public function invalidatePhotoCache(string $url): void
	{
		if (str_starts_with($url, 'https://storage.travio.it/')) {
			$path = $this->convertUrlToCachePath($url);
			if ($path and file_exists(INCLUDE_PATH . $path))
				unlink($path);
		}
	}

	private function convertUrlToCachePath(string $url): string
	{
		return 'app-data/travio/cache/' . substr($url, 26);
	}

	public function importService(string $travioId): int
	{
		$db = Db::getConnection();

		$config = $this->retrieveConfig();

		$serviceData = $this->request('static-data', [
			'type' => 'service',
			'id' => $travioId,
			'all-langs' => true,
			'get-availability' => $config['import']['services']['availability'] ?? false,
		])['data'];

		try {
			$db->beginTransaction();

			$data = [
				'code' => $serviceData['code'],
				'name' => $serviceData['name'],
				'type' => $serviceData['type'],
				'typology' => $serviceData['typology'],
				'geo' => $serviceData['geo'][0]['id'] ?? null,
				'classification_id' => $serviceData['classification'] ? $serviceData['classification']['id'] : null,
				'classification' => $serviceData['classification'] ? $serviceData['classification']['code'] : null,
				'classification_level' => $serviceData['classification'] ? $serviceData['classification']['level'] : null,
				'lat' => $serviceData['lat'],
				'lng' => $serviceData['lng'],
				'address' => $serviceData['address'],
				'zip' => $serviceData['zip'],
				'tel' => $serviceData['tel'],
				'email' => $serviceData['email'],
				'notes' => $serviceData['notes'],
				'departs_from' => $serviceData['departs_from'],
				'price' => $serviceData['price'],
				'min_date' => $serviceData['min_date'],
				'max_date' => $serviceData['max_date'],
				'visible' => 1,
				'has_suppliers' => $serviceData['travio_service'] ? 1 : 0,
				'last_update' => $serviceData['last_update'],
			];

			$check = $db->select('travio_services', ['travio' => $travioId]);
			if ($check) {
				foreach (($config['import']['services']['override'] ?? []) as $k => $override) {
					if (!$override)
						unset($data[$k]);
				}

				$id = $check['id'];
				$db->update('travio_services', $id, $data);

				$db->delete('travio_services_tags', ['service' => $id]);
				if ($config['import']['services']['override']['descriptions'] ?? true)
					$db->delete('travio_services_descriptions', ['service' => $id]);
				$db->delete('travio_services_geo', ['service' => $id]);
				$db->delete('travio_services_amenities', ['service' => $id]);
				$db->delete('travio_services_files', ['service' => $id]);
				$db->delete('travio_services_videos', ['service' => $id]);
				$db->delete('travio_services_availability', ['service' => $id]);
			} else {
				$data['travio'] = $serviceData['id'];
				$id = $db->insert('travio_services', $data);
			}

			if ($config['import']['subservices']['import']) {
				$db->delete('travio_subservices_tags', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);
				$db->delete('travio_subservices_descriptions', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);
				$db->delete('travio_subservices_amenities', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);
				$db->delete('travio_subservices_files', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);

				foreach ($serviceData['subservices'] as $subservice) {
					$ss_id = $db->updateOrInsert('travio_subservices', [
						'id' => $subservice['id'],
					], [
						'service' => $id,
						'code' => $subservice['code'],
						'type' => $subservice['type'],
						'name' => $subservice['name'],
					]);

					foreach ($subservice['tags'] as $tagId => $tag) {
						$db->insert('travio_subservices_tags', [
							'subservice' => $ss_id,
							'tag' => $tagId,
						], ['defer' => true]);
					}

					foreach ($subservice['descriptions'] as $description) {
						$db->insert('travio_subservices_descriptions', [
							'subservice' => $ss_id,
							'tag' => $description['keyword'],
							'title' => $description['title'],
							'text' => $description['text'],
						]);
					}

					$present_photos = [];
					foreach ($subservice['photos'] as $photoIdx => $photo) {
						$dataToUpdate = ['order' => $photoIdx + 1];
						if ($config['import']['services']['override']['images_descriptions'] ?? true)
							$dataToUpdate['description'] = $photo['description'];

						if ($photo['url'])
							$this->invalidatePhotoCache($photo['url']);
						if ($photo['thumb'])
							$this->invalidatePhotoCache($photo['thumb']);

						$present_photos[] = $db->updateOrInsert('travio_subservices_photos', [
							'subservice' => $ss_id,
							'url' => $photo['url'],
							'thumb' => $photo['thumb'] ?: $photo['url'],
							'tag' => $photo['tag'],
						], $dataToUpdate);
					}

					if ($present_photos) {
						$db->delete('travio_subservices_photos', [
							'subservice' => $ss_id,
							'id' => ['NOT IN', $present_photos],
						]);
					} else {
						$db->delete('travio_subservices_photos', ['subservice' => $ss_id]);
					}

					foreach ($subservice['amenities'] as $amenity_id => $amenity) {
						$db->insert('travio_subservices_amenities', [
							'subservice' => $ss_id,
							'amenity' => $amenity_id,
							'name' => $amenity['name'],
							'tag' => $amenity['tag'] ?: null,
						], ['defer' => true]);
					}

					foreach ($subservice['files'] as $file) {
						$db->insert('travio_subservices_files', [
							'subservice' => $ss_id,
							'name' => $file['name'],
							'url' => $file['url'],
						], ['defer' => true]);
					}
				}

				$db->bulkInsert('travio_subservices_tags');
				$db->bulkInsert('travio_subservices_amenities');
				$db->bulkInsert('travio_subservices_files');
			}

			foreach ($serviceData['tags'] as $tagId => $tag) {
				$db->insert('travio_services_tags', [
					'service' => $id,
					'tag' => $tagId,
				], ['defer' => true]);
			}

			$db->bulkInsert('travio_services_tags');

			if (($config['import']['services']['override']['descriptions'] ?? true) or !$item['existing']) {
				foreach ($serviceData['descriptions'] as $description) {
					$db->insert('travio_services_descriptions', [
						'service' => $id,
						'tag' => $description['keyword'],
						'title' => $description['title'],
						'text' => $description['text'],
					]);
				}
			}

			$present_photos = [];
			foreach ($serviceData['photos'] as $photoIdx => $photo) {
				$dataToUpdate = ['order' => $photoIdx + 1];
				if ($config['import']['services']['override']['images_descriptions'] ?? true)
					$dataToUpdate['description'] = $photo['description'];

				if ($photo['url'])
					$this->invalidatePhotoCache($photo['url']);
				if ($photo['thumb'])
					$this->invalidatePhotoCache($photo['thumb']);

				$present_photos[] = $db->updateOrInsert('travio_services_photos', [
					'service' => $id,
					'url' => $photo['url'],
					'thumb' => $photo['thumb'] ?: $photo['url'],
					'tag' => $photo['tag'] ?? null,
				], $dataToUpdate);
			}

			if ($present_photos) {
				$db->delete('travio_services_photos', [
					'service' => $id,
					'id' => ['NOT IN', $present_photos],
				]);
			} else {
				$db->delete('travio_services_photos', ['service' => $id]);
			}

			foreach ($serviceData['geo'] as $geo) {
				if (!$geo['id'])
					continue;
				$db->insert('travio_services_geo', [
					'service' => $id,
					'geo' => $geo['id'],
				], ['defer' => true]);
			}

			$db->bulkInsert('travio_services_geo');

			foreach ($serviceData['amenities'] as $amenity_id => $amenity) {
				$db->insert('travio_services_amenities', [
					'service' => $id,
					'amenity' => $amenity_id,
					'name' => $amenity['name'],
					'tag' => $amenity['tag'] ?: null,
				], ['defer' => true]);
			}

			$db->bulkInsert('travio_services_amenities');

			foreach ($serviceData['files'] as $file) {
				$db->insert('travio_services_files', [
					'service' => $id,
					'name' => $file['name'],
					'url' => $file['url'],
				], ['defer' => true]);
			}

			$db->bulkInsert('travio_services_files');

			foreach ($serviceData['videos'] as $video) {
				$db->insert('travio_services_videos', [
					'service' => $id,
					'video' => $video,
				], ['defer' => true]);
			}

			$db->bulkInsert('travio_services_videos');

			foreach ($serviceData['availability'] as $availability) {
				$db->insert('travio_services_availability', [
					'service' => $id,
					'from' => $availability['from'],
					'to' => $availability['to'],
					'type' => $availability['type'],
					'in_monday' => (int)$availability['in_monday'],
					'in_tuesday' => (int)$availability['in_tuesday'],
					'in_wednesday' => (int)$availability['in_wednesday'],
					'in_thursday' => (int)$availability['in_thursday'],
					'in_friday' => (int)$availability['in_friday'],
					'in_saturday' => (int)$availability['in_saturday'],
					'in_sunday' => (int)$availability['in_sunday'],
					'out_monday' => (int)$availability['out_monday'],
					'out_tuesday' => (int)$availability['out_tuesday'],
					'out_wednesday' => (int)$availability['out_wednesday'],
					'out_thursday' => (int)$availability['out_thursday'],
					'out_friday' => (int)$availability['out_friday'],
					'out_saturday' => (int)$availability['out_saturday'],
					'out_sunday' => (int)$availability['out_sunday'],
					'min_stay' => $availability['min_stay'],
					'only_multiples_of' => $availability['only_multiples_of'],
					'fixed_duration' => $availability['fixed_duration'],
				], ['defer' => true]);
			}

			$db->bulkInsert('travio_services_availability');

			$db->commit();

			return $id;
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
}
