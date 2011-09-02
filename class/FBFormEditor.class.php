<?php

class FBFormEditor extends FBObject
{
	private $form 	= false;
	private $errors = array();
	
	function FBFormEditor(FBForm $form)
	{
		$this->form = $form;
	}
	
	/**
	 * Determine whether we have all the information we need to validate the form.
	 * @return boolean true if is ready.
	 */
	function isReadyToValidate()
	{
		if($_POST['formbuilder'] AND $_POST['formbuilderfields'])
			return(true);
		else
			return(false);
	}
	
	/**
	 * Go through the submitted form data and ensure everything has been filled out properly.
	 */
	function validate()
	{
		if($this->isReadyToValidate())
		{
			$formData = formbuilder_array_stripslashes($_POST['formbuilder']);
			$fields = formbuilder_array_stripslashes($_POST['formbuilderfields']);
			
			$result = $this->form->loadFromArray( $formData, $fields );
	
			if($result === false)
			{
				$errors = $this->form->getErrors();
				$this->errors = array_merge($this->errors, $errors);
				return(false);
			}
			else
			{
				return(true);
			}
		}
	}
	
	function hasErrors()
	{
		if(count($this->errors) > 0)
			return(true);
		else
			return(false);
	}
	
	function showErrors()
	{
		foreach($this->errors as $error)
			echo "ERROR: {$error}<br/>";
	}
	
	function getErrors()
	{
		return($this->errors);
	}
	
