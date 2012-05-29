<?php

class FBForm extends FBObject
{
	
	//////////////////////////////////////
	////          Properties          ////
	
	public $id				= false;
	
	/** String describing the form. */
	public $name 			= false;
	
	/** String with the form subject to be sent in the email. */
	public $subject 		= false;
	
	/** String with the recipient email address for the form to be sent to. */
	public $recipient 		= false;
	
	/** String defining GET or POST method for the form. */
	public $method 		= false;
	
	/** String defining the URL to which the form will be posted. */
	public $action 		= false;
	
	/** String with text to show the visitor once the form is filled out. */
	public $thankyoutext 	= false;
	
	/** String with text to show the visitor once the form is filled out. */
	public $helptext 	= false;
	
	/** ID of the autoresponse to be sent to the visitor once the form is filled out. */
	public $autoresponse 	= 0;
	
	/** Errors encountered while performing functions on the form. */
	private $errors = array();
	
	/** Array of FBField objects */
	private $fields			= false;
	
	
	
	
	//////////////////////////////////////
	////        Public Methods        ////
	
	function FBForm($form_id)
	{
		$this->id = $form_id;
	}
	
	function loadNow()
	{
		$this->loadForm();
		$this->loadFields();
	}

	/**
	 * Private field public accessors

	 */
	function getId()		{ return($this->getVar('id')); }
	function getName()		{ return($this->getVar('name')); }
	function getSubject()	{ return($this->getVar('subject')); }
	function getRecipient()	{ return($this->getVar('recipient')); }
	function getMethod()	{ return($this->getVar('method')); }
	function getAction()	{ return($this->getVar('action')); }
	function getThanks()	{ return($this->getVar('thankyoutext')); }
	function getResponse()	{ return($this->getVar('autoresponse')); }
	function getHelpText()	{ return($this->getVar('helptext')); }
	
	/**
	 * Add a new field to the form.
	 */
	function addField(FBField $field)
	{
		$this->lazyLoadFields();
		$this->fields[] = $field;
	}
	
	/**
	 * Insert a field at a given order location moving all others below it.
	 * @param $field
	 * @param $order
	 */
	function insertField(FBField $fieldToInsert, $order)
	{
		foreach($this->fields as $field)
		{
			$fieldOrder = $field->getOrder();
			if($fieldOrder >= $order)
			{
				$fieldOrder++;
				$field->setOrder($fieldOrder);
			}
		}
		$fieldToInsert->setOrder($order);
		$this->addField($fieldToInsert);
	}
	
	function replaceField($fieldIndex, $fieldObj)
	{
		$this->lazyLoadFields();
		$this->fields[$fieldIndex] = $fieldObj;
	}
	
	/**
	 * Retrieve the field at the indicated index.
	 * @param $index
	 */
	function getField($index)
	{
		$this->lazyLoadFields();
		return($this->fields[$index]);
	}
	
	function getFieldById($id)
	{
		$this->lazyLoadFields();
		$index = $this->getFieldIndexByID($id);
		return($this->getField($index));
	}
	
	/**
	 * Get the number of fields in the form.
	 */
	function getNumFields()
	{
		$this->lazyLoadFields();
		return(count($this->fields));
	}
	
	/**
	 * Retrieve an array of all the fields.
	 */
	function getAllFields()
	{
		$this->lazyLoadFields();
		return($this->fields);
	}
	
	/**
	 * Get the last field on the form.
	 */
	function getLastField()
	{
		$this->lazyLoadFields();
		return(end($this->fields));
	}
	
	/**
	 * Get the integer representing the next value to be used in adding a field to the end of the list of form fields.
	 */
	function getNextFieldOrder()
	{
		$lastField = $this->getLastField();
		$order = $lastField->getOrder();
		return($order + 1);
	}
	
	/**
	 * Get an array of key => proper value pairs for importing an array to the object.
	 */
	function getValidArrayFields()
	{
		$accepted = array(
			'name'			=> '.+',
			'subject'		=> '.+',
			'recipient'		=> '.+',
			'method'		=> '.+',
			'action'		=> '.*',
			'thankyoutext'	=> '.*',
			'autoresponse'	=> '.*',
		);
		return($accepted);
	}
	
	/**
	 * Take an array of key/value pairs and validate against required form object fields.
	 * Use before loading form data from an array.
	 * @param $data
	 * @return array of errors
	 */
	function validateArray( $formData = array() )
	{
		$storeData = false;
		$accepted = $this->getValidArrayFields();
		foreach($formData as $key=>$value)
		{
			if(isset($accepted[$key]) AND preg_match('/^' . $accepted[$key] . '$/isU', $value))
			{
					$storeData[$key] = $value;
			}
			else
			{
				$this->addError("You must enter a proper value for '" . $key . "'.");
			}
		}
		return($storeData);
	}
	
	
	function loadFromArray( $formData = array(), $fields = array() )
	{
		$storeData = $this->validateArray( $formData );
		if($storeData === false)
			return(false);
	
		// Replace each value in the object with the post data.
		foreach($storeData as $key=>$value)
		{
			if(isset($this->$key))
				$this->$key = $value;
		}
		
		// Load the form fields.
		$displayOrder = 1;
		foreach($fields as $fieldID=>$field)
		{
			$field['display_order'] = $displayOrder++;
			$fieldObj = new FBField();
			$fieldObj->setFormId($this->id);
			$fieldObj->loadFromArray($fieldID, $field);
			
			$fieldIndex = $this->getFieldIndexByID($fieldID);
			if($fieldIndex !== false)
				$this->replaceField($fieldIndex, $fieldObj);
			else
				$this->addField($fieldObj);
		}
		
		return(true);
	}
	
