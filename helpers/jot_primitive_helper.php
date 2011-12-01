<?php

if ( ! function_exists('is_blank'))
{
	function is_blank($value) {
		if ( $value === NULL ) {
			return TRUE;
		}

		if ( $value === '' ) {
			return TRUE;
		}

		if ( $value === FALSE ) {
			return TRUE;
		}

		return FALSE;
	}
}