	function doFieldActions()
	{
			if(isset($_POST['fieldAction']) AND is_array($_POST['fieldAction']))
			{
				$fieldAction = $_POST['fieldAction'];
				$fieldKey = key($fieldAction);
				$fieldValue = current($fieldAction);
				$formID = $this->form->getId();	
				
				// If the "add new field" button was pressed.
				if($fieldValue == __('Add New Field', 'formbuilder'))
				{
					if($fieldKey == "newField")
					{
						// Create a new field at the end of the form.
						$nextOrder = $this->form->getNextFieldOrder();
						$field = FBField::create($formID);
						$field->setOrder($nextOrder);
						$this->form->addField($field);
						$this->form->saveToDB();
						$rowID = $field->getId();
					}
					if(!isset($errors)) echo "<meta http-equiv='refresh' content='0;url=#formbuilderEditFieldWrapper-$rowID' />";
				}
				
				
				// if the Add Another Field button was pressed.
				if($fieldValue == __("Add Another", 'formbuilder'))
				{
					$this->debug('Inserting new field at ' . $fieldKey);
					$oldField = $this->form->getFieldById($fieldKey);
					$oldFieldOrder = $oldField->getOrder();
					
					$field = FBField::create($formID);
					
					$rowID = $field->getId();
					$this->form->insertField($field, $oldFieldOrder);
					$this->form->saveToDB();
					if(!isset($errors)) echo "<meta http-equiv='refresh' content='0;url=#formbuilderEditFieldWrapper$rowID' />";
				}
				
				
				// if the Delete button was pressed.
				if($fieldValue == __("Delete", 'formbuilder'))
				{
#					$actionRow = $tableFields->load_row($fieldKey);
#					$relatedRows = $tableFields->search_rows("$form_id", "form_id", "display_order ASC");
#					$tableFields->remove_row($fieldKey);

					$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE id = $fieldKey ORDER BY display_order DESC;";
					$results = $wpdb->get_results($sql, ARRAY_A);
					$actionRow = $results[0];

					$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE form_id = $form_id ORDER BY display_order ASC;";
					$relatedRows = $wpdb->get_results($sql, ARRAY_A);

					$sql = "DELETE FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE id = '$fieldKey';";
					$wpdb->query($sql);
					
					foreach($relatedRows as $row)
					{
#						$row = $tableFields->load_row($tableRowID);
						$tableRowID = $row['id'];
						
						if($row['display_order'] > $actionRow['display_order'])
						{
							$row['display_order'] = $row['display_order'] - 1;
#							$tableFields->save_row($tableRowID, $row);
							$wpdb->update(FORMBUILDER_TABLE_FIELDS, $row, array('id'=>$tableRowID));
						}
					}
					if(!isset($errors)) echo "<meta http-equiv='refresh' content='0;url=#field_" . $relatedRows[0]['id'] . "' />";
				}
				if($fieldValue == __("Move Up", 'formbuilder'))
				{
#					$actionRow = $tableFields->load_row($fieldKey);
#					$relatedRows = $tableFields->search_rows("$form_id", "form_id", "display_order ASC");

					$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE id = $fieldKey ORDER BY display_order DESC;";
					$results = $wpdb->get_results($sql, ARRAY_A);
					$actionRow = $results[0];

					$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE form_id = $form_id ORDER BY display_order ASC;";
					$relatedRows = $wpdb->get_results($sql, ARRAY_A);

#					$firstRow = $tableFields->load_row(reset($relatedRows));
					$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE id = '" . $relatedRows[0]['id'] . "' ORDER BY display_order DESC;";
					$results = $wpdb->get_results($sql, ARRAY_A);
					$firstRow = $results[0];

					$firstPos = $firstRow['display_order'];

					$current_pos = $actionRow['display_order'];

					if($current_pos > $firstPos)
					{
						$current_pos -= 1;
						$actionRow['display_order'] = $current_pos;

						foreach($relatedRows as $row)
						{
#							$row = $tableFields->load_row($tableRowID);
							$tableRowID = $row['id'];

							if($row['display_order'] == $current_pos)
							{
								$row['display_order'] = $row['display_order'] + 1;
#								$tableFields->save_row($tableRowID, $row);
								$wpdb->update(FORMBUILDER_TABLE_FIELDS, $row, array('id'=>$tableRowID));
							}
						}
#						$tableFields->save_row($fieldKey, $actionRow);
						$wpdb->update(FORMBUILDER_TABLE_FIELDS, $actionRow, array('id'=>$fieldKey));
					}
					if(!isset($errors)) echo "<meta http-equiv='refresh' content='0;url=#field_$fieldKey' />";
				}
				if($fieldValue == __("Move Down", 'formbuilder'))
				{
#					$actionRow = $tableFields->load_row($fieldKey);
					$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE id = $fieldKey ORDER BY display_order DESC;";
					$results = $wpdb->get_results($sql, ARRAY_A);
					$actionRow = $results[0];

#					$relatedRows = $tableFields->search_rows("$form_id", "form_id", "display_order DESC");
					$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE form_id = $form_id ORDER BY display_order DESC;";
					$relatedRows = $wpdb->get_results($sql, ARRAY_A);


#					$firstRow = $tableFields->load_row(reset($relatedRows));
					$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE id = '" . $relatedRows[0]['id'] . "' ORDER BY display_order DESC;";
					$results = $wpdb->get_results($sql, ARRAY_A);
					$firstRow = $results[0];

					$lastPos = $firstRow['display_order'];

					$current_pos = $actionRow['display_order'];


					if($current_pos < $lastPos)
					{
						$current_pos += 1;
						$actionRow['display_order'] = $current_pos;

						foreach($relatedRows as $row)
						{
#							$row = $tableFields->load_row($tableRowID);
							$tableRowID = $row['id'];

							if($row['display_order'] == $current_pos)
							{
								$row['display_order'] = $row['display_order'] - 1;
#								$tableFields->save_row($tableRowID, $row);
								$wpdb->update(FORMBUILDER_TABLE_FIELDS, $row, array('id'=>$tableRowID));
							}
						}
#						$tableFields->save_row($fieldKey, $actionRow);
						$wpdb->update(FORMBUILDER_TABLE_FIELDS, $actionRow, array('id'=>$fieldKey));
					}
					if(!isset($errors)) echo "<meta http-equiv='refresh' content='0;url=#field_$fieldKey' />";
				}
			}
		
	}
	
