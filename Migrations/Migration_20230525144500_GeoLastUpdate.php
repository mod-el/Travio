<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20230525144500_GeoLastUpdate extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_geo', 'last_update', ['type' => 'datetime']);
	}
}
