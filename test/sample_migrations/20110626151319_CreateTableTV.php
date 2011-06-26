<?php
class CreateTableTV
{
	function up()
	{
		create_table('tv', array(
			array('name'=>'name', 'type' => 'string', 'NOT_NULL' => TRUE),
			MIGRATION_TIMESTAMPS
		));
	}
}