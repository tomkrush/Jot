<?php

class JotHookTestCase extends UnitTestCase
{
	public function __construct()
	{		
		// $this->load->database();
		// $this->load->dbutil();
		
		$this->load->model('jot_hook_mock_model');
		$this->load->model('blog_hook_model');
	}
	
	public function test_simple_callback()
	{
		$blog = new Blog_Hook_Model();
		$blog->save();

		$result = $blog->hooks_called('before_create');		
		$this->assertTrue($result, 'Before create was called');

		$result = $blog->hooks_called('before_create');		
		$this->assertTrue($result, 'After create was called');

		$result = $blog->hooks_called('before_update');		
		$this->assertFalse($result, 'Before update was not called because blog is new');

		$result = $blog->hooks_called('before_update');		
		$this->assertFalse($result, 'After update was not called because blog is new');
		
		$result = $blog->hooks_called('before_save');		
		$this->assertTrue($result, 'Before save was called');
		
		$result = $blog->hooks_called('after_save');		
		$this->assertTrue($result, 'After save was called');

		$result = $blog->hooks_called('before_update');		
		$this->assertFalse($result, 'Before update was called because blog');

		$result = $blog->hooks_called('before_update');		
		$this->assertFalse($result, 'After update was called because blog');
		
		$blog->save();
	}
}