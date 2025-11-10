<?php

use Phinx\Migration\AbstractMigration;

class FixCustomTablesForeignKeys2 extends AbstractMigration
{
	public function up()
	{
		// List of all custom tables and their main tables
		$custom_tables = [
			'travio_amenities_custom' => 'travio_amenities',
			'travio_subservices_custom' => 'travio_subservices',
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
			'travio_amenities_custom',
			'travio_subservices_custom',
		];

		// Remove foreign keys
		foreach ($custom_tables as $custom_table) {
			$table = $this->table($custom_table);
			if ($table->hasForeignKey('id'))
				$table->dropForeignKey('id')->update();
		}
	}
}

