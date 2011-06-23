<?php

class JotRecordFindTestCase extends UnitTestCase
{
	public function __construct()
	{		
		$this->load->database();
		$this->load->dbutil();
		
		$this->load->model(array('blog_model', 'article_model', 'page_model'));

		$this->db->truncate('blogs');
		$this->db->truncate('articles');
		$this->db->truncate('pages');

		$this->build();
	}
	
	public function build()
	{
		for($i = 0; $i < 20; $i++)
		{
			$this->blog_model->create(array(
				'name' => 'Blog #'.$i,
				'slug' => 'blog_'.$i
			));
		}
		
		$this->page_model->create(array(
			'name' => 'Homepage',
			'slug' => 'index'
		));
	}
	
	public function test_count()
	{
		$count = $this->blog_model->count();
		
		$this->assertEquals(20, $count, 'Specified number of rows should return');
	}
	
	public function test_exists()
	{
		$exists = $this->blog_model->exists(array(
			'name' => 'Blog #1'
		));
		
		$this->assertTrue($exists, 'Blog does exist');
	}
	
	public function test_not_exists()
	{
		$exists = $this->blog_model->exists(array(
			'name' => 'Blog'
		));
		
		$this->assertFalse($exists, 'Blog does not exist');
	}
	
	public function test_first()
	{
		$blog = $this->blog_model->first();
		$this->assertTrue($blog, 'Blog is returned');

		$blog = $this->blog_model->first(1);
		$this->assertTrue($blog, 'Blog is returned with id');
		
		$blog = $this->blog_model->first(array('name' => 'Blog #1'));
		$this->assertTrue($blog, 'Blog is returned with conditions');
	}
	
	public function test_last()
	{
		$blog = $this->blog_model->first();
		$this->assertTrue($blog, 'Blog is returned');

		$blog = $this->blog_model->last(1);
		$this->assertTrue($blog, 'Blog is returned with id');
		
		$blog = $this->blog_model->last(array('name' => 'Blog #1'));
		$this->assertTrue($blog, 'Blog is returned with conditions');		
	}
	
	public function test_all()
	{
		$blogs = $this->blog_model->all();
		$this->assertEquals(20, count($blogs), 'Blog should return specified number rows');
		
		$blogs = $this->blog_model->all(array('id <' => 4));
		$this->assertEquals(3, count($blogs), 'Blog should return specified number rows');
	}
	
	public function test_find()
	{
		$blogs = $this->blog_model->find(NULL, 0, 20);
		$this->assertEquals(20, count($blogs), 'Blog should return specified number rows');

		$blogs = $this->blog_model->find(NULL, 0, 10);
		$this->assertEquals(10, count($blogs), 'Limit affects return');

		$blogs = $this->blog_model->find(array('id <' => 7), 1, 5);
		$this->assertEquals(5, count($blogs), 'Condition and limit will affect returned result');
	}
}