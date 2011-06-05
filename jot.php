<?php

require 'jot_form.php';

class Jot extends CI_Model 
{

/*-------------------------------------------------
PROPERTIES
-------------------------------------------------*/
public $table_name = '';
protected $timestamps = TRUE;
protected $primary_key = 'id';

protected $validates = FALSE;
protected $field_validations = array();
protected $errors = array();

protected $created_at_column_name = 'created_at';
protected $updated_at_column_name = 'updated_at';

protected $attributes = array();
protected $changed_attributes = array();
	
protected $new_record = TRUE;
protected $destroyed = FALSE;

protected $hooks = array();

/*-------------------------------------------------
MAGIC METHODS
-------------------------------------------------*/
public function __construct($attributes = array(), $options = array()) 
{
	parent::__construct();
	
	$this->init();
	$this->load->helper('inflector');

	$this->_tablename();
			
	if ( is_object($attributes) || is_array($attributes) )
	{
		$this->assign_attributes($attributes);

		$this->new_record = array_key_exists('new_record', $options) ? !!$options['new_record'] : TRUE;
	}
}

# Returns string describing object
public function __toString()
{		
	$string = '';
	
	$string .= $this->singularTableName();
	
  	
	foreach($this->attributes as $key => $value)
	{
		if ($key == 'created_at' || $key == 'updated_at')
		{
			$value = date('"F j, Y, g:i a"', $value);
		}
		else if ( is_string($value) )
		{
			$value = '"'.$value.'"';
		}
		
		if ( $value )
		{
			$fields_strings[] = $key.': '.$value;
		}
	}
		
	$string .= ' '.implode(', ', $fields_strings);
	
	return $string;
}

# Sets row attributes
public function __set($key, $value)
{
	$this->write_attribute($key, $value);
}

# Returns row attributes and properties from CodeIgniter.
public function __get($key)
{
	# Return property from CodeIgniter if exists
	$CI =& get_instance();
	if (property_exists($CI, $key)) return $CI->$key;		
	
	# Only retrieve attribute if it exists
	if ( $this->has_attribute($key) )
	{
		return $this->read_attribute($key);
	}
}

/*-------------------------------------------------
BUILD FUNCTION
-------------------------------------------------*/

# Called before row is persisted.
protected function before_save($hook)
{
	$this->add_hook('before_save', $hook);
}

# Called after row is persisted.
protected function after_save($hook)
{
	$this->add_hook('after_save', $hook);
}

# Called before row is created.
protected function before_create($hook)
{
	$this->add_hook('before_create', $hook);
}

# Called after row is created.
protected function after_create($hook)
{
	$this->add_hook('after_create', $hook);
}

# Called before row is updated.
protected function before_update($hook)
{
	$this->add_hook('before_update', $hook);
}

# Called after row is updated.
protected function after_update($hook)
{
	$this->add_hook('after_update', $hook);
}

# Called before row is validated.
protected function before_validation($hook)
{
	$this->add_hook('before_validation', $hook);
}

# Called after row is validated.
protected function after_validation($hook)
{
	$this->add_hook('after_validation', $hook);
}

# Add hook to jot model
protected function add_hook($name, $hook)
{
	# If hook method exists, add hook to memory
	if ( method_exists($this, $hook) )
	{
		$this->hooks[$name][] = $hook;
	}
}

# Call hook on jot model
protected function call_hook($name)
{
	# Return hooks if exist otherwise return empty array.
	$hooks = $this->_element($name, $this->hooks, array());

	# Execute each hook
	foreach($hooks as $hook)
	{
		$this->$hook();
	}	
}

/*-------------------------------------------------
BUILD FUNCTION
-------------------------------------------------*/
		
# Builds empty object using attributes.
public function build($attributes = array())
{
	return new $this($attributes);
}
	
/*-------------------------------------------------
ATTRIBUTE METHODS
-------------------------------------------------*/
	
# Allows you to assign multiple attributes.
public function assign_attributes($attributes)
{
	$attributes = (array)$attributes;
	foreach($attributes as $key => $value)
	{
		$this->write_attribute($key, $value);
	}		
}

# Returns attribute value if exists otherwise null
public function read_attribute($key)
{
	if ( array_key_exists($key, $this->attributes) )
	{
		return $this->attributes[$key];
	}
	
	return NULL;
}

# Writes attribute value to object
public function write_attribute($key, $value)
{
	$this->attributes[$key] = $value;
}

# Returns TRUE if attribute exists
public function has_attribute($attribute)
{
	return array_key_exists($attribute, $this->attributes);
}

# Writes the attributes to object and saves to the memory
public function update_attribute($key, $value)
{
	$this->write_attribute($key, $value);
	$this->save();
}

# Assigns attributes and saves changes.
# Note: (This will update all changed attributes on parent object;
#		not just the attributes sent through the arguments)
public function update_attributes($attributes)
{
	$this->assign_attributes($attributes);
	$this->save();
}
	
/*-------------------------------------------------
SAVE
-------------------------------------------------*/	
	
# Saves the object
#
# A database row is created if this object is a new_record, otherwise
# it will update the existing record in the database.
public function save()
{		
	$this->call_hook('before_save');

	# Save new record and call appropriate hooks
	if ( $this->new_record )
	{
		$this->call_hook('before_create');
		$this->_create();
		$this->call_hook('after_create');
	}
	
	# Update record and call appropriate hooks
	else
	{
		$this->call_hook('before_update');
		$this->_update();
		$this->call_hook('after_update');
	}
	
	$this->call_hook('after_save');
	
	return $this;
}

# Internal Method for updating a row in the database
protected function _update()
{	
	$id = $this->read_attribute($this->primary_key);
	$this->db->update($this->table_name, $this->attributes, array($this->primary_key=>$id));
}

# Internal Method for creating a row in the database
protected function _create()
{		
	$this->db->insert($this->table_name, $this->attributes);
	$this->new_record = FALSE;
	$id = $this->db->insert_id();
	$this->write_attribute($this->primary_key, $id);
}

/*-------------------------------------------------
ERRORS
-------------------------------------------------*/	

# Return errors from validation
public function errors()
{
	$errors = array();
	
	foreach($this->errors as $error)
	{
		$errors[] = $error[1];
	}
	
	return $errors;
}
	
/*-------------------------------------------------
INITALIZERS
-------------------------------------------------*/
public function init() {}

# Set table name
protected function tablename($table_name)
{
	$this->table_name = $table_name;
}

# Model uses timestamps
protected function has_timestamps($bool)
{				
	$this->timestamps = $bool;
}

# Guess table name using model name.
protected function _tablename()
{
	if ( empty($this->table_name) )
	{
		$this->table_name = plural(str_replace('_model', '', strtolower(get_class($this))));
	}

	return $this->table_name;
}

# Returns singular form of model name.
public function singularTableName()
{
	return strtolower(singular($this->table_name));
}

# Returns plural form of model name.
public function pluralTableName()
{
	return strtolower(plural($this->table_name));		
}
	
/*-------------------------------------------------
PERSISTANCE
-------------------------------------------------*/

# Returns boolean if object is persisted. A persisted object
# is stored in the database.
# If the object is new or destroy an object is *not* persisted.
public function persisted()
{
	return ! ($this->new_record || $this->destroyed);
}

# Creates single object using attributes.
# Returns object
public function create($attributes)
{
 	return $this->build($attributes)->save();
}

# Updates a single object usings attributes. 
# You can update multiple objects by passing 
# multiple ids (array) in argument.
#
# 	# Single object
# 	$this->person_model->update(1, array('name'=>'John'));
#
# 	# Multiple Objects using same changes
#	$this->person_model->update(array(1, 2, 3), array('is_online'=>TRUE));
#
#	# Multiple Objects with individual changes
#	$this->person_model->update(array(1=>));
#
public function update($id, $attributes = NULL)
{
	if ( is_array($id) )
	{
		$objects = array();
		
		foreach($id as $key => $value)
		{
			if ( is_numeric($value) )
			{
				# Update each object using same changes
				$objects[] = $this->update($value, $attributes);
			}
			else
			{
				# Update each object using inidiviual changes
				$objects[] = $this->update($key, $value);
			}
		}				
		
		# Return list of objects
		return $objects;
	}
	else
	{
		$object = $this->first($id);
		$object->update_attributes($attributes);
		
		# Return Object
		return $object;
	}
}

# Destroy an object
# This function has split functionality. If an integer or 
# array is supplied it will destroy that object(s).
# Otherwise the function will call destroy_object method.
public function destroy($id = NULL)
{
	return isset($id) ? $this->destroy_id($id) : $this->destroy_object();
}

# Destroy on object based on an id or array.
# The method calls the destroy method.
protected function destroy_id($id)
{
	$ids = is_array($id) ? $id : array($id);

	$objects = array();

	foreach($ids as $id)
	{
		$objects[] = $object = $this->first($id);
		$object->destroy();
	}
	
	return $objects;
}

# This method delete an actual object from memory.
protected function destroy_object()
{
	# Only delete object if persisted.
	if ( $this->persisted() )
	{
		$id = $this->read_attribute($this->primary_key);
		
		$this->db->delete($this->table_name, array($this->primary_key => $id));
	}
	
	$this->destroyed = TRUE;
}
	
/*-------------------------------------------------
FINDERS
-------------------------------------------------*/	

# Validates conditions variable.
protected function _conditions($conditions)
{
	# Return empty array if conditions do not exist
	if ( $conditions == NULL ) 
	{
		return array();
	}
	 
	# If conditions is a single integer or list of ids return ids.
	if ( is_numeric($conditions) || ! $this->_is_assoc($conditions) )
	{
		$conditions = array($this->primary_key => $conditions);
	}
	
	# Make sure conditions is an array
	if ( ! is_array($conditions) )
	{
		$conditions = array();
	}
	
	return $conditions;	
}

# Return true if conditions return results.
public function exists($conditions = array())
{
	return !!$this->count($conditions);		
}	

# Return count of items using conditions.
public function count($conditions = array())
{
	$conditions = $this->_conditions($conditions);

	$this->_find($conditions);

	return $this->db->count_all_results();		
}

# Returns first row using conditions
public function first($conditions = array())
{
	$conditions = $this->_conditions($conditions);
			
	$this->db->order_by($this->primary_key.' ASC');
	$this->db->limit(1);
	$result = $this->find($conditions);
	return count($result) ? $result[0] : NULL;
}

# Returns last row using conditions
public function last($conditions = array())
{
	$conditions = $this->_conditions($conditions);
			
	$this->db->order_by($this->primary_key.' DESC');
	$this->db->limit(1);
	$result = $this->find($conditions);
	
	return count($result) ? $result[0] : NULL;
}

# Returns all rows using conditions
public function all($conditions = array())
{
	$conditions = $this->_conditions($conditions);
	
	return $this->find($conditions, 1, 0);		
}

# Returns a range of rows using conditions
public function find($conditions = array(), $page = 1, $limit = 10)
{
	$conditions = $this->_conditions($conditions);
			
	$this->_find($conditions);
	
	if ( $limit > 0 )
	{
		if ( $limit && $page )
		{
			$this->db->limit($limit, ($page - 1) * $limit);
		} 
		else
		{
			$this->db->limit($limit, ($page - 1) * $limit);
		}
	}

	$r = $this->db->get();
	$r->result_object();
	$result = array();
	
	for ($i=0, $len=count($r->result_object); $i<$len; $i++)
	{	
		$result[] = new $this($r->result_object[$i], array(
			'new_record' => FALSE,
		));
	}

	return $result;		
}

# Private find method. The purpose of this is too apply db functions
# to the core CodeIgniter object.
protected function _find($conditions = array())
{	
	if ( is_array($conditions) )
	{
		foreach($conditions as $key => $value)
		{
			if ( is_array($value) )
			{
				$this->db->where_in($key, $value);
			}
			else
			{
				$this->db->where($key, $value);
			}
		}
	}
	
	$this->db->from($this->table_name);
}

/*-------------------------------------------------
DEPENDENCIES
-------------------------------------------------*/	

# Returns true if array returned is an assocative array
protected function _is_assoc($array)
{
    return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
}

# Returns value from key if in array. If value does not exist
# return default value.
#
# Examples:
# $this->_element('name', $object, 'Jot');
# $this->_element('article.published', $article, TRUE);
protected function _element($keys, $array, $default = FALSE)
{
	$array = (array)$array;

	if (empty($array))
		return $default;

	# Prepare for loop
	$keys = explode('.', $keys);

	do
	{
		# Get the next key
		$key = array_shift($keys);

		if (isset($array[$key]))
		{
			if (is_array($array[$key]) AND ! empty($keys))
			{
				# Dig down to prepare the next loop
				$array = $array[$key];
			}
			else
			{
				# Requested key was found
				return $array[$key];
			}
		}
		else
		{
			# Requested key is not set
			break;
		}
	}
	while ( ! empty($keys));

	return $default;
}

} # End Class