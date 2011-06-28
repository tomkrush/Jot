<?php

require_once APPPATH.'third_party/jot/test/JotUnitTestCase.php';

class JotRecordPersistanceTestCase extends JotUnitTestCase
{
	public function __construct()
	{		
		parent::__construct();
		
		$this->load->model(array('blog_model'));
	}
	
	public function setup()
	{
		$this->truncate('blogs');
	}
	
	public function test_build()
	{
		$blog = $this->blog_model->build();
		$blog->name = "Blog";
		
		$this->assertEquals("Blog", $blog->name, "Name should stay the same and return from database");
		$this->assertEquals(NULL, $blog->slug, "Slug should transform and return correctly from database");
		$this->assertEquals(NULL, $blog->description, "Description should return NULL");
		$this->assertEquals(NULL, $blog->rss_url, "RSS URL should return NULL");

		$blog = $this->blog_model->build(array(
			'name' => 'Blog #1',
			'slug' => 'blog-1'
		));
		
		$this->assertEquals('Blog #1', $blog->name, "Name should stay the same and return from database");
		$this->assertEquals('blog-1', $blog->slug, "Slug should transform and return correctly from database");
		$this->assertEquals(NULL, $blog->description, "Description should return NULL");
		$this->assertEquals(NULL, $blog->rss_url, "RSS URL should return NULL");		
	}
		
	public function test_save()
	{
		$blog = $this->blog_model->build(array(
			'name' => "Testing",
			'slug' => "slug"
		));

		$this->assertFalse($blog->persisted(), "Should be a new record");
		$blog->save();

		$this->assertTrue($blog->persisted(), "Should be a persisted record");

		$this->assertEquals('Testing', $blog->name, "Name should stay the same and return from database");
		$this->assertEquals('slug', $blog->slug, "Slug should transform and return correctly from database");
		$this->assertEquals(NULL, $blog->description, "Description should return NULL");
		$this->assertEquals(NULL, $blog->rss_url, "RSS URL should return NULL");
		

		$blog->description = "testing";
		$blog->save();
		$this->assertEquals('testing', $blog->description, "Description should return string");		
	}
	
	public function test_reload() {
		$blog = $this->blog_model->create(array(
			'name' => 'Test title'
		));
		
		$name = $blog->name;
		
		$blog->name = NULL;
		
		$this->assertNotEquals($name, $blog->name, 'Attribute changed');
		
		$blog->reload();

		$this->assertEquals('Test title', $blog->name, 'Attribute reloaded');
	}
	
	public function test_create()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #1',
			'slug' => 'blog',
		));

		$this->assertEquals(array(), $blog->errors(), 'There should be zero errors');

		$this->assertEquals('Blog #1', $blog->name, "Name should stay the same and return from database");
		$this->assertEquals('blog', $blog->slug, "Slug should transform and return correctly from database");
		$this->assertEquals(NULL, $blog->description, "Description should return NULL");
		$this->assertEquals(NULL, $blog->rss_url, "RSS URL should return NULL");
	}
	
	public function test_update()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #1',
			'slug' => 'Blog #1',
		));	
		
		$blog = $this->blog_model->update($blog->id, array(
			'name' => 'test',
			'slug' => 'blog-1'
		));

		$this->assertEquals(array(), $blog->errors(), 'There should be zero errors');
		$this->assertEquals("1", $blog->id, "ID should be the same");
		$this->assertEquals('test', $blog->name, "Name should be updated");
		$this->assertEquals('blog-1', $blog->slug, "Slug should be the same");
	}
	
	public function test_timestamps()
	{
		$blog = $this->blog_model->build(array(
			'name' => 'Blog #1'
		));

		$this->assertFalse($blog->read_attribute('created_at'), 'Created at timestamp does not exist');
		$this->assertFalse($blog->read_attribute('updated_at'), 'Updated at timestamp does not exist');
		
		$blog->save();
		
		$created_at = $blog->read_attribute('created_at');
		$updated_at = $blog->read_attribute('updated_at');
		
		$this->assertTrue($created_at, 'Created at timestamp does not exist');
		$this->assertTrue($updated_at, 'Updated at timestamp does not exist');		

		// $blog->save();
		
		// $this->assertEquals($created_at, $blog->read_attribute('created_at'), 'Created at timestamp not have changed.');
		// $this->assertNotEquals($updated_at, $blog->read_attribute('updated_at'), 'Updated at timestamp should have changed.');
	}
	
	public function test_update_attribute()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #1',
			'slug' => 'Blog #1',
		));
		
		$name = $blog->name;
		
		$blog->update_attribute('name', 'Blog #2');
		
		$new_blog = $this->blog_model->first($blog->id);
		
		$this->assertNotEquals($name, $new_blog->name, 'Update attribute should have changed the value and saved to database');
	}
	
	public function test_update_attributes()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #1',
			'slug' => 'Blog #1',
		));

		$name = $blog->name;
		$slug = $blog->slug;
		
		$blog->update_attributes(array(
			'name' => 'Blog',
			'slug' => 'slug'
		));
		
		$new_blog = $this->blog_model->first($blog->id);
		
		$this->assertNotEquals($name, $new_blog->name, 'Update attribute should have changed the value and saved to database');
		$this->assertNotEquals($slug, $new_blog->slug, 'Update attribute should have changed the value and saved to database');
	}
	
	public function test_destroy_single()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #1',
			'slug' => 'Blog #1',
		));	

		$blog->destroy();

		$this->assertFalse($blog->persisted(), 'Object should be destroyed');
	}
	
	public function test_destroy_multiple()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #1',
			'slug' => 'Blog #1',
		));
		
		$blog2 = $this->blog_model->create(array(
			'name' => 'Blog #2',
			'slug' => 'Blog #2',
		));	
			
		$blogs = $this->blog_model->destroy(array($blog->id, $blog2->id));	

		$this->assertFalse($blogs[0]->persisted(), 'Object should be destroyed');
		$this->assertFalse($blogs[1]->persisted(), 'Object should be destroyed');
	}
}