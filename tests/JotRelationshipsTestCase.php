<?php

class JotRelationshipsTestCase extends UnitTestCase
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
		
		// for($i = 0; $i < 3; $i++)
		// {
		// 	$CI->blogs_model->create(array(
		// 		'name' => 'Blog #'.$i,
		// 		'slug' => 'blog_'.$i
		// 	));
		// }		
	}
	
	public function test_has_many_relationship()
	{
		$CI =& get_instance();
		
		$blog = $CI->blogs_model->create(array(
			'name' => 'Blog #2',
			'slug' => 'blog' 
		))->row();
		
		$article = $blog->articles->create(array(
			'title' => 'Article Title',
			'contents' => 'Testing'
		))->row();
		
		// $article = $blog->articles->create(array(
		// 	'title' => 'Article Title 2',
		// 	'contents' => 'Testing'
		// ))->row();
	}
}