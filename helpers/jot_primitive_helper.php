<?php

if ( ! function_exists('is_blank'))
{
	function is_blank($value) {
		if ( $value === null ) {
			return true;
		}

		if ( $value === '' ) {
			return true;
		}

		if ( $value === false ) {
			return true;
		}

		return false;
	}
}