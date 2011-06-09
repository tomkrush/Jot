<?php

class JotForm
{
	protected $record;
	
	public function __construct($record)
	{
		$CI =& get_instance();
		$CI->load->helper('form');
		
		$this->record = $record;	
	}
	
	private function field_name($field)
	{
		$table_name = $this->record->singular_table_name();

		return strtolower(sprintf('%s[%s]', $table_name, $field));
	}
	
	private function field_value($field)
	{
		return $this->record->$field;
	}
	
	private function field_id($field)
	{
		$table_name = $this->record->singular_table_name();

		return strtolower(sprintf('%s_%s_field', $table_name, $field));
	}
	
	public function check_box($field, $options = array(), $checked_value = "1", $unchecked_value = "0")
	{				
		$html = '';
		
		$options['name'] = array_key_exists('name', $options) ? $options['name'] : $this->field_name($field);

		$options['id'] = array_key_exists('id', $options) ? $options['id'] : $this->field_id($field);
		$options['value'] = $checked_value;
		$options['checked'] = $this->field_value($field) == $checked_value ? TRUE : FALSE;
		$html .= form_checkbox($options);
		
		$html .= form_hidden($options['name'], $unchecked_value);
		
		return $html;
	}
	
	public function select($field, $options = array(), $html_options = array())
	{
		$name = $this->field_name($field);
		$value = $this->field_value($field);
						
		$default['id'] = $this->field_id($field);
		
		$html_options = _parse_form_attributes($html_options, $default);
		
		return form_dropdown($name, $options, $value, $html_options);
	}
	
	public function file_field($field, $options = array())
	{
		$options['name'] = array_key_exists('name', $options) ? $options['name'] : $this->field_name($field);
		$options['id'] = array_key_exists('id', $options) ? $options['id'] : $this->field_id($field);
		
		return form_upload($options);
	}
	
	public function hidden_field($field, $options = array())
	{
		$name = array_key_exists('name', $options) ? $options['name'] : $this->field_name($field);
		$value = $this->field_value($field);
		
		return form_hidden($name, $value);		
	}
	
	public function label($field, $text = FALSE, $options = array())
	{	
		// Support Translations?

		$text = $text ? $text : ucwords(str_replace('_', ' ', $field));
		$field = $this->field_id($field);
		
		return form_label($text, $field, $options);		
	}
	
	public function password_field($field, $options = array())
	{
		$options['name'] = array_key_exists('name', $options) ? $options['name'] : $this->field_name($field);
		$options['id'] = array_key_exists('id', $options) ? $options['id'] : $this->field_id($field);
		$options['value'] = $this->field_value($field);
		
		return form_password($options);
	}
	
	public function radio_button($field, $radio_value, $options = array())
	{
		$options['name'] = array_key_exists('name', $options) ? $options['name'] : $this->field_name($field);
		$options['id'] = array_key_exists('id', $options) ? $options['id'] : $this->field_id($field);
		$options['value'] = $radio_value;

		$options['checked'] = $this->record->$field == $checked_value ? TRUE : FALSE;
		
		return form_radio($options);		
	}
	
	public function text_area($field, $options = array())
	{
		$options['name'] = array_key_exists('name', $options) ? $options['name'] : $this->field_name($field);
		$options['id'] = array_key_exists('id', $options) ? $options['id'] : $this->field_id($field);
		$options['value'] = $this->field_value($field);
		
		return form_textarea($options);		
	}
	
	public function text_field($field, $options = array())
	{
		$options['name'] = array_key_exists('name', $options) ? $options['name'] : $this->field_name($field);
		$options['id'] = array_key_exists('id', $options) ? $options['id'] : $this->field_id($field);
		$options['value'] = $this->field_value($field);
		
		return form_input($options);		
	}
}