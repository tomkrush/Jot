<?php

if ( ! function_exists('geocode') )
{
	function geocode($address)
	{	
		$url = "http://where.yahooapis.com/geocode?flags=P&q=".urlencode($address);
		
			
		$ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $data = unserialize(curl_exec($ch));

	    curl_close($ch);
		
		if ( $data == FALSE ) return FALSE;
				
		$data = value_for_key('ResultSet.Result', $data);
							
		if ( count($data) && $data = array_shift($data) )
		{
			$latitude = value_for_key('latitude', $data);
			$longitude = value_for_key('longitude', $data);
			
			if ( $latitude && $longitude)
			{
				return array(
					'latitude' => $latitude,
					'longitude' => $longitude
				);
			}
		}
		
		return FALSE;
	}
}