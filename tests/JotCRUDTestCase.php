<?php

class JotCRUDTestCase extends UnitTestCase
{
	public function __construct()
	{		
		$CI =& get_instance();
		$CI->load->database();
		$CI->load->dbutil();
		
		$CI->load->model(array('blogs_model'));
	}
	
	public function setup()
	{
		$CI =& get_instance();
		
		$CI->db->truncate('blogs');
	}
	
	public function test_temporary()
	{
		$CI =& get_instance();

		$blog = $CI->blogs_model->temporary();
		$this->assertEquals(NULL, $blog->name, "Name should stay the same and return from database");
		$this->assertEquals(NULL, $blog->slug, "Slug should transform and return correctly from database");
		$this->assertEquals(NULL, $blog->description, "Description should return NULL");
		$this->assertEquals(NULL, $blog->rss_url, "RSS URL should return NULL");

		$blog = $CI->blogs_model->temporary(array(
			'name' => 'Blog #1',
			'slug' => 'blog-1'
		));
		
		$this->assertEquals('Blog #1', $blog->name, "Name should stay the same and return from database");
		$this->assertEquals('blog-1', $blog->slug, "Slug should transform and return correctly from database");
		$this->assertEquals(NULL, $blog->description, "Description should return NULL");
		$this->assertEquals(NULL, $blog->rss_url, "RSS URL should return NULL");		
	}	
	
	public function test_create()
	{
		$CI =& get_instance();
		
		$blog = $CI->blogs_model->create(array(
			'name' => 'Blog #1',
			'slug' => 'Blog #1',
		));

		$this->assertEquals(array(), $blog->errors(), 'There should be zero errors');

		$this->assertEquals('Blog #1', $blog->name, "Name should stay the same and return from database");
		$this->assertEquals('blog-1', $blog->slug, "Slug should transform and return correctly from database");
		$this->assertEquals(NULL, $blog->description, "Description should return NULL");
		$this->assertEquals(NULL, $blog->rss_url, "RSS URL should return NULL");
	}
	
	public function test_update()
	{
		$CI =& get_instance();
		
		$blog = $CI->blogs_model->create(array(
			'name' => 'Blog #1',
			'slug' => 'Blog #1',
		));	

		$blog = $CI->blogs_model->update($blog->id, array(
			'name' => 'test',
			'slug' => 'blog-1'
		));

		$this->assertEquals(array(), $blog->errors(), 'There should be zero errors');
	
		$this->assertEquals(1, $blog->id, "ID should be the same");
		$this->assertEquals('test', $blog->name, "Name should be updated");
		$this->assertEquals('blog-1', $blog->slug, "Slug should be the same");	
		
		$blog = $CI->blogs_model->update($blog->id, array(
			'id' => 3,
			'name' => 'testa',
			'slug' => 'testa'
		));

		$this->assertEquals(3, $blog->id, "ID should be updated");
		$this->assertEquals('testa', $blog->name, "Name should be updated");
		$this->assertEquals('testa', $blog->slug, "Slug should be the same");	
	}
	
	public function test_destroy_single()
	{
		$CI =& get_instance();
		
		$blog = $CI->blogs_model->create(array(
			'name' => 'Blog #1',
			'slug' => 'Blog #1',
		));	

		$blog = $CI->blogs_model->destroy($blog->id);

		$this->assertTrue($blog, 'Blog should have been deleted');
	}
	
	public function test_destroy_multiple()
	{
		$CI =& get_instance();
		
		$blog1 = $CI->blogs_model->create(array(
			'name' => 'Blog #1',
			'slug' => 'Blog #1',
		));
		
		$blog2 = $CI->blogs_model->create(array(
			'name' => 'Blog #2',
			'slug' => 'Blog #2',
		));	
				
		$result = $CI->blogs_model->destroy(array('id' => array($blog1->id, $blog2->id)));

		$this->assertTrue($result, 'Blogs should have been deleted');
	}
}