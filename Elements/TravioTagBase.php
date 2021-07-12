<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioTagBase extends Element
{
	public static $table = 'travio_tags';

	public function init()
	{
		$this->has('sub', [
			'element' => 'TravioTag',
			'field' => 'parent',
		]);

		$this->belongsTo('TravioTag', [
			'field' => 'parent',
			'children' => 'sub',
		]);
	}
}
