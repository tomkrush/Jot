<?php

class JotRecordNestedAttributesTestCase extends UnitTestCase
{
	public function __construct()
	{
		$this->load->database();
		$this->load->dbutil();
		
		$this->load->model(array(
			'blog_model', 
			'article_model', 
			'page_model',
			'company_model',
			'person_model',
			'image_model'
		));
	}
	
	public function setup()
	{
		$this->db->truncate('blogs');
		$this->db->truncate('articles');	
		$this->db->truncate('pages');	

		$this->db->truncate('companies');
		$this->db->truncate('people');	
		$this->db->truncate('images');
	}

	public function test_create_has_many()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #2',
			'articles_attributes' => array(
				array('title' => 'Article 1'),
				array('title' => 'Article 2')
			)
		));
		
		$this->assertEquals(2, $blog->articles->count(), 'Articles were created');
	}
	
	public function test_update_has_many()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #2'
		));
		
		$article = $this->article_model->create(array('title'=>'Article 1'));
		$article->blog = $blog;
		
		$blog->assign_attributes(array(
			'articles_attributes' => array(
				array('id' => 1, 'title'=>'Updated Title')
			)
		));
		
		$blog->save();
		
		$article->reload();
				
		$this->assertEquals('Updated Title', $article->title, 'Objects can be updated');
	}
	
	public function test_create_has_one()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #2',
			'page_attributes' => array('name' => 'Page')
		));
		
		$this->assertEquals('Page', $blog->page->name, 'Has One nested works');		
	}

	public function test_update_has_one()
	{
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #2',
		));

		$page = $this->page_model->create(array(
			'name' => 'Page'
		));

		$page->blog = $blog;
		$page->save();
		
		$blog->assign_attributes(array(
			'page_attributes' => array('id'=>$page->id, 'name'=>'Updated Page')
		));
		
		$blog->save();
		
		$this->assertEquals('Updated Page', $blog->page->name, 'Has One update nested works');		
	}
	
	public function test_create_belongs_to()
	{
		$page = $this->page_model->create(array(
			'name' => 'Page',
			'blog_attributes' => array('name' => 'Blog #1')
		));
		
		$this->assertEquals('Blog #1', $page->blog->name, 'Belongs to nested works');		
	}
	
	public function test_update_belongs_to()
	{
		$page = $this->page_model->create(array(
			'name' => 'Page'
		));
		
		$blog = $this->blog_model->create(array(
			'name' => 'Blog #2',
		));

		$blog->page = $page;
		$blog->save();
		
		$page->assign_attributes(array(
			'blog_attributes' => array('id'=>$blog->id, 'name'=>'Updated Blog')
		));
		
		$page->save();
		
		$this->assertEquals('Updated Blog', $page->blog->name, 'Belongs to update nested works');		
	}

}