<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210203151500_MasterData extends Migration
{
	public function exec()
	{
		$this->createTable('travio_master_data');
		$this->addColumn('travio_master_data', 'name');
		$this->addColumn('travio_master_data', 'surname');
		$this->addColumn('travio_master_data', 'business_name');
		$this->addColumn('travio_master_data', 'full_name');
		$this->addColumn('travio_master_data', 'category', ['type' => 'int']);
		$this->addColumn('travio_master_data', 'username');

		$this->createTable('travio_master_data_custom');
	}
}
