<?php

class Image_Model extends My_Model
{
	public function init()
	{
		$this->belongs_to('imageable', array(
			'polymorphic' => TRUE
		));
	}
}