	function saveToDB()
	{
		global $wpdb;
		if(!is_numeric($this->id))
			throw new Exception("Form ID to save is not numeric.  Not sure how this happened.");
		else
			$id = $this->id;
			
		// Compile data to store:
		$data = array(
			'id' 			=> $this->id,
			'name' 			=> $this->name,
			'subject' 		=> $this->subject,
			'recipient' 	=> $this->recipient,
			'method' 		=> $this->method,
			'action' 		=> $this->action,
			'thankyoutext' 	=> $this->thankyoutext,
			'autoresponse' 	=> $this->autoresponse,
		);
			
		// Check to see if the form already exists in the DB.
		$sql = "SELECT id FROM " . FORMBUILDER_TABLE_FORMS . " WHERE id = '" . $id . "';";
		$results = $wpdb->get_results($sql, ARRAY_A);
		
		if(count($results) == 0)
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
			$sql = "INSERT INTO " . FORMBUILDER_TABLE_FORMS . " ({$keySQL}) VALUES ({$valueSQL});";
		}
		else
		{
			$inserSQLArray = array();
			foreach($data as $key=>$value)
			{
				$inserSQLArray[$key] = "$key = '" . addslashes($value) . "'";
			}
			$insertSQL = implode(", ", $inserSQLArray);
			$sql = "UPDATE " . FORMBUILDER_TABLE_FORMS . " SET {$insertSQL} WHERE id = '{$id}';";
		} 
		
		$saveResult = $wpdb->query($sql);
		if($saveResult === false)
		{
			$this->addError("For some reason, your form failed to save while trying to execute the following SQL query: " . $sql);
			return(false);
		}
		else
		{
			
			$return = true;
			foreach($this->fields as $field)
			{
				$result = $field->saveToDB();
				if($result === false)
				{
					$return = false;
					$this->addError("Failed to save field: " . $field->getName());
				}
			}
			
			return($return);
		}

/*
		// from the old code.
			// Save the form data.
			$saveResult = $wpdb->update( FORMBUILDER_TABLE_FORMS, $storeData, array('id'=>$this->form->id) );
			if($saveResult === false) 
				$this->errors[] = "ERROR.  Your form failed to save.";
*/
			
	}
	
	function hasErrors()
	{
		if(count($this->errors) > 0)
			return(true);
		else
			return(false);
	}
	
	function getErrors()
	{
		return($this->errors);
	}
	
	function addError($msg)
	{
		$this->errors[] = $msg;
	}
	
	
	//////////////////////////////////////
	////       Private Methods        ////
	
	private function getFieldIndexByID($id)
	{
		$this->lazyLoadFields();
		foreach($this->fields as $key=>$field)
		{
			if($field->getID() == $id)
				return($key);
		}
		return(false);
	}
	
	private function getVar($fieldName)
	{
		$this->lazyLoadForm();
		if(isset($this->$fieldName))
			return($this->$fieldName);
		else
			return(false);
	}
	
	private function lazyLoadForm()
	{
		if($this->name === false)
		{
			$this->loadForm();
		}
	}
	
	private function loadForm()
	{
		if(is_numeric($this->id))
		{
			global $wpdb;
			
			// Load the form from the database.
			$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FORMS . " WHERE id = '" . $this->id . "';";
			$result = $wpdb->get_results($sql, ARRAY_A);
			if(count($result) < 1) throw new Exception("No form found for id: " . $this->id);
			$formArray = $result[0];
			
			foreach($formArray as $key=>$value)
			{
				if(($key != 'id') AND (isset($this->$key)))
					$this->$key = $value;
			}
		}
		else
			throw new Exception("No form ID set.  We can't load the form right now.");
	}
	
	private function lazyLoadFields()
	{
		if($this->fields === false)
		{
			$this->loadFields();
		}
	}
	
	private function loadFields()
	{
		$this->lazyLoadForm();
		
		global $wpdb;
		if($this->id !== false)
		{
			$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE form_id = '{$this->id}' ORDER BY display_order;";
			$results = $wpdb->get_results($sql, ARRAY_A);
			if(count($results) > 0)
			{
				$this->fields = array();
				foreach($results as $fieldArray)
				{
					$fieldID = $fieldArray['id'];
					$field = new FBField();
					$field->loadFromArray($fieldID, $fieldArray);
					$this->addfield($field );
				}
			}
		}
		else
			throw new Exception("No form ID given.");
	}
	
}

?>