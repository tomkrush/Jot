<?php

class JotRecordSerializationTestCase extends UnitTestCase
{	
	public function __construct()
	{
		$this->load->model('blog_serialize_model');
	}
	
	public function test_serialize()
	{
		$blog = new Blog_Serialize_Model;
				
		$blog->is_valid();
		
		$this->assertTrue($blog->errors(), 'Errors exist');

		$blog->title = "test";

		$this->assertEquals('test', $blog->title, 'Title exists');
				
		$serialize = serialize($blog);

		$string = 'C:20:"Blog_Serialize_Model":169:{a:4:{s:6:"errors";a:1:{i:0;a:2:{i:0;s:5:"title";i:1;s:17:"Title is required";}}s:10:"attributes";a:1:{s:5:"title";s:4:"test";}s:10:"new_record";b:1;s:9:"destroyed";b:0;}}';
			
		$this->assertEquals($string, $serialize, 'Serialized success');	
				
		unset($blog);

		$blog = unserialize($serialize);
		
		$this->assertEquals('test', $blog->title, 'Title exists');
		$this->assertTrue($blog->errors(), 'Errors exist');
		
		$blog->is_valid();

		$this->assertFalse($blog->errors(), 'Errors do not exist');
	}
}