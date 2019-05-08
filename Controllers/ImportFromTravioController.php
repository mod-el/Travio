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
					$presents = [];

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
							if (!$item['code'])
								continue;

							$presents[] = $item['id'];

							$check = $this->model->select('travio_services', ['travio' => $item['id']]);
							if (!$check or ($item['last_update'] and ($check['last_update'] === null or date_create($check['last_update']) < date_create($item['last_update'])))) {
								$serviceData = $this->model->_Travio->request('static-data', [
									'type' => 'service',
									'code' => $item['code'],
									'all-langs' => true,
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
									'visibile' => 1,
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

								foreach ($serviceData['amenities'] as $amenity_id => $amenity) {
									$this->model->_Db->insert('travio_services_amenities', [
										'service' => $id,
										'amenity' => $amenity_id,
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

					$this->model->_Db->update('travio_services', [
						'travio' => ['NOT IN', $presents],
					], ['visible' => 0]);
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
				case 'amenities':
					$list = $this->model->_Travio->request('static-data', [
						'type' => 'amenities',
					]);

					foreach ($list['list'] as $id => $item) {
						if ($item['tag']) {
							$type = $this->model->select('travio_amenities_types', ['name' => trim($item['tag'])], 'id');
							if (!$type)
								$type = $this->model->insert('travio_amenities_types', ['name' => trim($item['tag'])]);
						} else {
							$type = null;
						}

						$this->model->updateOrInsert('travio_amenities', [
							'id' => $id,
						], [
							'name' => $item['name'],
							'type' => $type,
						]);
					}
					break;
				case 'ports':
					$presents = [];

					foreach ($config['target-types'] as $target) {
						$payload = [
							'type' => 'ports',
							'search-type' => $target['search'],
						];

						if ($target['type'] ?? null)
							$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						foreach ($list['list'] as $item) {
							$check = $this->model->select('travio_ports', [
								'id' => $item['id'],
							]);

							$presents[] = $item['id'];

							if ($check) {
								$this->model->update('travio_ports', [
									'id' => $item['id'],
								], [
									'code' => $item['code'],
								]);
							} else {
								$this->model->insert('travio_ports', [
									'id' => $item['id'],
									'code' => $item['code'],
									'name' => $item['name'],
								]);
							}
						}
					}

					foreach ($this->model->select_all('travio_ports', $presents ? ['id' => ['NOT IN', $presents]] : []) as $port) {
						try {
							$this->model->delete('travio_ports', $port['id']);
						} catch (\Exception $e) {
						}
					}
					break;
				case 'airports':
					$presents = [];

					foreach ($config['target-types'] as $target) {
						$payload = [
							'type' => 'airports',
							'search-type' => $target['search'],
						];

						if ($target['type'] ?? null)
							$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						foreach ($list['list'] as $item) {
							$check = $this->model->select('travio_airports', [
								'id' => $item['id'],
							]);

							$presents[] = $item['id'];

							if ($check) {
								$this->model->update('travio_airports', [
									'id' => $item['id'],
								], [
									'code' => $item['code'],
								]);
							} else {
								$this->model->insert('travio_airports', [
									'id' => $item['id'],
									'code' => $item['code'],
									'name' => $item['name'],
								]);
							}
						}
					}

					foreach ($this->model->select_all('travio_airports', $presents ? ['id' => ['NOT IN', $presents]] : []) as $airport) {
						try {
							$this->model->delete('travio_airports', $airport['id']);
						} catch (\Exception $e) {
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
