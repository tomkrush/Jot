<?php
class CreateTableTeachers
{
	function up()
	{
		create_table('teachers', array(
			array('name' => 'first_name', 'type' => 'string'),
			array('name' => 'last_name', 'type' => 'string'),
			array('name' => 'position', 'type' => 'string'),
			MIGRATION_TIMESTAMPS
		));
	}
}