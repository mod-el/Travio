<?php namespace Model\Travio\Controllers;

use Model\Core\Controller;

class GetDatesController extends Controller
{
	public function index()
	{
		if (empty($_GET['type']) or empty($_GET['id']))
			throw new \Exception('Missing type or id parameter');

		$side = $_GET['side'] ?? 'checkin';

		switch ($side) {
			case 'checkin':
				switch ($_GET['type']) {
					case 'geo':
						return $this->model->_Travio->getCheckinFromGeo((int)$_GET['id'], $_GET['search_type'] ?? 'services', isset($_GET['poi']) ? json_decode($_GET['poi'], true) : null);

					case 'service':
						return $this->model->_Travio->getCheckinFromService((int)$_GET['id'], $_GET['search_type'] ?? 'services', isset($_GET['poi']) ? json_decode($_GET['poi'], true) : null);

					case 'package':
						return $this->model->_Travio->getCheckinFromPackage((int)$_GET['id'], isset($_GET['poi']) ? json_decode($_GET['poi'], true) : null);

					case 'tag':
						return $this->model->_Travio->getCheckinFromTag((int)$_GET['id']);

					default:
						throw new \Exception('Invalid type parameter');
				}

			case 'checkout':
				if (empty($_GET['date']))
					throw new \Exception('Missing date parameter for checkout');

				$checkin = date_create($_GET['date']);
				if (!$checkin)
					throw new \Exception('Invalid date parameter for checkout');

				switch ($_GET['type']) {
					case 'geo':
						return $this->model->_Travio->getCheckoutFromGeo((int)$_GET['id'], $checkin, $_GET['search_type'] ?? 'services', isset($_GET['poi']) ? json_decode($_GET['poi'], true) : null);

					case 'service':
						return $this->model->_Travio->getCheckoutFromService((int)$_GET['id'], $checkin, $_GET['search_type'] ?? 'services', isset($_GET['poi']) ? json_decode($_GET['poi'], true) : null);

					case 'package':
						return $this->model->_Travio->getCheckoutFromPackage((int)$_GET['id'], $checkin, isset($_GET['poi']) ? json_decode($_GET['poi'], true) : null);

					case 'tag':
						return $this->model->_Travio->getCheckoutFromTag((int)$_GET['id'], $checkin);

					default:
						throw new \Exception('Invalid type parameter for checkout');
				}
		}
	}
} 
