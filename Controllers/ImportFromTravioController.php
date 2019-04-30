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
							'search-type' => $target['search'],
						];

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
				case 'services':
					foreach ($config['target-types'] as $target) {
						if ($target['search'] !== 'service')
							continue;

						$payload = [
							'type' => 'service',
							'show-names' => true,
						];

						if (isset($target['type']))
							$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						foreach ($list['list'] as $item) {
							$check = $this->model->select('travio_services', ['travio' => $item['id']]);
							if (!$check or ($item['last_update'] and ($check['last_update'] === null or date_create($check['last_update']) < date_create($item['last_update'])))) {
								$serviceData = $this->model->_Travio->request('static-data', [
									'type' => 'service',
									'code' => $item['code'],
								])['data'];

								$id = $this->model->updateOrInsert('travio_services', [
									'travio' => $serviceData['id'],
								], [
									'code' => $serviceData['code'],
									'name' => $serviceData['name'],
									'type' => $serviceData['type'],
									'typology' => $serviceData['typology'],
									'geo' => $serviceData['geo'][0]['id'] ?? null,
									'classification' => $serviceData['classification'] ? $serviceData['classification']['code'] : null,
									'classification_level' => $serviceData['classification'] ? $serviceData['classification']['level'] : null,
									'lat' => $serviceData['lat'],
									'lng' => $serviceData['lng'],
									'address' => $serviceData['address'],
									'price' => $serviceData['price'],
									'min_date' => $serviceData['min_date'],
									'max_date' => $serviceData['max_date'],
									'last_update' => $item['last_update'],
								]);

								/*if ($check) {
									$this->model->_Db->delete('servizi_tags', ['servizio' => $id]);
									$this->model->_Db->delete('servizi_descrizioni', ['servizio' => $id]);
								}

								foreach ($serviceData['tags'] as $tag) {
									$this->model->_Db->insert('servizi_tags', [
										'servizio' => $id,
										'tag' => $tag,
									], ['defer' => true]);
								}

								$this->model->_Db->bulkInsert('servizi_tags');

								foreach ($serviceData['descriptions'] as $description) {
									$this->model->_Db->insert('servizi_descrizioni', [
										'servizio' => $id,
										'titolo' => $description['title'],
										'testo' => $description['text'],
									], ['defer' => true]);
								}

								$this->model->_Db->bulkInsert('servizi_descrizioni');*/
							}
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
