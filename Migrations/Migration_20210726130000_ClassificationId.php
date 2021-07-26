<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210726130000_ClassificationId extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_services', 'classification_id', ['type' => 'INT', 'after' => 'geo']);
		$this->changeColumn('travio_services', 'classification_level', ['type' => 'DECIMAL(2,1)']);
		$this->addForeignKey('travio_services', 'travio_services_classification', 'classification_id', 'travio_classifications', 'id', ['on-delete' => 'SET NULL']);

		$this->query('UPDATE `travio_services` SET `last_update` = NULL WHERE `classification` IS NOT NULL');
	}
}
