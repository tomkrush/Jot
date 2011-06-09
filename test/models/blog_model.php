<?php

class Blog_Model extends My_Model 
{	
	public function init()
	{
		$this->has_one('page');
		$this->has_many('articles');
	}
}