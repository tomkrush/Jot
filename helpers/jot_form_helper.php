<?php

if ( ! function_exists('form_for'))
{
	function form_for(&$jot_form, $record, $action, $options = array())
	{
		$CI =& get_instance();
		$CI->load->helper('form');
	
		$jot_form = new Jot_form($record);
	
		$options['method'] = array_key_exists('method', $options) ? $options['method'] : 'POST';
	
		if ( array_key_exists('multipart', $options) && $options['multipart'] == TRUE)
		{
			unset($options['multipart']);
		
			return form_open_multipart($action, $options);
		}
		else
		{
			return form_open($action, $options);
		}
	}
}

if ( ! function_exists('form_end'))
{
	function form_end()
	{
		$CI =& get_instance();
		$CI->load->helper('form');
	
		return form_close();;
	}
}

if ( ! function_exists('submit_tag'))
{
	function submit_tag($value = "Save changes", $options = array())
	{
		$CI =& get_instance();
		$CI->load->helper('form');
	
		$options['name'] = array_key_exists('name', $options) ? $options['name'] : 'commit';
		$options['value'] = $value;
	
		return form_submit($options);	
	}
}

class Jot_Form
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