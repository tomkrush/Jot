<?php
class CreateTableRoom
{
	function up()
	{
		create_table('pages', array(
			array('name'=>'name', 'type' => 'string', 'NOT_NULL' => TRUE),
			MIGRATION_TIMESTAMPS
		));
	}
}