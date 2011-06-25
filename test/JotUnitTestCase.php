<?php

class JotUnitTestCase extends UnitTestCase
{
	public function __construct()
	{		
		$this->load->database();
		$this->load->dbutil();
		$this->load->dbforge();
	}
	
	public function truncate()
	{
		foreach(func_get_args() as $table) {
			$this->db->truncate($table);
		}
	}
	
	public function drop()
	{
		foreach(func_get_args() as $table) {
			$this->dbforge->drop_table($table);
		}		
	}
}