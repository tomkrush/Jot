<?php

class JotRecordValidationTestCase extends UnitTestCase
{
	public function __construct()
	{
		$this->load->model('base_validation_model'); 
	}
	
	public function test_basic_validate()
	{
		$blog = new Blog_Validation_Model;
		$blog->write_attribute('slug', 'presence');
		$blog->write_attribute('title', 'test');
		$blog->write_attribute('status', 'draft');
				
		$this->assertTrue($blog->is_valid(), 'Blog should be valid');
		
		$blog->save();
	}
	
	public function test_no_validate()
	{
		$blog = new Blog_Validation_Model;
		$blog->write_attribute('slug', 'presence');
		
		$blog->save(FALSE);
		$this->assertEquals(array(), $blog->errors(), 'There should not be errors');
	}
}