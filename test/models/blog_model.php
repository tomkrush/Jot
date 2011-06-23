<?php

class Blog_Model extends My_Model 
{	
	public function init()
	{
		$this->has_one('page');
		$this->has_many('articles');
	}
	
	public function get_popularity()
	{
		return 10;
	}
	
	public function set_category($attribute, $value)
	{
		$this->write_attribute($attribute, strtoupper($value));
	}
}