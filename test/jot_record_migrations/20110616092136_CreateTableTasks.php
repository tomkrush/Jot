<?php
class CreateTableTasks
{
	function up()
	{
		create_table('tasks', array(
			array('name' => 'name', 'type' => 'string'),
			array('name' => 'completed', 'type'=> 'bool'),
			MIGRATION_TIMESTAMPS
		));
	}
}