	function showTheEditForm()
	{
		$t = new FBTemplatizer('form_edit.phtml');
		
		$t->set('FORM_ID', $this->form->getId());
		$t->set('ADMIN_PATH', FB_ADMIN_PLUGIN_PATH);
		
		$t->set('NAME_VALUE', htmlentities($this->form->getName(), ENT_QUOTES, get_option('blog_charset')));
		$t->set('SUBJECT_VALUE', htmlentities($this->form->getSubject(), ENT_QUOTES, get_option('blog_charset')));
		$t->set('RECIPIENT_VALUE', htmlentities($this->form->getRecipient(), ENT_QUOTES, get_option('blog_charset')));
		
		$method = $this->form->getMethod();
		if($method == 'POST')
		{
			$t->set('METHOD_SELECT', "<option value='POST' selected='selected'>POST</option><option value='GET'>GET</option>");
		}
		else
		{
			$t->set('METHOD_SELECT', "<option value='POST'>POST</option><option value='GET' selected='selected'>GET</option>");
		}
		
		$formAction = $this->form->getAction();
		$allActions = formbuilder_get_actions();
		$html = "<option value=''>Form to Email - Convert the form results to an email.</option>";
		foreach($allActions as $action)
		{
			if($action['file'] == $formAction)
				$selected = "selected = 'selected'";
			else
				$selected = "";
			$html .= "<option value='{$action['file']}' {$selected}>{$action['name']}</option>";
		}
		$t->set('ACTION_SELECT', $html);
		
		$t->set('THANKS_VALUE', htmlentities($this->form->getThanks(), ENT_QUOTES, get_option('blog_charset')));
		
		$formResponse = $this->form->getResponse();
		$allResponses = formbuilder_get_responses();
		$html = "<option value='' ></option>";
		foreach($allResponses as $response)
		{
			if($response['id'] == $formResponse)
				$selected = "selected = 'selected'";
			else
				$selected = "";
			$html .= "<option value='{$response['id']}' {$selected}>{$response['name']}</option>";
		}
		$t->set('AUTORESPONSE_SELECT', $html);
		
		
		
		
		// show the fields
		$allFields = $this->form->getAllFields();
		$fieldHTML = "";
		foreach($allFields as $field)
		{
			$fieldHTML .= $this->showTheEditField($field);
		}
		$t->set('SHOW_FIELDS', $fieldHTML);
		
		
		
		$t->output();
	}
	
	private function showTheEditField(FBField $field)
	{
		static $t;
		if(!$t) $t = new FBTemplatizer('field_edit.phtml');
		
		$t->clear();
		$t->set('FIELD_ID', $field->getId());
		$t->set('FIELD_NAME', htmlentities($field->getName(), ENT_QUOTES, get_option('blog_charset')));
		$t->set('FIELD_VALUE', htmlentities($field->getValue(), ENT_QUOTES, get_option('blog_charset')));
		$t->set('FIELD_LABEL', htmlentities($field->getLabel(), ENT_QUOTES, get_option('blog_charset')));
		$t->set('FIELD_ERROR', htmlentities($field->getError(), ENT_QUOTES, get_option('blog_charset')));
		$t->set('FIELD_HELP', htmlentities($field->getHelp(), ENT_QUOTES, get_option('blog_charset')));
		
		
		$all_field_types = formbuilder_get_field_types();
		$html = '';  $help = '';
		foreach($all_field_types as $key=>$value)
		{
			if($key == $field->getType())
				$selected = "selected = 'selected'";
			else
				$selected = "";
			$html .= "<option value='{$key}' {$selected}>{$key}</option>";
			$help .= "{$key}: {$value}\\n";
		}
		$t->set('FIELD_TYPE_SELECT', $html);
		$t->set('FIELD_TYPE_HELP', htmlentities($help, ENT_QUOTES, get_option('blog_charset')));
		
		
		$all_required_types = formbuilder_get_required_types();
		$html = '';  $help = '';
		foreach($all_required_types as $key=>$value)
		{
			if($key == $field->getRequired())
				$selected = "selected = 'selected'";
			else
				$selected = "";
			$html .= "<option value='{$key}' {$selected}>{$key}</option>";
			$help .= "{$key}: {$value}\\n";
		}
		$t->set('FIELD_REQUIRED_SELECT', $html);
		$t->set('FIELD_REQUIRED_HELP', htmlentities($help, ENT_QUOTES, get_option('blog_charset')));
		
		return($t->parse());
	}
	
}