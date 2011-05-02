<?php

class JotFindTestCase extends UnitTestCase
{
	public function __construct()
	{		
		$CI =& get_instance();
		$CI->load->database();
		$CI->load->dbutil();
		
		$CI->load->model(array('blogs_model', 'articles_model'));

		$CI->db->truncate('blogs');
		$CI->db->truncate('articles');

		$this->build();
	}
	
	public function build()
	{
		$CI =& get_instance();
		
		for($i = 0; $i < 20; $i++)
		{
			$CI->blogs_model->create(array(
				'name' => 'Blog #'.$i,
				'slug' => 'blog_'.$i
			));
		}
	}
	
	public function test_count()
	{
		$CI =& get_instance();

		$count = $CI->blogs_model->count();
		
		$this->assertEquals(20, $count, 'Specified number of rows should return');
	}
	
	public function test_exists()
	{
		$CI =& get_instance();

		$exists = $CI->blogs_model->exists(array(
			'name' => 'Blog #1'
		));
		
		$this->assertTrue($exists, 'Blog does exist');
	}
	
	public function test_not_exists()
	{
		$CI =& get_instance();

		$exists = $CI->blogs_model->exists(array(
			'name' => 'Blog'
		));
		
		$this->assertFalse($exists, 'Blog does not exist');
	}
	
	public function test_first()
	{
		$CI =& get_instance();

		$blog = $CI->blogs_model->first();
		$this->assertTrue($blog, 'Blog is returned');

		$blog = $CI->blogs_model->first(1);
		$this->assertTrue($blog, 'Blog is returned with id');
		
		$blog = $CI->blogs_model->first(array('name' => 'Blog #1'));
		$this->assertTrue($blog, 'Blog is returned with conditions');
	}
	
	public function test_last()
	{
		$CI =& get_instance();

		$blog = $CI->blogs_model->first();
		$this->assertTrue($blog, 'Blog is returned');

		$blog = $CI->blogs_model->last(1);
		$this->assertTrue($blog, 'Blog is returned with id');
		
		$blog = $CI->blogs_model->last(array('name' => 'Blog #1'));
		$this->assertTrue($blog, 'Blog is returned with conditions');		
	}
	
	public function test_all()
	{
		$CI =& get_instance();

		$blogs = $CI->blogs_model->all();
		$this->assertEquals(20, count($blogs), 'Blog should return specified number rows');
		
		$blogs = $CI->blogs_model->all(array('id <' => 4));
		$this->assertEquals(3, count($blogs), 'Blog should return specified number rows');
	}
	
	public function test_find()
	{
		$CI =& get_instance();

		$blogs = $CI->blogs_model->find(NULL, 1, 20);
		$this->assertEquals(20, count($blogs), 'Blog should return specified number rows');

		$blogs = $CI->blogs_model->find(NULL, 1, 10);
		$this->assertEquals(10, count($blogs), 'Limit affects return');

		$blogs = $CI->blogs_model->find(array('id <' => 7), 1, 5);
		$this->assertEquals(5, count($blogs), 'Condition and limit will affect returned result');
	}
}