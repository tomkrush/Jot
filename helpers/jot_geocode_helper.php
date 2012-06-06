<?php

if ( ! function_exists('geocode') )
{
	function geocode($address)
	{	
		$url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=".urlencode($address);

		$ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $data = curl_exec($ch);
		
	    curl_close($ch);
		
		if ( $data == false ) return false;

		$data = json_decode($data, true);

		$data = value_for_key('results.0.geometry.location', $data);
		
		if ( is_array($data) )
		{
			$latitude = value_for_key('lat', $data);
			$longitude = value_for_key('lng', $data);
		
			if ( $latitude && $longitude)
			{
				return array(
					'latitude' => $latitude,
					'longitude' => $longitude
				);
			}
		}
		
		return false;
	}
}