<?php
class CreateTableCompanies
{
	function up()
	{
		create_table('companies', array(
			array('name' => 'name', 'type' => 'string'),
			MIGRATION_TIMESTAMPS
		));
	}
}