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
	public function write($value)
	{
		$options = $this->options();
		$jot = $this->object;
		$key = $this->name;
					
		if ( $polymorphic = value_for_key('polymorphic', $options) )
		{
			$foreign_type = $key.'_type';
			$foreign_key = $key.'_id';
			$foreign_id = $value->read_attribute($value->primary_key());
			
			$jot->assign_attributes(array(
				$foreign_type => $value->singular_table_name(),
				$foreign_key => $foreign_id
			));			
		}
		else
		{
			$foreign_key = $value->singular_table_name().'_id';
			$foreign_id = $value->read_attribute($value->primary_key());

			# Add Association
			$jot->write_attribute($foreign_key, $foreign_id);
		}	
	}

	public function read()
	{
		$options = $this->options();
		$object = $this->object;
		$key = $this->name;
								
		if (  value_for_key('polymorphic', $options) )
		{
			$foreign_type = $object->read_attribute($key.'_type');
			
			$modelName = ucwords($foreign_type).'_Model';

			$this->load->model($modelName);

			$id = $object->read_attribute($key.'_id');
		}
		else
		{
			$modelName = ucwords($key).'_Model';

			$this->load->model($modelName);
			
			$foreign_type = $key.'_id';
			
			$id = $object->read_attribute($foreign_type);
		}
	
		$conditions = array($this->$modelName->primary_key() => $id);
					
		return $this->$modelName->first($conditions);
	}
}