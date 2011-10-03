<?php

/* JotImage is based on a PHP image manipulation library. Original author unknown. */

class JotImage 
{
	protected $image;
	protected $image_type;
	protected $width;
	protected $height;

	function load($filename) 
	{
		$this->width = NULL;
		$this->height = NULL;

		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		
		if( $this->image_type == IMAGETYPE_JPEG ) 
		{
			$this->image = imagecreatefromjpeg($filename);
		} 
		elseif( $this->image_type == IMAGETYPE_GIF ) 
		{
			$this->image = imagecreatefromgif($filename);
		} 
		elseif( $this->image_type == IMAGETYPE_PNG ) 
		{
			$this->image = imagecreatefrompng($filename);
		}
	}

	function save($filename, $image_type=NULL, $compression=75, $permissions=null) 
	{	
		$image_type = $image_type ? $image_type : $this->image_type;
		
		if( $image_type == IMAGETYPE_JPEG ) 
		{
			imagejpeg($this->image,$filename,$compression);
		} 
		elseif( $image_type == IMAGETYPE_GIF ) 
		{
			imagegif($image,$filename);
		} 
		elseif( $image_type == IMAGETYPE_PNG ) 
		{
			imagepng($this->image,$filename);
		}
		
		if( $permissions != null) 
		{
			chmod($filename,$permissions);
		}
	}
	
	function output($image_type=IMAGETYPE_JPEG) 
	{
		if( $image_type == IMAGETYPE_JPEG ) 
		{
			imagejpeg($this->image);
		} 
		elseif( $image_type == IMAGETYPE_GIF ) 
		{
			imagegif($this->image);
		} 
		elseif( $image_type == IMAGETYPE_PNG ) 
		{
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

	function resizeToHeight($height) 
	{
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width,$height, FALSE);
	}

	function resizeToWidth($width) 
	{
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width,$height, FALSE);
	}

	function scale($scale) 
	{
		$width = $this->getWidth() * $scale/100;
		$height = $this->getheight() * $scale/100;
		$this->resize($width,$height, FALSE);
	}
	
	function resize_and_clip($width, $height, $corner = NULL)
	{
		$actual_width = $this->getWidth();
		$actual_height = $this->getHeight();		
		
		$new_ratio = $width / $height;
		$old_ratio = $actual_width / $actual_height;

		if ( $new_ratio != $old_ratio )
		{
			if ( $new_ratio > $old_ratio )
			{
				$this->resizeToWidth($width);
			}
			else
			{
				$this->resizeToHeight($height);
			}

			$actual_width = $this->getWidth();
			$actual_height = $this->getHeight();

			switch($corner)
			{	
				case 'nw':
					$this->crop(0, 0, $width, $height);
				break;
				
				case 'ne':
					$this->crop(-($actual_width - $width), 0, $width, $height);
				break;
				
				case 'sw':
					$this->crop(0, -($actual_height - $height), $width, $height);
				break;
				
				case 'se':
					$this->crop(-($actual_width - $width), -($actual_height - $height), $width, $height);
				break;
				
				default:
					$this->crop(-($actual_width / 2) + ($width / 2), -($actual_height / 2) + ($height / 2), $width, $height);
				break;
			}
		}
		else
		{
			$this->resize($width, $height);
		}		
	}

	function crop($x, $y, $width, $height)
	{
		$new_image = imagecreatetruecolor($width, $height);
		imagecopy($new_image, $this->image, $x, $y, 0, 0, $this->getWidth(), $this->getHeight()); 
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
			
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
			$transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
			imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
			
			imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
			
			$this->image = $new_image;			
		}

		$this->width = NULL;
		$this->height = NULL;
	}      
}