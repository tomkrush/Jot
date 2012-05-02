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

class JotHasAndBelongsToManyAssociation extends JotAssociation
{
	public function create($attributes)
	{
		return false;
	}
	
	public function set($value)
	{
	
	}
	
	public function get()
	{
		return false;
	}
}

class JotHasManyAssociation extends JotAssociation
{
	public function create($attributes)
	{
		return false;
	}

	public function set($value)
	{
		$foreign_key = $this->foreign_key();
		$id = $this->object_id();
		
		if ( $as = $this->polymorphic() )
		{
			$foreign_type = $as.'_type';
			
			foreach($value as $object)
			{
				$object->update_attributes(array(
					$foreign_type => $this->object->singular_table_name(),
					$foreign_key => $id
				));
			}
		}
		else
		{
			foreach($value as $object)
			{
				$object->update_attribute($foreign_key, $id);
			}
		}
	}

	public function get()
	{
		# Create Object
		$class_name = $this->class_name();	

		$this->load->model($class_name);

		$options = array();
		
		if ( $order = $this->order() ) {
			$options['order'] = $order;
		}
		
		if ( $limit = $this->limit() ) {
			$options['limit'] = $limit;
		}
		
		if ( $conditions = $this->conditions() ) {
			$options['conditions'] = $conditions;
		}

		$object = new $class_name(null, $options);
		
		return $object;
	}
	
	protected function conditions()
	{
		$conditions = value_for_key('conditions', $this->options, array());
		
		if ( $as = $this->polymorphic() )
		{
			$conditions[$as.'_type'] = $this->object->singular_table_name();
		}
		
		$conditions[$this->foreign_key()] = $this->object_id();
		
		return $conditions;
	}
	
	protected function limit()
	{
		return value_for_key('limit', $this->options);
	}
	
	protected function order()
	{
		return value_for_key('order', $this->options);
	}

	protected function polymorphic()
	{
		return value_for_key('as', $this->options);
	}
		
	protected function foreign_key()
	{
		return value_for_key('foreign_key', $this->options, $this->name().'_id');
	}
	
	protected function name()
	{
		if ( $as = $this->polymorphic() )	
		{
			return $as;
		}

		return $this->object->singular_table_name();
	}
	
	protected function object_id()
	{
		return $this->object->read_attribute($this->object->primary_key());
	}

	protected function class_name()
	{		
		$default = $this->inflector->singularize($this->name);	
		return value_for_key('class_name', $this->options, ucwords($default).'_Model');
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
				
		# Create Conditions
		$conditions = array(
			$this->foreign_key() => $this->object_id()
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
		return value_for_key('foreign_key', $this->options, $this->name().'_id');
	}

	protected function object_id()
	{
		return $this->object->read_attribute($this->object->primary_key());
	}

	protected function name()
	{
		if ( $as = $this->polymorphic() )	
		{
			return $as;
		}

		return $this->object->singular_table_name();
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
		$this->object->update_attribute($this->foreign_key(), $this->object_id());

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
		$class_name = $this->class_name();
		$name = $this->name();
	
		return $this->object->read_attribute($this->foreign_key());
	}
	
	protected function name()
	{
		if ( $this->polymorphic() )	
		{
			return $this->name;
		}
		
		$class_name = $this->class_name();
		$this->load->model($class_name);
		
		return $this->$class_name->singular_table_name();
	}
	
	protected function foreign_key()
	{	
		return value_for_key('foreign_key', $this->options, $this->name().'_id');
	}
	
	protected function foreign_type()
	{		
		return value_for_key('foreign_type', $this->options, $this->name().'_type');
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