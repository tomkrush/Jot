<?php
class CreateTableLists
{
	function up()
	{
		create_table('lists', array(
			array('name' => 'name', 'type' => 'string'),
			MIGRATION_TIMESTAMPS
		));
	}
}