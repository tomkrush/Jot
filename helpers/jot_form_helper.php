<?php

if ( ! function_exists('form_for'))
{
	function form_for(&$jot_form, $record, $action, $options = array())
	{
		$CI =& get_instance();
		$CI->load->helper('form');
	
		$jot_form = new JotForm($record);
	
		$options['method'] = array_key_exists('method', $options) ? $options['method'] : 'POST';
	
		if ( array_key_exists('multipart', $options) && $options['multipart'] == TRUE)
		{
			unset($options['multipart']);
		
			return form_open_multipart($action, $options);
		}
		else
		{
			return form_open($action, $options);
		}
	}
}

if ( ! function_exists('form_end'))
{
	function form_end()
	{
		$CI =& get_instance();
		$CI->load->helper('form');
	
		return form_close();;
	}
}

if ( ! function_exists('submit_tag'))
{
	function submit_tag($value = "Save changes", $options = array())
	{
		$CI =& get_instance();
		$CI->load->helper('form');
	
		$options['name'] = array_key_exists('name', $options) ? $options['name'] : 'commit';
		$options['value'] = $value;
	
		return form_submit($options);	
	}
}