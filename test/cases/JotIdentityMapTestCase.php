<?php

class JotIdentityMapMock
{
	protected $id;
	
	public function __construct($id)
	{
		$this->id = $id;
	}
	
	public function read_attribute()
	{
		return $this->id;
	}
	
	public function primary_key()
	{
		return 'id';
	}
}

class JotIdentityMapTestCase extends UnitTestCase
{	
	public function __construct()
	{
		$this->load->model('Blog_Model');
	}
	
	public function teardown()
	{
		JotIdentityMap::clear();
	}
	
	public function test_add()
	{
		$original = new JotIdentityMapMock(1);

		$result = JotIdentityMap::add($original);
		
		$this->assertTrue($result, 'Object is added');
	}
	
	public function test_get()
	{
		$original = new JotIdentityMapMock(1);

		JotIdentityMap::add($original);
				
		$new = JotIdentityMap::get('JotIdentityMapMock', 1);
		$this->assertEquals($original, $new, 'Object is added');		
	}
	
	public function test_count()
	{
		$original = new JotIdentityMapMock(1);

		JotIdentityMap::add($original);
		
		$this->assertEquals(1, JotIdentityMap::count(), 'Repository has 1 object.');				
	}
	
	public function test_duplication()
	{
		$original = new JotIdentityMapMock(1);

		JotIdentityMap::add($original);
		JotIdentityMap::add($original);	
		
		$this->assertNotEquals(2, JotIdentityMap::count(), 'Repository did not duplicate object');	
	}
	
	public function test_clear()
	{	
		$original = new JotIdentityMapMock(1);

		JotIdentityMap::clear();
		$this->assertEquals(0, JotIdentityMap::count(), 'Repository is cleared.');
	}

	public function test_remove_by_id()
	{
		$original = new JotIdentityMapMock(1);
	
		JotIdentityMap::add($original);
		JotIdentityMap::remove_by_id('JotIdentityMapMock', 1);
		$new = JotIdentityMap::get('JotIdentityMapMock', 1);
		$this->assertNotEquals($original, $new, 'Object is removed by id');		
	}

	public function test_remove()
	{	
		$original = new JotIdentityMapMock(1);
	
		JotIdentityMap::add($original);
		JotIdentityMap::remove($original);
		$new = JotIdentityMap::get('JotIdentityMapMock', 1);
		$this->assertNotEquals($original, $new, 'Object is removed');
	}
	
	public function test_object_create_with_no_attributes()
	{
		$blog = new Blog_Model;
				
		$this->assertEquals(0, JotIdentityMap::count(), "Doesn't add objects without attributes.");		
	}
	
	public function test_object_create_with_no_id()
	{		
		$blog = new Blog_Model(array(
			'title' => 'test',
		));
			
		$this->assertEquals(0, JotIdentityMap::count(), 'No object added because attributes are present but no id.');		
	}
	
	public function test_object_create_with_id()
	{		
		$blog = new Blog_Model(array(
			'title' => 'test',
			'id'	=> 1
		));
			
		$this->assertEquals(1, JotIdentityMap::count(), 'Added object to identity map.');		
		
	}
}