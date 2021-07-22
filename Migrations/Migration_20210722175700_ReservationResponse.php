<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210722175700_ReservationResponse extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_orders', 'response', ['type' => 'text']);
	}
}
