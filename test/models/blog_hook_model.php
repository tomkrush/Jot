<?php

class Blog_Hook_Model extends My_Model 
{
	protected $hooks_called = array();
	
	protected function call_hook($name)
	{
		$this->hooks_called[$name] = TRUE;
		
		parent::call_hook($name);
	}
	
	public function hooks_called($hook)
	{
		return !!element($hook, $this->hooks_called, FALSE);
	}
	
	public function hooks_reset()
	{
		$this->hooks_called = array();
	}
	
	protected function _update() {}
	
	protected function _delete() {}
	
	protected function _create()
	{
		$this->new_record = FALSE;
	}
	
	protected function perform_validations()
	{
		return TRUE;
	}
}