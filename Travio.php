<?php namespace Model\Travio;

use Model\Core\Globals;
use Model\Core\Module;
use Model\TravioAssets\Elements\TravioService;

class Travio extends Module
{
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
