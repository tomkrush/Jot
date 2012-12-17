<?php

class JotUnitTestCase extends UnitTestCase
{
	public $migration_path = 'third_party/jot_tests/jot_record_migrations/';
	public $seed_path = false;
	
	public function __construct()
	{		
		$this->load->database();
		$this->load->dbutil();
		$this->load->dbforge();
		$this->load->helper('jot_migrations');			
		
		if ( $this->migration_path )
		{
			JotSchema::destroy();

			$migrations = new JotMigrations($this->migration_path, $this->seed_path);
			$migrations->up();			
		}
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