<?php

class Type_Model extends My_Model 
{	
	public function init()
	{
		$this->transient('description');
	}
}