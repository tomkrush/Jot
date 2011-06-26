<?php

class JotMigrationTestCase extends UnitTestCase
{
	protected $sample_path = 'third_party/jot/test/sample_migrations/';
	protected $sample_seed_path = 'third_party/jot/test/seeds/sample.php';

	public function __construct()
	{
		$this->load->helper('jot_migrations');

		$this->load->database();
		$this->load->dbutil();
		$this->load->dbforge();
		
		JotSchema::destroy();
	}
	
	public function test_create()
	{
		$migrations = new JotMigrations($this->sample_path, $this->sample_seed_path);
		
		$migration = $migrations->create('CreateTableBlog');
		
		$this->assertTrue($migration, 'I want a migration to be created');
		
		unlink($migration);
	}
	
	public function test_list()
	{		
		$migrations = new JotMigrations($this->sample_path, $this->sample_seed_path);
		
		$list = $migrations->list_migrations();
		
		$this->assertEquals(2, count($list), 'I want to find 2 migrations');
	}
	
	public function test_up()
	{
		$migrations = new JotMigrations($this->sample_path, $this->sample_seed_path);
		
		$this->assertEquals(2, $migrations->up(), 'I want 2 migrations to run');
		$this->assertEquals(0, $migrations->up(), 'I want no migrations to run');
	}
}