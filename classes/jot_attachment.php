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
	
		if ( $file_name = $this->instance->read_attribute("{$this->name}_file_name") )
		{
			if ( $style )
			{
				$url = str_replace('{filename}', $style.'/{filename}', $url);
			}			
			
			$url = str_replace('{filename}', $file_name, $url);
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

class JotImage {
 
   var $image;
   var $image_type;
 	protected $width;
	protected $height;

   function load($filename) {
		$this->width = NULL;
		$this->height = NULL;
	
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
 
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
 
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
 
         $this->image = imagecreatefrompng($filename);
      }
   }

   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
 
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
 
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
 
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->image);
      }
   }
	function getWidth() 
	{
		if ( ! $this->width )
		{
			$this->width = imagesx($this->image);
		}
		
		return $this->width;
	}
	
	function getHeight() 
	{
		if ( ! $this->height )
		{
			$this->height = imagesy($this->image);
		}
		
		return $this->height;
	}

   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height, FALSE);
   }
 
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height, FALSE);
   }
 
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height, FALSE);
   }

	function crop($x, $y, $width, $height)
	{
		$new_image = imagecreatetruecolor($width, $height);
		imagecopy($new_image, $this->image, $x, $y, 0, 0, $width, $height); 
		$this->image = $new_image;
	}

	function resize($width,$height, $maintainAspectRatio = TRUE) {
		if ( $maintainAspectRatio )
		{
			if ( $this->getWidth() > $this->getHeight())
			{
				$this->resizeToWidth($width);
			}
			else
			{
				$this->resizeToHeight($width);
			}
		}
		else
		{
			$new_image = imagecreatetruecolor($width, $height);
			imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
			$this->image = $new_image;			
		}
		
		$this->width = NULL;
		$this->height = NULL;
	}      
 
}