<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20211014145500_PaymentConditions extends Migration
{
	public function exec()
	{
		$this->createTable('travio_payment_conditions');
		$this->addColumn('travio_payment_conditions', 'name', ['null' => false]);

		$this->createTable('travio_payment_conditions_custom');
		$this->addForeignKey('travio_payment_conditions_custom', 'travio_payment_conditions_custom', 'id', 'travio_payment_conditions', 'id', ['on-delete' => 'CASCADE']);
	}
}
