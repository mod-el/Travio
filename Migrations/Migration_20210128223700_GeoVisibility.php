<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20210128223700_GeoVisibility extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_geo', 'visible', ['type' => 'tinyint', 'null' => false, 'default' => 1]);
	}
}
