<?php

class Company_Model extends My_Model
{
	public function init()
	{
		$this->has_many('images', array(
			'as' => 'imageable'
		));
	}
}