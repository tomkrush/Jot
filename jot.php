<?php

require 'jot_form.php';

class Jot extends CI_Model 
{

/*-------------------------------------------------
PROPERTIES
-------------------------------------------------*/
public $table_name = '';
protected $timestamps = TRUE;
protected $transient = array();
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

public function __toString()
{		
	$string = '';
	
	$string .= $this->singularTableName();
	
	$fields_strings = array();
	
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

public function __set($key, $value)
{
	$this->write_attribute($key, $value);
}

public function __get($key)
{
	$CI =& get_instance();

	if (property_exists($CI, $key)) return $CI->$key;		

	$value = NULL;
	
	if ( $this->has_attribute($key) )
	{
		return $this->read_attribute($key);
	}
	
	return NULL;
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
	$result = $this->new_record ? $this->_create() : $this->_update();
	return $result ? $result : FALSE;
}

# Internal Method for updating a row in the database
private function _update()
{
	$id = $this->read_attribute($this->primary_key);

	$this->db->update($this->table_name, $this->attributes, array($this->primary_key=>$id));

	return $this;
}

# Internal Method for creating a row in the database
private function _create()
{
	$this->db->insert($this->table_name, $this->attributes);
	
	$this->new_record = FALSE;
	
	$id = $this->db->insert_id();
	
	$this->write_attribute($this->primary_key, $id);
	
	return $this;
}

/*-------------------------------------------------
ERRORS
-------------------------------------------------*/	

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

public function init()
{

}

protected function transient($fields)
{
	$this->transient = func_get_args();
}

protected function tablename($table_name)
{
	$this->table_name = $table_name;
}

protected function has_timestamps($bool)
{
	$this->timestamps = $bool;
}

protected function _tablename()
{
	if ( empty($this->table_name) )
	{
		$this->table_name = plural(str_replace('_model', '', strtolower(get_class($this))));
	}

	return $this->table_name;
}

public function singularTableName()
{
	return strtolower(singular($this->table_name));
}

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
# You can update multiple objects by passing multiple ids (array) in argument.
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


public function destroy($id = NULL)
{
	return isset($id) ? $this->destroy_id($id) : $this->destroy_object();
}

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

protected function destroy_object()
{
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

protected function _conditions($conditions)
{
	if ( $conditions == NULL ) return array();
	 
	$conditions = is_numeric($conditions) || ! $this->_is_assoc($conditions) ? array($this->primary_key => $conditions) : $conditions;	
	$conditions = is_array($conditions) ? $conditions : array();

	if ($this->base_filter !== null)
	{
		$conditions = array_merge($this->base_filter, $conditions);
	}
	
	if ($this->base_join !== null)
	{
		$this->db->join($this->base_join[0], $this->base_join[1]);
	}
	
	return $conditions;	
}

public function exists($conditions = array())
{
	$conditions = $this->_conditions($conditions);

	$this->_find($conditions);
	
	return $this->db->count_all_results() ? TRUE : FALSE;		
}	

public function count($conditions = array())
{
	$conditions = $this->_conditions($conditions);

	$this->_find($conditions);

	return $this->db->count_all_results();		
}

public function first($conditions = array())
{
	$conditions = $this->_conditions($conditions);
			
	$this->db->order_by($this->primary_key.' ASC');
	$this->db->limit(1);
	$result = $this->find($conditions);
	return count($result) ? $result[0] : NULL;
}

public function last($conditions = array())
{
	$conditions = $this->_conditions($conditions);
			
	$this->db->order_by($this->primary_key.' DESC');
	$this->db->limit(1);
	$result = $this->find($conditions);
	return count($result) ? $result[0] : NULL;
}

public function all($conditions = array())
{
	$conditions = $this->_conditions($conditions);
	
	return $this->find($conditions, 1, 0);		
}

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

private function _is_assoc($array) 
{
    return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
}

} // End Class