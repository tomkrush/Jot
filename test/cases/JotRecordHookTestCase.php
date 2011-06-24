<?php

class JotRecordHookTestCase extends UnitTestCase
{
	public function __construct()
	{				
		$this->load->model('blog_hook_model');
	}
	
	public function test_simple_hooks()
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

		$result = $blog->hooks_called('before_validation');		
		$this->assertTrue($result, 'Before validation was called');

		$result = $blog->hooks_called('after_validation');		
		$this->assertTrue($result, 'After validation was called');
	}
	
	public function test_update_hooks()
	{
		$blog = new Blog_Hook_Model();
		$blog->save();
		
		$blog->reset_hooks();
		
		$blog->save(FALSE);
		
		$result = $blog->hooks_called('before_update');		
		$this->assertTrue($result, 'Before update was called because blog existed');

		$result = $blog->hooks_called('after_update');		
		$this->assertTrue($result, 'After update was called because blog existed');		
	}
	
	public function test_destroy_hooks()
	{
		$blog = new Blog_Hook_Model();
		$blog->save();
		$blog->destroy();
		
		$result = $blog->hooks_called('before_destroy');		
		$this->assertTrue($result, 'Before destroy was called');

		$result = $blog->hooks_called('after_destroy');		
		$this->assertTrue($result, 'After destroy was called');		
	}
	
	public function test_validation_hooks()
	{
		$blog = new Blog_Hook_Model();
		$blog->save(FALSE);
		
		$blog->reset_hooks();
		
		$blog->save(FALSE);

		$result = $blog->hooks_called('before_validation');	
		$this->assertFalse($result, 'Before validation was not called');

		$result = $blog->hooks_called('after_validation');		
		$this->assertFalse($result, 'After validation was not called');
	}
}