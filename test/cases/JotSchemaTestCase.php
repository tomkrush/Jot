<?php

class JotSchemaTestCase extends UnitTestCase
{
	public function __construct()
	{
		$this->load->helper('jot_migrations');

		$this->load->database();
		$this->load->dbutil();
		$this->load->dbforge();
		
		JotSchema::destroy();
	}
	
	public function test_schema_version()
	{
		$this->assertEquals(0, JotSchema::version(), 'I want the version to be 0.');
		
		JotSchema::setVersion(1);
		$this->assertEquals(1, JotSchema::version(), 'I want the version to be 1.');
	}
}