<?php
class CreateTableStudents
{
	function up()
	{
		create_table('students', array(
			array('name' => 'first_name', 'type' => 'string'),
			array('name' => 'last_name', 'type' => 'string'),
			array('name' => 'grade', 'type' => 'string'),
			MIGRATION_TIMESTAMPS
		));
	}
}