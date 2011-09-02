<?php

class FBField extends FBObject {
	
	//////////////////////////////////////
	////          Properties          ////
	
	/** The id of the field. */
	private $id					= false;
	
	/** The form_id of the field. */
	private $form_id			= false;
	
	/** The display_order of the field. */
	private $display_order		= false;
	
	/** The type of the field.  Used as type on form. */
	private $field_type			= false;
	
	/** The name of the field.  Used as name on form. */
	private $field_name			= false;
	
	/** The value of the field when submitted. */
	private $field_value		= false;
	
	/** The label text to use on the form when displaying the field. */
	private $field_label		= false;
	
	/** The required data of a field. */
	private $required_data		= false;
	
	/** The error message to be shown if the required data isn't met. */
	private $error_message		= false;
	
	/** The optional help text used in explaning how the field should be filled in. */
	private $help_text			= false;
	
	
	
	
	
	//////////////////////////////////////
	////        Public Methods        ////

	function getId() 		{ return($this->getVal('id')); }
	function getFormId() 	{ return($this->getVal('form_id')); }
	function getOrder() 	{ return($this->getVal('display_order')); }
	function getType() 		{ return($this->getVal('field_type')); }
	function getName() 		{ return($this->getVal('field_name')); }
	function getValue() 	{ return($this->getVal('field_value')); }
	function getLabel() 	{ return($this->getVal('field_label')); }
	function getRequired() 	{ return($this->getVal('required_data')); }
	function getHelp() 		{ return($this->getVal('help_text')); }
	function getError() 	{ return($this->getVal('error_message')); }

	function setId($val) 		{ $this->id = $val; }
	function setFormId($val) 	{ $this->form_id = $val; }
	function setOrder($val) 	{ $this->display_order = $val; }
	function setType($val) 		{ $this->field_type = $val; }
	function setName($val) 		{ $this->field_name = $val; }
	function setValue($val) 	{ $this->field_value = $val; }
	function setLabel($val) 	{ $this->field_label = $val; }
	function setRequired($val) 	{ $this->required_data = $val; }
	function setHelp($val) 		{ $this->help_text = $val; }
	function setError($val) 	{ $this->error_message = $val; }
	
	/**
	 * Load the details for this field from a given ID.
	 * @param $fieldID
	 */
	function loadFromID($fieldID)
	{
		global $wpdb;
		
		// Load the form from the database.
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE id = '" . $fieldID . "';";
		$result = $wpdb->get_results($sql, ARRAY_A);
		if(count($result) < 1) throw new Exception("No field found for id: " . $fieldID);
		$fieldArray = $result[0];
		
		return($this->loadFromArray($fieldID, $fieldArray));
	}
	
	/**
	 * Load the details for this field from an array of matching values.
	 * @param $fieldArray
	 */
	function loadFromArray($fieldID, $fieldArray)
	{
		$this->id = $fieldID;
		foreach($fieldArray as $key=>$value)
		{
			if(isset($this->$key))
				$this->$key = $value;
		}
	}
	
	/**
	 * Store this field's info in the DB.
	 */
	function saveToDB()
	{
		global $wpdb;
		
		$id = $this->id;
		
		$data = array(
			'id' 			=> $this->id,
			'form_id' 		=> $this->form_id,
			'display_order'	=> $this->display_order,
			'field_type' 	=> $this->field_type,
			'field_name' 	=> $this->field_name,
			'field_value' 	=> $this->field_value,
			'field_label' 	=> $this->field_label,
			'required_data'	=> $this->required_data,
			'error_message'	=> $this->error_message,
			'help_text' 	=> $this->help_text,
		);
		
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE id = '{$this->id}';";
		$result = $wpdb->get_results($sql, ARRAY_A);
		
		if(count($result) == 0)
		{
			$keySQL = "";
			$valueSQL = "";
			foreach($data as $key=>$value)
			{
				$value = addslashes($value);
				$keySQL .= '`$key`, ';
				$valueSQL .= "'" . $value . "', ";
			}
			$keySQL = trim($keySQL, ", ");
			$valueSQL = trim($valueSQL, ", ");
			$sql = "INSERT INTO " . FORMBUILDER_TABLE_FIELDS . " ({$keySQL}) VALUES ({$valueSQL});";
		}
		else
		{
			$inserSQLArray = array();
			foreach($data as $key=>$value)
			{
				$inserSQLArray[$key] = "$key = '" . addslashes($value) . "'";
			}
			$insertSQL = implode(", ", $inserSQLArray);
			$sql = "UPDATE " . FORMBUILDER_TABLE_FIELDS . " SET {$insertSQL} WHERE id = '{$id}';";
		}
		
		$result = $wpdb->query($sql);
		if($result === false)
		{
			return(false);
		}
		else
		{
			return(true);
		}
	}
	
	/**
	 * Retrieve the HTML code used to display this field.
	 * @param array $extraParams to be set in the template overwriting existing params.
	 */
	function getHtml()
	{
		if(!$this->field_type)
			throw new Exception("No field type defined.");
		
		// Load the appropriate field template.
		$fieldTemplate = "field_" . str_replace(" ", "_", $this->field_type) . ".phtml";
		$t = new FBTemplatizer($fieldTemplate);
		if(!$t->exists())
		{
			unset($t);
			$fieldTemplate = "field_single_line_text_box.phtml";
			$t = new FBTemplatizer($fieldTemplate);
		}
		
		$t->set('ERROR', $this->getError());
		$t->set('NAME', $this->getName());
		$t->set('LABEL', $this->getLabel());
		$t->set('TYPE', str_replace(" ", "", $this->getType()));
		$t->set('HELPTEXT', $this->getHelp());
		$t->set('VALUE', $this->getValue());
		
		$html = $t->parse();
		return($html);
	}
	
	
	/**
	 * Print out the HTML code used to display this field.
	 */
	function showHtml()
	{
		$html = $this->getHtml();
		echo $html;
		return;
	}
	
	
	///////////////////////////////////////
	////        Private Methods        ////
	private function getVal($key)
	{
		return($this->$key);
	}
	
	
	///////////////////////////////////////
	////        Static Methods        ////
	static function create($form_id)
	{
		global $wpdb;
		
		$sql = "INSERT INTO " . FORMBUILDER_TABLE_FIELDS . " (`form_id`) VALUES ('$form_id');";
		$result = $wpdb->query($sql);
		$insert_id = $wpdb->insert_id;
		
		$field = new FBField();
		$field->loadFromID($insert_id);
		return($field);
	}
	
}