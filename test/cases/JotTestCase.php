<?php

class JotTestCase extends UnitTestCase
{
	public function __construct()
	{		
		$this->load->database();
		$this->load->dbutil();
		
		$this->load->model(array('blog_model', 'articles_model'));
	}
	
	public function setup()
	{
		$this->db->truncate('blogs');
		$this->db->truncate('articles');	
	}
	
	public function test_to_string()
	{		
		$blog = $this->blog_model->build(array(
			'name' => 'Blog #2',
			'slug' => 'blog' 
		));
		
		$this->assertTrue($blog, 'string is returned');
	}
	
	public function test_inflection()
	{		
		$blog = $this->blog_model->build(array(
			'name' => 'Blog #2',
			'slug' => 'blog' 
		));
		
		$this->assertEquals('blog', $blog->singularTableName(), 'Should be singluar');
		$this->assertEquals('blogs', $blog->pluralTableName(), 'Should be singluar');
	}
}