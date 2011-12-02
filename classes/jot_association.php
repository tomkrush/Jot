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
	
	abstract public function write($value);
	abstract public function read();
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

	public function write($value)
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

	public function read()
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
		$jot = $this->object;
		$key = $this->name;
		
		$modelName = ucwords($key).'_Model';
		$object = $this->load->model($modelName);
		
		# Create associated object.
		$foreign_type = $jot->singular_table_name().'_id';
		$foreign_id = $jot->read_attribute($foreign_type);

		# Add Association
		$attributes[$foreign_type] = $foreign_id;

		return $this->$modelName->create($attributes);
	}

	public function write($value) 
	{
		$options = $this->options();
		$object = $this->object;
		$key = $this->name;
			
		$foreign_id = $object->read_attribute($object->primary_key());

		if ( $polymorphic = value_for_key('as', $options) )
		{
			$foreign_type = $polymorphic.'_type';
			$foreign_key = $polymorphic.'_id';

			$value->update_attributes(array(
				$foreign_type => $object->singular_table_name(),
				$foreign_key => $foreign_id
			));
		}
		else
		{
			$foreign_key = $object->singular_table_name().'_id';

			# Add Association
			$value->write_attribute($foreign_key, $foreign_id);				
		}
	}

	public function read()
	{
		$options = $this->options();
		$object = $this->object;
		$key = $this->name;

		if ( $polymorphic = value_for_key('as', $options) )
		{
			$foreign_type = $polymorphic.'_type';
			$foreign_id = $polymorphic.'_id';

			$id = $object->read_attribute($object->primary_key());

			$modelName = ucwords($this->inflector->singularize($key)).'_Model';
			
			$this->load->model($modelName);
			
			$conditions = array(
				$foreign_type => $object->singular_table_name(),
				$foreign_id => $id
			);			
		}
		else
		{
			$modelName = ucwords($key).'_Model';

			$this->load->model($modelName);
							
			# Create Conditions
			$conditions = array(
				$object->singular_table_name().'_id' => $object->read_attribute($object->primary_key())
			);			
		}

		# Load Object
		$object = $this->$modelName->first($conditions);
		
		return $object;
	}
}

class JotBelongsToAssociation extends JotAssociation
{
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

	public function write($value)
	{
		# Polymorphic writes a foreign type.
		if ( $this->polymorphic() )
		{	
			$this->object->write_attribute($this->foreign_type(), $value->singular_table_name());	
		}
		
		# Write key
		$this->object->write_attribute($this->foreign_key(), $value->read_attribute($value->primary_key()));
	}

	public function read()
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
	
	protected function class_name()
	{	
		if ( $this->polymorphic() ) {
			$default = $this->object->read_attribute($this->foreign_type());  
		}
		else {
			$default = $this->name;
		}
						
		return value_for_key('class_name', $this->options, ucwords($default).'_Model');
	}
	
	protected function polymorphic()
	{
		return !!value_for_key('polymorphic', $this->options);
	}
}