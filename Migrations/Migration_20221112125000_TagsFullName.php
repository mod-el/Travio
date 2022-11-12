<?php namespace Model\Travio\Migrations;

use Model\Db\Migration;

class Migration_20221112125000_TagsFullName extends Migration
{
	public function exec()
	{
		$this->addColumn('travio_tags_texts', 'full_name', ['null' => false]);
	}
}
