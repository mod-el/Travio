<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20250420173500_PaymentMethodsGateway extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_payment_methods', 'gateway', ['null' => true]);
	}
}
