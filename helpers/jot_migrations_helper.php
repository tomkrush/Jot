<?php

class JotSchema
{
	public static function createIfNotExists()
	{
		create_table('schema_migrations', array(
			array('name' => 'version', 'type' => 'integer')
		), NULL, TRUE);		
	}
	
	public static function version()
	{
		self::createIfNotExists();
		
		$CI =& get_instance();

		$row = $CI->db->select_max('version')->get('schema_migrations')->row();
		$version = $row ? $row->version : 0;		

		return (int)$version;		
	}
	
	public static function destroy()
	{
		$CI =& get_instance();
		$CI->load->dbforge();

		$tables = $CI->db->list_tables();

		foreach ($tables as $table)
		{
			$CI->dbforge->drop_table($table);
		}
		
		$CI->db->data_cache = array();
	}
	
	public static function setVersion($version)
	{
		if ( isset($version) )
		{
			self::createIfNotExists();

			$CI =& get_instance();
			$CI->db->insert('schema_migrations', array('version' => $version));
		}
	}
}

class JotSeed
{
	public function __get($key)
	{
		$CI =& get_instance();
		return $CI->$key;
	}
	
	public function run($file)
	{
		if (file_exists($file)) require($file);
	}
}

class JotMigrations
{
	protected $migration_path;
	protected $seed_file_path;
	protected $migration_files;
	
	public function __construct($migration_path = 'db/migrate/', $seed_file_path = 'db/seed.php')
	{
		$this->migration_path = $migration_path;
		$this->seed_file_path = $seed_file_path;
		
		$this->clear();
	}
	
	public function migration_path()
	{
		return APPPATH.$this->migration_path;
	}

	public function seed_file_path()
	{
		return APPPATH.$this->seed_file_path;
	}
	
	public function up()
	{
		// Schema Information
		$current_schema_version = JotSchema::version();
		$new_schema_version = NULL;

		$path = $this->migration_path();

		$files = $this->list_migrations();

		$count = 0;

		// Migrate each file
		foreach($files as $file)
		{
			$file_path = $path . $file;

			$class = explode('_', $file, 2);
			$new_schema_version = strtotime($class[0]);

			// Only execute a migration if it NEEDs to be done
			if ( $current_schema_version < $new_schema_version )
			{			
				$class = explode('.', $class[1]);
				$class = $class[0];

				require_once($file_path);
				$count++;

				$migration = new $class;
				$migration->up();
			}
		}

		if ( $new_schema_version > $current_schema_version) JotSchema::setVersion($new_schema_version);		

		return $count;
	}
	
	public function seed()
	{		
		$seed = new JotSeed;
		$seed->run($this->seed_file_path());
	}
	
	protected function create_schema_table_if_not_exists()
	{
		create_table('schema_migrations', array(
			array('name' => 'version', 'type' => 'integer')
		), NULL, TRUE);		
	}
	
	public function reset($seed = FALSE)
	{
		JotSchema::destroy();
		
		$this->up();
		$seed && $this->seed();
	}
	
	public function list_migrations()
	{
		if ( ! $this->migration_files )
		{
			$path = $this->migration_path();

			// Force files into numerical order
			$this->migration_files = directory_map($path);
			// print_r($this->migration_files);
			sort($this->migration_files);
		}

		return $this->migration_files;		
	}
	
	public function create($file)
	{		
		$CI =& get_instance();
		$CI->load->helper('inflector');

		$path = $this->migration_path();

		$path .= date('YmdHis').'_'.$file.'.php';

		if ( ! file_exists($path) )
		{
			$class_name = str_replace(' ','_', ucwords(str_replace('_',' ', $file)));	

			$template = "<?php\nclass {$class_name}\n{\n\tfunction up()\n\t{\n\n\t}\n}";

			file_put_contents($path, $template);
			$this->clear();
			
			if ( file_exists($path)) return $path;
		}
		
		return FALSE;		
	}
	
	public function clear()
	{
		$this->migration_files = array();
	}
}


define('MIGRATION_TIMESTAMPS', 1);

function jot_migration_prepare_column($column, $include_name = FALSE)
{
	$column = array_change_key_case($column, CASE_UPPER);
	$name = value_for_key('NAME', $column);
			
	if ( empty($name) )
	{
		continue;
	}
	
	if ( $include_name && $value = value_for_key('NAME', $column) )
	{
		$field['NAME'] = $value;
	}	
 
	if ( $value = value_for_key('TYPE', $column) )
	{
		$result = _migration_get_type_and_constraint($value);
		if ( count($result) == 2 )
		{
			list($type, $constraint) = $result;
		}
		else
		{
			list($type) = $result;
		}

		if ( isset($type) )
		{
			$field['TYPE'] = $type;
		}

		if ( isset($constraint) )
		{
			$field['CONSTRAINT'] = $constraint;
		}			
	}
	
	$value = value_for_key('UNSIGNED', $column);
	if ( is_bool($value) )
	{
		$field['UNSIGNED'] = $value;
	}

	$value = value_for_key('NOT_NULL', $column);
	if ( is_bool($value) )
	{
		$field['NULL'] = !$value;
	}
	
	if ( $value = value_for_key('DEFAULT', $column) )
	{
		$field['DEFAULT'] = $value;
	}
	
	$value = value_for_key('AUTO_INCREMENT', $column);
	if ( is_bool($value) )
	{
		$field['AUTO_INCREMENT'] = $value;
	}
	
	return $field;	
}

