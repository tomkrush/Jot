<?php

class Jot_Hook_Mock_Model extends My_Model 
{
	protected function _update()
	{

	}
	
	protected function _create()
	{
		$this->new_record = FALSE;
	}
}