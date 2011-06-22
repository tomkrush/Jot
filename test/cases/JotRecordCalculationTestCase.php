<?php

class JotRecordCalculationTestCase extends UnitTestCase
{	
	public function __construct()
	{
		$this->load->database();
		$this->load->dbutil();
		
		$this->load->model('person_model');
		
		$names = array('Ted', 'John', 'Becky', 'Jason', 'Jake', 'Brandon', 'David');
		
		$this->db->truncate('people');
		
		for($i = 0; $i < 50; $i++)
		{
			$this->person_model->create(array(
				'name' => array_rand($names),
				'age' => rand(20, 50)
			));
		}
	}
	
	public function test_average()
	{

	}
}