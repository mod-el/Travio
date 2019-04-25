<?php namespace Model\Travio\Controllers;

use Model\Core\Controller;

class ImportFromTravioController extends Controller
{
	public function index()
	{
		$config = $this->model->_Travio->retrieveConfig();

		try {
			switch ($this->model->getInput('type')) {
				case 'geo':
					foreach ($config['target-types'] as $target) {
						$payload = [
							'type' => 'geo',
						];

						$payload['search-type'] = $target['search'];
						$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						foreach ($list['list'] as $item) {
							$this->model->updateOrInsert('travio_geo', [
								'id' => $item['id'],
							], [
								'name' => $item['name'],
								'parent' => $item['parent'],
								'parent_name' => $item['parent-name'],
							]);
						}
					}
					break;
				default:
					$this->model->error('Unknown type');
					break;
			}
		} catch (\Exception $e) {
			echo getErr($e);
			die();
		}

		return 'ok';
	}
}
