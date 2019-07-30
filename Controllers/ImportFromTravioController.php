<?php namespace Model\Travio\Controllers;

use Model\Core\Controller;

class ImportFromTravioController extends Controller
{
	public function init()
	{
		$this->model->switchEvents(false);
		$this->model->_Db->setQueryLimit('table', 0);
	}

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
							'all-langs' => true,
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

							$this->model->_TravioAssets->importGeo($item['id']);
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
							'allow-external' => true,
							'show-names' => true,
						];

						if (isset($target['type']))
							$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						foreach ($list['list'] as $item) {
							if (!$item['id'])
								continue;

							$presents[] = $item['id'];

							$check = $this->model->select('travio_services', ['travio' => $item['id']]);
							if (!$check or ($item['last_update'] and ($check['last_update'] === null or date_create($check['last_update']) < date_create($item['last_update'])))) {
								$serviceData = $this->model->_Travio->request('static-data', [
									'type' => 'service',
									'id' => $item['id'],
									'all-langs' => true,
								])['data'];

								try {
									$this->model->_Db->beginTransaction();

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
										'notes' => $serviceData['notes'],
										'price' => $serviceData['price'],
										'min_date' => $serviceData['min_date'],
										'max_date' => $serviceData['max_date'],
										'visible' => 1,
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
										if (!$geo['id'])
											continue;
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

									/***********************/

									$this->model->_TravioAssets->importService($id, $serviceData['id']);

									$this->model->_Db->commit();
								} catch (\Exception $e) {
									$this->model->_Db->rollBack();
									throw $e;
								}
							}
						}
					}

					if ($presents) {
						$this->model->_Db->update('travio_services', [
							'travio' => ['NOT IN', $presents],
						], ['visible' => 0]);

						$this->model->_Db->update('travio_services', [
							'travio' => ['IN', $presents],
						], ['visible' => 1]);
					} else {
						$this->model->_Db->update('travio_services', [], ['visible' => 0], ['confirm' => true]);
					}
					break;
				case 'packages':
					$presents = [];

					foreach ($config['target-types'] as $target) {
						if ($target['search'] !== 'package')
							continue;

						$payload = [
							'type' => 'package',
							'show-names' => true,
						];

						if (isset($target['type']))
							$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						foreach ($list['list'] as $item) {
							if (!$item['id'])
								continue;

							$presents[] = $item['id'];

							$check = $this->model->select('travio_packages', ['travio' => $item['id']]);
							if (!$check or ($item['last_update'] and ($check['last_update'] === null or date_create($check['last_update']) < date_create($item['last_update'])))) {
								$packageData = $this->model->_Travio->request('static-data', [
									'type' => 'package',
									'id' => $item['id'],
									'all-langs' => true,
								])['data'];

								try {
									$this->model->_Db->beginTransaction();

									$id = $this->model->updateOrInsert('travio_packages', [
										'travio' => $packageData['id'],
									], [
										'code' => $packageData['code'],
										'name' => $packageData['name'],
										'notes' => $packageData['notes'],
//										'price' => $packageData['price'], // TODO: rendere gestibile nel config cosa viene sovrascritto all'update e cosa no
										'geo' => $packageData['geo'][0]['id'] ?? null,
										'visible' => 1,
										'last_update' => $item['last_update'],
									]);

									if ($check) {
										$this->model->_Db->delete('travio_packages_tags', ['package' => $id]);
										$this->model->_Db->delete('travio_packages_descriptions', ['package' => $id]);
										$this->model->_Db->delete('travio_packages_photos', ['package' => $id]);
										$this->model->_Db->delete('travio_packages_geo', ['package' => $id]);
										$this->model->_Db->delete('travio_packages_files', ['package' => $id]);
										$this->model->_Db->delete('travio_packages_departures', ['package' => $id]);
										$this->model->_Db->delete('travio_packages_hotels', ['package' => $id]);
									}

									foreach ($packageData['tags'] as $tag) {
										$this->model->_Db->insert('travio_packages_tags', [
											'package' => $id,
											'tag' => $tag,
										], ['defer' => true]);
									}

									$this->model->_Db->bulkInsert('travio_packages_tags');

									/***********************/

									foreach ($packageData['descriptions'] as $description) {
										$this->model->_Db->insert('travio_packages_descriptions', [
											'package' => $id,
											'tag' => $description['keyword'],
											'title' => $description['title'],
											'text' => $description['text'],
										]);
									}

									/***********************/

									foreach ($packageData['photos'] as $photo) {
										$this->model->_Db->insert('travio_packages_photos', [
											'package' => $id,
											'url' => $photo['url'],
											'thumb' => $photo['thumb'],
											'description' => $photo['description'],
										], ['defer' => true]);
									}

									$this->model->_Db->bulkInsert('travio_packages_photos');

									/***********************/

									foreach ($packageData['geo'] as $geo) {
										$this->model->_Db->insert('travio_packages_geo', [
											'package' => $id,
											'geo' => $geo['id'],
										], ['defer' => true]);
									}

									$this->model->_Db->bulkInsert('travio_packages_geo');

									/***********************/

									foreach ($packageData['files'] as $file) {
										$this->model->_Db->insert('travio_packages_files', [
											'package' => $id,
											'name' => $file['name'],
											'url' => $file['url'],
										], ['defer' => true]);
									}

									$this->model->_Db->bulkInsert('travio_packages_files');

									/***********************/

									foreach ($packageData['departures'] as $departure) {
										$this->model->_Db->insert('travio_packages_departures', [
											'package' => $id,
											'date' => $departure['date'],
											'departure_airport' => $departure['departure-airport'] ? ($this->model->select('travio_airports', ['code' => $departure['departure-airport']], 'id') ?: null) : null,
											'arrival_airport' => $departure['arrival-airport'] ? ($this->model->select('travio_airports', ['code' => $departure['arrival-airport']], 'id') ?: null) : null,
											'departure_port' => $departure['departure-port'] ? ($this->model->select('travio_ports', ['code' => $departure['departure-port']], 'id') ?: null) : null,
											'arrival_port' => $departure['arrival-port'] ? ($this->model->select('travio_ports', ['code' => $departure['arrival-port']], 'id') ?: null) : null,
										], ['defer' => true]);
									}

									$this->model->_Db->bulkInsert('travio_packages_departures');

									/***********************/

									foreach ($packageData['hotels'] as $hotel) {
										try {
											$this->model->_Db->insert('travio_packages_hotels', [
												'package' => $id,
												'hotel' => $this->model->select('travio_services', ['code' => $hotel['code']], 'id'),
											]);
										} catch (\Exception $e) {
										}
									}

									/***********************/

									$this->model->_TravioAssets->importPackage($id, $packageData['id']);

									$this->model->_Db->commit();
								} catch (\Exception $e) {
									$this->model->_Db->rollBack();
									throw $e;
								}
							}
						}
					}

					if ($presents) {
						$this->model->_Db->update('travio_packages', [
							'travio' => ['NOT IN', $presents],
						], ['visible' => 0]);

						$this->model->_Db->update('travio_packages', [
							'travio' => ['IN', $presents],
						], ['visible' => 1]);
					} else {
						$this->model->_Db->update('travio_packages', [], ['visible' => 0], ['confirm' => true]);
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

						$this->model->_TravioAssets->importTag($id);
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

						$this->model->_TravioAssets->importAmenity($id);
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
									'departure' => $item['departure'] ? 1 : 0,
									// TODO: far scegliere in config cosa sovrascrivere e cosa no (tipo il name)
								]);
							} else {
								$this->model->insert('travio_ports', [
									'id' => $item['id'],
									'code' => $item['code'],
									'name' => $item['name'],
									'departure' => $item['departure'] ? 1 : 0,
								]);
							}

							$this->model->_TravioAssets->importPort($item['id']);
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
									'departure' => $item['departure'] ? 1 : 0,
									// TODO: far scegliere in config cosa sovrascrivere e cosa no (tipo il name)
								]);
							} else {
								$this->model->insert('travio_airports', [
									'id' => $item['id'],
									'code' => $item['code'],
									'name' => $item['name'],
									'departure' => $item['departure'] ? 1 : 0,
								]);
							}

							$this->model->_TravioAssets->importAirport($item['id']);
						}
					}

					foreach ($this->model->select_all('travio_airports', $presents ? ['id' => ['NOT IN', $presents]] : []) as $airport) {
						try {
							$this->model->delete('travio_airports', $airport['id']);
						} catch (\Exception $e) {
						}
					}
					break;
				case 'stations':
					foreach ($config['target-types'] as $target) {
						if ($target['search'] !== 'service')
							continue;

						$payload = [
							'type' => 'stations',
							'all-langs' => true,
						];

						if (isset($target['type']))
							$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						foreach ($list['list'] as $id => $item) {
							$this->model->updateOrInsert('travio_stations', [
								'id' => $id,
							], [
								'code' => $item['code'],
								'name' => $item['name'],
							]);

							$this->model->_TravioAssets->importStation($item['id']);
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
