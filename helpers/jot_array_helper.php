<?php

if ( ! function_exists('is_assoc'))
{
	function is_assoc($array) {
	    return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
	}
}

if ( ! function_exists('rotate'))
{
	function rotate($source_array, $keep_keys = true)
	{
		$new_array = array();
		foreach ($source_array as $key => $value)
		{
			$value = ($keep_keys === true) ? $value : array_values($value);

			foreach ($value as $k => $v)
			{
				$new_array[$k][$key] = $v;
			}
		}

		return $new_array;
	}
}

if ( ! function_exists('array_fill_key_value') )
{
	function array_fill_key_value($array)
	{
		return array_combine($array, $array);
	}
}

if ( ! function_exists('value_for_key'))
{
	function value_for_key($keys, $array, $default = false)
	{	
		// Cast all variables as array.
		if ( ! is_array($array) )
		{
			if ( is_object($array) )
			{
				$array = (array)$array;
			}
			else
			{
				return $default;	
			}
		}
		
		// If array is empty return default.
		if ( empty($array) )
		{
			return $default;
		}
		
		if ( array_key_exists($keys, $array) )
		{
			return $array[$keys];		
		}

		// Prepare for loop
		$keys = explode('.', $keys);

		// If there is one key than we can skip the loop and check directly.
		if ( count($keys) == 1 )
		{
			return $default;
		}
		
		// Loop through array tree and find value.
		do
		{
			// Get the next key
			$key = array_shift($keys);

			if (isset($array[$key]))
			{
				if (is_array($array[$key]) AND ! empty($keys))
				{
					// Dig down to prepare the next loop
					$array = $array[$key];
				}
				else
				{
					// Requested key was found
					return $array[$key];
				}
			}
			else
			{
				// Requested key is not set
				break;
			}
		}
		while ( ! empty($keys));

		// Nothing found so return default.
		return $default;
	}
}