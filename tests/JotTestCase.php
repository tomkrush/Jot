<?php

class JotTestCase extends UnitTestCase
{
	public function __construct()
	{		
		$CI =& get_instance();
		$CI->load->database();
		$CI->load->dbutil();
		
		$CI->load->model(array('blogs_model', 'articles_model'));
	}
	
	public function setup()
	{
		$CI =& get_instance();

		$CI->db->truncate('blogs');
		$CI->db->truncate('articles');	
	}
	
	public function test_to_string()
	{
		$CI =& get_instance();
		
		$blog = $CI->blogs_model->create(array(
			'name' => 'Blog #2',
			'slug' => 'blog' 
		))->row();
		
		$this->assertTrue($blog, 'string is returned');

		// $article = $blog->articles->create(array(
		// 	'title' => 'Article Title 2',
		// 	'contents' => 'Testing'
		// ))->row();
	}
}