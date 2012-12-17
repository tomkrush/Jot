<?php

if ( ! function_exists('is_blank'))
{
	function is_blank($value) {
		if ( $value === null ) {
			return true;
		}

		if ( $value === '' ) {
			return true;
		}

		if ( $value === false ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists('get_extension_by_mime'))
{
	function get_extension_by_mime($mime)
	{
		global $mimes;

		if ( ! is_array($mimes))
		{
			if (defined('ENVIRONMENT') AND is_file(APPPATH.'config/'.ENVIRONMENT.'/mimes.php'))
			{
				include(APPPATH.'config/'.ENVIRONMENT.'/mimes.php');
			}
			elseif (is_file(APPPATH.'config/mimes.php'))
			{
				include(APPPATH.'config/mimes.php');
			}

			if ( ! is_array($mimes))
			{
				return FALSE;
			}
		}
		
		// Does it exist?
		$key = null;
		$key = array_search($mime, $mimes);
		
		if ( is_bool($key) == false )
		{
			return $key;
		}

		foreach($mimes as $extension => $possibilities)
		{			
			if ( is_array($possibilities) )
			{	
				$key = array_search($mime, $possibilities);
				
				if ( is_bool($key) == false )
				{
					return $extension;
				}
			}
		}

		return false;
	}
}