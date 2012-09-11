<?php

if ( ! function_exists('jot_human_readable_attribute'))
{
	function jot_human_readable_attribute($attribute)
	{
		return ucfirst(str_replace('_', ' ', $attribute));
	}
}

if ( ! function_exists('jot_validate_required'))
{
	function jot_validate_required($object, $attribute, $options) 
	{
		$value = $object->read_attribute($attribute);
		
		if ( is_blank($value) )
		{
			$object->add_error(array($attribute, jot_human_readable_attribute($attribute).' is required'));
			return false;
		}

		return true;
	}
}

if ( ! function_exists('jot_validate_valid_url') )
{
	function jot_validate_valid_url($object, $attribute, $options)
	{
		if ( $object->has_attribute($attribute) )
		{
			$value = $object->read_attribute($attribute);
			
			if ( ! is_url_valid($value) )
			{
				$object->add_error(array($attribute, jot_human_readable_attribute($attribute).' is not a valid url.'));
				return false;
			}
			
			return true;
		}
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

			if ( isset($options['exclude_self']) && $options['exclude_self'] == true )
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
				$object->add_error(array($attribute, jot_human_readable_attribute($attribute).' "'.$object->read_attribute($attribute).'" already exist'));
		 		return false;
			}
		}

		return true;
	}
}

if ( ! function_exists('jot_validate_length'))
{
	function jot_validate_length($object, $attribute, $options)
	{
		if ( $object->has_attribute($attribute) )
		{
			$value = $object->read_attribute($attribute);
			
			$minimum = isset($options['minimum']) ? $options['minimum'] : null;
			$maximum = isset($options['maximum']) ? $options['maximum'] : null;
		
			$validated = true;
		
			if ( $minimum && strlen($value) <= $minimum )
			{
				$object->add_error(array($attribute, jot_human_readable_attribute($attribute).' "'.$value.'" must be longer than '.$minimum.' characters'));
				$validated = false;
			}
		
			if ( $maximum && strlen($value) >= $maximum )
			{
				$object->add_error(array($attribute, jot_human_readable_attribute($attribute).' "'.$value.'" must be shorter than '.$maximum.' characters'));
				$validated = false;
			}
		
			return $validated;
		}
	
		return true;
	}
}

if ( ! function_exists('jot_validate_confirm'))
{
	function jot_validate_confirm($object, $attribute, $options)
	{
		$confirm_attribute = "confirm_{$attribute}";

		if ( $object->has_attribute($attribute) && $object->has_attribute($confirm_attribute) )
		{
			$value = $object->read_attribute($attribute);
			
			$confirm = $object->read_attribute($confirm_attribute);
			
			if ( $value != $confirm )
			{
				$object->add_error(array($attribute, jot_human_readable_attribute($attribute)." doesn't match confirmation"));
				return false;
			}
			
			if ( ! $object->has_transient($confirm_attribute) )
			{
				$object->add_transient($confirm_attribute);
			}
		}
	
		return true;		
	}
}

if ( ! function_exists('jot_validate_attachment_required'))
{
	function jot_validate_attachment_required($object, $attribute, $options) 
	{		
		$file = $object->_files($attribute);
		$error = value_for_key('error', $file);				

		if ( empty($file) || $error > 0 )
		{	
			switch($error)
			{
				case 1:
					$max_upload_size = min(let_to_num(ini_get('post_max_size')), let_to_num(ini_get('upload_max_filesize')));

					$object->add_error(array($attribute, jot_human_readable_attribute($attribute)." was not uploaded because file is larger than {$max_upload_size}."));
				break;

				case 4:
					$object->add_error(array($attribute, jot_human_readable_attribute($attribute).' is required.'));
				break;

				default:
					$object->add_error(array($attribute, jot_human_readable_attribute($attribute).' failed to upload.'));
				break;
			}
			
			return false;
		}

		return true;
	}
}

// if ( ! function_exists('jot_validate_attachment_size'))
// {
// 	function jot_validate_attachment_size($object, $attribute, $options) 
// 	{
// 		$file = $object->_files($attribute);
// 		$size = (int)value_for_key('size', $file);
// 		
// 		if ( $ )
// 		{
// 			$object->add_error(array($attribute, ucfirst($attribute).' is required'));
// 			return false;
// 		}
// 
// 		return true;
// 	}
// }
// 
if ( ! function_exists('jot_validate_attachment_content_type'))
{
	function jot_validate_attachment_content_type($object, $attribute, $options) 
	{		
		$file = $object->_files($attribute);
		$error = value_for_key('error', $file);	

		if ( isset($file, $error) && $error === 0 )
		{
			$type = value_for_key('type', $file);
			$options = is_array($options) ? $options : array($options);
									
			if ( ! in_array($type, $options) )
			{
				$is_extension = get_extension_by_mime($type);
				$is_extension = $is_extension ? $is_extension : 'unknown type';
				
				$should_be = array();
				
				foreach($options as $option)
				{
					$should_be[] = get_extension_by_mime($option);
				}
			
				$object->add_error(array($attribute, 'Uploaded '.jot_human_readable_attribute($attribute).' is a '.$is_extension.'. Should be a '.implode(', ', $should_be).'.'));
				return false;
			}
		}
		
		return true;
	}
}