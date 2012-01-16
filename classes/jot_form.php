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
	
	private function field_name($field)
	{
		$record_name = $this->record->singular_table_name();

		return strtolower(sprintf('%s%s[%s]', $record_name, $this->index !== null ? '['.$this->index.']' : '', $field));
	}
	
	private function field_value($field)
	{
		return $this->record->read_attribute($field);
	}
	
	private function field_id($field)
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
		$options['name'] = value_for_key('name', $options, $this->field_value($field));

		$options['id'] = value_for_key('id', $options, $this->field_value($field));
		$options['value'] = $checked_value;
		$options['checked'] = $this->field_value($field) == $checked_value ? TRUE : FALSE;

		$html .= form_hidden($options['name'], $unchecked_value);

		$html .= form_checkbox($options);
				
		return $html;
	}
	
	public function select($field, $options = array(), $html_options = array(), $default_value = FALSE)
	{
		$name = $this->field_name($field);
		$value = $this->field_value($field);
						
		$default['id'] = $this->field_id($field);
				
		$value = $value == FALSE && $default_value ? $default_value : $value;
				
		$html_options = _parse_form_attributes($html_options, $default);
		
		return form_dropdown($name, $options, $value, $html_options);
	}
	
	public function file_field($field, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_value($field));
		$options['id'] = value_for_key('id', $options, $this->field_value($field));
		
		return form_upload($options);
	}
	
	public function hidden_field($field, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_value($field));
		$value = $this->field_value($field);
		
		return form_hidden($name, $value);		
	}
	
	public function label($field, $text = FALSE, $options = array())
	{	
		$text = $text ? $text : ucwords(str_replace('_', ' ', $field));
		$field = $this->field_id($field);
		
		return form_label($text, $field, $options);		
	}
	
	public function password_field($field, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_value($field));
		$options['id'] = value_for_key('id', $options, $this->field_value($field));
		$options['value'] = value_for_key('value', $options, $this->field_value($field));
		
		return form_password($options);
	}
	
	public function radio_button($field, $radio_value, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_value($field));
		$options['id'] = value_for_key('id', $options, $this->field_value($field));
		$options['value'] = $radio_value;

		$options['checked'] = $this->record->$field == $checked_value ? TRUE : FALSE;
		
		return form_radio($options);		
	}
	
	public function text_area($field, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_value($field));
		$options['id'] = value_for_key('id', $options, $this->field_value($field));
		$options['value'] = value_for_key('value', $options, $this->field_value($field));
		
		return form_textarea($options);		
	}
	
	public function text_field($field, $options = array())
	{
		$options['name'] = value_for_key('name', $options, $this->field_value($field));
		$options['id'] = value_for_key('id', $options, $this->field_value($field));
		$options['value'] = value_for_key('value', $options, $this->field_value($field));
		
		return form_input($options);		
	}

	public function date_field($field, $options = array())
	{
		$timestamp = $this->field_value($field);
		$timestamp = $timestamp ? $timestamp : time();
		$name = $this->field_name($field);
		
		$m = date('n', $timestamp);
		$d = date('j', $timestamp);
		$y = date('Y', $timestamp);

		$html = '';

		$default['id'] = $this->field_id($field);
		$html_options = _parse_form_attributes($default, $default);

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
		
		$html .= form_dropdown($name.'[month]', $months, $m, $html_options);
		
		// Days
		$days = array();
		for($i = 1; $i <= 31; $i++) $days[$i] = $i;

		$html .= form_dropdown($name.'[day]', $days, $d);

		// Years
		$years = array();
		for($i = date('Y'); $i <= date('Y')+3; $i++) $years[$i] = $i;

		$html .= form_dropdown($name.'[year]', $years, $y);
		
		return $html;
		
	}
}