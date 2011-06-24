<?php

class JotIdentityMap 
{
	public $repository;
	public $log;
	private static $instance;
	
	private function __construct() {}
	
	public static function getInstance() 
    { 
        if ( ! self::$instance) 
        { 
            self::$instance = new JotIdentityMap(); 
        }
        
        return self::$instance; 
    }

	public static function get($class, $id)
	{
		$self = self::getInstance();
		
		$object = isset($self->repository[$class][$id]) ? $self->repository[$class][$id] : FALSE;

		// if ($self->log) echo 'object found';

		return $object;
	}

	public static function add($object)
	{
		$self = self::getInstance();
	
		$class = get_class($object);
		
		$id = $object->read_attribute($object->primary_key());

		$self->repository[$class][$id] = $object;
		
		return TRUE;
	}

	public static function remove($object)
	{
		$self = self::getInstance();
	
		$class = get_class($object);
		$id = $object->read_attribute($object->primary_key());		
	
		self::remove_by_id($class, $id);
	}

	public static function remove_by_id($class, $id)
	{
		$self = self::getInstance();

		unset($self->repository[$class][$id]);	
	}
	
	public static function count()
	{
		$self = self::getInstance();
		
		return count($self->repository);		
	}
	
	public static function log($log)
	{
		$self = self::getInstance();
		$self->log = $log;
	}
	
	public static function exists($object)
	{
		$self = self::getInstance();
		
		$class = get_class($object);
		$id = $object->read_attribute($object->primary_key());
		
		return isset($self->repository[$class][$id]);
	}

	public static function clear()
	{
		$self = self::getInstance();

		$self->repository = array();
	}
}
