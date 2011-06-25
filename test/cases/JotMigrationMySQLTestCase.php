<?php

require_once APPPATH.'third_party/jot/test/JotUnitTestCase.php';

class JotMigrationMySQLTestCase extends JotUnitTestCase
{
	public function setup()
	{
		$this->drop('blog');
	}
	
	public function test_create_table()
	{
		
	}
}