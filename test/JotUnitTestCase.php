<?php

class JotUnitTestCase extends UnitTestCase
{
	public function __construct()
	{		
		$this->load->database();
		$this->load->dbutil();
	}
	
	public function truncate()
	{
		foreach(func_get_args() as $table) {
			$this->db->truncate($table);
		}
	}
}