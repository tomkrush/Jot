<?php

class JotRelationshipsTestCase extends UnitTestCase
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
		
		for($i = 0; $i < 3; $i++)
		{
			$CI->blogs_model->create(array(
				'name' => 'Blog #'.$i,
				'slug' => 'blog_'.$i
			));
		}
	}
	
	public function test_count()
	{
		
	}
}