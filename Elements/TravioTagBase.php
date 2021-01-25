<?php namespace Model\Travio\Elements;

use Model\ORM\Element;

class TravioTagBase extends Element
{
	public static $table = 'travio_tags';

	public function init()
	{
		$this->has('type', [
			'type' => 'single',
			'element' => 'TravioTagType',
		]);
	}
}
