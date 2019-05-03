<?php namespace Model\Travio;

use Model\Core\Globals;
use Model\Core\Module;
use Model\TravioAssets\Elements\TravioService;

class Travio extends Module
{
	private $cartCache = null;

	public function init(array $options)
	{
		if (!isset(Globals::$data['adminAdditionalPages']))
			Globals::$data['adminAdditionalPages'] = [];

		Globals::$data['adminAdditionalPages'][] = [
			'name' => 'Travio',
			'sub' => [
				[
					'name' => 'Destinazioni',
					'page' => 'TravioGeo',
					'rule' => 'travio-geo',
					'visualizer' => 'Table',
					'mobile-visualizer' => 'Table',
				],
				[
					'name' => 'Servizi',
					'page' => 'TravioServices',
					'rule' => 'travio-services',
					'visualizer' => 'Table',
					'mobile-visualizer' => 'Table',
				],
				[
					'name' => 'Tags',
					'page' => 'TravioTags',
					'rule' => 'travio-tags',
					'visualizer' => 'Table',
					'mobile-visualizer' => 'Table',
				],
			],
		];

		$this->model->_Db->linkTable('travio_geo');
		$this->model->_Db->linkTable('travio_services');

		$this->model->addJS('model/Travio/files/admin.js', ['with' => 'AdminFront']);
	}

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

		if ($this->model->isLoaded('Multilang') and !isset($payload['lang']))
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

	private function getSessionId(): string
	{
		if (!isset($_SESSION['sessionId'])) {
			$response = $this->request('get-session-id');
			$_SESSION['sessionId'] = $response['SessionId'];
		}

		return $_SESSION['sessionId'];
	}

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
		if (array_key_exists('travio-login-cache', $_SESSION))
			unset($_SESSION['travio-login-cache']);
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
			if ($req and $req['user']) {
				$_SESSION['travio-login-cache'] = $req['user'];
			} else {
				$_SESSION['travio-login-cache'] = null;
			}
		}

		return $_SESSION['travio-login-cache'];
	}

	/**
	 * @return bool
	 */
	public function logout(): bool
	{
		if (array_key_exists('travio-login-cache', $_SESSION))
			unset($_SESSION['travio-login-cache']);
		return $this->request('logout')['status'];
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
		$this->cartCache = null;
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
	public function getCart(): array
	{
		return $this->getCartCache();
	}

	/**
	 * @return array
	 */
	private function getCartCache(): array
	{
		if ($this->cartCache === null) {
			if (isset($_SESSION['travio-cart-cache'])) {
				$this->cartCache = $_SESSION['travio-cart-cache'];
			} else {
				$this->cartCache = $this->request('view-cart');
				$_SESSION['travio-cart-cache'] = $this->cartCache;
			}
		}
		return $this->cartCache;
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
