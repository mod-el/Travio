<?php namespace Model\Travio\Controllers;

use MJS\TopSort\Implementations\FixedArraySort;
use Model\Cache\Cache;
use Model\Core\Controller;
use Model\Db\Db;
use Model\Travio\TravioClient;

class ImportFromTravioController extends Controller
{
	public function init()
	{
		if (class_exists('\\Model\\Logger\\Logger'))
			\Model\Logger\Logger::disable();
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

					$currents = [];
					foreach ($db->selectAll('travio_geo') as $g)
						$currents[$g['id']] = $g['last_update'];

					$visible_ids = [];
					$seen_ids = [];

					foreach ($config['target_types'] as $target) {
						$payload = [
							'type' => 'geo',
							'search-type' => $target['search'],
							'all-langs' => true,
						];

						if (isset($target['type']))
							$payload['service-type'] = $target['type'];

						$list = $this->model->_Travio->request('static-data', $payload);

						$sorter = new FixedArraySort();
						$geoMap = [];
						foreach ($list['list'] as $g) {
							$geoMap[$g['id']] = $g;
							$sorter->add($g['id'], $g['parent'] ?? null);
						}

						$sortedGeo = $sorter->sort();

						foreach ($sortedGeo as $geoId) {
							if (($target['geo'] ?? true) and !in_array($geoId, $visible_ids))
								$visible_ids[] = $geoId;

							if (in_array($geoId, $seen_ids))
								continue;

							$seen_ids[] = $geoId;

							$item = $geoMap[$geoId];

							if (!isset($item['meta']['last_update']))
								$item['meta']['last_update'] = null;

							if (array_key_exists($item['id'], $currents)) {
								if ($currents[$item['id']] === null and $item['meta']['last_update'] === null)
									continue;
								if ($currents[$item['id']] and date_create($item['meta']['last_update']) <= date_create($currents[$item['id']]))
									continue;
							}

							$db->updateOrInsert('travio_geo', [
								'id' => $item['id'],
							], [
								'name' => $item['name'],
								'parent' => $item['parent'],
								'parent_name' => $item['parent-name'],
								'has_suppliers' => (int)$item['has_suppliers'],
								'visible' => 1,
								'last_update' => $item['meta']['last_update'],
							]);

							$this->model->_TravioAssets->importGeo($item);
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

					if ($visible_ids) {
						$db->update('travio_geo', [
							'id' => ['NOT IN', $visible_ids],
						], ['visible' => 0]);

						$db->update('travio_geo', [
							'id' => ['IN', $visible_ids],
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

						$id = $this->model->_Travio->importService($item['id']);
						$this->model->_TravioAssets->importService($id, $item);
					} elseif (isset($_POST['finalize'])) {
						$currents = json_decode($_POST['finalize'], true) ?: [];
						if ($currents) {
							$db->update('travio_services', [
								'travio' => ['NOT IN', $currents],
							], ['visible' => 0]);

							$db->update('travio_services', [
								'travio' => ['IN', $currents],
							], ['visible' => 1]);
						} else {
							$db->update('travio_services', [], ['visible' => 0], ['confirm' => true]);
						}
					} else {
						$items = [];

						foreach ($config['target_types'] as $target) {
							if ($target['search'] !== 'service')
								continue;

							$filters = [
								[
									'or' => [
										[
											'field' => 'visibility.web_single',
											'value' => true,
										],
										[
											'field' => 'visibility.web_package',
											'value' => true,
										],
									],
								],
							];
							if (isset($target['type'])) {
								$filters[] = [
									'field' => 'type',
									'value' => $target['type'],
								];
							}

							$list = TravioClient::restList('services', [
								'filters' => $filters,
								'sort_by' => [['id', 'ASC']],
								'per_page' => 0,
							]);

							foreach ($list['list'] as $item) {
								if (!$item['id'])
									continue;

								$check = $db->select('travio_services', ['travio' => $item['id']]);

								$items[] = [
									'id' => $item['id'],
									'last_update' => $item['_meta']['last_update'],
									'existing' => $check ? $check['id'] : null,
									'update' => (!$check or ($item['_meta']['last_update'] and ($check['last_update'] === null or date_create($check['last_update']) < date_create($item['_meta']['last_update'])))),
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

						$packageData = TravioClient::restGet('packages', $item['id']);

						try {
							$db->beginTransaction();

							$data = [
								'code' => $packageData['code'],
								'name' => $packageData['name'],
								'type' => $packageData['type'],
								'price' => $packageData['shown_price'],
								'geo' => $packageData['geo'][0][count($packageData['geo'][0]) - 1]['id'] ?? null,
								'duration' => $packageData['duration'],
								'min_pax' => $packageData['min_pax'],
								'visible' => 1,
								'last_update' => $packageData['_meta']['last_update'],
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

							foreach ($packageData['_tags'] as $tagId) {
								$db->insert('travio_packages_tags', [
									'package' => $id,
									'tag' => $tagId,
								], ['defer' => true]);
							}

							$db->bulkInsert('travio_packages_tags');

							$descriptions = [];
							foreach ($packageData['descriptions'] as $description) {
								$lang = $description['lang'];
								foreach ($description['paragraphs'] as $idx => $paragraph) {
									if (!isset($descriptions[$idx])) {
										$descriptions[$idx] = [
											'tag' => $paragraph['tag'],
											'title' => [],
											'text' => [],
										];
									}

									$descriptions[$idx]['title'][$lang] = $paragraph['title'];
									$descriptions[$idx]['text'][$lang] = $paragraph['text'];
								}
							}

							foreach ($descriptions as $description) {
								$db->insert('travio_packages_descriptions', [
									'package' => $id,
									...$description,
								]);
							}

							$present_photos = [];
							foreach ($packageData['images'] as $photoIdx => $photo) {
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

							foreach ($packageData['geo'][0] as $geo) {
								$db->insert('travio_packages_geo', [
									'package' => $id,
									'geo' => $geo['id'],
								], ['defer' => true]);
							}

							$db->bulkInsert('travio_packages_geo');

							foreach ($packageData['_attachments'] as $file) {
								$db->insert('travio_packages_files', [
									'package' => $id,
									'name' => $file['name'],
									'url' => $file['url'],
								], ['defer' => true]);
							}

							$db->bulkInsert('travio_packages_files');

							foreach ($packageData['schedule'] as $destination) {
								$dId = $db->insert('travio_packages_itinerary', [
									'package' => $id,
									'day' => $destination['day'],
									'name' => $destination['name'],
									'description' => $destination['description'],
								]);

								foreach ($destination['images'] as $photo) {
									$db->insert('travio_packages_itinerary_photos', [
										'itinerary' => $dId,
										'url' => $photo['url'],
										'thumb' => $photo['thumb'] ?: $photo['url'],
									]);
								}
							}

							$current_departures = [];
							foreach ($packageData['departures'] as $departure) {
								$departureQ = $db->select('travio_packages_departures', [
									'package' => $id,
									'date' => $departure['date'],
								]);

								if ($departureQ) {
									$departureId = $departureQ['id'];
								} else {
									$departureId = $db->insert('travio_packages_departures', [
										'package' => $id,
										'date' => $departure['date'],
									]);
								}

								$current_departures[] = $departureId;

								foreach ($departure['routes'] as $route) {
									$db->insert('travio_packages_departures_routes', [
										'departure' => $departureId,
										'departure_airport' => $route['departure']['airport'] ?? null,
										'departure_port' => $route['departure']['port'] ?? null,
										'arrival_airport' => $route['arrival']['airport'] ?? null,
										'arrival_port' => $route['arrival']['port'] ?? null,
									], ['defer' => true]);
								}
							}

							$db->bulkInsert('travio_packages_departures_routes');

							if ($current_departures) {
								$db->delete('travio_packages_departures', [
									'package' => $id,
									'id' => ['NOT IN', $current_departures],
								]);
							} else {
								$db->delete('travio_packages_departures', ['package' => $id]);
							}

							foreach ($packageData['rows'] as $row) {
								if (!$row['service'])
									continue;

								$existing = $db->select('travio_services', ['travio' => $row['service']]);
								if (!$existing)
									throw new \Exception('Il servizio ' . $row['service'] . ' del pacchetto ' . $packageData['code'] . ' non sembra esistere o essere visibile');

								$db->insert('travio_packages_services', [
									'package' => $id,
									'service' => $existing['id'],
									'type' => $existing['type'],
								]);
							}

							foreach ($packageData['guides'] as $guide) {
								try {
									$db->insert('travio_packages_guides', [
										'package' => $id,
										'guide' => $guide,
									]);
								} catch (\Exception $e) {
								}
							}

							$cacheAdapter = Cache::getCacheAdapter();
							$cacheAdapter->invalidateTags(['travio.departures']);

							$this->model->_TravioAssets->importPackage($id, $packageData);

							$db->commit();
						} catch (\Exception $e) {
							$db->rollBack();
							throw $e;
						}
					} elseif (isset($_POST['finalize'])) {
						$currents = json_decode($_POST['finalize'], true) ?: [];
						if ($currents) {
							$db->update('travio_packages', [
								'travio' => ['NOT IN', $currents],
							], ['visible' => 0]);

							$db->update('travio_packages', [
								'travio' => ['IN', $currents],
							], ['visible' => 1]);
						} else {
							$db->update('travio_packages', [], ['visible' => 0], ['confirm' => true]);
						}
					} else {
						$items = [];

						foreach ($config['target_types'] as $target) {
							if ($target['search'] !== 'package')
								continue;

							$filters = [
								[
									'field' => 'visibility.web',
									'value' => true,
								],
							];
							if (isset($target['type'])) {
								$filters[] = [
									'field' => 'type',
									'value' => $target['type'],
								];
							}

							$list = TravioClient::restList('packages', [
								'filters' => $filters,
								'sort_by' => [['id', 'ASC']],
								'per_page' => 0,
							]);

							foreach ($list['list'] as $item) {
								if (!$item['id'])
									continue;

								$check = $db->select('travio_packages', ['travio' => $item['id']]);

								$items[] = [
									'id' => $item['id'],
									'last_update' => $item['_meta']['last_update'],
									'existing' => $check ? $check['id'] : null,
									'update' => (!$check or ($item['_meta']['last_update'] and ($check['last_update'] === null or date_create($check['last_update']) < date_create($item['_meta']['last_update'])))),
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

					$list = TravioClient::restList('tags', ['per_page' => 0]);

					$sorter = new FixedArraySort();
					$tagsMap = [];
					foreach ($list['list'] as $tag) {
						$tagsMap[$tag['id']] = $tag;
						$sorter->add($tag['id'], $tag['parent'] ?? null);
					}

					$sortedTags = $sorter->sort();

					$idsList = [];
					foreach ($sortedTags as $tagId) {
						$tag = $tagsMap[$tagId];

						$db->updateOrInsert('travio_tags', [
							'id' => $tag['id'],
						], [
							'parent' => $tag['parent'],
							'name' => $tag['name'],
							'full_name' => $tag['_full_name'],
						]);

						$this->model->_TravioAssets->importTag($tag);
						$idsList[] = $tag['id'];
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
						$this->model->_TravioAssets->importAmenity($item);
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

						$this->model->_TravioAssets->importClassification($item);
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

					$currents = [];

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

							$currents[] = $item['id'];

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

							$this->model->_TravioAssets->importPort($item);
						}
					}

					foreach ($db->selectAll('travio_ports', $currents ? ['id' => ['NOT IN', $currents]] : []) as $port) {
						try {
							$db->delete('travio_ports', $port['id']);
						} catch (\Exception $e) {
						}
					}
					break;
				case 'airports':
					if (!$config['import']['airports']['import'])
						break;

					$currents = [];

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

							$currents[] = $item['id'];

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

							$this->model->_TravioAssets->importAirport($item);
						}
					}

					foreach ($db->selectAll('travio_airports', $currents ? ['id' => ['NOT IN', $currents]] : []) as $airport) {
						try {
							$db->delete('travio_airports', $airport['id']);
						} catch (\Exception $e) {
						}
					}
					break;
				case 'stations':
					if (!$config['import']['stations']['import'])
						break;

					$currents = [];

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

							$currents[] = $id;

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

							$this->model->_TravioAssets->importStation($item);
						}

						foreach ($db->selectAll('travio_stations', $currents ? ['id' => ['NOT IN', $currents]] : []) as $station) {
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

						$this->model->_TravioAssets->importLuggageType($item);
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

						$this->model->_TravioAssets->importMasterData($item);
					}
					break;
				case 'payment-methods':
					if (!$config['import']['payment_methods']['import'])
						break;

					$list = TravioClient::restList('payment-methods', [
						'filters' => [
							[
								'field' => 'visible_in',
								'value' => true,
							],
						],
					]);

					$idsList = [];
					foreach ($list['list'] as $item) {
						$check = $db->select('travio_payment_methods', $item['id']);
						if ($check) {
							$db->update('travio_payment_methods', $item['id'], [
								'name' => $item['name'],
								'gateway' => $item['gateway'],
							]);
						} else {
							$db->insert('travio_payment_methods', [
								'id' => $item['id'],
								'name' => $item['name'],
								'gateway' => $item['gateway'],
								'visible' => 0,
							]);
						}

						$this->model->_TravioAssets->importPaymentMethod($item);
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

						$this->model->_TravioAssets->importPaymentCondition($item);
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
