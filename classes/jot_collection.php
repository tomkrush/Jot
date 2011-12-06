<?php

class JotCollection extends ArrayObject
{
	public function contains( $value )
	{
		return in_array( $value, (array)$this );
	}
	
	public function map($key, $value)
	{
		$mapping = array();
		
		foreach($this as $object) 
		{
			$mapping[$object->read_attribute($key)] = $object->read_attribute($value);
		}
		
		return $mapping;
	}
	
	public function to_json()
	{
		$objects = array();
		
		foreach($this as $object) 
		{
			$objects[] = $object->attributes();
		}
		
		return json_encode($objects);
	}
	
	public function __toString()
	{
		$string = array();
		
		foreach($this as $object) 
		{
			$string[] = $object;
		}
		
		return '['.implode(",\n", $string).'] '.count($string)." objects\n";
	}
}