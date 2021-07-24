<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210724145100_PaymentMethods extends Migration
{
	public function exec()
	{
		$this->createTable('travio_payment_methods');
		$this->addColumn('travio_payment_methods', 'name', ['null' => false]);
		$this->addColumn('travio_payment_methods', 'visible', ['type' => 'tinyint', 'null' => false, 'default' => 1]);

		$this->createTable('travio_payment_methods_custom');
	}
}
