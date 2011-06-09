<?php

class JotRecordAssociationTestCase extends UnitTestCase
{
	public function __construct()
	{
		$this->load->database();
		$this->load->dbutil();
		
		$this->load->model(array('blog_model', 'article_model', 'page_model'));
	}
	
	public function setup()
	{
		$this->db->truncate('blogs');
		$this->db->truncate('articles');	
		$this->db->truncate('pages');	
	}
	
	public function test_has_one_association()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog',
			'slug' => 'blog'
		));
		
		$page = new Page_Model(array(
			'name' => 'Page Name',
			'description' => 'Lorem ipsum dolor sit amet...'
		));
						
		$blog->page = $page;
		
		$page->save();

		$this->assertTrue(@$blog->page, 'Association exists');
		$this->assertEquals('Lorem ipsum dolor sit amet...', @$blog->page->description, 'Contents should be correct');
	}

	public function test_belongs_to_association()
	{
		$page = $this->page_model->create(array(
			'name' => 'Page',
			'slug' => 'page' 
		));
				
		$blog = new Blog_Model(array(
			'name' => 'blog',
			'slug' => 'blog'
		));	
		$blog->save();

		$blog2 = new Blog_Model(array(
			'name' => 'blog2',
			'slug' => 'blog2'
		));	
		$blog2->save();

		$page->blog = $blog2;

		$this->assertEquals('blog2', $page->blog->name, 'Names should be the same');
		$this->assertEquals('blog2', $page->blog->slug, 'Slugs should be the same');	
		
	}
	
	public function test_chained_associations()
	{
		$page = $this->page_model->create(array(
			'name' => 'Page',
			'slug' => 'Slug'
		));

		$this->assertTrue($page, 'Page should exist');
		
		$blog = $page->create_blog(array(
			'name' => 'Blog',
			'slug' => 'blog'
		));
		
		$this->assertTrue($blog, 'Blog should exist');
	}

	
	public function test_has_many_association()
	{	
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #2',
			'slug' => 'blog' 
		));
		
		$article = $this->article_model->create(array(
			'title' => 'Lorem Ipsum'
		));

		$article2 = $this->article_model->create(array(
			'title' => 'Dolar'
		));

		$article3 = $this->article_model->create(array(
			'title' => 'Ipsum'
		));
		
		$blog->articles = array($article, $article2);

		$this->assertEquals(2, count($blog->articles->all()), 'Correct number of articles returned');
	}
	
}