<?php

class JotAttachment
{
	public $name;
	public $options;
	public $instance;
	protected $_url;
	protected $_path;
	
	public function __construct($name, $instance, $options = array())
	{
		$this->name = $name;
		$this->instance = $instance;
		$this->options = $options;
	}
	
	public function __get($key)
	{
		switch($key)
		{
			case 'url':
				return $this->get_url();
			break;
			
			case 'path':
				return $this->get_path();
			break;
		}
	}
	
	public function get_url()
	{
		if ( ! $this->_url )
		{
			$url = rtrim(value_for_key('url', $this->options, 'files'),'/').'/';
		
			if ( $file_name = $this->instance->read_attribute("{$this->name}_file_name") )
			{
				$url = str_replace('{filename}', $file_name, $url);
			}
			
			$this->_url = site_url($url);
		}
		
		return $this->_url;	
	}
	
	public function get_path()
	{
		if ( ! $this->_path )
		{
			$path = rtrim(value_for_key('path', $this->options, FCPATH.'files'),'/');
					
			if ( $file_name = $this->instance->read_attribute("{$this->name}_file_name") )
			{
				$path = str_replace('{filename}', $file_name, $path);
			}
			
			$this->_path = $path;
		}
		
		return $this->_path;		
	}
}