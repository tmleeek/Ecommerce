<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Interface_Builder {
	
	private $fields = array();
	public  $data   = array();

	public function __construct($data = array())
	{
		if(isset($data['data']))
		{
			$this->data = $data['data'];
		}

		if(isset($data['fields']))
		{
			$this->add_fields($fields);
		}
	}

	public function fieldsets($fieldsets = FALSE, $data = FALSE)
	{
		if(!$fieldsets)
		{
			$fieldsets = $this->fields;
		}

		if(!$data)
		{
			$data = $this->data;
		}

		$html = array();

		foreach($fieldsets as $fieldset_id => $fieldset)
		{
			$html[] = '<'.$fieldset->wrapper.' id="'.$fieldset_id.'">';

			if($fieldset->legend != NULL)
			{
				$html[] = '<legend>'.$fieldset->legend.'</legend>';
			}
			
			if($fieldset->title != NULL)
			{
				$html[] = '<h3>'.$fieldset->title.'</h3>';
			}

			if($fieldset->description != NULL)
			{
				$html[] = '<p>'.$fieldset->description.'</p>';
			}

			if($fieldset->details != NULL)
			{
				$html[] = '<div class="details">'.$fieldset->details.'</div>';
			}

			$html[] = $this->table($fieldset->fields, $data, $fieldset->attributes);

			$html[] = '</'.$fieldset->wrapper.'>';
		}

		return implode(NULl, $html);
	}

	public function table($fields, $data = FALSE, $attributes = array())
	{
		$html   = array();

		if(!$data)
		{
			$this->data = $data;
		}

		$fields = $this->build($fields, $data);

		$attribute_array = array();

		foreach($attributes as $name => $value)
		{
			$attribute_array[] = $name.'="'.$value.'"';
		}

		$html[] = '<table '.implode(NULL, $attribute_array).'>';
		$html[] = '<tr><th style="width:40%">Preference</th><th>Setting</th></tr>';

		foreach($fields as $field_name => $field)
		{
			$html[] = '<tr><td>'.$field->label.$field->description.'</td><td>'.$field->field.'</td></tr>';
		}

		$html[] = '</table>';

		return implode(NULL, $html);
	}

	public function build($fields, $data = FALSE)
	{
		$return = array();

		if(!$data)
		{
			$this->data = $data;
		}

		foreach($fields as $field_name => $field)
		{
			$data     = isset($this->data[$field_name]) ? $this->data[$field_name] : NULL;

			$obj      = $this->load($field_name, $this->convert_array($field));

			$return[$field_name] = (object) array(
				'label'       => $obj->display_label($data),
				'description' => $obj->display_description($data),
				'field'       => $obj->display_field($data)
			);
		}		

		return $return;
	}

	public function load($name, $field)
	{
		$class_name = ucfirst($field->type).'_IBField';

		require_once 'fieldtypes/'.ucfirst($field->type) . '.php';

		return new $class_name($name, $field);
	}

	public function add_fieldset($id, $fieldset)
	{
		$fieldset = $this->convert_array($fieldset);

		$count = count($this->fields) + 1;

		$default = array(
			'title'       => NULL,
			'id'          => 'fieldset_'.$count,
			'legend' 	  => NULL,
			'description' => NULL,
			'details'     => NULL,
			'fields'      => array(),
			'attributes'  => array(),
			'wrapper'	  => 'fieldset'
		);

		$this->fields[$id] = $this->set_default_values($fieldset, $default);

		$this->add_fields($id, $this->fields[$id]->fields);
	}

	public function add_fieldsets($fieldsets)
	{
		foreach($fieldsets as $fieldset_id => $fieldset)
		{
			$this->add_fieldset($fieldset_id, $fieldset);
		}
	}

	public function add_fields($fieldset, $fields = array())
	{
		foreach($fields as $field_name => $field)
		{
			$this->add_field($fieldset, $field_name, $field);
		}
	}

	public function add_field($fieldset, $name, $field = array())
	{
		$field = $this->convert_array($field);

		$count = count($this->fields) + 1;

		$default = array(
			'label'       => 'Field '.$count,
			'id'          => 'field_'.$count,
			'type'        => 'input',
			'description' => NULL,
			'default'     => NULL,
			'settings'    => NULL
		);

		$this->fields[$fieldset]->fields[$name] = $this->set_default_values($field, $default);
	}

	public function get_fieldsets()
	{
		return $this->fields;
	}

	public function get_fieldset($fieldset_id)
	{
		return $this->fields[$fieldset_id];
	}

	public function get_fields($fieldset_id)
	{
		return $this->fields[$fieldset_id]['fields'];
	}

	public function get_field($fieldset_id, $field_name)
	{
		return $this->fields[$fieldset_id]['fields'][$field_name];
	}

	public function remove_field($name)
	{
		unset($this->fields[$name]);
	}

	public function remove_fields()
	{
		$this->fields = array();
	}

	public function convert_array($array)
	{
		if(is_array($array))
		{
			$array = (object) $array;
		}

		return $array;
	}

	private function set_default_values($array, $default = array())
	{
		$array = $this->convert_array($array);

		foreach($default as $index => $value)
		{
			if(!isset($array->$index))
			{
				$array->$index = $value;
			}
		}

		return $array;
	}

}

abstract class IBFieldtype {

	public $name, $label, $id, $type, $default = '', $settings;

	public function __construct($name, $field)
	{
		$this->name = $name;

		foreach($field as $attr => $value)
		{
			$this->$attr = $value;
		}
		
		$this->EE           =& get_instance();
		$this->channel_data =& $this->EE->channel_data;
	}

	public function display_label($data = '')
	{
		return '<label for="'.$this->id.'">'.$this->label.'</label>';
	}

	public function display_description($data = '', $prepend = '<p>', $append = '</p>')
	{
		return !empty($this->description) ? $prepend.$this->description.$append : NULL;
	}

	abstract public function display_field($data = '');
	
	public function validate($data = '')
	{
		return TRUE;
	}

	public function save($data = '')
	{
		return $data;
	}

	public function form_prep($str = '', $field_name = '')
	{
		static $prepped_fields = array();

		// if the field name is an array we do this recursively
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = form_prep($val);
			}

			return $str;
		}

		if ($str === '')
		{
			return '';
		}
		
		if (isset($prepped_fields[$field_name]))
		{
			return $str;
		}

		$str = htmlspecialchars($str);

		// In case htmlspecialchars misses these.
		$str = str_replace(array("'", '"'), array("&#39;", "&quot;"), $str);

		if ($field_name != '')
		{
			$prepped_fields[$field_name] = $field_name;
		}

		return $str;
	}
}