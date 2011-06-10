<?php

class JotArrayHelperTestCase extends UnitTestCase
{
	public function __construct()
	{
		$this->load->helper('array_helper');
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
	
	public function test_single_level_element()
	{
		$assoc = array(
			'name' => 'John'
		);
		
		$this->assertEquals('John', element('name', $assoc));
	}
	
	public function test_single_level_no_element()
	{
		$assoc = array();
	
		$this->assertFalse(element('name', $assoc));
	}
	
	public function test_default_value_element()
	{
		$person = array();
		
		$this->assertEquals('John Doe', element('name', $person, 'John Doe'), 'Used default value because no value was found');
	}
	
	public function test_no_index_element()
	{
		$indexed = array(1);
		
		$this->assertFalse(element('name', $indexed), 'Can not find indexed elements');
	}
	
	public function test_deep_element()
	{
		$assoc = array(
			'person' => array(
				'name' => array(
					'first' => 'John',
					'last'  => 'Doe'
				)
			)
		);
		
		$this->assertEquals('John', element('person.name.first', $assoc), 'Found deep element');
	}
}