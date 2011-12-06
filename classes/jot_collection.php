<?php

class JotCollection extends ArrayObject
{
	# Return array from key value mapping.	
	public function map($key, $value)
	{
		$mapping = array();
		
		foreach($this as $object) 
		{
			$mapping[$object->read_attribute($key)] = $object->read_attribute($value);
		}
		
		return $mapping;
	}
	
	# Return json object
	public function to_json()
	{
		$objects = array();
		
		foreach($this as $object) 
		{
			$objects[] = $object->attributes();
		}
		
		return json_encode($objects);
	}
	
	# Return string
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