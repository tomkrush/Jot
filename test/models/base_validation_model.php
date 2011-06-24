<?php

class Base_Validation_Model extends My_Model 
{	
	protected function _update() {}
	
	protected function _create()
	{
		$this->new_record = FALSE;
	}
}

class Blog_Validation_Model extends Base_Validation_Model
{
	public function init()
	{
		$this->validates('slug', 'required');
		$this->validates('title', array('required'));
		$this->validates('status', array(
			'required', 
			'valid' => array('test'=>'adf'))
		);
	}
	
	public function validate_valid($object, $attribute, $options)
	{
		return TRUE;
	}
}