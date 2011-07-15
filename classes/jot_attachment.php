<?php

class JotAttachment
{
	public $name;
	public $options;
	public $instance;
	
	public function __construct($name, $instance, $options = array())
	{
		$this->name = $name;
		$this->instance = $instance;
		$this->options = $options;
		
		if ( value_for_key('styles', $options) )
		{
			$CI =& get_instance();
			$CI->load->library('image_lib');
		}
	}
	
	public function __get($key)
	{
		switch($key)
		{
			case 'url':
				return $this->url();
			break;
			
			case 'file_path':
				return $this->file_path();
			break;
			
			case 'base_path':
				return $this->base_path();
			break;
		}
	}
	
	public function url($style = NULL)
	{
		$url = rtrim(value_for_key('url', $this->options, 'assets/files'),'/').'/';

		if ( $style )
		{
			$url = str_replace('{filename}', $style.'/{filename}', $url);
		}
	
		if ( $file_name = $this->instance->read_attribute("{$this->name}_file_name") )
		{			
			$url = str_replace('{filename}', $file_name, $url);
		}
		else
		{
			$url = str_replace('{filename}', 'missing.jpg', $url);
		}
		
		return site_url($url);
	}
	
	public function base_path($style = NULL)
	{
		$path = rtrim(value_for_key('path', $this->options, FCPATH.'assets/files'),'/');
		
		if ( $style )
		{
			$path = str_replace('{filename}', $style.'/{filename}', $path);
		}
		
		return $path;
	}
	
	public function folder_path($style = NULL)
	{
		return str_replace('{filename}', '', $this->base_path($style));
	}
	
	public function file_path($style = NULL)
	{
		$path = $this->base_path($style);
				
		if ( $file_name = $this->instance->read_attribute("{$this->name}_file_name") )
		{
			$path = str_replace('{filename}', $file_name, $path);
		}
		
		return $path;
	}
}