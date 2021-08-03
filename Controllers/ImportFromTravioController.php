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
					if (!$config['import']['geo']['import'])
						break;

					$presents = [];

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
							if (in_array($item['id'], $presents))
								continue;

							$presents[] = $item['id'];

							$this->model->updateOrInsert('travio_geo', [
								'id' => $item['id'],
							], [
								'name' => $item['name'],
								'parent' => $item['parent'],
								'parent_name' => $item['parent-name'],
								'visible' => 1,
							]);

							$this->model->_TravioAssets->importGeo($item['id']);
						}
					}

					$this->model->delete('travio_geo_parents', [], ['confirm' => true]);

					foreach ($this->model->all('TravioGeo') as $geo) {
						$parents = [];
						$el = clone $geo;
						while ($el and $el['parent']) {
							if ($el->parent) // Verifico l'effettiva esistenza a db
								$parents[] = $el['parent'];
							$el = $el->parent;
						}

						foreach ($parents as $parent) {
							$this->model->insert('travio_geo_parents', [
								'geo' => $geo['id'],
								'parent' => $parent,
							], [
								'defer' => 100,
							]);
						}
					}

					if ($presents) {
						$this->model->_Db->update('travio_geo', [
							'id' => ['NOT IN', $presents],
						], ['visible' => 0]);

						$this->model->_Db->update('travio_geo', [
							'id' => ['IN', $presents],
						], ['visible' => 1]);
					} else {
						$this->model->_Db->update('travio_geo', [], ['visible' => 0], ['confirm' => true]);
					}
					break;
				case 'services':
					if (!$config['import']['services']['import'])
						break;

					if (isset($_GET['item'])) {
						$item = json_decode($_GET['item'], true);
						if (!$item or !array_key_exists('id', $item) or !array_key_exists('existing', $item) or !array_key_exists('last_update', $item))
							die('Wrong item format');

						$serviceData = $this->model->_Travio->request('static-data', [
							'type' => 'service',
							'id' => $item['id'],
							'all-langs' => true,
						])['data'];

						try {
							$this->model->_Db->beginTransaction();

							$data = [
								'code' => $serviceData['code'],
								'name' => $serviceData['name'],
								'type' => $serviceData['type'],
								'typology' => $serviceData['typology'],
								'geo' => $serviceData['geo'][0]['id'] ?? null,
								'classification_id' => $serviceData['classification'] ? $serviceData['classification']['id'] : null,
								'classification' => $serviceData['classification'] ? $serviceData['classification']['code'] : null,
								'classification_level' => $serviceData['classification'] ? $serviceData['classification']['level'] : null,
								'lat' => $serviceData['lat'],
								'lng' => $serviceData['lng'],
								'address' => $serviceData['address'],
								'zip' => $serviceData['zip'],
								'tel' => $serviceData['tel'],
								'email' => $serviceData['email'],
								'notes' => $serviceData['notes'],
								'price' => $serviceData['price'],
								'min_date' => $serviceData['min_date'],
								'max_date' => $serviceData['max_date'],
								'visible' => 1,
								'last_update' => $item['last_update'],
							];

							if ($item['existing']) {
								foreach (($config['import']['services']['override'] ?? []) as $k => $override) {
									if (!$override)
										unset($data[$k]);
								}

								$id = $item['existing'];
								$this->model->update('travio_services', $id, $data);

								$this->model->_Db->delete('travio_services_tags', ['service' => $id]);
								$this->model->_Db->delete('travio_services_descriptions', ['service' => $id]);
								$this->model->_Db->delete('travio_services_photos', ['service' => $id]);
								$this->model->_Db->delete('travio_services_geo', ['service' => $id]);
								$this->model->_Db->delete('travio_services_amenities', ['service' => $id]);
								$this->model->_Db->delete('travio_services_files', ['service' => $id]);
								$this->model->_Db->delete('travio_services_videos', ['service' => $id]);
							} else {
								$data['travio'] = $serviceData['id'];
								$id = $this->model->insert('travio_services', $data);
							}

							if ($config['import']['subservices']['import']) {
								$this->model->_Db->delete('travio_subservices_tags', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);
								$this->model->_Db->delete('travio_subservices_descriptions', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);
								$this->model->_Db->delete('travio_subservices_photos', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);
								$this->model->_Db->delete('travio_subservices_amenities', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);
								$this->model->_Db->delete('travio_subservices_files', ['service' => $id], ['joins' => ['travio_subservices' => ['service']]]);

								foreach ($serviceData['subservices'] as $subservice) {
									$ss_id = $this->model->_Db->updateOrInsert('travio_subservices', [
										'id' => $subservice['id'],
									], [
										'service' => $id,
										'code' => $subservice['code'],
										'type' => $subservice['type'],
										'name' => $subservice['name'],
									]);

									foreach ($subservice['tags'] as $tagId => $tag) {
										$this->model->_Db->insert('travio_subservices_tags', [
											'subservice' => $ss_id,
											'tag' => $tagId,
										], ['defer' => true]);
									}

									foreach ($subservice['descriptions'] as $description) {
										$this->model->_Db->insert('travio_subservices_descriptions', [
											'subservice' => $ss_id,
											'tag' => $description['keyword'],
											'title' => $description['title'],
											'text' => $description['text'],
										]);
									}

									foreach ($subservice['photos'] as $photo) {
										$this->model->_Db->insert('travio_subservices_photos', [
											'subservice' => $ss_id,
											'url' => $photo['url'],
											'thumb' => $photo['thumb'],
											'description' => $photo['description'],
										], ['defer' => true]);
									}

									foreach ($subservice['amenities'] as $amenity_id => $amenity) {
										$this->model->_Db->insert('travio_subservices_amenities', [
											'subservice' => $ss_id,
											'amenity' => $amenity_id,
											'name' => $amenity['name'],
											'tag' => $amenity['tag'] ?: null,
										], ['defer' => true]);
									}

									foreach ($subservice['files'] as $file) {
										$this->model->_Db->insert('travio_subservices_files', [
											'subservice' => $ss_id,
											'name' => $file['name'],
											'url' => $file['url'],
										], ['defer' => true]);
									}
								}

								$this->model->_Db->bulkInsert('travio_subservices_tags');
								$this->model->_Db->bulkInsert('travio_subservices_photos');
								$this->model->_Db->bulkInsert('travio_subservices_amenities');
								$this->model->_Db->bulkInsert('travio_subservices_files');
							}

							foreach ($serviceData['tags'] as $tagId => $tag) {
								$this->model->_Db->insert('travio_services_tags', [
									'service' => $id,
									'tag' => $tagId,
								], ['defer' => true]);
							}

							$this->model->_Db->bulkInsert('travio_services_tags');

							foreach ($serviceData['descriptions'] as $description) {
								$this->model->_Db->insert('travio_services_descriptions', [
									'service' => $id,
									'tag' => $description['keyword'],
									'title' => $description['title'],
									'text' => $description['text'],
								]);
							}

							foreach ($serviceData['photos'] as $photo) {
								$this->model->_Db->insert('travio_services_photos', [
									'service' => $id,
									'url' => $photo['url'],
									'thumb' => $photo['thumb'],
									'description' => $photo['description'],
								], ['defer' => true]);
							}

							$this->model->_Db->bulkInsert('travio_services_photos');

							foreach ($serviceData['geo'] as $geo) {
								if (!$geo['id'])
									continue;
								$this->model->_Db->insert('travio_services_geo', [
									'service' => $id,
									'geo' => $geo['id'],
								], ['defer' => true]);
							}

							$this->model->_Db->bulkInsert('travio_services_geo');

							foreach ($serviceData['amenities'] as $amenity_id => $amenity) {
								$this->model->_Db->insert('travio_services_amenities', [
									'service' => $id,
									'amenity' => $amenity_id,
									'name' => $amenity['name'],
									'tag' => $amenity['tag'] ?: null,
								], ['defer' => true]);
							}

							$this->model->_Db->bulkInsert('travio_services_amenities');

							foreach ($serviceData['files'] as $file) {
								$this->model->_Db->insert('travio_services_files', [
									'service' => $id,
									'name' => $file['name'],
									'url' => $file['url'],
								], ['defer' => true]);
							}

							$this->model->_Db->bulkInsert('travio_services_files');

							foreach ($serviceData['videos'] as $video) {
								$this->model->_Db->insert('travio_services_videos', [
									'service' => $id,
									'video' => $video,
								], ['defer' => true]);
							}

							$this->model->_Db->bulkInsert('travio_services_videos');

							$this->model->_TravioAssets->importService($id, $serviceData['id']);

							$this->model->_Db->commit();
						} catch (\Exception $e) {
							$this->model->_Db->rollBack();
							throw $e;
						}
					} elseif (isset($_GET['finalize'])) {
						$presents = json_decode($_GET['finalize'], true) ?: [];
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
					} else {
						$items = [];

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

								$check = $this->model->select('travio_services', ['travio' => $item['id']], [
									'auto_ml' => false,
									'auto-join-linked-tables' => false,
								]);

								$items[] = [
									'id' => $item['id'],
									'last_update' => $item['last_update'],
									'existing' => $check ? $check['id'] : null,
									'update' => (!$check or ($item['last_update'] and ($check['last_update'] === null or date_create($check['last_update']) < date_create($item['last_update'])))),
								];
							}
						}

						return [
							'items' => $items,
						];
					}
					break;
				case 'packages':
					if (!$config['import']['packages']['import'])
						break;

					if (isset($_GET['item'])) {
						$item = json_decode($_GET['item'], true);
						if (!$item or !array_key_exists('id', $item) or !array_key_exists('existing', $item) or !array_key_exists('last_update', $item))
							die('Wrong item format');

						$packageData = $this->model->_Travio->request('static-data', [
							'type' => 'package',
							'id' => $item['id'],
							'all-langs' => true,
						])['data'];

						try {
							$this->model->_Db->beginTransaction();

							$data = [
								'code' => $packageData['code'],
								'name' => $packageData['name'],
								'type' => $packageData['type'],
								'notes' => $packageData['notes'],
								'price' => $packageData['price'],
								'geo' => $packageData['geo'][0]['id'] ?? null,
								'duration' => $packageData['duration'],
								'visible' => 1,
								'last_update' => $item['last_update'],
							];

							if ($item['existing']) {
								foreach (($config['import']['packages']['override'] ?? []) as $k => $override) {
									if (!$override)
										unset($data[$k]);
								}

								$id = $item['existing'];
								$this->model->update('travio_packages', $id, $data);

								$this->model->_Db->delete('travio_packages_tags', ['package' => $id]);
								$this->model->_Db->delete('travio_packages_descriptions', ['package' => $id]);
								$this->model->_Db->delete('travio_packages_photos', ['package' => $id]);
								$this->model->_Db->delete('travio_packages_geo', ['package' => $id]);
								$this->model->_Db->delete('travio_packages_files', ['package' => $id]);
								$this->model->_Db->delete('travio_packages_departures', ['package' => $id]);
								$this->model->_Db->delete('travio_packages_hotels', ['package' => $id]);
								$this->model->_Db->delete('travio_packages_itinerary', ['package' => $id]);
							} else {
								$data['travio'] = $packageData['id'];
								$id = $this->model->insert('travio_packages', $data);
							}

							foreach ($packageData['tags'] as $tagId => $tag) {
								$this->model->_Db->insert('travio_packages_tags', [
									'package' => $id,
									'tag' => $tagId,
								], ['defer' => true]);
							}

							$this->model->_Db->bulkInsert('travio_packages_tags');

							foreach ($packageData['descriptions'] as $description) {
								$this->model->_Db->insert('travio_packages_descriptions', [
									'package' => $id,
									'tag' => $description['keyword'],
									'title' => $description['title'],
									'text' => $description['text'],
								]);
							}

							foreach ($packageData['photos'] as $photo) {
								$this->model->_Db->insert('travio_packages_photos', [
									'package' => $id,
									'url' => $photo['url'],
									'thumb' => $photo['thumb'],
									'description' => $photo['description'],
								], ['defer' => true]);
							}

							$this->model->_Db->bulkInsert('travio_packages_photos');

							foreach ($packageData['geo'] as $geo) {
								$this->model->_Db->insert('travio_packages_geo', [
									'package' => $id,
									'geo' => $geo['id'],
								], ['defer' => true]);
							}

							$this->model->_Db->bulkInsert('travio_packages_geo');

							foreach ($packageData['files'] as $file) {
								$this->model->_Db->insert('travio_packages_files', [
									'package' => $id,
									'name' => $file['name'],
									'url' => $file['url'],
								], ['defer' => true]);
							}

							$this->model->_Db->bulkInsert('travio_packages_files');

							foreach ($packageData['itinerary'] as $destination) {
								$dId = $this->model->_Db->insert('travio_packages_itinerary', [
									'package' => $id,
									'name' => $destination['name'],
									'description' => $destination['description'],
								]);

								foreach ($destination['photos'] as $photo) {
									$this->model->_Db->insert('travio_packages_itinerary_photos', [
										'itinerary' => $dId,
										'thumb' => $photo['thumb'],
										'url' => $photo['url'],
									]);
								}
							}

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

							foreach ($packageData['hotels'] as $hotel) {
								try {
									$this->model->_Db->insert('travio_packages_hotels', [
										'package' => $id,
										'hotel' => $this->model->select('travio_services', ['code' => $hotel['code']], 'id'),
									]);
								} catch (\Exception $e) {
									$this->model->error('L\'hotel ' . $hotel['code'] . ' del pacchetto ' . $packageData['code'] . ' non sembra esistere o essere visibile');
								}
							}

							$this->model->_TravioAssets->importPackage($id, $packageData['id']);

							$this->model->_Db->commit();
						} catch (\Exception $e) {
							$this->model->_Db->rollBack();
							throw $e;
						}
					} elseif (isset($_GET['finalize'])) {
						$presents = json_decode($_GET['finalize'], true) ?: [];
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
					} else {
						$items = [];

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

								$check = $this->model->select('travio_packages', ['travio' => $item['id']], [
									'auto_ml' => false,
									'auto-join-linked-tables' => false,
								]);

								$items[] = [
									'id' => $item['id'],
									'last_update' => $item['last_update'],
									'existing' => $check ? $check['id'] : null,
									'update' => (!$check or ($item['last_update'] and ($check['last_update'] === null or date_create($check['last_update']) < date_create($item['last_update'])))),
								];
							}
						}

						return [
							'items' => $items,
						];
					}
					break;
				case 'tags':
					if (!$config['import']['tags']['import'])
						break;

					$list = $this->model->_Travio->request('static-data', [
						'type' => 'tags',
						'all-langs' => true,
					]);

					$idsList = [];
					foreach ($list['list'] as $item) {
						$this->model->updateOrInsert('travio_tags', [
							'id' => $item['id'],
						], [
							'parent' => $item['parent'],
							'name' => $item['name'],
							'full_name' => $item['full_name'],
						]);

						$this->model->_TravioAssets->importTag($item['id']);
						$idsList[] = $item['id'];
					}

					if ($idsList)
						$this->model->_Db->delete('travio_tags', ['id' => ['NOT IN', $idsList]]);
					else
						$this->model->_Db->delete('travio_tags', [], ['confirm' => true]);
					break;
				case 'amenities':
					if (!$config['import']['amenities']['import'])
						break;

					$list = $this->model->_Travio->request('static-data', [
						'type' => 'amenities',
						'all-langs' => true,
					]);

					$idsList = [];
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

						$idsList[] = $id;
						$this->model->_TravioAssets->importAmenity($id);
					}

					if ($idsList)
						$this->model->_Db->delete('travio_amenities', ['id' => ['NOT IN', $idsList]]);
					else
						$this->model->_Db->delete('travio_amenities', [], ['confirm' => true]);
					break;
				case 'classifications':
					if (!$config['import']['classifications']['import'])
						break;

					$list = $this->model->_Travio->request('static-data', [
						'type' => 'classifications',
						'all-langs' => true,
					]);

					$idsList = [];
					foreach ($list['list'] as $item) {
						$this->model->updateOrInsert('travio_classifications', [
							'id' => $item['id'],
						], [
							'code' => $item['code'],
							'name' => $item['name'],
							'level' => $item['level'],
						]);

						$this->model->_TravioAssets->importClassification($item['id']);
						$idsList[] = $item['id'];
					}

					if ($idsList)
						$this->model->_Db->delete('travio_classifications', ['id' => ['NOT IN', $idsList]]);
					else
						$this->model->_Db->delete('travio_classifications', [], ['confirm' => true]);
					break;
				case 'ports':
					if (!$config['import']['ports']['import'])
						break;

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
							$check = $this->model->select('travio_ports', $item['id'], [
								'auto_ml' => false,
								'auto-join-linked-tables' => false,
							]);

							$presents[] = $item['id'];

							if ($check) {
								$data = [
									'code' => $item['code'],
									'name' => $item['name'],
									'departure' => $item['departure'] ? 1 : 0,
								];

								foreach (($config['import']['ports']['override'] ?? []) as $k => $override) {
									if (!$override)
										unset($data[$k]);
								}

								$this->model->update('travio_ports', $item['id'], $data);
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
					if (!$config['import']['airports']['import'])
						break;

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
							$check = $this->model->select('travio_airports', $item['id'], [
								'auto_ml' => false,
								'auto-join-linked-tables' => false,
							]);

							$presents[] = $item['id'];

							if ($check) {
								$data = [
									'code' => $item['code'],
									'name' => $item['name'],
									'departure' => $item['departure'] ? 1 : 0,
								];

								foreach (($config['import']['airports']['override'] ?? []) as $k => $override) {
									if (!$override)
										unset($data[$k]);
								}

								$this->model->update('travio_airports', $item['id'], $data);
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
					if (!$config['import']['stations']['import'])
						break;

					$presents = [];

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
							$check = $this->model->select('travio_stations', $id, [
								'auto_ml' => false,
								'auto-join-linked-tables' => false,
							]);

							$presents[] = $id;

							if ($check) {
								$data = [
									'code' => $item['code'],
									'name' => $item['name'],
								];

								foreach (($config['import']['stations']['override'] ?? []) as $k => $override) {
									if (!$override)
										unset($data[$k]);
								}

								$this->model->update('travio_stations', $id, $data);
							} else {
								$this->model->insert('travio_stations', [
									'id' => $id,
									'code' => $item['code'],
									'name' => $item['name'],
								]);
							}

							$this->model->_TravioAssets->importStation($id);
						}

						foreach ($this->model->select_all('travio_stations', $presents ? ['id' => ['NOT IN', $presents]] : []) as $station) {
							try {
								$this->model->delete('travio_stations', $station['id']);
							} catch (\Exception $e) {
							}
						}
					}
					break;
				case 'luggage-types':
					if (!$config['import']['luggage-types']['import'])
						break;

					$list = $this->model->_Travio->request('static-data', [
						'type' => 'luggage-types',
						'all-langs' => true,
					]);

					$idsList = [];
					foreach ($list['list'] as $item) {
						$this->model->updateOrInsert('travio_luggage_types', [
							'id' => $item['id'],
						], [
							'name' => $item['name'],
							'weight' => $item['weight'],
							'length' => $item['length'],
							'width' => $item['width'],
							'height' => $item['height'],
						]);

						$this->model->_TravioAssets->importLuggageType($item['id']);
						$idsList[] = $item['id'];
					}

					if ($idsList)
						$this->model->_Db->delete('travio_luggage_types', ['id' => ['NOT IN', $idsList]]);
					else
						$this->model->_Db->delete('travio_luggage_types', [], ['confirm' => true]);
					break;
				case 'master-data':
					if (!$config['import']['master-data']['import'])
						break;

					$list = $this->model->_Travio->request('static-data', [
						'type' => 'master-data',
						'filters' => $config['import']['master-data']['filters'] ?? [],
					]);

					foreach ($list['list'] as $item) {
						$this->model->updateOrInsert('travio_master_data', [
							'id' => $item['id'],
						], [
							'name' => $item['name'],
							'surname' => $item['surname'],
							'business_name' => $item['business-name'],
							'full_name' => $item['full-name'],
							'category' => $item['category'],
							'username' => $item['username'],
						]);

						$this->model->_TravioAssets->importMasterData($item['id']);
					}
					break;
				case 'payment-methods':
					if (!$config['import']['payment-methods']['import'])
						break;

					$list = $this->model->_Travio->request('static-data', [
						'type' => 'payment-methods',
						'filters' => $config['import']['payment-methods']['filters'],
						'all-langs' => true,
					]);

					$idsList = [];
					foreach ($list['list'] as $item) {
						$this->model->updateOrInsert('travio_payment_methods', [
							'id' => $item['id'],
						], [
							'name' => $item['name'],
						]);

						$this->model->_TravioAssets->importPaymentMethod($item['id']);
						$idsList[] = $item['id'];
					}

					if ($idsList)
						$this->model->_Db->delete('travio_payment_methods', ['id' => ['NOT IN', $idsList]]);
					else
						$this->model->_Db->delete('travio_payment_methods', [], ['confirm' => true]);
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
