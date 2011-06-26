<?php
class CreateTableImages
{
	function up()
	{
		create_table('images', array(
			array('name' => 'image', 'type' => 'string'),
			array('name' => 'imageable_id', 'type' => 'integer'),
			array('name' => 'imageable_type', 'type' => 'string'),
			MIGRATION_TIMESTAMPS
		));
	}
}