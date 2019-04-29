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

						if (isset($target['search']))
							$payload['search-type'] = $target['search'];
						if (isset($target['type']))
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
				case 'tags':
					$list = $this->model->_Travio->request('static-data', [
						'type' => 'tags',
					]);

					foreach ($list['list'] as $id => $item) {
						$this->model->updateOrInsert('travio_tags', [
							'id' => $id,
						], [
							'name' => $item['name'],
							'type' => $item['type'],
							'type_name' => $item['type-name'],
						]);
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
