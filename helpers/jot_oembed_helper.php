<?php

if ( ! function_exists('get_embed_code'))
{
	function get_embed_code($url, $options = array())
	{
		if ( $oEmbed = valid_oembed_url($url) )
		{						
			$options['url'] = $url;
			$options['format'] = 'json';

			foreach($options as $key=>$value)
			{
				$oEmbed .= '&'.$key.'='.$value;
			}
			
			$ch = curl_init($oEmbed);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		    $response = curl_exec($ch);

		    curl_close($ch);
			
			$object = json_decode($response);

			return $object;
		}

		return false;
	}
}

if ( ! function_exists('valid_oembed_url'))
{
	function valid_oembed_url($url)
	{	
		$services = array(
			array(
				'domain' => 'youtube.com',
				'api' => 'http://www.youtube.com/oembed?'
			),
			array(
				'domain' => 'vimeo.com',
				'api' => 'http://vimeo.com/api/oembed.json?',
			),
			array(
				'domain' => 'vzaar.com',
				'api' => 'http://vzaar.com/api/oembed.json?',
			),			
		);

		foreach($services as $service)
		{
			if ( preg_match("/".$service['domain']."/", $url) )
			{
				return $service['api'];
			}
		}

		return false;
	}
}