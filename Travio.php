<?php namespace Model\Travio;

use Model\Assets\Assets;
use Model\Cache\Cache;
use Model\Core\Globals;
use Model\Core\Module;
use Model\Db\Db;
use Model\Multilang\Ml;
use Model\TravioAssets\Elements\TravioOrder;
use Model\TravioAssets\Elements\TravioService;
use Model\Travio\TravioClient;

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

		if ($poi) {
			if (!isset($poi['type']) or !in_array($poi['type'], ['airport', 'port']))
				throw new \Exception('Invalid poi type');
			if (!isset($poi['id']) or !is_numeric($poi['id']))
				throw new \Exception('Invalid poi id');
		}

		$cacheKey = 'd' . $geoId . '-' . $search_type . '-' . ($poi ? $poi['type'] . '-' . $poi['id'] . '-' : '') . date('Y-m-d');
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

				$joins = [
					'travio_packages_geo' => [
						'on' => ['package' => 'package'],
						'fields' => ['geo' => 'join_geo'],
					],
				];

				if ($poi) {
					$where['departure_' . $poi['type']] = $poi['id'];
					$joins['travio_packages_departures_routes'] = [
						'on' => ['id' => 'departure'],
						'fields' => ['departure_' . $poi['type']],
					];
				}

				$datesQ = $this->model->select_all('travio_packages_departures', $where, [
					'joins' => $joins,
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
	 * @param string $search_type
	 * @param array|null $poi
	 * @return array
	 */
	public function getDatesFromService(int $serviceId, string $search_type, ?array $poi = null): array
	{
		$cache = Cache::getCacheAdapter();

		if ($poi) {
			if (!isset($poi['type']) or !in_array($poi['type'], ['airport', 'port']))
				throw new \Exception('Invalid poi type');
			if (!isset($poi['id']) or !is_numeric($poi['id']))
				throw new \Exception('Invalid poi id');
		}

		$cacheKey = 's' . $serviceId . '-' . $search_type . '-' . ($poi ? $poi['type'] . '-' . $poi['id'] . '-' : '') . '-' . date('Y-m-d');
		[$dates, $airports, $ports] = $cache->get('travio.dates.' . $cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($serviceId, $search_type, $poi) {
			$item->expiresAfter(3600 * 24);
			$item->tag('travio.dates');

			$db = Db::getConnection();

			$el = $this->model->one('TravioService', ['travio' => $serviceId]);

			$airports = [];
			$ports = [];

			$departures = $db->query('SELECT d.`id`, d.`date` FROM `travio_packages_departures` d INNER JOIN `travio_packages_services` s ON s.`package` = d.`package` INNER JOIN `travio_packages` p ON p.`id` = d.`package` AND p.`visible` = 1 WHERE s.`service` = ' . $el['id'] . ' AND d.`date`>\'' . date('Y-m-d') . '\' ORDER BY d.`date`')->fetchAll();
			$departures_ids = array_map(fn($departure) => $departure['id'], $departures);
			$routes = $departures_ids ? $db->query('SELECT r.`departure`, r.`departure_airport`, r.`departure_port`, a.`code` AS `airport_code`, a.`name` AS `airport_name`, p.`code` AS `port_code`, p.`name` AS `port_name` FROM `travio_packages_departures_routes` r LEFT JOIN `travio_airports` a ON a.`id` = r.`departure_airport` LEFT JOIN `travio_ports` p ON p.`id` = r.`departure_port` WHERE r.`departure` IN (' . implode(',', $departures_ids) . ')')->fetchAll() : [];

			if ($search_type === 'packages')
				$dates = ['list' => []];

			foreach ($departures as $d) {
				$found_poi = $poi === null;
				foreach ($routes as $r) {
					if ($r['departure'] === $d['id']) {
						if ($poi) {
							if ($poi['type'] === 'airport' and $r['departure_airport'] === $poi['id'])
								$found_poi = true;
							elseif ($poi['type'] === 'port' and $r['departure_port'] === $poi['id'])
								$found_poi = true;
							else
								continue;
						}

						if ($r['departure_airport'] and !isset($airports[$r['departure_airport']])) {
							$airports[$r['departure_airport']] = [
								'id' => $r['departure_airport'],
								'code' => $r['airport_code'],
								'name' => $r['airport_name'],
							];
						}

						if ($r['departure_port'] and !isset($ports[$r['departure_port']])) {
							$ports[$r['departure_port']] = [
								'id' => $r['departure_port'],
								'code' => $r['port_code'],
								'name' => $r['port_name'],
							];
						}
					}
				}

				if ($search_type === 'packages' and !in_array($d['date'], $dates['list']) and $found_poi)
					$dates['list'][] = $d['date'];
			}

			$airports = array_values($airports);
			$ports = array_values($ports);

			if ($search_type !== 'packages') {
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
			}

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
	 * @param array|null $poi
	 * @return array
	 */
	public function getDatesFromPackage(int $packageId, ?array $poi = null): array
	{
		$cache = Cache::getCacheAdapter();

		if ($poi) {
			if (!isset($poi['type']) or !in_array($poi['type'], ['airport', 'port']))
				throw new \Exception('Invalid poi type');
			if (!isset($poi['id']) or !is_numeric($poi['id']))
				throw new \Exception('Invalid poi id');
		}

		$cacheKey = 'p' . $packageId . '-' . ($poi ? $poi['type'] . '-' . $poi['id'] . '-' : '') . '-' . date('Y-m-d');
		[$dates, $airports, $ports] = $cache->get('travio.dates.' . $cacheKey, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($packageId, $poi) {
			$item->expiresAfter(3600 * 24);
			$item->tag('travio.dates');

			$db = Db::getConnection();

			$el = $this->model->one('TravioPackage', ['travio' => $packageId]);

			$poi_where = '';
			if ($poi)
				$poi_where = ' AND r.`departure_' . $poi['type'] . '` = ' . $poi['id'];

			$q = $db->query('SELECT d.`date`, r.`departure_airport`, r.`departure_port`, a.code AS airport_code, a.name AS airport_name,p.code AS port_code, p.name AS port_name FROM travio_packages_departures d LEFT JOIN travio_packages_departures_routes r ON r.departure = d.id LEFT JOIN travio_airports a ON a.id = r.departure_airport LEFT JOIN travio_ports p ON p.id = r.departure_port WHERE d.package = ' . $el['id'] . ' AND d.`date`>\'' . date('Y-m-d') . '\' ' . $poi_where)->fetchAll();

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

		if (is_numeric($travioId)) {
			$newServiceData = TravioClient::restGet('services', $travioId, [
				'unfold' => ['classification_id', 'master_data', 'amenities'],
			]);
			$is_external = false;
		} else {
			if (!str_starts_with($travioId, 'TR'))
				throw new \Exception('Invalid travio service id: ' . $travioId);
			$newServiceData = TravioClient::restGet('suppliers-hotels', (int)substr($travioId, 2));
			$is_external = true;
		}

		try {
			$db->beginTransaction();

			$data = [
				'code' => $newServiceData['code'],
				'name' => $newServiceData['name'],
				'type' => $newServiceData['type'],
				'typology' => $newServiceData['typology'] ?? null,
				'geo' => $newServiceData['geo'] ? $newServiceData['geo'][0][count($newServiceData['geo'][0]) - 1]['id'] : null,
				'classification_id' => $newServiceData['classification_id'] ? $newServiceData['classification_id']['id'] : null,
				'classification' => $newServiceData['classification_id'] ? $newServiceData['classification_id']['code'] : null,
				'classification_level' => $newServiceData['classification'] ?? null,
				'lat' => $newServiceData['location'] ? $newServiceData['location']['lat'] : null,
				'lng' => $newServiceData['location'] ? $newServiceData['location']['lng'] : null,
				'address' => (!empty($newServiceData['master_data']) and $newServiceData['master_data']['addresses']) ? $newServiceData['master_data']['addresses'][0]['address'] : null,
				'zip' => (!empty($newServiceData['master_data']) and $newServiceData['master_data']['addresses']) ? $newServiceData['master_data']['addresses'][0]['postal_code'] : null,
				'tel' => (!empty($newServiceData['master_data']) and $newServiceData['master_data']['contacts'] and $newServiceData['master_data']['contacts'][0]['phone']) ? $newServiceData['master_data']['contacts'][0]['phone'][0] : null,
				'email' => (!empty($newServiceData['master_data']) and $newServiceData['master_data']['contacts'] and $newServiceData['master_data']['contacts'][0]['email']) ? $newServiceData['master_data']['contacts'][0]['email'][0] : null,
				'notes' => $newServiceData['_notes'] ? implode('<br/>', array_filter($newServiceData['_notes'], fn($n) => $n['type'] === 'web')) : '',
				'departs_from' => null,
				'price' => $newServiceData['estimated_price_per_pax'] ?? null,
				'min_date' => !empty($newServiceData['availability']) ? $newServiceData['availability'][0]['from'] : null,
				'max_date' => !empty($newServiceData['availability']) ? $newServiceData['availability'][count($newServiceData['availability']) - 1]['to'] : null,
				'visible' => 1,
				'has_suppliers' => $is_external ? 1 : ($newServiceData['supplier_hotel'] ? 1 : 0),
				'last_update' => $newServiceData['_meta']['last_update'],
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
				$db->delete('travio_services_stop_sales', ['service' => $id]);
			} else {
				$data['travio'] = $newServiceData['id'];
				$id = $db->insert('travio_services', $data);
			}

			if ($config['import']['subservices']['import']) {
				$db->delete('travio_subservices_tags', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);
				$db->delete('travio_subservices_descriptions', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);
				$db->delete('travio_subservices_amenities', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);
				$db->delete('travio_subservices_files', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);

				foreach ($newServiceData['subservices'] as $subservice) {
					$subservice = TravioClient::restGet('subservices', $subservice['id'], [
						'unfold' => ['amenities'],
					]);

					$ss_id = $db->updateOrInsert('travio_subservices', [
						'id' => $subservice['id'],
					], [
						'service' => $id,
						'code' => $subservice['code'],
						'type' => $subservice['type'],
						'name' => $subservice['name'],
					]);

					foreach ($subservice['_tags'] as $tag) {
						$db->insert('travio_subservices_tags', [
							'subservice' => $ss_id,
							'tag' => $tag,
						], ['defer' => true]);
					}

					$descriptions = [];
					foreach ($subservice['descriptions'] as $lang_descriptions) {
						foreach ($lang_descriptions['paragraphs'] as $paragraph_idx => $paragraph) {
							if (!isset($descriptions[$paragraph_idx])) {
								$descriptions[$paragraph_idx] = [
									'keyword' => $paragraph['tag'],
									'title' => [],
									'text' => [],
								];
							}

							$descriptions[$paragraph_idx]['title'][$lang_descriptions['lang']] = $paragraph['title'];
							$descriptions[$paragraph_idx]['text'][$lang_descriptions['lang']] = $paragraph['text'];
						}
					}

					foreach ($descriptions as $description) {
						$db->insert('travio_subservices_descriptions', [
							'subservice' => $ss_id,
							'tag' => $description['keyword'],
							'title' => $description['title'],
							'text' => $description['text'],
						]);
					}

					$present_photos = [];
					foreach ($subservice['images'] as $imageIdx => $image) {
						$dataToUpdate = ['order' => $imageIdx + 1];
						if ($config['import']['services']['override']['images_descriptions'] ?? true)
							$dataToUpdate['description'] = $image['description'];

						if ($image['url'])
							$this->invalidatePhotoCache($image['url']);
						if ($image['thumb'])
							$this->invalidatePhotoCache($image['thumb']);

						$present_photos[] = $db->updateOrInsert('travio_subservices_photos', [
							'subservice' => $ss_id,
							'url' => $image['url'],
							'thumb' => $image['thumb'] ?: $image['url'],
							'tag' => $image['tag'],
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

					foreach ($subservice['amenities'] as $amenity) {
						$db->insert('travio_subservices_amenities', [
							'subservice' => $ss_id,
							'amenity' => $amenity['id'],
							'name' => $amenity['name']['it'],
							'tag' => $amenity['type'] ?: null,
						], ['defer' => true]);
					}

					foreach (($subservice['_attachments'] ?? []) as $file) {
						$db->insert('travio_subservices_files', [
							'subservice' => $ss_id,
							'name' => $file['name'],
							'url' => 'https://storage.travio.it/' . $file['url'],
						], ['defer' => true]);
					}
				}

				$db->bulkInsert('travio_subservices_tags');
				$db->bulkInsert('travio_subservices_amenities');
				$db->bulkInsert('travio_subservices_files');
			}

			foreach ($newServiceData['_tags'] as $tag) {
				$db->insert('travio_services_tags', [
					'service' => $id,
					'tag' => $tag,
				], ['defer' => true]);
			}

			$db->bulkInsert('travio_services_tags');

			if (($config['import']['services']['override']['descriptions'] ?? true) or !$item['existing']) {
				$descriptions = [];
				foreach ($newServiceData['descriptions'] as $lang_descriptions) {
					foreach ($lang_descriptions['paragraphs'] as $paragraph_idx => $paragraph) {
						if (!isset($descriptions[$paragraph_idx])) {
							$descriptions[$paragraph_idx] = [
								'keyword' => $paragraph['tag'],
								'title' => [],
								'text' => [],
							];
						}

						$descriptions[$paragraph_idx]['title'][$lang_descriptions['lang']] = $paragraph['title'];
						$descriptions[$paragraph_idx]['text'][$lang_descriptions['lang']] = $paragraph['text'];
					}
				}

				foreach ($descriptions as $description) {
					$db->insert('travio_services_descriptions', [
						'service' => $id,
						'tag' => $description['keyword'],
						'title' => $description['title'],
						'text' => $description['text'],
					]);
				}
			}

			$present_photos = [];
			foreach ($newServiceData['images'] as $imageIdx => $image) {
				$dataToUpdate = ['order' => $imageIdx + 1];
				if ($config['import']['services']['override']['images_descriptions'] ?? true)
					$dataToUpdate['description'] = $image['description'];

				if ($image['url'])
					$this->invalidatePhotoCache($image['url']);
				if ($image['thumb'])
					$this->invalidatePhotoCache($image['thumb']);

				$present_photos[] = $db->updateOrInsert('travio_services_photos', [
					'service' => $id,
					'url' => $image['url'],
					'thumb' => $image['thumb'] ?: $image['url'],
					'tag' => $image['tag'] ?? null,
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

			foreach ($newServiceData['geo'] as $geoChain) {
				foreach ($geoChain as $geo) {
					if (!$geo['id'])
						continue;
					$db->insert('travio_services_geo', [
						'service' => $id,
						'geo' => $geo['id'],
					], ['defer' => true]);
				}
			}

			$db->bulkInsert('travio_services_geo');

			foreach ($newServiceData['amenities'] as $amenity) {
				$db->insert('travio_services_amenities', [
					'service' => $id,
					'amenity' => $amenity['id'],
					'name' => $amenity['name']['it'],
					'tag' => $amenity['type'] ?: null,
				], ['defer' => true]);
			}

			$db->bulkInsert('travio_services_amenities');

			if (!$is_external) {
				foreach ($newServiceData['_attachments'] as $file) {
					$db->insert('travio_services_files', [
						'service' => $id,
						'name' => $file['name'],
						'url' => 'https://storage.travio.it/' . $file['url'],
					], ['defer' => true]);
				}

				$db->bulkInsert('travio_services_files');

				foreach ($newServiceData['video'] as $video) {
					$db->insert('travio_services_videos', [
						'service' => $id,
						'video' => $video['youtube'],
					], ['defer' => true]);
				}

				$db->bulkInsert('travio_services_videos');

				foreach ($newServiceData['availability'] as $availability) {
					$db->insert('travio_services_availability', [
						'service' => $id,
						'from' => $availability['from'],
						'to' => $availability['to'],
						'type' => $availability['type'],
						'in_monday' => (int)in_array('monday', $availability['weekdays_in']),
						'in_tuesday' => (int)in_array('tuesday', $availability['weekdays_in']),
						'in_wednesday' => (int)in_array('wednesday', $availability['weekdays_in']),
						'in_thursday' => (int)in_array('thursday', $availability['weekdays_in']),
						'in_friday' => (int)in_array('friday', $availability['weekdays_in']),
						'in_saturday' => (int)in_array('saturday', $availability['weekdays_in']),
						'in_sunday' => (int)in_array('sunday', $availability['weekdays_in']),
						'out_monday' => (int)in_array('monday', $availability['weekdays_out']),
						'out_tuesday' => (int)in_array('tuesday', $availability['weekdays_out']),
						'out_wednesday' => (int)in_array('wednesday', $availability['weekdays_out']),
						'out_thursday' => (int)in_array('thursday', $availability['weekdays_out']),
						'out_friday' => (int)in_array('friday', $availability['weekdays_out']),
						'out_saturday' => (int)in_array('saturday', $availability['weekdays_out']),
						'out_sunday' => (int)in_array('sunday', $availability['weekdays_out']),
						'min_stay' => $availability['min_stay'],
						'only_multiples_of' => $availability['only_multiples_of'],
						'fixed_duration' => $availability['fixed_duration'],
					], ['defer' => true]);
				}

				$db->bulkInsert('travio_services_availability');

				foreach ($newServiceData['stop_sales'] as $stop_sale) {
					$db->insert('travio_services_stop_sales', [
						'service' => $id,
						'created' => $stop_sale['created'],
						'type' => $stop_sale['type'],
						'from' => $stop_sale['from'],
						'to' => $stop_sale['to'],
						'notes' => $stop_sale['notes'],
					], ['defer' => true]);
				}

				$db->bulkInsert('travio_services_stop_sales');
			}

			$db->commit();

			return $id;
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e;
		}
	}
}
