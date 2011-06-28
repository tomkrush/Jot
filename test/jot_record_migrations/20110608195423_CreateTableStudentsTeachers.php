<?php
class CreateTableStudentsTeachers
{
	function up()
	{
		create_table('students_teachers', array(
			array('name' => 'student_id', 'type' => 'integer'),
			array('name' => 'teacher_id', 'type' => 'integer'),
			MIGRATION_TIMESTAMPS
		));
	}
}