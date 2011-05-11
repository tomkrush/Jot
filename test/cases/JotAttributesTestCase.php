<?php

class JotAttributesTestCase extends UnitTestCase
{
	public function __construct()
	{		
		$this->load->model('blog_model');
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
}