<?php

class Blog_Hook_Model extends My_Model 
{
	protected $hooks_called = array();
	
	public function init()
	{
		$this->before_save('check_before_save');
		$this->after_save('check_after_save');
	}
	
	protected function call_hook($name)
	{
		$this->hooks_called[$name] = TRUE;
		
		parent::call_hook($name);
	}
	
	public function hooks_called($hook)
	{
		return !!element($hook, $this->hooks_called, FALSE);
	}
	
	protected function _update() {}
	
	protected function _create()
	{
		$this->new_record = FALSE;
	}
}