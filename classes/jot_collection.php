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
	public function to_json($options = array())
	{
		
		$objects = array();
		
		foreach($this as $object) 
		{
			$attributes = $object->attributes();
			if (value_for_key('only', $options) && is_array($options['only']))
			{
				$clean = array();
				foreach ($options['only'] as $attribute)
				{
					$clean[$attribute] = isset($attributes[$attribute]) ? $attributes[$attribute] : $object->$attribute;
				}
				$attributes = $clean;
			}
			elseif (value_for_key('except', $options) && is_array($options['except']))
			{
				foreach ($options['except'] as $attribute)
				{
					unset($attributes[$attribute]);
				}
			}
			if (value_for_key('include', $options) && is_array($options['include']))
			{
				foreach ($options['include'] as $include)
				{
					if ($object->$include && $object->$include->attributes()) 
					{
						$attributes[$include] = $object->$include->attributes();
					}
				}
			}
			$objects[] = $attributes;
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