function create_table($table_name, $columns = array(), $options = array(), $if_not_exists = FALSE)
{
	$CI =& get_instance();
	$CI->load->dbforge();
	$CI->load->helper('jot_array');
	
	$fields = array();	
	$primary_key = value_for_key('primary_key', $options, 'id');	
		
	// Primary Key
	if ( isset($primary_key) && $primary_key != FALSE )
	{		
		array_unshift($columns, array(
			'name' => $primary_key,
			'type' => 'integer',
			'not_null' => TRUE,
			'auto_increment' => TRUE
		));
		
		$CI->dbforge->add_key($primary_key, TRUE);	
	}	
		
	// Timestamps
	if ( $key = array_search(MIGRATION_TIMESTAMPS, $columns) )
	{
		unset($columns[$key]);
		
		$columns[] = array(
			'name' => 'created_at',
			'type' => 'integer',
			'NOT_NULL' => TRUE
		);
		
		$columns[] = array(
			'name' => 'updated_at',
			'type' => 'integer',
			'NOT_NULL' => TRUE
		);
	}		
		
	// Columns		
	foreach($columns as $column)
	{	
		$field = array();

		$column = array_change_key_case($column, CASE_UPPER);
		$name = value_for_key('NAME', $column);
		
		$fields[$name] = jot_migration_prepare_column($column);
	}	
	
	// Add Fields
	$CI->dbforge->add_field($fields);
	
	// Create Table
	$CI->dbforge->create_table($table_name, $if_not_exists);
}

function rename_table($old, $new)
{
	$CI =& get_instance();
	$CI->load->dbforge();

	$CI->dbforge->rename_table($old, $new);	
}

function drop_table($table)
{
	$CI =& get_instance();
	$CI->load->dbforge();

	$CI->dbforge->drop_table($table);
}

function create_column($table, $column)
{
	$CI =& get_instance();
	$CI->load->dbforge();

	$fields = array();
	
	$name = value_for_key('name', $column);
	$column = jot_migration_prepare_column($column);
	$fields[$name]  = $column;
									
	$CI->dbforge->add_column($table, $fields);	
}

function change_column($table, $name, $column)
{
	$CI =& get_instance();
	$CI->load->dbforge();

	$fields = array();
	
	$column = jot_migration_prepare_column($column, TRUE);
	$fields[$name]  = $column;
				
	$CI->dbforge->modify_column($table, $fields);	
}

function drop_column($table, $name)
{
	$CI =& get_instance();
	$CI->load->dbforge();

	$CI->dbforge->drop_column($table, $name);	
}

function _migration_get_type_and_constraint($type)
{
	$type = _MigrationDataType($type);
	
	preg_match('/([a-z]*)\(([0-9]*)\)/', $type, $matches);
	
	if ( count($matches) == 0 ) return array($type);
			
	list($all, $type, $constraint) = $matches;
		
	return array(
		$type,
		$constraint
	);
}

function _MigrationDataType($type)
{
	$CI =& get_instance();
	$driver = $CI->db->dbdriver ;
		
	switch($driver)
	{
		case 'sqlite3':
			if ( $type == 'binary' ) 					$type = 'blob';
			else if ( $type == 'boolean') 		$type = 'boolean';
			else if ( $type == 'date' ) 			$type = 'date';
			else if ( $type == 'datetime' ) 	$type = 'datetime';
			else if ( $type == 'decimal' ) 		$type = 'decimal';
			else if ( $type == 'float' ) 			$type = 'float';
			else if ( $type == 'integer' ) 		$type = 'integer';
			else if ( $type == 'string' ) 		$type = 'varchar(255)';
			else if ( $type == 'text' ) 			$type = 'text';
			else if ( $type == 'time' ) 			$type = 'datetime';
			else if ( $type == 'timestamp' ) 	$type = 'datetime';		
		break;
		
		default:
			if ( $type == 'binary' ) 					$type = 'blob';
			else if ( $type == 'boolean') 		$type = 'tinyint(1)';
			else if ( $type == 'date' ) 			$type = 'date';
			else if ( $type == 'datetime' ) 	$type = 'datetime';
			else if ( $type == 'decimal' ) 		$type = 'decimal';
			else if ( $type == 'float' ) 			$type = 'float';
			else if ( $type == 'integer' ) 		$type = 'int(11)';
			else if ( $type == 'string' ) 		$type = 'varchar(255)';
			else if ( $type == 'text' ) 			$type = 'text';
			else if ( $type == 'time' ) 			$type = 'time';
			else if ( $type == 'timestamp' ) 	$type = 'datetime';		
		break;
	}	
	
	return $type;
}