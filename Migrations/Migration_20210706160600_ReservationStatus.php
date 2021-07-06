<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210706160600_ReservationStatus extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_orders', 'initial_status', ['type' => 'tinyint']);
	}
}
