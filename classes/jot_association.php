<?php

abstract class JotAssociation
{
	protected $options;
	protected $object;
	protected $name;
	
	public function __construct($name, $object, $options = array())
	{
		$this->name = $name;
		$this->object = $object;
		$this->options = $options;
	}
	
	abstract public function set($value);
	abstract public function get();
	abstract public function create($attributes);
	
	public function options()
	{
		return $this->options;
	}
	
	public function __get($key)
	{
		$CI =& get_instance();
		
		# Return property from CodeIgniter if exists
		if ( property_exists($CI, $key) )
		{
			return $CI->$key;
		}	
	}
}

class JotHasManyAssociation extends JotAssociation
{
	public function create($attributes)
	{
		return FALSE;
	}

	public function set($value)
	{
		$options = $this->options();
		$jot = $this->object;
		$key = $this->name;
					
		$foreign_id = $jot->read_attribute($jot->primary_key());
		
		if ( $polymorphic = value_for_key('as', $options) )
		{
			$foreign_type = $polymorphic.'_type';
			$foreign_key = $polymorphic.'_id';
			
			foreach($value as $object)
			{
				$object->update_attributes(array(
					$foreign_type => $jot->singular_table_name(),
					$foreign_key => $foreign_id
				));
			}
		}
		else
		{
			$foreign_key = $jot->singular_table_name().'_id';
			$foreign_id = $jot->read_attribute($jot->primary_key());

			foreach($value as $object)
			{
				$object->update_attribute($foreign_key, $foreign_id);
			}
		}
	}

	public function get()
	{
		$options = $this->options();
		$jot = $this->object;
		$key = $this->name;

		# Create Object			
		$modelName = ucwords($this->inflector->singularize($key)).'_Model';

		$this->load->model($modelName);

		$object = new $modelName;
		$id = $jot->read_attribute($jot->primary_key());
		
		if ( $polymorphic = value_for_key('as', $options) )
		{
			$foreign_type = $polymorphic.'_type';
			$foreign_id = $polymorphic.'_id';
			
			$object->set_base_filter(array(
				$foreign_type => $jot->singular_table_name(),
				$foreign_id => $id
			));
		}
		else
		{
			$foreign_type = $jot->singular_table_name().'_id';

			$object->set_base_filter(array(
				$foreign_type => $id
			));
		}
												
		return $object;
	}
}

class JotHasOneAssociation extends JotAssociation
{
	public function create($attributes)
	{		
		$class_name = $this->class_name();
		
		# Create associated object.
		return $this->$class_name->create(array(
			$this->foreign_key() => $this->object_id()
		));
	}

	public function set($value) 
	{
		# Polymorphic writes a foreign type.
		if ( $as = $this->polymorphic() )
		{
			$value->write_attribute($as.'_type', $this->object->singular_table_name());
		}
		
		# Write key
		$value->write_attribute($this->foreign_key(), $this->object_id());
		
		#Persist
		$value->save();
	}

	public function get()
	{
		$class_name = $this->class_name();
		$this->load->model($class_name);
		
		$foreign_key = $this->foreign_key();
		
		# Create Conditions
		$conditions = array(
			$foreign_key => $this->object_id()
		);	

		if ( $as = $this->polymorphic() )
		{						
			$conditions[$as.'_type'] = $this->object->singular_table_name();			
		}

		# Load Object		
		return $this->$class_name->first($conditions);
	}
	
	protected function polymorphic()
	{
		return value_for_key('as', $this->options);
	}
		
	protected function foreign_key()
	{
		if ( $as = $this->polymorphic() ) {
			$default = $as.'_id';
		} else {
			$default = $this->object->singular_table_name().'_id';
		}
		
		return value_for_key('foreign_key', $this->options, $default);
	}

	protected function object_id()
	{
		return $this->object->read_attribute($this->object->primary_key());
	}

	protected function class_name()
	{		
		# Return stored class name
		if ( $this->polymorphic() ) 
		{		
			$default = $this->inflector->singularize($this->name);
		}
		
		# Use name of object
		else 
		{
			$default = $this->name;
		}
		
		return value_for_key('class_name', $this->options, ucwords($default).'_Model');
	}
}

class JotBelongsToAssociation extends JotAssociation
{
	# Create new association
	public function create($attributes)
	{				
		$class_name = $this->class_name();
		$object = $this->load->model($class_name);
		
		# Create associated object.
		$object = $this->$class_name->create($attributes);

		# Create association with this model.
		$foreign_type = $object->singular_table_name().'_id';
		$foreign_id = $object->read_attribute($object->primary_key());

		$this->object->update_attribute($foreign_type, $foreign_id);

		return $object;
	}

	# Associated object
	public function set($value)
	{
		# Polymorphic writes a foreign type.
		if ( $this->polymorphic() )
		{	
			$this->object->write_attribute($this->foreign_type(), $value->singular_table_name());	
		}
		
		# Write key
		$this->object->write_attribute($this->foreign_key(), $value->read_attribute($value->primary_key()));
		
		#Persist
		$this->object->save();
	}

	# Retrieve associated object.
	public function get()
	{
		# What is the class of the associated object?
		$class_name = $this->class_name();
		
		# Load the class
		$this->load->model($class_name);

		# Conditions to load associated object
		$conditions = array(
			$this->$class_name->primary_key() => $this->object_id()
		);
		
		# Load associated object
		return $this->$class_name->first($conditions);
	}
	
	protected function object_id()
	{
		return $this->object->read_attribute($this->name.'_id');
	}
	
	protected function foreign_key()
	{
		return value_for_key('foreign_key', $this->options, $this->name.'_id');
	}
	
	protected function foreign_type()
	{		
		return value_for_key('foreign_type', $this->options, $this->name.'_type');
	}
	
	# Return user defined class_name otherwise use default.			
	protected function class_name()
	{	
		# Return stored class name
		if ( $this->polymorphic() ) 
		{
			$default = $this->object->read_attribute($this->foreign_type());  
		}
		
		# Use name of object
		else 
		{
			$default = $this->name;
		}
			
		return value_for_key('class_name', $this->options, ucwords($default).'_Model');
	}
	
	# Return whether association is polymorphic.
	protected function polymorphic()
	{
		return !!value_for_key('polymorphic', $this->options);
	}
}