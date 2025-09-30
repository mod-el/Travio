<?php namespace Model\Travio\Controllers;

use Model\Core\Controller;

class GetDatesController extends Controller
{
	public function index()
	{
		if (empty($_GET['type']) or empty($_GET['id']))
			throw new \Exception('Missing type or id parameter');

		switch ($_GET['type']) {
			case 'geo':
				return $this->model->_Travio->getDatesFromGeo((int)$_GET['id'], $_GET['search_type'] ?? 'services', isset($_GET['poi']) ? json_decode($_GET['poi'], true) : null);

			case 'service':
				return $this->model->_Travio->getDatesFromService((int)$_GET['id'], $_GET['search_type'] ?? 'services', isset($_GET['poi']) ? json_decode($_GET['poi'], true) : null);

			case 'package':
				return $this->model->_Travio->getDatesFromPackage((int)$_GET['id'], isset($_GET['poi']) ? json_decode($_GET['poi'], true) : null);

			case 'tag':
				return $this->model->_Travio->getDatesFromTag((int)$_GET['id']);

			default:
				throw new \Exception('Invalid type parameter');
		}
	}
} 
