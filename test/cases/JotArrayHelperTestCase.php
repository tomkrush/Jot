<?php

class JotArrayHelperTestCase extends UnitTestCase
{
	public function __construct()
	{
		$this->load->helper('jot_array');
	}
	
	public function test_is_assoc()
	{
		$assoc = array(
			'name' => 'John Doe',
			'description' => 'Lorem Ipsum'
		);
		
		$indexed = array(1, 2, 3, 4, 5);
		
		$this->assertTrue(is_assoc($assoc), 'Is associated array');
		$this->assertFalse(is_assoc($index), 'Is not associated array');
	}
	
	public function test_single_level_value_for_key()
	{
		$assoc = array(
			'name' => 'John'
		);
		
		$this->assertEquals('John', value_for_key('name', $assoc));
	}
	
	public function test_single_level_no_value_for_key()
	{
		$assoc = array();
	
		$this->assertFalse(value_for_key('name', $assoc));
	}
	
	public function test_default_value_value_for_key()
	{
		$person = array();
		
		$this->assertEquals('John Doe', value_for_key('name', $person, 'John Doe'), 'Used default value because no value was found');
	}
	
	public function test_no_index_value_for_key()
	{
		$indexed = array(1);
		
		$this->assertFalse(value_for_key('name', $indexed), 'Can not find indexed elements');
	}
	
	public function test_deep_value_for_key()
	{
		$assoc = array(
			'person' => array(
				'name' => array(
					'first' => 'John',
					'last'  => 'Doe'
				)
			)
		);
		
		$this->assertEquals('John', value_for_key('person.name.first', $assoc), 'Found deep element');
	}
}