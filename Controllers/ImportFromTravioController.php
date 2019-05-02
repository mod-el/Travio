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

								if ($check) {
									$this->model->_Db->delete('travio_services_tags', ['service' => $id]);
									$this->model->_Db->delete('travio_services_descriptions', ['service' => $id]);
									$this->model->_Db->delete('travio_services_photos', ['service' => $id]);
									$this->model->_Db->delete('travio_services_geo', ['service' => $id]);
									$this->model->_Db->delete('travio_services_amenities', ['service' => $id]);
									$this->model->_Db->delete('travio_services_files', ['service' => $id]);
									$this->model->_Db->delete('travio_services_videos', ['service' => $id]);
								}

								foreach ($serviceData['tags'] as $tag) {
									$this->model->_Db->insert('travio_services_tags', [
										'service' => $id,
										'tag' => $tag,
									], ['defer' => true]);
								}

								$this->model->_Db->bulkInsert('travio_services_tags');

								/***********************/

								foreach ($serviceData['descriptions'] as $description) {
									$this->model->_Db->insert('travio_services_descriptions', [
										'service' => $id,
										'tag' => $description['keyword'],
										'title' => $description['title'],
										'text' => $description['text'],
									]);
								}

								/***********************/

								foreach ($serviceData['photos'] as $photo) {
									$this->model->_Db->insert('travio_services_photos', [
										'service' => $id,
										'url' => $photo['url'],
										'thumb' => $photo['thumb'],
										'description' => $photo['description'],
									], ['defer' => true]);
								}

								$this->model->_Db->bulkInsert('travio_services_photos');

								/***********************/

								foreach ($serviceData['geo'] as $geo) {
									$this->model->_Db->insert('travio_services_geo', [
										'service' => $id,
										'geo' => $geo['id'],
									], ['defer' => true]);
								}

								$this->model->_Db->bulkInsert('travio_services_geo');

								/***********************/

								foreach ($serviceData['amenities'] as $amenity) {
									$this->model->_Db->insert('travio_services_amenities', [
										'service' => $id,
										'name' => $amenity['name'],
										'tag' => $amenity['tag'] ?: null,
									], ['defer' => true]);
								}

								$this->model->_Db->bulkInsert('travio_services_amenities');

								/***********************/

								foreach ($serviceData['files'] as $file) {
									$this->model->_Db->insert('travio_services_files', [
										'service' => $id,
										'name' => $file['name'],
										'url' => $file['url'],
									], ['defer' => true]);
								}

								$this->model->_Db->bulkInsert('travio_services_files');

								/***********************/

								foreach ($serviceData['videos'] as $video) {
									$this->model->_Db->insert('travio_services_videos', [
										'service' => $id,
										'video' => $video,
									], ['defer' => true]);
								}

								$this->model->_Db->bulkInsert('travio_services_videos');
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
