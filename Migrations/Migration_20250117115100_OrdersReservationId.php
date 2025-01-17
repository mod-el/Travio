<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20250117115100_OrdersReservationId extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_orders', 'reservation', ['type' => 'INT', 'null' => false, 'after' => 'id']);
	}
}
