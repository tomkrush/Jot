<?php

if ( ! function_exists('is_url_valid') )
{
	function is_url_valid($url)
    {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }	
}