<?php

use Phinx\Migration\AbstractMigration;

class FixCustomTablesForeignKeys extends AbstractMigration
{
	public function up()
	{
		// List of all custom tables and their main tables
		$custom_tables = [
			'travio_airports_custom' => 'travio_airports',
			'travio_classifications_custom' => 'travio_classifications',
			'travio_geo_custom' => 'travio_geo',
			'travio_geo_custom_texts' => 'travio_geo_texts',
			'travio_master_data_custom' => 'travio_master_data',
			'travio_orders_custom' => 'travio_orders',
			'travio_packages_custom' => 'travio_packages',
			'travio_packages_custom_texts' => 'travio_packages_texts',
			'travio_packages_departures_custom' => 'travio_packages_departures',
			'travio_payment_conditions_custom' => 'travio_payment_conditions',
			'travio_payment_methods_custom' => 'travio_payment_methods',
			'travio_ports_custom' => 'travio_ports',
			'travio_services_custom' => 'travio_services',
			'travio_services_custom_texts' => 'travio_services_texts',
			'travio_stations_custom' => 'travio_stations',
			'travio_stations_custom_texts' => 'travio_stations_texts',
			'travio_tags_custom' => 'travio_tags',
			'travio_tags_custom_texts' => 'travio_tags_texts',
		];

		foreach ($custom_tables as $custom_table => $main_table)
			$this->fix_custom_table($custom_table, $main_table);
	}

	protected function fix_custom_table($custom_table, $main_table)
	{
		// Delete orphan rows (rows in custom table where id doesn't exist in main table)
		$this->execute("
			DELETE FROM `{$custom_table}`
			WHERE `id` NOT IN (SELECT `id` FROM `{$main_table}`)
		");

		// Add foreign key constraint with CASCADE delete
		$table = $this->table($custom_table);
		
		// Check if the foreign key already exists, if not add it
		if (!$table->hasForeignKey('id'))
			$table->addForeignKey('id', $main_table, 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])->update();
	}

	public function down()
	{
		// List of all custom tables
		$custom_tables = [
			'travio_airports_custom',
			'travio_classifications_custom',
			'travio_geo_custom',
			'travio_master_data_custom',
			'travio_orders_custom',
			'travio_packages_custom',
			'travio_packages_departures_custom',
			'travio_payment_conditions_custom',
			'travio_payment_methods_custom',
			'travio_ports_custom',
			'travio_services_custom',
			'travio_stations_custom',
			'travio_tags_custom',
		];

		// Remove foreign keys
		foreach ($custom_tables as $custom_table) {
			$table = $this->table($custom_table);
			if ($table->hasForeignKey('id'))
				$table->dropForeignKey('id')->update();
		}
	}
}


