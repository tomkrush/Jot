<?php

class JotForm
{
	protected $record;
	
	public function __construct($record, $index = null)
	{
		$CI =& get_instance();
		$CI->load->helper('form');
		
		$this->record = $record;
		$this->index  = $index;
	}
	
	public function __call($name, $arguments)
	{
		$callback = 'jot_form_'.$name;

		if ( function_exists($callback) )
		{
			$field = $arguments[0];
			$options = $arguments[1];
		
			return call_user_func($callback, $this, $field, $options);
		}
		
		return null;
	}
	
	public function record()
	{
		return $this->record;
	}
		
	protected function field_name($field)
	{
		$record_name = $this->record->singular_table_name();

		return strtolower(sprintf('%s%s[%s]', $record_name, $this->index !== null ? '['.$this->index.']' : '', $field));
	}
	
	protected function field_value($field)
	{
		return $this->record->read_attribute($field);
	}
	
	protected function field_id($field)
	{
		$table_name = $this->record->singular_table_name();

		return strtolower(sprintf('%s_%s_field', $table_name, $field));
	}
	
	public function fields_for($field)
	{
		$associations = $this->record->read_association($field);
		$associations = is_array($associations) ? $associations : array($associations);
		
		$forms = array();
		
		foreach($associations as $association)
		{
			$forms[] = new self($association);
		}
		
		return $forms;
	}
	
	public function fields_end()
	{
		
	}
	
	public function check_box($field, $options = array(), $checked_value = "1", $unchecked_value = "0")
	{				
		$html = '';		
		$options['name'] = value_for_key('name', $options, $this->field_name($field));

		$options['id'] = value_for_key('id', $options, $this->field_id($field));
		$options['value'] = $checked_value;
		$options['checked'] = $this->field_value($field) == $checked_value ? true : false;

		$html .= form_hidden($options['name'], $unchecked_value);

		$html .= form_checkbox($options);
				
		return $html;
	}
	
	public function select($field, $options = array(), $html_options = array(), $default_value = false)
	{
		$name = $this->field_name($field);
		$value = $this->field_value($field);
						
		$default['id'] = $this->field_id($field);

		$value = is_blank($value) ? $default_value : $value;
						
		$html_options = _parse_form_attributes($html_options, $default);
		
		return form_dropdown($name, $options, $value, $html_options);
	}
	
	public function file_field($field, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_name($field));
		$options['id'] = value_for_key('id', $options, $this->field_id($field));
		
		return form_upload($options);
	}
	
	public function hidden_field($field, $options = array())
	{
		$name = value_for_key('name', $options, $this->field_name($field));
		$value = $this->field_value($field);

		return form_hidden($name, $value);		
	}
	
	public function label($field, $text = false, $options = array())
	{	
		$text = $text ? $text : ucwords(str_replace('_', ' ', $field));
		$field = $this->field_id($field);
		
		return form_label($text, $field, $options);		
	}
	
	public function password_field($field, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_name($field));
		$options['id'] = value_for_key('id', $options, $this->field_id($field));
		$options['value'] = value_for_key('value', $options, $this->field_value($field));
		
		return form_password($options);
	}
	
	public function radio_button($field, $radio_value, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_name($field));
		$options['id'] = value_for_key('id', $options, $this->field_id($field));
		$options['value'] = $radio_value;

		$options['checked'] = $this->record->$field == $radio_value ? true : false;
		
		return form_radio($options);		
	}
	
	public function text_area($field, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_name($field));
		$options['id'] = value_for_key('id', $options, $this->field_id($field));
		$options['value'] = value_for_key('value', $options, $this->field_value($field));
		
		return form_textarea($options);		
	}
	
	public function text_field($field, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_name($field));
		$options['id'] = value_for_key('id', $options, $this->field_id($field));
		$options['value'] = value_for_key('value', $options, $this->field_value($field));

		return form_input($options);		
	}
	
	public function time_field($field, $options = array(), $increment = 1)
	{
		$timestamp = $this->field_value($field);
		
		$timestamp = is_string($timestamp) ? $timestamp : date('H:i:s');

		list($h, $m) = explode(':', $timestamp);
		
		$p = $h < 12 || $h == '00' ? 'am' : 'pm';
		$h = $p == 'pm' ? ($h > 12 ? $h - 12 : $h) : ($h == '00' ? 12 : $h);
				
		$name = $this->field_name($field);

		$html = '';

		$default['id'] = $this->field_id($field);
		$html_options = _parse_form_attributes($default, $default);

		$hours = array();
		for ($i = 1; $i <= 12; $i++)
		{
			$s = $i < 10 ? "0{$i}" : "{$i}";
			$hours[$s] = $s;
		}
		
		$html .= form_dropdown($name.'[hours]', $hours, $h, $html_options);
		
		$html .= ':';
		
		$minutes = array();
		
		for ($i = 0; $i < 60; $i += $increment) 
		{
			$s = $i < 10 ? "0{$i}" : "{$i}";
			$minutes[$s] = $s;
		}

		$html .= form_dropdown($name.'[minutes]', $minutes, $m);

		// Period
		$period = array('am'=>'am','pm'=>'pm');
		$html .= form_dropdown($name.'[period]', $period, $p);
		
		return $html;
	}

	public function date_field($field, $options = array(), $html_options = array())
	{
		$year_range = value_for_key('year_range', $options, array(0, 3));
		$format = value_for_key('format', $options, 'array');
		$show_day = value_for_key('show_day', $options, true);
	
		$timestamp = $this->field_value($field);

		if ( strstr($timestamp, '-') )
		{
			list($y, $m, $d) = explode('-', $timestamp);
		}
		else
		{
			$timestamp = $timestamp ? $timestamp : time();	
			$m = date('n', $timestamp);
			$d = date('j', $timestamp);
			$y = date('Y', $timestamp);
		}

		$name = $this->field_name($field);

		$html = '';

		$default['id'] = $this->field_id($field);
		$html_options = _parse_form_attributes($html_options, $default);

		// Years
		$months = array(
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December'
		);
		
		$html .= form_dropdown($format == 'array' ? $name.'[month]' : preg_replace('/\[([a-z_0-9]*)\]$/', '[$1_month]', $name), $months, $m, $html_options);
		
		if ( $show_day )
		{
			// Days
			$days = array();
			for($i = 1; $i <= 31; $i++) $days[$i] = $i;
	
			$html .= form_dropdown($format == 'array' ? $name.'[day]' : preg_replace('/\[([a-z_0-9]*)\]$/', '[$1_day]', $name), $days, $d);
		}

		// Years
		$b = $y ? $y : date('Y');

		$years = array();
		for($i = date('Y')-$year_range[0]; $i <= $b; $i++) $years[$i] = $i;
		for($i = $b; $i <= date('Y')+$year_range[1]; $i++) $years[$i] = $i;

		$html .= form_dropdown($format == 'array' ? $name.'[year]' : preg_replace('/\[([a-z_0-9]*)\]$/', '[$1_year]', $name), $years, $y);
		
		return $html;
	}
}