<?php

class Person_Model extends My_Model
{
	public function init()
	{
		$this->has_one('image', array(
			'as' => 'imageable'
		));
	}
}