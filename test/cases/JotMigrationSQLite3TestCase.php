<?php

class JotMigrationSQLite3TestCase extends UnitTestCase
{	
	public function __construct()
	{
		$this->load->helper('jot_migrations');

		$this->load->database();
		$this->load->dbutil();
		$this->load->dbforge();
		
		JotSchema::destroy();
	}
	
	public function setup()
	{
		$this->dbforge->drop_table('blogs');
		$this->dbforge->drop_table('news');
	}

	public function test_get_type_and_constraint()
	{
		list($type, $constraint) = _migration_get_type_and_constraint('string');
		$this->assertEquals('varchar', $type, 'Type is correct');
		$this->assertEquals('255', $constraint, 'Contraint is correct');
		
		list($type, $constraint) = _migration_get_type_and_constraint('integer');
		$this->assertEquals('integer', $type, 'Type is correct');
		$this->assertEquals(NULL, $constraint, 'Contraint is correct');
		
		list($type, $constraint) = _migration_get_type_and_constraint('float');
		$this->assertEquals('float', $type, 'Type is correct');
		$this->assertEquals(NULL, $constraint, 'Contraint is correct');
	}
	
	public function test_create_table()
	{
		create_table('blogs', array(
			array('name' => 'name', 'type' => 'string', 'NOT_NULL' => false),
			array('name' => 'description', 'type' => 'string'),
		));
		
		$expects = "CREATE TABLE `blogs` (id integer NOT NULL,name varchar(255) NULL,description varchar(255) NULL,PRIMARY KEY (id));";
		$actual = str_replace(array("\n", "\t"), "", $this->db->last_query());
		
		$this->assertEquals($expects, $actual, 'Table created successfully');
	}
	
	public function test_create_with_custom_primary_key()
	{
		create_table('blogs', array(
			array('name' => 'blog_id', 'type' => 'integer', 'primary_key' => TRUE, 'auto_increment'=> TRUE),
			array('name' => 'name', 'type' => 'string', 'NOT_NULL' => false),
			array('name' => 'description', 'type' => 'string'),
		), array('primary_key'=>'blog_id'));
	
		$expects = "CREATE TABLE `blogs` (blog_id integer NULL,name varchar(255) NULL,description varchar(255) NULL,PRIMARY KEY (blog_id));";
		$actual = str_replace(array("\n", "\t"), "", $this->db->last_query());
		
		$this->assertEquals($expects, $actual, 'Table created successfully');
	}
	
	public function test_create_without_primary_key()
	{
		create_table('blogs', array(
			array('name' => 'name', 'type' => 'string', 'NOT_NULL' => false),
			array('name' => 'description', 'type' => 'string'),
		), array('primary_key'=>FALSE));
		
		$expects = "CREATE TABLE `blogs` (name varchar(255) NULL,description varchar(255) NULL);";
		$actual = str_replace(array("\n", "\t"), "", $this->db->last_query());
						
		$this->assertEquals($expects, $actual, 'Table created successfully');		
	}
	
	public function test_create_table_with_timestamps()
	{
		create_table('blogs', array(
			array('name' => 'name', 'type' => 'string', 'NOT_NULL' => false),
			array('name' => 'description', 'type' => 'string'),
			MIGRATION_TIMESTAMPS
		));	
		
		$expects = "CREATE TABLE `blogs` (id integer NOT NULL,name varchar(255) NULL,description varchar(255) NULL,created_at integer NOT NULL,updated_at integer NOT NULL,PRIMARY KEY (id));";
		$actual = str_replace(array("\n", "\t"), "", $this->db->last_query());
		
		$this->assertEquals($expects, $actual, 'Table created successfully with timestamps');	
	}
	
	public function test_drop_table()
	{
		create_table('blogs', array(
			array('name' => 'name', 'type' => 'string', 'NOT_NULL' => false),
			array('name' => 'description', 'type' => 'string'),
		));
		
		drop_table('blogs');
		
		$expects = "DROP TABLE IF EXISTS `blogs`";
		$actual = $this->db->last_query();
		
		$this->assertEquals($expects, $actual, 'Table was dropped');		
	}
	
	public function test_rename_table()
	{
		create_table('blogs', array(
			array('name' => 'name', 'type' => 'string', 'NOT_NULL' => false),
			array('name' => 'description', 'type' => 'string'),
		));
		
		rename_table('blogs', 'news');
		
		$expects = "ALTER TABLE blogs RENAME TO news";
		$actual = $this->db->last_query();
		
		$this->assertEquals($expects, $actual, 'Table was renamed');	
	}
	
	public function test_create_column()
	{
		create_table('blogs', array(
			array('name' => 'name', 'type' => 'string', 'NOT_NULL' => false),
			array('name' => 'description', 'type' => 'string'),
		));
		
		create_column('blogs', array(
			'name' => 'slug',
			'type' => 'string'
		));
		
		$expects = "ALTER TABLE blogs ADD slug varchar(255) NULL";
		$actual = str_replace(array("\n", "\t"), "", $this->db->last_query());
	
		$this->assertEquals($expects, $actual, 'Column was created');
	}
	
	public function test_change_column()
	{
		// create_table('blogs', array(
		// 	array('name' => 'name', 'type' => 'string', 'NOT_NULL' => false),
		// 	array('name' => 'description', 'type' => 'string'),
		// ));
		// 
		// change_column('blogs', 'name', array(
		// 	'name' => 'title',
		// 	'type' => 'string',
		// 	'NOT_NULL' => FALSE
		// ));	
		// 
		// $expects = "ALTER TABLE blogs CHANGE name name  varchar(255) NULL";
		// $actual = str_replace(array("\n", "\t"), "", $this->db->last_query());
	
		// $this->assertEquals($expects, $actual, 'Column was updated');
		
		$this->assertTrue(FALSE, 'I want change column to work with sqlite3');
	}
	
	public function test_drop_column()
	{
		$this->assertTrue(FALSE, 'I want drop column to work with sqlite3');
	}
	
	// public function test_drop_column()
	// {
	// 	create_table('blogs', array(
	// 		array('name' => 'name', 'type' => 'string', 'NOT_NULL' => false),
	// 		array('name' => 'description', 'type' => 'string'),
	// 	));
	// 	
	// 	drop_column('blogs', 'description');
	// 	
	// 	$expects = "ALTER TABLE `blogs` DROP `description`";
	// 	$actual = $this->db->last_query();
	// 	
	// 	$this->assertEquals($expects, $actual, 'Table column was dropped');		
	// }
}