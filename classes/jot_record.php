<?php

class JotRecord extends CI_Model implements Serializable
{
		
/*-------------------------------------------------
ATTRIBUTE METHODS
-------------------------------------------------*/

protected $attributes = array();
protected $changed_attributes = array();
protected $transient_attributes = array();

#Set transient attributes
public function transient($attributes)
{
	$attributes = is_array($attributes) ? $attributes : array($attributes);

	$this->transient_attributes = $attributes;
}

# Allows you to assign multiple attributes.
public function assign_attributes($attributes)
{
	$attributes = (array)$attributes;

	foreach($attributes as $key => $value)
	{
		$this->write_attribute($key, $value);
	}		
}

# Returns attribute value if get function exists
public function read_attribute_function($attribute)
{
	$method_name = 'get_'.$attribute;

	if ( method_exists($this, $method_name) )
	{
		return $this->$method_name();
	}

	return NULL;
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
public function read_attribute($key)
{
	if ( array_key_exists($key, $this->attributes) )
	{
		return $this->attributes[$key];
	}

	return NULL;
}

protected function save_associations()
{
	$save_associations = $this->save_associations;
	$this->save_associations = array();

	$id = $this->read_attribute($this->primary_key());

	foreach($save_associations as $key => $value)
	{
		$modelName = ucwords($this->inflector->singularize($key)).'_Model';
		$this->load->model($modelName);
		$primary_key = $this->$modelName->primary_key();		

		if ( $this->has_many_association($key) )
		{
			foreach($value as $attributes)
			{
				$associated_id = value_for_key($primary_key, $attributes);

				$foreign_key = $this->singular_table_name().'_id';

				$attributes[$foreign_key] = $id;

				if ( $associated_id )
				{
					unset($attributes[$primary_key]);
					$this->$modelName->update($associated_id, $attributes);
				}
				else
				{
					$this->$modelName->create($attributes);
				}
			}
		}
		else if ($this->has_one_association($key) )
		{	
			$associated_id = value_for_key($primary_key, $value);

			$foreign_key = $this->singular_table_name().'_id';
			$value[$foreign_key] = $id;

			if ( $associated_id )
			{
				unset($value[$primary_key]);
				$object = $this->$modelName->update($associated_id, $value);
			}
			else
			{
				$object = $this->$modelName->create($value);
			}			
		}
		else if ($this->has_belongs_to_association($key) )
		{
			$associated_id = value_for_key($primary_key, $value);

			if ( $associated_id )
			{
				unset($value[$primary_key]);
				$object = $this->$modelName->update($associated_id, $value);
			}
			else
			{
				$object = $this->$modelName->create($value);
			}

			$foreign_key = $object->singular_table_name().'_id';			
			$this->update_attribute($foreign_key, $object->read_attribute($this->primary_key()));	
		}
	}
}

# Writes attribute value to object
public function write_attribute($key, $value)
{	
	$nested_attributes = str_replace('_attributes', '', $key);

	if ( $this->has_association($nested_attributes) )
	{
		$this->save_associations[$nested_attributes] = $value;
	}
	else
	{
		$this->attributes[$key] = $value;		
	}
}

# Read all attributes
public function attributes($transient = TRUE)
{
	$attributes = $this->attributes;

	if ( $transient === FALSE )
	{
		foreach($this->transient_attributes as $attribute)
		{
			unset($attributes[$attribute]);
		}
	}

	return $attributes;
}

# Returns boolean value if read function exists for attribute
public function has_read_attribute_function($attribute)
{
	$method_name = 'get_'.$attribute;

	return method_exists($this, $method_name);
}

# Returns boolean value if write function exists for attribute
public function has_write_attribute_function($attribute)
{
	$method_name = 'set_'.$attribute;

	return method_exists($this, $method_name);
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
BUILD FUNCTION
-------------------------------------------------*/
		
# Builds empty object using attributes.
public function build($attributes = array())
{
	return new $this($attributes);
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
	$hooks = value_for_key($name, $this->hooks, array());

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
	
	foreach($this->errors as $error)
	{
		$errors[] = $error[1];
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

protected $timestamps = TRUE;
protected $created_at_column_name = 'created_at';
protected $updated_at_column_name = 'updated_at';

# Model uses timestamps
protected function has_timestamps($bool)
{				
	$this->timestamps = $bool;
}

/*-------------------------------------------------
ASSOCATIONS
-------------------------------------------------*/
protected $base_filter = null;

protected $relationships = array('has_many' => array(), 'has_one' => array(), 'belongs_to' => array());
protected $relationship_vars = array();

public function write_association($association_name, $value)
{	
	# If has one association exists link objects
	if ($this->has_one_association($association_name) )
	{
		$options = $this->get_has_one_association($association_name);
		$foreign_id = $this->read_attribute($this->primary_key());

		if ( $polymorphic = value_for_key('as', $options) )
		{
			$foreign_type = $polymorphic.'_type';
			$foreign_key = $polymorphic.'_id';

			$value->update_attributes(array(
				$foreign_type => $this->singular_table_name(),
				$foreign_key => $foreign_id
			));
		}
		else
		{
			$foreign_key = $this->singular_table_name().'_id';

			# Add Association
			$value->write_attribute($foreign_key, $foreign_id);				
		}
	}
	
	# If has belongs to association link objects
	elseif ($this->has_belongs_to_association($association_name) )
	{
		$options = $this->get_belongs_to_association($association_name);

		if ( $polymorphic = value_for_key('polymorphic', $options) )
		{
			$foreign_type = $association_name.'_type';
			$foreign_key = $association_name.'_id';
			$foreign_id = $value->read_attribute($value->primary_key());
			
			$this->assign_attributes(array(
				$foreign_type => $value->singular_table_name(),
				$foreign_key => $foreign_id
			));			
		}
		else
		{
			$foreign_key = $value->singular_table_name().'_id';
			$foreign_id = $value->read_attribute($value->primary_key());

			# Add Association
			$this->write_attribute($foreign_key, $foreign_id);
		}	
	}
	
	# If has many assocation links objects
	elseif ( $this->has_many_association($association_name) )
	{
		$options = $this->get_has_many_association($association_name);

		$options = $this->get_has_many_association($association_name);
		$foreign_id = $this->read_attribute($this->primary_key());
		
		if ( $polymorphic = value_for_key('as', $options) )
		{
			$foreign_type = $polymorphic.'_type';
			$foreign_key = $polymorphic.'_id';
			
			foreach($value as $object)
			{
				$object->update_attributes(array(
					$foreign_type => $this->singular_table_name(),
					$foreign_key => $foreign_id
				));
			}
		}
		else
		{
			$foreign_key = $this->singular_table_name().'_id';
			$foreign_id = $this->read_attribute($this->primary_key());

			foreach($value as $object)
			{
				$object->update_attribute($foreign_key, $foreign_id);
			}
		}
	}	
}

public function read_association($key)
{
	if ( $this->has_association($key) )
	{
		if ( $this->has_many_association($key) )
		{
			# Create Object			
			$modelName = ucwords($this->inflector->singularize($key)).'_Model';

			$this->load->model($modelName);

			$options = $this->get_has_many_association($key);
			$object = new $modelName;
			$id = $this->read_attribute($this->primary_key());
			
			if ( $polymorphic = value_for_key('as', $options) )
			{
				$foreign_type = $polymorphic.'_type';
				$foreign_id = $polymorphic.'_id';
				
				$object->set_base_filter(array(
					$foreign_type => $this->singular_table_name(),
					$foreign_id => $id
				));
			}
			else
			{
				$foreign_type = $this->singular_table_name().'_id';

				$object->set_base_filter(array(
					$foreign_type => $id
				));
			}
											
			return $object;
		}
		
		# Object has one association
		else if ($this->has_one_association($key) )
		{
			$options = $this->get_has_one_association($key);
	
			if ( $polymorphic = value_for_key('as', $options) )
			{
				$foreign_type = $polymorphic.'_type';
				$foreign_id = $polymorphic.'_id';

				$id = $this->read_attribute($this->primary_key());

				$modelName = ucwords($this->inflector->singularize($key)).'_Model';
				
				$this->load->model($modelName);
				
				$conditions = array(
					$foreign_type => $this->singular_table_name(),
					$foreign_id => $id
				);
			}
			else
			{
				$modelName = ucwords($key).'_Model';

				$this->load->model($modelName);
								
				# Create Conditions
				$conditions = array(
					$this->singular_table_name().'_id' => $this->read_attribute($this->primary_key())
				);
			}

			# Load Object
			$object = $this->$modelName->first($conditions);
			
			return $object;
		}
		
		# Object has belongs association
		else if ($this->has_belongs_to_association($key) )
		{
			$options = $this->get_belongs_to_association($key);

			if (  value_for_key('polymorphic', $options) )
			{
				$foreign_type = $this->read_attribute($key.'_type');

				$modelName = ucwords($foreign_type).'_Model';

				$this->load->model($modelName);

				$id = $this->read_attribute($key.'_id');
			}
			else
			{
				$modelName = ucwords($key).'_Model';
				
				$this->load->model($modelName);
				
				$foreign_type = $this->$modelName->singular_table_name().'_id';
				$id = $this->read_attribute($foreign_type);
			}
		
		
			$conditions = array($this->$modelName->primary_key() => $id);
						
			return $this->$modelName->first($conditions);			
		}
	}	
}

protected function set_base_filter($conditions)
{
	if (is_array($conditions) === false) return;
	$this->base_filter = $conditions;
}

protected function has_association($association)
{	
	$has_many = $this->has_many_association($association);
	$has_one = $this->has_one_association($association);
	$belongs_to = $this->has_belongs_to_association($association);
	
	# If any association exists return TRUE.
	return $has_many || $has_one || $belongs_to;
}

protected function get_has_one_association($association)
{	
	return value_for_key("has_one.{$association}", $this->relationships, FALSE);
}

protected function has_one_association($association)
{
	$association = $this->get_has_one_association($association);
	return isset($association) && $association !== FALSE;
}

protected function has_one($association, $options = array())
{
	$this->relationships['has_one'][$association] = $options;
	$this->relationship_vars[] = $this->inflector->singularize($association);
}

protected function get_belongs_to_association($association)
{
	return value_for_key("belongs_to.{$association}", $this->relationships, FALSE);
}

protected function has_belongs_to_association($association)
{
	$association = $this->get_belongs_to_association($association);
	return isset($association) && $association !== FALSE;
}

protected function belongs_to($association, $options = array())
{
	$this->relationships['belongs_to'][$association] = $options;
	$this->relationship_vars[] = $this->inflector->singularize($association);
}

protected function get_has_many_association($association)
{
	return value_for_key("has_many.{$association}", $this->relationships, FALSE);
}

protected function has_many_association($association)
{	
	$association = $this->get_has_many_association($association);
	return isset($association) && $association !== FALSE;
}

protected function has_many($association, $options = array())
{
	$this->relationships['has_many'][$association] = $options;
	$this->relationship_vars[] = $this->inflector->pluralize($association);
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
protected function validates($attribute, $validators)
{
	# Add validator to object
	$this->validators[$attribute] = $validators;
}

# Perform validators (Should include caching)
protected function perform_validations()
{
	# By default validation passes.
	$validates = TRUE;
	
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
					$validates = FALSE;
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
	$callback = FALSE;

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
protected $new_record = TRUE;
protected $destroyed = FALSE;

protected $save_associations = array();

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
public function destroy($id = NULL)
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
	$this->destroyed = TRUE;
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
		$this->db->delete($this->table_name, array($this->primary_key() => $id));	
	}
}

/*-------------------------------------------------
SAVE
-------------------------------------------------*/	

# Saves the object
#
# A database row is created if this object is a new_record, otherwise
# it will update the existing record in the database.
public function save($validate  = TRUE)
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
	
	$this->save_associations();
	
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
	$this->db->update($this->table_name, $this->attributes(FALSE), array($this->primary_key=>$id));
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
	$this->db->insert($this->table_name, $this->attributes(FALSE));
	
	# Set primary key.
	$id = $this->db->insert_id();
	$this->write_attribute($this->primary_key(), (string)$id);
	
	# This object is now persisted
	$this->new_record = FALSE;
	
	# Add to Identity Map
	JotIdentityMap::add($this);
}
	
/*-------------------------------------------------
CALCULATIONS
-------------------------------------------------*/	
# Return count of items using conditions.
public function count($conditions = array())
{
	$this->db->flush_cache();
	$conditions = $this->_conditions($conditions);

	$this->_find($conditions);

	return (int)$this->db->count_all_results();		
}

public function average($attribute, $conditions = array())
{
	$this->db->select_avg($attribute);
	$conditions = $this->_conditions($conditions);
	$this->_find($conditions);
	$result =  $this->db->get()->row();

	return (float)value_for_key($attribute, $result);
}

public function maximum($attribute, $conditions = array())
{
	$this->db->select_max($attribute);
	
	$conditions = $this->_conditions($conditions);
	$this->_find($conditions);
	
	$result =  $this->db->get()->row();

	return (float)value_for_key($attribute, $result);
}

public function minimum($attribute, $conditions = array())
{
	$this->db->select_min($attribute);
	
	$conditions = $this->_conditions($conditions);
	$this->_find($conditions);
	
	$result =  $this->db->get()->row();

	return (float)value_for_key($attribute, $result);
}

public function sum($attribute, $conditions = array())
{
	$this->db->select_sum($attribute);
	
	$conditions = $this->_conditions($conditions);
	$this->_find($conditions);
	
	$result =  $this->db->get()->row();

	return (float)value_for_key($attribute, $result);
}
	
/*-------------------------------------------------
FINDERS
-------------------------------------------------*/	

# Validates conditions variable.
protected function _conditions($conditions)
{
	# Set Base Filter
	if ($this->base_filter !== null)
	{
		$conditions = array_merge($this->base_filter, $conditions);
	}	
	
	# Return empty array if conditions do not exist
	if ( $conditions == NULL ) 
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

# Return true if conditions return results.
public function exists($conditions = array())
{
	return !!$this->count($conditions);		
}

# Returns first row using conditions
public function first($conditions = array())
{						
	// $this->db->order_by($this->primary_key().' ASC');

	$result = $this->find($conditions, 0, 1);
	return count($result) ? $result[0] : NULL;
}

# Returns last row using conditions
public function last($conditions = array())
{			
	// $this->db->order_by($this->primary_key.' DESC');

	$result = $this->find($conditions, 0, 1);
	
	return count($result) ? $result[0] : NULL;
}

# Returns all rows using conditions
public function all($conditions = array())
{
	$conditions = $this->_conditions($conditions);
	
	return $this->find($conditions, 1, 0);		
}

# Returns a range of rows using conditions
public function find($conditions = array(), $page = 0, $limit = 10)
{
	$primary_key = $this->primary_key();

	if ( count($conditions) == 1 && $id = value_for_key($primary_key, $conditions))
	{
		if ( $id && $object = JotIdentityMap::get(get_class($this), $id))
		{
			$this->db->flush_cache();
			return array($object);
		}
	}
	
	$conditions = $this->_conditions($conditions);
						
	$this->_find($conditions);
	
	if ( $limit > 0 )
	{
		if ( $limit && $page )
		{
			$this->db->limit($limit, $page);
		} 
		else
		{
			$this->db->limit($limit, $page);
		}
	}

	$r = $this->db->get();
		
	$r->result_object();
	$result = array();
	
	for ($i=0, $len=count($r->result_object); $i<$len; $i++)
	{			
		// $result[] = new self($r->result_object[$i], array(
		// 	'new_record' => FALSE
		// ));
		
		$result[] = $this->instantiate($r->result_object[$i], array(
			'new_record' => FALSE
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
TABLE NAME
-------------------------------------------------*/

public $table_name = '';

# Set table name
protected function tablename($table_name)
{
	$this->table_name = $table_name;
}

# Guess table name using model name.
protected function _tablename()
{
	if ( empty($this->table_name) )
	{	
		$this->table_name = $this->inflector->pluralize(str_replace('_model', '', strtolower(get_class($this))));
	}

	return $this->table_name;
}

# Returns singular form of model name.
public function singular_table_name()
{
	return strtolower($this->inflector->singularize($this->table_name));
}

# Returns plural form of model name.
public function plural_table_name()
{
	return strtolower($this->inflector->pluralize($this->table_name));		
}

/*-------------------------------------------------
SERIALIZABLE
-------------------------------------------------*/

# Serializes the information that is required to recreate
# object.
#
# - Attributes
# - Errors
# - Persisted
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

public function unserialize($data)
{	
	$this->__construct();
	
	$data = unserialize($data);
	
	$this->errors = value_for_key('errors', $data);
	$this->attributes = value_for_key('attributes', $data);
	$this->new_record = value_for_key('new_record', $data);
	$this->destroyed = value_for_key('destroyed', $data);
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
	parent::__construct();

	$this->init();
	
	$this->load->add_package_path(APPPATH.'third_party/jot');

	$this->load->library('inflector');
	$this->load->helper('jot_validation');
	$this->load->helper('jot_array_helper');
	
	# Load in Table Name
	$this->_tablename();

	# If attributes exist assign them.
	if ( is_object($attributes) || (is_array($attributes) && count($attributes) > 0) )
	{
		$this->assign_attributes($attributes);

		$this->new_record = array_key_exists('new_record', $options) ? !!$options['new_record'] : TRUE;

		$id = value_for_key($this->primary_key(), $attributes);

		if ( $id && $object = JotIdentityMap::get(get_class($this), $id))
		{
			return $object;
		}

		if ( $id )
		{
			JotIdentityMap::add($this);
		}

		return $this;
	}
}

public function instantiate($attributes = array(), $options = array())
{
	# If attributes exist assign them.
	if ( is_object($attributes) || (is_array($attributes) && count($attributes) > 0) )
	{
		$id = value_for_key($this->primary_key(), $attributes);

		if ( $id && $object = JotIdentityMap::get(get_class($this), $id))
		{
			return $object;
		}	
	}	

	return new $this($attributes, $options);
}

# Returns string describing object
#
# format: blog name: "Blog", slug: "blog"
#
public function __toString()
{		
	$string = '';
	
	$string .= $this->singular_table_name();
	
	$fields_strings = array();
	
	foreach($this->attributes as $attribute => $value)
	{
		if ($attribute == 'created_at' || $attribute == 'updated_at')
		{
			$value = date('"F j, Y, g:i a"', $value);
		}
		else if ( is_string($value) )
		{
			$value = '"'.$value.'"';
		}
		
		if ( $value )
		{
			$fields_strings[] = $attribute.': '.$value;
		}
	}
		
	$string .= ' '.implode(', ', $fields_strings);

	return $string;
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
	# Is call a create_ method?
	if ( substr($name, 0, 7) == 'create_' )
	{
		# What is create_(x)?
		$key = strtolower(substr($name, 7));

		#Is key an association?
		if ( $this->has_association($key) )
		{
			$conditions = $arguments[0];
			$modelName = ucwords($key).'_Model';
			$object = $this->load->model($modelName);

			# Create Has One Assocation
			if ($this->has_one_association($key) )
			{	
				$foreign_type = $this->singular_table_name().'_id';
				$foreign_id = $this->read_attribute($foreign_type);

				# Add Association
				$conditions[$foreign_type] = $foreign_id;

				return $this->$modelName->create($conditions);
			}
			
			# Create Belongs To Association
			elseif ($this->has_belongs_to_association($key) )
			{
				# Create associated object.
				$object = $this->$modelName->create($conditions);
				
				# Create association with this model.
				$foreign_type = $object->singular_table_name().'_id';
				$foreign_id = $object->read_attribute($object->primary_key());

				$this->update_attribute($foreign_type, $foreign_id);
								
				return $object;
			}
		}			
	}
}

# Returns row attributes and properties from CodeIgniter.
public function __get($key)
{
	# Return property from CodeIgniter if exists
	$CI =& get_instance();
	if (property_exists($CI, $key)) return $CI->$key;		
	
	if ( $this->has_association($key) )
	{
		return $this->read_association($key);
	}
	
	# Retrieve attribute if getter function exists
	if ( $this->has_read_attribute_function($key) )
	{
		return $this->read_attribute_function($key);
	}
	
	# Only retrieve attribute if it exists
	if ( $this->has_attribute($key) )
	{
		return $this->read_attribute($key);
	}
	
	# There is absolutely nothing to return.
	return NULL;
}

} # End Class