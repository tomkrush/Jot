<?php

class JotAttributesTestCase extends UnitTestCase
{
	public function __construct()
	{		
		$this->load->model('blog_model');
		$this->load->model('type_model');
	}
	
	public function test_read_and_write_attribute()
	{			
		$blog = $this->blog_model->build();

		$blog->write_attribute('name', 'Blog');
		
		$this->assertEquals('Blog', $blog->read_attribute('name'), 'Attribute should write & read correctly');
	}
	
	public function test_write_and_write_attributes_using_get_magic_method()
	{
		$blog = $this->blog_model->build();
		
		$blog->name = "Blog";
		
		$this->assertEquals('Blog', $blog->name, 'Attribute reads & writes correctly using get magic method');
	}
	
	public function test_has_attribute()
	{
		$blog = $this->blog_model->build(array(
			'name' => 'Blog'
		));
		
		$this->assertTrue($blog->has_attribute('name'), 'Blog attribute should exist');
		$this->assertFalse($blog->has_attribute('slug'), 'Blog attribute should exist');
	}
	
	public function test_attribute_integer()
	{
		$object = new Type_Model;
		$object->count = 5;
		$object->save();
		
		$object->reload();
		
		$type = is_int($object->count);
		
		$this->assertTrue($type, 'Attribute is integer');
	}
	
	public function test_attribute_string()
	{
		$object = new Type_Model;
		$object->name = "Untitled Object";
		$object->save();
		
		$object->reload();
		
		$type = is_string($object->name);
		
		$this->assertTrue($type, 'Attribute is string');		
	}
	
	public function test_attribute_boolean()
	{
		$object = new Type_Model;
		$object->is_flag = TRUE;
		$object->save();
		
		$object->reload();
		
		$type = is_bool($object->is_flag);
		
		$this->assertTrue($type, 'Attribute is boolean');		
	}
}