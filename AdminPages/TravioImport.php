<?php namespace Model\Travio\AdminPages;

use Model\Admin\AdminPage;

class TravioImport extends AdminPage
{
	public function customize()
	{
		$this->model->viewOptions['template-module'] = 'Travio';
		$this->model->viewOptions['template'] = 'travio-import';
	}
}
