<?php
class CreateTablePeople
{
	function up()
	{
		create_table('people', array(
			array('name' => 'name', 'type' => 'string'),
			array('name' => 'age', 'type' => 'integer'),
			MIGRATION_TIMESTAMPS
		));
	}
}