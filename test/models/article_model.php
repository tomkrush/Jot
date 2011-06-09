<?php

class Article_Model extends My_Model 
{	
	public function init()
	{
		$this->belongs_to('blog');
	}
}