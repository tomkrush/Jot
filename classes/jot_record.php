<?php

class JotRecord extends CI_Model implements Serializable
{

/*-------------------------------------------------
ATTRIBUTE METHODS
-------------------------------------------------*/

protected $attributes = array();
protected $changed_attributes = array();
protected $transient_attributes = array();

#Has transient attribute
public function has_transient($attribute)
{
	return in_array($attribute, $this->transient_attributes);
}

#Set transient attribute
public function add_transient($attribute)
{	
	$this->transient_attributes[] = $attribute;
}

#Set transient attributes
public function transient($attributes)
{
	$attributes = is_array($attributes) ? $attributes : array($attributes);

	$this->transient_attributes = $attributes;
}

# Allows you to assign multiple attributes.
public function assign_attributes($attributes)
{
	$this->attributes = array_merge($this->attributes, (array)$attributes);
}

# Returns attribute value if get function exists
public function read_attribute_function($attribute)
{
	$method_name = 'get_'.$attribute;

	if ( method_exists($this, $method_name) )
	{
		return $this->$method_name();
	}

	return null;
}

# Write attribute with value
public function write_attribute_function($attribute, $value)
{
	$method_name = 'set_'.$attribute;

	if ( method_exists($this, $method_name) )
	{
		$this->$method_name($attribute, $value);
	}
}

# Returns attribute value if exists otherwise null
public function read_attribute($attribute)
{
	return value_for_key($attribute, $this->attributes, null);
}

# Writes attribute value to object
public function write_attribute($attribute, $value)
{	
	$this->attributes[$attribute] = $value;		
}

# Read all attributes
public function attributes($transient = true)
{
	$attributes = $this->attributes;

	if ( $transient === false )
	{
		foreach($this->transient_attributes as $attribute)
		{
			unset($attributes[$attribute]);
		}
	}

	return $attributes;
}

# Returns boolean value if read function exists for attribute
protected function has_read_attribute_function($attribute)
{
	$method_name = 'get_'.$attribute;

	return method_exists($this, $method_name);
}

# Returns boolean value if write function exists for attribute
protected function has_write_attribute_function($attribute)
{
	$method_name = 'set_'.$attribute;

	return method_exists($this, $method_name);
}

# Returns true if attribute exists
public function has_attribute($attribute)
{
	return array_key_exists($attribute, $this->attributes);
}

# Writes the attributes to object and saves to the memory
public function update_attribute($attribute, $value)
{
	$this->write_attribute($attribute, $value);
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
BUILD FUNCTION
-------------------------------------------------*/
		
# Builds empty object using attributes.
public function build($attributes = array())
{
	return $this->instantiate($attributes);
}

/*-------------------------------------------------
HOOKS FUNCTION
-------------------------------------------------*/

protected $hooks = array();

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

# Called before row is destroyed.
protected function before_destroy($hook)
{
	$this->add_hook('before_destroy', $hook);
}

# Called after row is destroyed.
protected function after_destroy($hook)
{
	$this->add_hook('after_destroy', $hook);
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
	if ( array_key_exists($name, $this->hooks) )
	{
		$hooks = $this->hooks[$name];
	}
	else
	{
		$hooks = array();
	}
	
	# Execute each hook
	foreach($hooks as $hook)
	{
		$this->$hook();
	}	
}

/*-------------------------------------------------
ERRORS
-------------------------------------------------*/	

protected $errors = array();

# Return errors
public function errors()
{
	$errors = array();
	foreach($this->errors as $key => $error)
	{
		$errors[$error[0]] = $error[1];
	}
	
	return $errors;
}

# Add Error
public function add_error($error)
{
	$this->errors[] = $error;
}

# Reset Errors
public function reset_errors()
{
	$this->errors = array();
}
	
/*-------------------------------------------------
INITALIZERS
-------------------------------------------------*/

# Init is called by object for initialization.
public function init() {}

/*-------------------------------------------------
PRE-DEFINED COLUMNS
-------------------------------------------------*/

/* PRIMARY KEY */
protected $primary_key = 'id';

# Return attribute name of primary key.
public function primary_key()
{
	return $this->primary_key;
}

/* TIMESTAMPS */

protected $timestamps = true;
protected $created_at_column_name = 'created_at';
protected $updated_at_column_name = 'updated_at';

# Model uses timestamps
protected function has_timestamps($bool)
{				
	$this->timestamps = $bool;
}

# Objects can now be touched. When touched the database forces the updated_at
# attribute to newest timestamp.
public function touch()
{
	if ( $this->persisted() && $this->timestamps )
	{
		$key = $this->primary_key();
		$value = $this->read_attribute($key);
		
		$updated_at = time();
		
		$this->db->update($this->table_name(), array(
			$this->updated_at_column_name => $updated_at
		), array($key => $value));
		
		$this->write_attribute($this->updated_at_column_name, $updated_at);
		
		return true;
	}
	
	return false;
}

/*-------------------------------------------------
ASSOCATIONS
-------------------------------------------------*/
protected $associations = array();

public function write_association($name, $value)
{	
	$association = $this->get_association($name);
	if ($association) $association->set($value);
}

public function read_association($key)
{
	$association = $this->get_association($key);
	return $association ? $association->get() : null;
}

protected function has_one($association, $options = array())
{
	$this->associations[$association] = new JotHasOneAssociation($association, $this, $options);
}

protected function has_and_belongs_to_many($association, $options = array())
{
	$this->associations[$association] = new JotHasAndBelongsToManyAssociation($association, $this, $options);
}

protected function belongs_to($association, $options = array())
{	
	$this->associations[$association] = new JotBelongsToAssociation($association, $this, $options);
}

protected function has_many($association, $options = array())
{
	$this->associations[$association] = new JotHasManyAssociation($association, $this, $options);
}

protected function has_association($name)
{	
	return !!$this->get_association($name);
}

protected function get_association($name) 
{
	foreach($this->associations as $key => $value ) {
		if ( $key == $name ) {
			return $value;
		}
	}

	return false;
}
	
/*-------------------------------------------------
VALIDATION
-------------------------------------------------*/	

protected $validators = array();

# Did validation perform
public function is_valid()
{
	$this->call_hook('before_validation');
	
	$validates = $this->perform_validations();
	
	$this->call_hook('after_validation');
	
	return $validates;
}

# Attach validators to model
public function validates($attribute, $validators)
{
	# Add validator to object
	if ( array_key_exists($attribute, $this->validators) )
	{
		$old_validators = $this->validators[$attribute];
	}
	else
	{
		$old_validators = array();
	}
	
	$validators = is_array($validators) ? $validators : array($validators);
	
	$this->validators[$attribute] = array_merge($old_validators, $validators);
}

# Perform validators (Should include caching)
protected function perform_validations()
{
	# By default validation passes.
	$validates = true;
	
	# Reset errors to prevent inaccuracies.
	$this->reset_errors();

	# If validators are present, lets execute them.
	if ( count($this->validators) )
	{
		foreach($this->validators as $attribute => $validators)
		{	
			# Validators per attribute
			$validators = is_array($validators) ? $validators : array($validators);
			
			# Execute individual validators
			foreach($validators as $name => $options) 
			{
				# Validator Name
				$validator = is_numeric($name) ? $options : $name;
				
				# Validator Options
				$options = !is_numeric($name) ? $options : array();

				# Execute
				if ( ! $this->call_validator($validator, $this, $attribute, $options) )
				{
					# Validation failed. But we'll keep looping.
					$validates = false;
				}
			}
		}
	}

	return $validates;
}

# Find validator and execute it.
protected function call_validator($validator, $object, $attribute, $options)
{
	# Get Callback Signature.
	$callback = $this->validator_callback($validator);

	# If callback exists, than run callback.
	return $callback && call_user_func($callback, $object, $attribute, $options);
}

# Find callback signature for validator.
protected function validator_callback($validator)
{
	$callback = false;

	# Create string signature.
	$function_name = 'jot_validate_'.strtolower($validator);
	$method_name = 'validate_'.strtolower($validator);
	
	# If method exists on model use it.
	if ( method_exists($this, $method_name ) )
	{
		$callback = array($this, $method_name);
	}
	# If function exists use it.
	else if ( function_exists($function_name) )
	{
		$callback = $function_name;
	}
	
	return $callback;
}

/*-------------------------------------------------
PERSISTANCE
-------------------------------------------------*/
protected $new_record = true;
protected $destroyed = false;

# Returns boolean if object is persisted. A persisted object
# is stored in the database.
# If the object is new or destroy an object is *not* persisted.
public function persisted()
{
	return ! ($this->new_record || $this->destroyed);
}

# Reload attributes from database.
public function reload()
{
	# Can only reload object if it's persisted.
	if ( $this->persisted() )
	{
		# Get primary key so we can retrieve record from database.
		$id = $this->read_attribute($this->primary_key());
		
		JotIdentityMap::disable();
		
		# Load object from database.
		$new_object = $this->first($id);
		
		JotIdentityMap::enable();
		
		# Assign attributes from new object to this object.
		$this->attributes = $new_object->attributes();
		
		# Free Memory of object.
		unset($new_object);
	}
	
	# Enforce chainability.
	return $this;
}

# Creates single object using attributes and returns object.
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
#	$this->person_model->update(array(1, 2, 3), array('is_online'=>true));
#
#	# Multiple Objects with individual changes
#	$this->person_model->update(array(1=>));
#
public function update($id, $attributes = null)
{
	# There are multiple id's.
	# Will update all objects using attributes in ids.
	if ( is_array($id) )
	{
		# We will return a list of the objects updated.
		$objects = array();
		
		# Iterate over each id.
		foreach($id as $key => $value)
		{
			if ( is_numeric($value) )
			{
				# Update each object using same changes.
				# Stores object so we can later return it.
				$objects[] = $this->update($value, $attributes);
			}
			else
			{
				# Update each object using inidiviual changes.
				# Stores object so we can later return it.
				$objects[] = $this->update($key, $value);
			}
		}				
		
		# Return list of objects
		return $objects;
	}
	
	# Object single object.
	else
	{
		# Find object with id.
		$object = $this->first($id);
		
		# Update attributes on record.
		$object->update_attributes($attributes);
		
		# Return Object
		return $object;
	}
}

# Destroy an object
# This function has split functionality. If an integer or 
# array is supplied it will destroy that object(s).
# Otherwise the function will call destroy_object method.
public function destroy($id = null)
{
	return isset($id) ? $this->destroy_id($id) : $this->destroy_object();
}

# Destroy on object based on an id or array.
# The method calls the destroy method.
protected function destroy_id($id)
{
	# Ensure array is ids or id used.
	$ids = is_array($id) ? $id : array($id);

	# We will return a list of the objects destroyed.
	$objects = array();

	# Loop each id and destroy id.
	foreach($ids as $id)
	{
		# Add object to array for later return.
		$objects[] = $object = $this->first($id);
		
		# Destroy object.
		$object->destroy();
	}
	
	# Return objects that were destroyed.
	return $objects;
}

# This method delete an actual object from memory.
protected function destroy_object()
{
	# Only delete object if persisted.
	if ( $this->persisted() )
	{
		$this->call_hook('before_destroy');
		$this->_delete();
		$this->call_hook('after_destroy');		
	}
	
	# This object is no longer persisted.
	$this->destroyed = true;
}

# Internal Method for deleting a record in the database.
protected function _delete()
{
	# Record id to delete from table.
	$id = $this->read_attribute($this->primary_key);
	
	# Only delete if id exists.
	if ( $id )
	{ 	
		# Delete record from table.
		$this->db->delete($this->table_name(), array($this->primary_key() => $id));	
	}
}

/*-------------------------------------------------
SAVE
-------------------------------------------------*/	

# Saves the object
#
# A database row is created if this object is a new_record, otherwise
# it will update the existing record in the database.
public function save($validate  = true)
{		
	# We do not want previous errors conflicting with new errors.
	$this->reset_errors();

	# If validation is required and fails, do not save object
	if ( $validate && ! $this->is_valid() ) 
	{
		return $this;
	}
	
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

# Internal Method for updating a record in the database
protected function _update()
{
	# Set created and updated at attributes if timestamps exist.
	if ( $this->timestamps)
	{
		$this->write_attribute($this->updated_at_column_name, time());
	}	
		
	# Get ID of record we should update.
	$id = $this->read_attribute($this->primary_key);
	
	# Update record in database.
	$this->db->update($this->table_name(), $this->attributes(false), array($this->primary_key=>$id));
}

# Internal Method for creating a record in the database
protected function _create()
{		
	# Set created and updated at attributes if timestamps exist.
	if ( $this->timestamps)
	{
		$this->write_attribute($this->created_at_column_name, time());
		$this->write_attribute($this->updated_at_column_name, time());
	}
	
	# Insert object into table
	$this->db->insert($this->table_name(), $this->attributes(false));
	
	# Set primary key.
	$id = $this->db->insert_id();
	$this->write_attribute($this->primary_key(), (string)$id);
	
	# This object is now persisted
	$this->new_record = false;
	
	# Add to Identity Map
	JotIdentityMap::add($this);
}
	
/*-------------------------------------------------
CALCULATIONS
-------------------------------------------------*/	
# Counts rows with conditions and returns int.
public function count($conditions = array())
{
	$this->db->flush_cache();
	$this->_find($conditions);

	return (int)$this->db->count_all_results();		
}

# Calculates average on attribute and returns float.
public function average($attribute, $conditions = array())
{
	$this->db->select_avg($attribute);
	$this->_find($conditions);
	
	return (float)value_for_key($attribute, $this->db->get()->row());
}

# Calculates maximum for attribute and returns float.
public function maximum($attribute, $conditions = array())
{
	$this->db->select_max($attribute);
	$this->_find($conditions);
	
	return (float)value_for_key($attribute, $this->db->get()->row());
}

# Calculates minimum for attribute and returns float.
public function minimum($attribute, $conditions = array())
{
	$this->db->select_min($attribute);
	$this->_find($conditions);
	
	return (float)value_for_key($attribute, $this->db->get()->row());
}

# Calculates sum of attribute and returns float.
public function sum($attribute, $conditions = array())
{
	$this->db->select_sum($attribute);
	$this->_find($conditions);
	
	return (float)value_for_key($attribute, $this->db->get()->row());
}
	
/*-------------------------------------------------
FINDERS
-------------------------------------------------*/

protected $limit;
protected $order;
protected $conditions = array();
protected $join = array();

# Return true if conditions return results.
public function exists($conditions = array())
{
	return !!$this->count($conditions);		
}

# Returns first row using conditions
public function first($conditions = array())
{
	return $this->first_or_last('first', $conditions);
}

# Returns last row using conditions
public function last($conditions = array())
{
	return $this->first_or_last('last', $conditions);
}

protected function first_or_last($type, $conditions = array()) 
{
	$_order = $this->_order();

	if ( $type == 'last' )
	{
		$order = strtolower($_order);
		$order = str_replace('desc', 'ASC', $order);
		$this->order = str_replace('asc', 'DESC', $order);
	}
	
	$result = $this->find($conditions, 0, 1);

	$this->order = $_order;
		
	return count($result) ? $result[0] : null;
}

# Returns all rows using conditions
public function all($conditions = array())
{	
	return $this->find($conditions, 0, 0);		
}

# Returns rows filterd by conditions
public function find($conditions = array(), $offset = 0, $limit = null)
{
	# Load Primary Key
	$primary_key = $this->primary_key();
	
	# Array to store includes
	$includes = array();
	
	# Array to store joins
	$join = array();

	# Load object from identity map if possible.
	if ( count($conditions) == 1 && $id = value_for_key($primary_key, $conditions))
	{
		# If object id exists in Identity Map return object.
		if ( $id && $object = JotIdentityMap::get(get_class($this), $id))
		{
			# This data should be cleared because it was orginally was going
			# to be used to generate sql for this find.
			$this->db->flush_cache();
			
			# Return object from Identity Map.
			return array($object);
		}
	}

	# Lets set limit if available
	if (isset($limit)) 
	{
		$this->limit = $limit;
	}

	# Check $conditions array to see if it is a one for all
	if ( 	value_for_key('conditions', $conditions) || 
			value_for_key('includes', $conditions) ||
			value_for_key('order', $conditions) ||
			value_for_key('offset', $conditions) ||
			value_for_key('page', $conditions) ||
			value_for_key('limit', $conditions))
	{		
		$conf 		= $conditions;
		$conditions = isset($conf['conditions']) ? $conf['conditions'] : array();
		$offset		= isset($conf['offset']) ? $conf['offset'] : 0;

		# We have a limit. Lets store it!
		if(isset($conf['limit'])) 
		{
			$this->limit = $conf['limit'];
		}
		
		# We have an order. Lets store it!
		if(isset($conf['order'])) 
		{
			$this->order = $conf['order'];
		}

		# We have an order. Lets store it!
		if(isset($conf['includes'])) 
		{
			$includes = $conf['includes'];
		}
				
		# Page isset so lets do the math and fix things up.
		if (isset($conf['page']) && $page = $conf['page'])
		{
			$offset = $this->_limit() * ($page - 1);
		}
	}

	# Turn conditions into where statements	.	
	$this->_find($conditions);

	# If needed set default order			
	$this->db->order_by($this->_order());
	
	# Limit and Offset
	if ( $this->_limit() )
	{
		$this->db->limit($this->_limit(), $offset);
	}

	# Instantiate jot objects from database rows.
	$r = $this->db->get();

	# Force CodeIgniter to load rows info objects.
	$r->result_object();
	$result = array();
	
	$result = new JotCollection;
	
	for ($i=0, $len=count($r->result_object); $i<$len; $i++)
	{
		$result[] = $this->instantiate($r->result_object[$i], array(
			'new_record' => false,
		));
	}
	
	# Return array of jot objects
	return $result;
}

# Private find method. The purpose of this is too apply db functions
# to the core CodeIgniter object.
protected function _find($conditions = array())
{	
	# Finialize Conditions
	$conditions = $this->_conditions($conditions);

	# Create Where Statements
	foreach($conditions as $key => $value)
	{
		# If value is indexed. It will be used for where_in
		# array('id'=>array(1,2,3))
		if ( is_array($value) && ! is_assoc($value) )
		{
			$this->db->where_in($key, $value);
		}
		
		# Simple where statement using 'AND'
		else
		{
			$this->db->where($key, $value);
		}
	}
	
	if ( $this->join )
	{
		$this->db->join($this->join[0], $this->join[1]);
	}
	
	# Set From
	$this->db->from($this->table_name());
}

# Validates conditions variable.
protected function _conditions($conditions = array())
{
	# Make sure conditions is an array.
	if ( isset($conditions) && ! is_array($conditions) )
	{
		$conditions = array($conditions);
	}	
		
	# Set Conditions
	$conditions = is_array($conditions) ? array_merge($this->conditions, $conditions) : $this->conditions;
	
	# Return empty array if conditions do not exist
	if ( $conditions == null ) 
	{
		return array();
	}
	 
	# If conditions is a single integer or list of ids return ids.
	if ( is_numeric($conditions) || ! is_assoc($conditions) )
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

# Return limit
protected function _limit()
{
	# Set limit to default (Only when unset)
	if ( ! isset($this->limit) )
	{
		$this->limit = 10;
	}
	
	# If limit is -1. Than limit is unlimited.
	if ( $this->limit == -1 )
	{
		return false;
	}
	
	return $this->limit;
}

# Return order (id ASC)
protected function _order()
{
	# Set default order
	if ( ! isset($this->order) )
	{
		$this->order = $this->primary_key().' ASC';
	}
	
	return $this->order;	
}

/*-------------------------------------------------
TABLE NAME
-------------------------------------------------*/

protected $table_name = null;
protected $singular_table_name = null;
protected $plural_table_name = null;

# Set and get tablename
protected function table_name($table_name = null)
{	
	# Set table name.
	if ( empty($this->table_name) || $table_name )
	{	
		$generated_table_name = $this->inflector->pluralize(str_replace('_model', '', strtolower(get_class($this))));
		$this->table_name = $table_name ? $table_name : $generated_table_name;
	}	
	
	return $this->table_name;
}

# Returns singular form of model name.
public function singular_table_name()
{
	# Cache singular table name.
	if ( ! $this->singular_table_name )
	{
		$this->singular_table_name = strtolower($this->inflector->singularize($this->table_name()));
	}
	
	return $this->singular_table_name;
}

# Returns plural form of model name.
public function plural_table_name()
{
	# Cache plural table name.
	if ( ! $this->plural_table_name )
	{
		$this->plural_table_name = strtolower($this->inflector->pluralize($this->table_name()));
	}
	
	return $this->plural_table_name;	
}

/*-------------------------------------------------
SERIALIZABLE
-------------------------------------------------*/

# Serializes the data that is required to recreate
# object.
#
# - Attributes
# - Errors
# - Persistance
#
public function serialize()
{
	$data = array();
	
	$data['errors'] = $this->errors;
	$data['attributes'] = $this->attributes;
	$data['new_record'] = $this->new_record;
	$data['destroyed'] = $this->destroyed;
	
	return serialize($data);
}

# Unserializes the data that to recreate
# object.
#
# NOTE: JOT MODEL MUST BE IN MEMORY BEFORE RUNNING
# THIS FUNCTION!
#
public function unserialize($data)
{		
	$data = unserialize($data);
	
	$this->errors = value_for_key('errors', $data);
	$this->attributes = value_for_key('attributes', $data);
	$this->new_record = value_for_key('new_record', $data);
	$this->destroyed = value_for_key('destroyed', $data);
}

/*-------------------------------------------------
UPLOAD METHODS
-------------------------------------------------*/

# Cache stores information from $_FILES
protected $files_cache = null;

# Store attachment objects
protected $attachments = array();

# Creates attachment object and hooks into Jot.
public function has_attached_file($name, $options = array())
{
	$this->attachments[$name] = new JotAttachment($name, $this, $options);
	$this->add_transient($name);
	
	if ( ! in_array('save_attached_files', value_for_key('before_save', $this->hooks) ? value_for_key('before_save', $this->hooks) : array()) )
	{
		$this->before_save('save_attached_files');
	}
}

# Writes attributes to object and moves file to final path.
public function save_attached_files()
{
	foreach($this->attachments as $name => $attachment)
	{
		# If file exists lets attach it.
		if ( $file = $this->_files($name) )
		{
			# Write attributes
			$this->write_attribute("{$name}_file_name", $file['name']);
			$this->write_attribute("{$name}_content_type", $file['type']);
			$this->write_attribute("{$name}_file_size", $file['size']);
			$this->write_attribute("{$name}_updated_at", time());

			# Move file to attachment path
			$this->write_file($file, $attachment);
			$this->generate_attachment_styles($attachment);
		}
	}
}

public function regenerate_attachment_styles()
{
	foreach($this->attachments as $name => $attachment)
	{
		$this->generate_attachment_styles($attachment);
	}
}

public function generate_attachment_styles($attachment)
{
	// Generate Styled Files
	$options = $attachment->options;
	$valid_types = array(
		'image/jpeg',
		'image/png'
	);
	
	$path = str_replace('{filename}', '', $attachment->base_path);

	if ( $styles = value_for_key('styles', $options) )
	{
		foreach($styles as $name => $dimensions)
		{
			$dir = $attachment->folder_path($name);

			if ( ! is_dir($dir) )
			{
				mkdir($dir);
			}
												
			$file_name = $this->read_attribute("{$attachment->name}_file_name");
			$image = new JotImage;
			$image->load($attachment->file_path);
		
			$image->style($dimensions);
		
			$image->save($attachment->file_path($name));
		}
	}
}

# Write file to filesystem.
protected function write_file($file, $attachment)
{
	if ( value_for_key('downloaded', $file) )
	{
		if ( file_exists($file['tmp']) )
		{
			rename($file['tmp'], $attachment->file_path);
		}
	}
	else
	{
		$path = str_replace(FCPATH, '', $attachment->folder_path());
		$folders = explode('/', $path);
		$path = rtrim(FCPATH, '/');
				
		foreach($folders as $folder)
		{
			if ( $folder) {
				$path .= '/'.$folder;
	
				if ( ! file_exists($path) )
				{					
					mkdir($path);
				}
			}
		}
							
		move_uploaded_file($file['tmp'], $attachment->file_path);	
	}
}

# Return attachment
protected function read_attachment($name)
{
	return value_for_key($name, $this->attachments);
}

# Does attachment exist?
protected function is_attachment($name)
{
	return array_key_exists($name, $this->attachments);
}

# Download and get information about file.
protected function _url($name, $url)
{
	$attachment = $this->read_attachment($name);
	$folder_path = $attachment->folder_path('_temp');
	
	if ( ! is_dir($folder_path) )
	{
		mkdir($folder_path);
	}
	
	$info = pathinfo($url);
		
	$file   = array_shift(explode('?', basename($url)));
	$ext 	= value_for_key('extension', $info);
		
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
	$info = curl_getinfo($ch);
	$content_type = value_for_key('content_type', $info);
	$content_size = value_for_key('request_size', $info);

    curl_close($ch);
	
	// Vzaar Thumbnail Support
	if (preg_match("/vzaar.com/", $url) )
	{
		preg_match("/\<a href=\"(?<url>.*)\"\>redirected\<\/a\>/", $response, $match);
		$url = value_for_key('url', $match);
		
		$info = pathinfo($url);

		$file   = array_shift(explode('?', basename($url)));
		$ext 	= value_for_key('extension', $info);

	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $response = curl_exec($ch);
		$info = curl_getinfo($ch);
		$content_type = value_for_key('content_type', $info);
		$content_size = value_for_key('request_size', $info);

	    curl_close($ch);	
	}
	
	$path = $folder_path.$file;
	
	file_put_contents($path, $response);

	return $this->set_file_attachment(
		$name,
		$ext,
		$content_type,
		$path,
		0,
		$content_size,
		true
	);
}

public function set_file_attachment($key, $ext, $type, $temp_path, $error, $size, $downloaded = false)
{
	$this->load->helper('string');
	
	$this->files_cache[$key] = array(
		'name' => random_string('alpha', 10).'.'.$ext,
		'type' => $type,
		'tmp' => $temp_path,
		'error' => $error,
		'size' => $size,
		'downloaded' => $downloaded
	);
}

# Return files
public function _files($attachment_name)
{
	if ( ! is_array($this->files_cache) ) $this->files_cache = array();
	
	# Does file cache exist
	if ( ! value_for_key($attachment_name, $this->files_cache) )
	{
		# Lets check each attachment to see if an associated file exists.
		foreach($this->attachments as $name => $attachment) 
		{
			$attachment_value = $this->read_attribute($attachment_name);			
		
			if ( is_url_valid($attachment_value) )
			{
				$this->_url($attachment_name, $attachment_value);
			}
			else
			{		
				$file = value_for_key($this->singular_table_name(), $_FILES);
				$filename = value_for_key("name.{$name}", $file);
				
				if ( $filename ) 
				{
				
					$info = pathinfo($filename);
					$ext = value_for_key('extension', $info);
	
					# Create file cache instance
					$type = value_for_key("type.{$name}", $file);
					$tmp_name = value_for_key("tmp_name.{$name}", $file);
					$error = value_for_key("error.{$name}", $file);
					$size = value_for_key("size.{$name}", $file);
					
					$this->set_file_attachment($name, $ext, $type, $tmp_name, $error, $size);
				}
			}
		}
	}
		
	# Return file attachment from file cache
	return value_for_key($attachment_name, $this->files_cache);
}

/*-------------------------------------------------
MAGIC METHODS
-------------------------------------------------*/

#
# Options:
#    new_record: true|false
#
public function __construct($attributes = array(), $options = array()) 
{
	# CodeIgniter adds access to super object.
	parent::__construct();
	
	static $loaded = false;

	$this->init();
	
	# We only want to execute the following functions once!
	if ( $loaded == false )
	{
		$this->load->add_package_path(APPPATH.'third_party/jot');
	
		$this->load->library('inflector');
		$this->load->helper(array(
			'jot_validation', 
			'jot_primitive_helper',
			'jot_array_helper', 
			'jot_url_helper'
		));
		
		$loaded = true;
	}
	
	# Set default order
	if ( $order = value_for_key('order', $options) ) 
	{	
		$this->order = $order;
	}
	
	# Set default limit
	if ( $limit = value_for_key('limit', $options) ) 
	{	
		$this->limit = $limit;
	}

	# Set default limit
	if ( $conditions = value_for_key('conditions', $options) ) 
	{	
		$this->conditions = $conditions;
	}
	
	# Set default join
	if ( $join = value_for_key('join', $options) ) 
	{	
		$this->join = $join;
	}

	# If attributes exist assign them.
	if ( $attributes && (is_array($attributes) || is_object($attributes)) )
	{
		$id = value_for_key($this->primary_key(), $attributes);

		if ( $id && $object = JotIdentityMap::get(get_class($this), $id))
		{
			return $object;
		}

		$this->assign_attributes($attributes);

		$this->new_record = array_key_exists('new_record', $options) ? !!$options['new_record'] : true;

		if ( $id )
		{
			JotIdentityMap::add($this);
		}

		return $this;
	}
}

# Returns object
#
# If attribute id exists and object exists in memory, that object will
# be returned. Otherwise a new object will be created using attributes.
#
public function instantiate($attributes = array(), $options = array())
{
	# If attributes exist assign them.
	if ( is_object($attributes) || (is_array($attributes) && count($attributes) > 0) )
	{
		# Get value for primary key
		$id = value_for_key($this->primary_key(), $attributes);

		# Check if object exists in identity map.
		if ( $id && $object = JotIdentityMap::get(get_class($this), $id))
		{
			# Object is in Identity Map
			return $object;
		}	
	}	

	# Create new Jot Object and return
	return new $this($attributes, $options);
}

# Returns string describing object
#
# format: blog name: "Blog", slug: "blog"
#
public function __toString()
{	
	$fields_strings = array();	
	$string = '';
	
	# Table Name
	$string .= $this->singular_table_name();

	foreach($this->attributes as $attribute => $value)
	{
		# Handle timestamps
		if ($attribute == 'created_at' || $attribute == 'updated_at')
		{
			$value = date('Y-m-d\TH:i:sP', $value);			
		}
		
		# Handle Strings
		else if ( is_string($value) )
		{
			$value = '"'.$value.'"';
		}
		
		# Create Attribute String
		if ( $value )
		{
			$fields_strings[] = $attribute.': '.$value;
		}
		
	}
		
	# Return formatted string
	return $string . ' '.implode(', ', $fields_strings);
}

# Allows for attributes and associations to be assigned.
public function __set($key, $value)
{	
	# Association
	if ( $this->has_association($key) )
	{		
		$this->write_association($key, $value);
	}

	# Retrieve attribute if getter function exists
	else if ( $this->has_write_attribute_function($key) )
	{
		$this->write_attribute_function($key, $value);
	}
	
	# Attribute
	else
	{			
		$this->write_attribute($key, $value);
	}
}

# Allows for meta functions to exist.
public function __call($name, $arguments)
{
	# Pseudo method to make creating new associated objects faster.
	if ( substr($name, 0, 7) == 'create_' )
	{		
		$association = $this->get_association(strtolower(substr($name, 7)));
		return $association ? $association->create($aruments[0]) : false;
	}

	# Pseudo method to make accessing find_by_ faster.
	elseif ( substr($name, 0, 8) == 'find_by_' )
	{
		$field = substr($name, 8);
		
		$conditions = array_merge(isset($arguments[1]) ? $arguments[1] : array(), array($field => $arguments[0]));
		$offset = isset($arguments[2]) ? $arguments[2] : 0;
		$limit  = isset($arguments[3]) ? $arguments[3] : null;
		
		return $this->find($conditions, $offset, $limit);
	}
	
	# Pseudo method to make accessing first_by_ faster.
	elseif ( substr($name, 0, 9) == 'first_by_' )
	{
		$field = substr($name, 9);
		
		$conditions = array_merge(isset($arguments[1]) ? $arguments[1] : array(), array($field => $arguments[0]));
		
		return $this->first($conditions);
	}
	
	# Pseudo method to make accessing last_by_ faster.
	elseif ( substr($name, 0, 8) == 'last_by_' )
	{
		$field = substr($name, 8);
		
		$conditions = array_merge(isset($arguments[1]) ? $arguments[1] : array(), array($field => $arguments[0]));
		
		return $this->last($conditions);
	}
}

# Returns row attributes and properties from CodeIgniter.
public function __get($key)
{
	$CI =& get_instance();
	
	# Return property from CodeIgniter if exists.
	if ( property_exists($CI, $key) )
	{
		return $CI->$key;
	}	
	
	# Retrieve attribute if getter function exists.
	if ( $this->has_read_attribute_function($key) )
	{
		return $this->read_attribute_function($key);
	}
	
	# Only retrieve attribute if it exists.
	if ( $this->has_attribute($key) )
	{
		return $this->read_attribute($key);
	}
	
	# Return attachment with key.
	if ( $this->is_attachment($key) )
	{
		return $this->read_attachment($key);
	}
	
	# Return association with key.
	if ( $this->has_association($key) )
	{
		return $this->read_association($key);
	}
	
	# There is absolutely nothing to return.
	return null;
}

} # End Class