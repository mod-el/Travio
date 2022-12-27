<?php namespace Model\Travio\Controllers;

use Model\Core\Controller;
use Model\Db\Db;

class ImportFromTravioController extends Controller
{
	public function init()
	{
		$this->model->switchEvents(false);
		Db::getConnection()->setQueryLimit('table', 0);
	}

	public function index()
	{
		$config = $this->model->_Travio->retrieveConfig();
		$db = Db::getConnection();

		try {
			switch ($this->model->getInput('type')) {
				case 'geo':
					if (!$config['import']['geo']['import'])
						break;

					$presents = [];

					foreach ($config['target_types'] as $target) {
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

							$db->updateOrInsert('travio_geo', [
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

					$db->delete('travio_geo_parents', [], ['confirm' => true]);

					foreach ($this->model->all('TravioGeo') as $geo) {
						$parents = [];
						$el = clone $geo;
						while ($el and $el['parent']) {
							if ($el->parent) // Verifico l'effettiva esistenza a db
								$parents[] = $el['parent'];
							$el = $el->parent;
						}

						foreach ($parents as $parent) {
							$db->insert('travio_geo_parents', [
								'geo' => $geo['id'],
								'parent' => $parent,
							], [
								'defer' => 100,
							]);
						}
					}

					if ($presents) {
						$db->update('travio_geo', [
							'id' => ['NOT IN', $presents],
						], ['visible' => 0]);

						$db->update('travio_geo', [
							'id' => ['IN', $presents],
						], ['visible' => 1]);
					} else {
						$db->update('travio_geo', [], ['visible' => 0], ['confirm' => true]);
					}
					break;
				case 'services':
					if (!$config['import']['services']['import'])
						break;

					if (isset($_GET['item'])) {
						$item = json_decode($_GET['item'], true);
						if (!$item or !array_key_exists('id', $item) or !array_key_exists('existing', $item))
							die('Wrong item format');

						$this->model->_Travio->importService($item['id']);
					} elseif (isset($_POST['finalize'])) {
						$presents = json_decode($_POST['finalize'], true) ?: [];
						if ($presents) {
							$db->update('travio_services', [
								'travio' => ['NOT IN', $presents],
							], ['visible' => 0]);

							$db->update('travio_services', [
								'travio' => ['IN', $presents],
							], ['visible' => 1]);
						} else {
							$db->update('travio_services', [], ['visible' => 0], ['confirm' => true]);
						}
					} else {
						$items = [];

						foreach ($config['target_types'] as $target) {
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
								if (!$item['id'])
									continue;

								$check = $db->select('travio_services', ['travio' => $item['id']]);

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
							$db->beginTransaction();

							$data = [
								'code' => $packageData['code'],
								'name' => $packageData['name'],
								'type' => $packageData['type'],
								'notes' => $packageData['notes'],
								'price' => $packageData['price'],
								'geo' => $packageData['geo'][0]['id'] ?? null,
								'departs_from' => $packageData['departs_from'],
								'duration' => $packageData['duration'],
								'min_pax' => $packageData['min_pax'],
								'visible' => 1,
								'last_update' => $item['last_update'],
							];

							if ($item['existing']) {
								foreach (($config['import']['packages']['override'] ?? []) as $k => $override) {
									if (!$override)
										unset($data[$k]);
								}

								$id = $item['existing'];
								$db->update('travio_packages', $id, $data);

								$db->delete('travio_packages_tags', ['package' => $id]);
								$db->delete('travio_packages_descriptions', ['package' => $id]);
								$db->delete('travio_packages_geo', ['package' => $id]);
								$db->delete('travio_packages_files', ['package' => $id]);
								$db->delete('travio_packages_services', ['package' => $id]);
								$db->delete('travio_packages_guides', ['package' => $id]);
								$db->delete('travio_packages_itinerary', ['package' => $id]);
							} else {
								$data['travio'] = $packageData['id'];
								$id = $db->insert('travio_packages', $data);
							}

							foreach ($packageData['tags'] as $tagId => $tag) {
								$db->insert('travio_packages_tags', [
									'package' => $id,
									'tag' => $tagId,
								], ['defer' => true]);
							}

							$db->bulkInsert('travio_packages_tags');

							foreach ($packageData['descriptions'] as $description) {
								$db->insert('travio_packages_descriptions', [
									'package' => $id,
									'tag' => $description['keyword'],
									'title' => $description['title'],
									'text' => $description['text'],
								]);
							}

							$present_photos = [];
							foreach ($packageData['photos'] as $photoIdx => $photo) {
								$dataToUpdate = ['order' => $photoIdx + 1];
								if ($config['import']['packages']['override']['images_descriptions'] ?? true)
									$dataToUpdate['description'] = $photo['description'];

								if ($photo['url'])
									$this->model->_Travio->invalidatePhotoCache($photo['url']);
								if ($photo['thumb'])
									$this->model->_Travio->invalidatePhotoCache($photo['thumb']);

								$present_photos[] = $db->updateOrInsert('travio_packages_photos', [
									'package' => $id,
									'url' => $photo['url'],
									'thumb' => $photo['thumb'] ?: $photo['url'],
									'tag' => $photo['tag'],
								], $dataToUpdate);
							}

							if ($present_photos) {
								$db->delete('travio_packages_photos', [
									'package' => $id,
									'id' => ['NOT IN', $present_photos],
								]);
							} else {
								$db->delete('travio_packages_photos', ['package' => $id]);
							}

							foreach ($packageData['geo'] as $geo) {
								$db->insert('travio_packages_geo', [
									'package' => $id,
									'geo' => $geo['id'],
								], ['defer' => true]);
							}

							$db->bulkInsert('travio_packages_geo');

							foreach ($packageData['files'] as $file) {
								$db->insert('travio_packages_files', [
									'package' => $id,
									'name' => $file['name'],
									'url' => $file['url'],
								], ['defer' => true]);
							}

							$db->bulkInsert('travio_packages_files');

							foreach ($packageData['itinerary'] as $destination) {
								$dId = $db->insert('travio_packages_itinerary', [
									'package' => $id,
									'day' => $destination['day'],
									'geo' => $destination['geo'],
									'name' => $destination['name'],
									'description' => $destination['description'],
								]);

								foreach ($destination['photos'] as $photo) {
									$db->insert('travio_packages_itinerary_photos', [
										'itinerary' => $dId,
										'url' => $photo['url'],
										'thumb' => $photo['thumb'] ?: $photo['url'],
									]);
								}
							}

							$present_departures = [];
							foreach ($packageData['departures'] as $departure) {
								$present_departures[] = $db->updateOrInsert('travio_packages_departures', [
									'package' => $id,
									'date' => $departure['date'],
								], [
									'departure_airport' => $departure['departure-airport'] ? ($db->select('travio_airports', ['code' => $departure['departure-airport']])['id'] ?: null) : null,
									'arrival_airport' => $departure['arrival-airport'] ? ($db->select('travio_airports', ['code' => $departure['arrival-airport']])['id'] ?: null) : null,
									'departure_port' => $departure['departure-port'] ? ($db->select('travio_ports', ['code' => $departure['departure-port']])['id'] ?: null) : null,
									'arrival_port' => $departure['arrival-port'] ? ($db->select('travio_ports', ['code' => $departure['arrival-port']])['id'] ?: null) : null,
								]);
							}

							if ($present_departures) {
								$db->delete('travio_packages_departures', [
									'package' => $id,
									'id' => ['NOT IN', $present_departures],
								]);
							} else {
								$db->delete('travio_packages_departures', ['package' => $id]);
							}

							foreach ($packageData['services'] as $service) {
								$existing = $db->select('travio_services', ['code' => $service['code']]);
								if (!$existing)
									throw new \Exception('Il servizio ' . $service['code'] . ' del pacchetto ' . $packageData['code'] . ' non sembra esistere o essere visibile');

								$db->insert('travio_packages_services', [
									'package' => $id,
									'service' => $existing['id'],
									'type' => $service['type'],
								]);
							}

							foreach ($packageData['guides'] as $guide) {
								try {
									$db->insert('travio_packages_guides', [
										'package' => $id,
										'guide' => $guide['master_data'],
									]);
								} catch (\Exception $e) {
									$this->model->error('La guida #' . $guide['master_data'] . ' del pacchetto ' . $packageData['code'] . ' non sembra essere stata importata');
								}
							}

							$this->model->_TravioAssets->importPackage($id, $packageData['id']);

							$db->commit();
						} catch (\Exception $e) {
							$db->rollBack();
							throw $e;
						}
					} elseif (isset($_POST['finalize'])) {
						$presents = json_decode($_POST['finalize'], true) ?: [];
						if ($presents) {
							$db->update('travio_packages', [
								'travio' => ['NOT IN', $presents],
							], ['visible' => 0]);

							$db->update('travio_packages', [
								'travio' => ['IN', $presents],
							], ['visible' => 1]);
						} else {
							$db->update('travio_packages', [], ['visible' => 0], ['confirm' => true]);
						}
					} else {
						$items = [];

						foreach ($config['target_types'] as $target) {
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

								$check = $db->select('travio_packages', ['travio' => $item['id']]);

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
						$db->updateOrInsert('travio_tags', [
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
						$db->delete('travio_tags', ['id' => ['NOT IN', $idsList]]);
					else
						$db->delete('travio_tags', [], ['confirm' => true]);
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
							$type = $db->select('travio_amenities_types', ['name' => trim($item['tag'])]);
							if ($type)
								$type = $type['id'];
							else
								$type = $db->insert('travio_amenities_types', ['name' => trim($item['tag'])]);
						} else {
							$type = null;
						}

						$db->updateOrInsert('travio_amenities', [
							'id' => $id,
						], [
							'name' => $item['name'],
							'type' => $type,
						]);

						$idsList[] = $id;
						$this->model->_TravioAssets->importAmenity($id);
					}

					if ($idsList)
						$db->delete('travio_amenities', ['id' => ['NOT IN', $idsList]]);
					else
						$db->delete('travio_amenities', [], ['confirm' => true]);
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
						$db->updateOrInsert('travio_classifications', [
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
						$db->delete('travio_classifications', ['id' => ['NOT IN', $idsList]]);
					else
						$db->delete('travio_classifications', [], ['confirm' => true]);
					break;
				case 'ports':
					if (!$config['import']['ports']['import'])
						break;

					$presents = [];

					foreach ($config['target_types'] as $target) {
						$payload = [
							'type' => 'ports',
							'search-type' => $target['search'],
						];

						if ($target['type'] ?? null)
							$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						foreach ($list['list'] as $item) {
							$check = $db->select('travio_ports', $item['id']);

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

								$db->update('travio_ports', $item['id'], $data);
							} else {
								$db->insert('travio_ports', [
									'id' => $item['id'],
									'code' => $item['code'],
									'name' => $item['name'],
									'departure' => $item['departure'] ? 1 : 0,
								]);
							}

							$this->model->_TravioAssets->importPort($item['id']);
						}
					}

					foreach ($db->selectAll('travio_ports', $presents ? ['id' => ['NOT IN', $presents]] : []) as $port) {
						try {
							$db->delete('travio_ports', $port['id']);
						} catch (\Exception $e) {
						}
					}
					break;
				case 'airports':
					if (!$config['import']['airports']['import'])
						break;

					$presents = [];

					foreach ($config['target_types'] as $target) {
						$payload = [
							'type' => 'airports',
							'search-type' => $target['search'],
						];

						if ($target['type'] ?? null)
							$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						foreach ($list['list'] as $item) {
							$check = $db->select('travio_airports', $item['id']);

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

								$db->update('travio_airports', $item['id'], $data);
							} else {
								$db->insert('travio_airports', [
									'id' => $item['id'],
									'code' => $item['code'],
									'name' => $item['name'],
									'departure' => $item['departure'] ? 1 : 0,
								]);
							}

							$this->model->_TravioAssets->importAirport($item['id']);
						}
					}

					foreach ($db->selectAll('travio_airports', $presents ? ['id' => ['NOT IN', $presents]] : []) as $airport) {
						try {
							$db->delete('travio_airports', $airport['id']);
						} catch (\Exception $e) {
						}
					}
					break;
				case 'stations':
					if (!$config['import']['stations']['import'])
						break;

					$presents = [];

					foreach ($config['target_types'] as $target) {
						if ($target['search'] !== 'service')
							continue;

						$payload = [
							'type' => 'stations',
							'all-langs' => true,
						];

						if (isset($target['type']))
							$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						$db->delete('travio_stations_links', [], ['confirm' => true]);

						foreach ($list['list'] as $id => $item) {
							$check = $db->select('travio_stations', $id);

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

								$db->update('travio_stations', $id, $data);
							} else {
								$db->insert('travio_stations', [
									'id' => $id,
									'code' => $item['code'],
									'name' => $item['name'],
								]);
							}

							$cacheServices = [];
							foreach ([
								         'departs' => 'departure',
								         'arrives' => 'arrival',
							         ] as $k => $link_type) {
								foreach ($item[$k] as $link) {
									if (!array_key_exists($link, $cacheServices)) {
										if (str_starts_with($link, 'ss')) {
											$existing = $db->select('travio_subservices', substr($link, 2));

											$cacheServices[$link] = [
												'type' => 'subservice',
												'id' => $existing ? $existing['id'] : null,
											];
										} elseif (str_starts_with($link, 's')) {
											$existing = $db->select('travio_services', ['travio' => substr($link, 1)]);

											$cacheServices[$link] = [
												'type' => 'service',
												'id' => $existing ? $existing['id'] : null,
											];
										} else {
											continue;
										}
									}

									if (!$cacheServices[$link]['id'])
										continue;

									$db->insert('travio_stations_links', [
										'type' => $link_type,
										'station' => $id,
										$cacheServices[$link]['type'] => $cacheServices[$link]['id'],
									]);
								}
							}

							$this->model->_TravioAssets->importStation($id);
						}

						foreach ($db->selectAll('travio_stations', $presents ? ['id' => ['NOT IN', $presents]] : []) as $station) {
							try {
								$db->delete('travio_stations', $station['id']);
							} catch (\Exception $e) {
							}
						}
					}
					break;
				case 'luggage-types':
					if (!$config['import']['luggage_types']['import'])
						break;

					$list = $this->model->_Travio->request('static-data', [
						'type' => 'luggage-types',
						'all-langs' => true,
					]);

					$idsList = [];
					foreach ($list['list'] as $item) {
						$db->updateOrInsert('travio_luggage_types', [
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
						$db->delete('travio_luggage_types', ['id' => ['NOT IN', $idsList]]);
					else
						$db->delete('travio_luggage_types', [], ['confirm' => true]);
					break;
				case 'master-data':
					if (!$config['import']['master_data']['import'])
						break;

					$list = $this->model->_Travio->request('static-data', [
						'type' => 'master-data',
						'filters' => $config['import']['master_data']['filters'] ?? [],
					]);

					foreach ($list['list'] as $item) {
						$db->updateOrInsert('travio_master_data', [
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
					if (!$config['import']['payment_methods']['import'])
						break;

					$list = $this->model->_Travio->request('static-data', [
						'type' => 'payment-methods',
						'filters' => $config['import']['payment_methods']['filters'],
						'all-langs' => true,
					]);

					$idsList = [];
					foreach ($list['list'] as $item) {
						$check = $db->select('travio_payment_methods', $item['id']);
						if ($check) {
							$db->update('travio_payment_methods', $item['id'], [
								'name' => $item['name'],
							]);
						} else {
							$db->insert('travio_payment_methods', [
								'id' => $item['id'],
								'name' => $item['name'],
								'visible' => 0,
							]);
						}

						$this->model->_TravioAssets->importPaymentMethod($item['id']);
						$idsList[] = $item['id'];
					}

					if ($idsList)
						$db->delete('travio_payment_methods', ['id' => ['NOT IN', $idsList]]);
					else
						$db->delete('travio_payment_methods', [], ['confirm' => true]);
					break;
				case 'payment-conditions':
					if (!$config['import']['payment_conditions']['import'])
						break;

					$list = $this->model->_Travio->request('static-data', [
						'type' => 'payment-conditions',
						'all-langs' => true,
					]);

					$idsList = [];
					foreach ($list['list'] as $item) {
						$db->updateOrInsert('travio_payment_conditions', [
							'id' => $item['id'],
						], [
							'name' => $item['name'],
						]);

						$this->model->_TravioAssets->importPaymentCondition($item['id']);
						$idsList[] = $item['id'];
					}

					if ($idsList)
						$db->delete('travio_payment_conditions', ['id' => ['NOT IN', $idsList]]);
					else
						$db->delete('travio_payment_conditions', [], ['confirm' => true]);
					break;
				default:
					throw new \Exception('Unknown type');
			}
		} catch (\Exception $e) {
			echo getErr($e);
			die();
		}

		return 'ok';
	}
}
