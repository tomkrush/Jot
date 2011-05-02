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
	}
	
	public function test_has_many_relationship()
	{
		$CI =& get_instance();
		
		$blog = $CI->blogs_model->create(array(
			'name' => 'Blog #2',
			'slug' => 'blog' 
		));
		
		$article = $blog->articles->create(array(
			'title' => 'Article Title',
			'contents' => 'Testing'
		));
		
		$this->assertEquals('blog', $article->blog->slug, 'Slugs should be the same');
		$this->assertEquals('Blog #2', $article->blog->name, 'Slugs should be the same');
		
		$article = $CI->articles_model->first();
		$this->assertEquals('blog', $article->blog->slug, 'Slug should be the correct');
		
		$article2 = $blog->articles->create(array(
			'title' => 'Article Title 2',
			'contents' => 'Testing'
		));
		
		$this->assertEquals(2, count($blog->articles->all()), 'Correct number of articles returned');
	}
}