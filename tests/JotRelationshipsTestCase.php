<?php

class JotRelationshipsTestCase extends UnitTestCase
{
	public function __construct()
	{
		$this->load->database();
		$this->load->dbutil();
		
		$this->load->model(array('blogs_model', 'articles_model', 'pages_model'));
	}
	
	public function setup()
	{
		$this->db->truncate('blogs');
		$this->db->truncate('articles');	
		$this->db->truncate('pages');	
	}
	
	public function test_has_one_relationship()
	{
		$blog = $this->blogs_model->create(array(
			'name' => 'Blog #2',
			'slug' => 'blog' 
		));
		
		$page = $blog->create_page(array(
			'name' => 'Page',
			'slug' => 'blog-page'
		));	
				
		$this->assertEquals('blog', $page->blog->slug, 'Slugs should be the same');
		$this->assertEquals('Blog #2', $page->blog->name, 'Names should be the same');		
	}

	public function test_belongs_to_relationship()
	{
		$page = $this->pages_model->create(array(
			'name' => 'Page',
			'slug' => 'page' 
		));
				
		$blog = $page->create_blog(array(
			'name' => 'blog',
			'slug' => 'blog'
		));	
						
		$this->assertEquals('page', $blog->page->slug, 'Slugs should be the same');
		$this->assertEquals('Page', $blog->page->name, 'Names should be the same');		
	}
	
	public function test_has_many_relationship()
	{	
		$blog = $this->blogs_model->create(array(
			'name' => 'Blog #2',
			'slug' => 'blog' 
		));
		
		$article = $blog->articles->create(array(
			'title' => 'Article Title',
			'contents' => 'Testing'
		));
		
		$this->assertEquals('blog', $article->blog->slug, 'Slugs should be the same');
		$this->assertEquals('Blog #2', $article->blog->name, 'Names should be the same');
		
		$article = $this->articles_model->first();
		$this->assertEquals('blog', $article->blog->slug, 'Slug should be the correct');
		
		$article2 = $blog->articles->create(array(
			'title' => 'Article Title 2',
			'contents' => 'Testing'
		));
		
		$this->assertEquals(2, count($blog->articles->all()), 'Correct number of articles returned');
	}
	
	public function test_chained_relationships()
	{
		$page = $this->pages_model->create(array(
			'name' => 'Page',
			'slug' => 'Slug'
		));
		
		$this->assertTrue($page, 'Page should exist');
		
		$blog = $page->create_blog(array(
			'name' => 'Blog',
			'slug' => 'blog'
		));
		
		$this->assertTrue($blog, 'Blog should exist');
		
		$article = $blog->articles->create(array(
			'title' => 'test',
			'contents' => 'testing the article'
		));	
						
		$this->assertTrue($article, 'Article should exist');
		
		$this->assertEquals('Page', $article->blog->page->name, 'Page name is correct');	
	}
}