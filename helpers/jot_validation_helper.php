<?php

if ( ! function_exists('jot_validate_required'))
{
	function jot_validate_required($object, $attribute, $options) 
	{
		$value = $object->read_attribute($attribute);
		
		if ( empty($value) )
		{
			$object->add_error(array($attribute, ucfirst($attribute).' is required'));
			return FALSE;
		}

		return TRUE;
	}
}

if ( ! function_exists('jot_validate_uniqueness'))
{
	function jot_validate_uniqueness($object, $attribute, $options) 
	{
		if ( $object->has_attribute($attribute) )
		{
			$scopes = isset($options['scope']) ? $options['scope'] : array();
			$scopes = is_string($scopes) ? array($options['scope']) : $scopes;

			$conditions = array($attribute => $object->read_attribute($attribute));

			foreach($scopes as $scope )
			{
				if ( $object->has_attribute($scope) )
				{
					$conditions[$scope] = $object->read_attribute($scope);
				}
			} 

			if ( isset($options['exclude_self']) && $options['exclude_self'] == TRUE )
			{
				$primary_key = $object->primary_key();
				$primary_key_value = $object->read_attribute($primary_key);
				
				if ( $primary_key_value )
				{
					$conditions[$primary_key.' !='] = $primary_key_value;
				}
			}

			if ( $object->exists($conditions) )
			{		
				$object->add_error(array($attribute, ucfirst($attribute).' "'.$object->read_attribute($attribute).'" already exist'));
		 		return FALSE;
			}
		}

		return TRUE;
	}
}

if ( ! function_exists('jot_validate_length'))
{
	function jot_validate_length($object, $attribute, $options)
	{
		if ( $object->has_attribute($attribute) )
		{
			$value = $object->read_attribute($attribute);
			
			$minimum = isset($options['minimum']) ? $options['minimum'] : NULL;
			$maximum = isset($options['maximum']) ? $options['maximum'] : NULL;
		
			$validated = TRUE;
		
			if ( $minimum && strlen($value) <= $minimum )
			{
				$object->add_error(array($attribute, ucfirst($attribute).' "'.$value.'" must be longer than '.$minimum.' characters'));
				$validated = FALSE;
			}
		
			if ( $maximum && strlen($value) >= $maximum )
			{
				$object->add_error(array($attribute, ucfirst($attribute).' "'.$value.'" must be shorter than '.$maximum.' characters'));
				$validated = FALSE;
			}
		
			return $validated;
		}
	
		return TRUE;
	}
}

if ( ! function_exists('jot_validate_confirm'))
{
	function jot_validate_confirm($object, $attribute, $options)
	{
		$confirm_attribute = "confirm_{$attribute}";
		$value = $object->read_attribute($attribute);
		
		if ( ! $object->has_attribute($confirm_attribute) )
		{
			$object->add_error(array($attribute, "Confirm {$value} is required"));
			return FALSE;
		}
	
		$confirm = $object->read_attribute($confirm_attribute);
	
		if ( isset($value, $confirm) )
		{
			
			if ( $value != $confirm )
			{
				$object->add_error(array($field, ucfirst($field)." doesn't match confirmation"));
				return FALSE;
			}
		}
		
		if ( ! $object->has_transient($confirm_attribute) )
		{
			$object->add_transient($confirm_attribute);
		}
	
		return TRUE;		
	}
}