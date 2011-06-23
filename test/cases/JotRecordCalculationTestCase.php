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
		
		for($i = 1; $i <= 50; $i++)
		{
			$this->person_model->create(array(
				'name' => array_rand($names),
				'age' => $i
			));
		}
	}
	
	public function test_average()
	{
		$average = $this->person_model->average('age');
		
		$this->assertTrue(is_float($average), 'Average is a float');
		$this->assertEquals(25.5, $average, 'Average calculated');
	}
	
	public function test_minimum()
	{
		$minimum = $this->person_model->minimum('age');
		
		$this->assertTrue(is_float($minimum), 'Minimum is a float');
		$this->assertEquals(1.0, $minimum, 'Minimum calculated');
	}
	
	public function test_maximum()
	{
		$maximum = $this->person_model->maximum('age');

		$this->assertTrue(is_float($maximum), 'Maximum is a float');
		$this->assertEquals(50.0, $maximum, 'Maximum calculated');
	}
	
	public function test_count()
	{
		$count = $this->person_model->count();		
		
		$this->assertTrue(is_int($count), 'Count is a integer');
		$this->assertEquals(50, $count, 'Count calculated');
	}
}