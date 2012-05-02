<?php

class JotIdentityMap 
{
	public $repository;
	private static $instance;
	protected $enabled = true;
	
	private function __construct() {}
	
	public static function getInstance() 
    { 
        if ( ! self::$instance) 
        { 
            self::$instance = new JotIdentityMap(); 
        }
        
        return self::$instance; 
    }

	public function enable()
	{
		$self = self::getInstance();
		$self->enabled = true;
	}
	
	public function disable()
	{
		$self = self::getInstance();
		$self->enabled = false;
	}

	public static function get($class, $id)
	{
		$self = self::getInstance();

		if ( $self->enabled )
		{		
			$id = (int)$id;
				
		 	$object = isset($self->repository[$class][$id]) ? $self->repository[$class][$id] : false;

			return $object;
		}
		
		return false;
	}

	public static function add($object)
	{
		$self = self::getInstance();

		if ( $self->enabled )
		{	
			$class = get_class($object);
		
			$id = (int)$object->read_attribute($object->primary_key());

			$self->repository[$class][$id] = $object;
		
			return true;
		}
		
		return false;
	}

	public static function remove($object)
	{
		$self = self::getInstance();

		if ( $self->enabled )
		{	
			$class = get_class($object);
			$id = $object->read_attribute($object->primary_key());		
	
			self::remove_by_id($class, $id);
		}
	}

	public static function remove_by_id($class, $id)
	{
		$self = self::getInstance();

		if ( $self->enabled )
		{
			unset($self->repository[$class][$id]);	
		}
	}
	
	public static function count()
	{
		$self = self::getInstance();

		if ( $self->enabled )
		{		
			return count($self->repository);
		}
		
		return false;		
	}
	
	public static function object_count()
	{
		$self = self::getInstance();

		if ( $self->enabled )
		{		
			$count = 0;
			
			foreach($self->repository as $repo)
			{
				$count += count($repo);
			}
			
			return $count;
		}
		
		return false;
	}
	
	public static function exists($object)
	{
		$self = self::getInstance();
	
		if ( $self->enabled )
		{		
			$class = get_class($object);
			$id = $object->read_attribute($object->primary_key());
		
			return isset($self->repository[$class][$id]);
		}
	}

	public static function clear()
	{
		$self = self::getInstance();

		if ( $self->enabled )
		{
			$self->repository = array();
		}
	}
}
