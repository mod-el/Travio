<?php namespace Model\Travio;

use Model\Core\Globals;
use Model\Core\Module;
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

		$this->model->addJS('model/Travio/files/admin.js', ['with' => 'AdminFront']);
		$this->model->addCSS('model/Travio/files/admin.css', ['with' => 'AdminFront']);
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
			$payload['lang'] = $this->model->_Multilang->lang;

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

	/**
	 * @param string $request
	 * @param array $get
	 * @return string
	 */
	private function makeUrl(string $request, array $get = []): string
	{
		$config = $this->retrieveConfig();

		if (DEBUG_MODE and $config['dev'])
			$url = 'https://dev.travio.it';
		else
			$url = 'https://bo.travio.it';

		$url .= '/api-' . $config['license'] . '/' . $request;

		$get['Key'] = $config['key'];
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
	public function setSessionId(string $code)
	{
		$_SESSION['sessionId'] = $code;
	}

	/**
	 * @param array $result
	 * @return TravioService
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

	/**
	 * @param string $username
	 * @param string $password
	 * @return array
	 */
	public function login(string $username, string $password)
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
	public function clearLoginCache()
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
	public function editProfile(array $data)
	{
		$this->checkPassword($data);
		$this->request('edit-profile', ['data' => $data]);
		$this->clearLoginCache();
	}

	/**
	 * @param array $data
	 */
	private function checkPassword(array &$data)
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
	public function emptyCartCache()
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
	public function getCart(bool $availability = true): array
	{
		$k = (int)$availability;
		if (!isset($this->cartCache[$k])) {
			if (isset($_SESSION['travio-cart-cache'][$k])) {
				$this->cartCache[$k] = $_SESSION['travio-cart-cache'][$k];
			} else {
				$this->cartCache[$k] = $this->request('view-cart', ['availability' => $availability]);
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
	public function confirmOrder(string $reference, ?float $paid = null): array
	{
		$payload = [
			'reference' => $reference,
		];
		if ($paid !== null)
			$payload['paid'] = $paid;

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
			'controller' => 'ImportFromTravio',
		];
	}
}
