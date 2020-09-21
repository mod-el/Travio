<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20200921110600_OrdersTable extends Migration
{
	public function exec()
	{
		$this->createTable('travio_orders');
		$this->addColumn('travio_orders', 'reference', ['null' => false]);
		$this->addColumn('travio_orders', 'amount', ['type' => 'decimal(12,2)', 'null' => false]);
		$this->addColumn('travio_orders', 'date', ['type' => 'datetime', 'null' => false]);
		$this->addColumn('travio_orders', 'gateway');
		$this->addColumn('travio_orders', 'paid', ['type' => 'datetime']);
		$this->addColumn('travio_orders', 'is_first_payment', ['type' => 'tinyint', 'null' => false, 'default' => 1]);
	}
}
