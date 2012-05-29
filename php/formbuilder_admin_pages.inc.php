<?php
/*
Created by the TruthMedia Internet Group
(website: truthmedia.com       email : webmaster@truthmedia.com)

Plugin Programming and Design by James Warkentin
http://www.warkensoft.com/about-me/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 3 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
 	function formbuilder_options_default()
 	{
 		global $wpdb, $formbuilder_admin_nav_options;
		$relative_path = FORMBUILDER_PLUGIN_URL;

		if(!formbuilder_user_can('create'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
		include(FORMBUILDER_PLUGIN_PATH . "html/options_default.inc.php");
 	}

 	function formbuilder_options_newForm()
 	{
		global $wpdb;
		if(!formbuilder_user_can('create'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
		_e("Creating new form.  Please wait...", 'formbuilder'); flush();
		
		$insertData = array();
		$insertData['name'] = __('A New Form', 'formbuilder');
		$insertData['subject'] = __('Generic Website Feedback Form', 'formbuilder');
		$insertData['recipient'] = get_option('admin_email');
		$insertData['thankyoutext'] = '';
		
		$result = $wpdb->insert(FORMBUILDER_TABLE_FORMS, $insertData);
		$insert_id = $wpdb->insert_id;
		
		if($result !== false)
		{
			$errorString = '';
			// Insert Name Field
			$insertData = array();
			$insertData['form_id'] = $insert_id;
			$insertData['display_order'] = 1;
			$insertData['field_type'] = 'single line text box';
			$insertData['field_name'] = 'Name';
			$insertData['field_value'] = '';
			$insertData['field_label'] = 'Name';
			$insertData['required_data'] = 'any text';
			$insertData['error_message'] = 'You must enter your name.';
			$insertData['help_text'] = '';
			$result = $wpdb->insert(FORMBUILDER_TABLE_FIELDS, $insertData);
			if($result === false) $errorString .= "\nError inserting Name field: " . $wpdb->last_error;
			
			// Insert Email Field
			$insertData = array();
			$insertData['form_id'] = $insert_id;
			$insertData['display_order'] = 2;
			$insertData['field_type'] = 'single line text box';
			$insertData['field_name'] = 'Email';
			$insertData['field_value'] = '';
			$insertData['field_label'] = 'Email';
			$insertData['required_data'] = 'email address';
			$insertData['error_message'] = 'You must enter your email address.';
			$insertData['help_text'] = '';
			$result = $wpdb->insert(FORMBUILDER_TABLE_FIELDS, $insertData);
			if($result === false) $errorString .= "\nError inserting Email field: " . $wpdb->last_error;
			
			// Insert Comments Field
			$insertData = array();
			$insertData['form_id'] = $insert_id;
			$insertData['display_order'] = 3;
			$insertData['field_type'] = 'large text area';
			$insertData['field_name'] = 'Comments';
			$insertData['field_value'] = '';
			$insertData['field_label'] = 'Comments';
			$insertData['required_data'] = '';
			$insertData['error_message'] = '';
			$insertData['help_text'] = '';
			$result = $wpdb->insert(FORMBUILDER_TABLE_FIELDS, $insertData);
			if($result === false) $errorString .= "\nError inserting Comments field: " . $wpdb->last_error;
				
			
			if($errorString != '')
			{
				formbuilder_admin_alert(__("Unable to create new form fields.  These are the errors: ", 'formbuilder'), $errorString);
			}
			else
			{
				$editURL = formbuilder_build_url( array('fbaction'=>'editForm', 'fbid'=>$insert_id), array('page', 'pageNumber', 'fbtag') );
				$editMSG = sprintf(__('The form has been created.  You can edit it %shere%s.', 'formbuilder'), '<a href="' . $editURL . '">', '</a>');
				$homeURL = formbuilder_build_url( array('fbaction'=>'forms', 'fbmsg'=>$editMSG), array('page', 'pageNumber', 'fbtag') );
				echo "<meta HTTP-EQUIV='REFRESH' content='0; url=" . $homeURL . "'>";
			}
		}
		else
		{
			formbuilder_admin_alert(__("Unable to create new form.  Attempted to run the following SQL: ", 'formbuilder'), $sql);
		}
 	}
 	
 	/**
 	 * 
 	 * @param $form_id
 	 */
 	function formbuilder_options_editFormObject($form_id)
 	{
		/*
		 * Permissions control.  Block users who can't create new forms.
		 */
		if(!formbuilder_user_can('create'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
		$form = new FBForm($form_id);
		$form->loadNow();
		
		$fe = new FBFormEditor($form);
		
		$fe->doFieldActions();
		
		if($fe->isReadyToValidate())
		{
			if($fe->validate())
			{
				if($form->saveToDB())
				{
					$message = sprintf(__("Your form has been saved.  %sYou may click here to return to the main FormBuilder options page.%s", 'formbuilder'), "<a href='" . FB_ADMIN_PLUGIN_PATH . "'>", "</a>");
					formbuilder_admin_alert($message);
				}
				else
				{
					$errors = $form->getErrors();
					foreach($errors as $error)
					{
						formbuilder_admin_alert($error);
					}
				}
			}
		}
		
		if($fe->hasErrors())
		{
			// Show errors regarding validating the form.
			$fe->showErrors();
		}
		
		// Show the form 
		$fe->showTheEditForm();
	}

	
	/**
	 * Old form editing controls.
	 * @param unknown_type $form_id
	 */
 	function formbuilder_options_editForm($form_id)
 	{
 		global $wpdb, $formbuilder_admin_nav_options;
 		
 		/*
 		 * Permissions control.  Block users who can't create new forms.
 		 */
		if(!formbuilder_user_can('create'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
		/*
		 * Process submitted form results.
		 */
 		if(isset($_POST['formbuilder']) AND is_array($_POST['formbuilder']))
		{
			$_POST['formbuilder'] = formbuilder_array_stripslashes($_POST['formbuilder']);
			$_POST['formbuilderfields'] = formbuilder_array_stripslashes($_POST['formbuilderfields']);

			// Verify the data that was posted.
				// No verification currently done on the main form fields.

			// Check for tags.  Add them separately if necessary.
			if(isset($_POST['formbuilder']['tags']))
			{
				$newTags = array();
				$tags = explode(',', $_POST['formbuilder']['tags']);
				foreach($tags as $tag)
				{
					$tag = trim($tag);
					$tag = preg_replace("/[^A-Za-z0-9 _-]/isU", "", $tag);
					if($tag != '') $newTags[] = $tag; 
				}
				
				// Remove tags no longer in the list.  And trim the new tags list to only include true new tags.
				$originalTags = array();
				$sql = "SELECT * FROM " . FORMBUILDER_TABLE_TAGS . " WHERE form_id = '{$form_id}' ORDER BY tag ASC;";
				$results = $wpdb->get_results($sql, ARRAY_A);
				foreach($results as $tag)
				{
					$newTagKey = array_search($tag['tag'], $newTags);
					if($newTagKey === false)
					{
						$sql = "DELETE FROM " . FORMBUILDER_TABLE_TAGS . " WHERE id='{$tag['id']}';";
						if($wpdb->query($sql) === false)
							$errors[] = __('ERROR.  Your tags failed to update.', 'formbuilder')
								 . ' ' . sprintf(__('The error was: %s', 'formbuilder'), $wpdb->last_error);
					}
					else
					{
						unset($newTags[$newTagKey]);
					}
				}
				
				// Add new tags from the list.
				foreach($newTags as $tag)
				{
					$request = array(
						'form_id'=>$form_id,
						'tag'=>$tag,
					);
					if($wpdb->insert(FORMBUILDER_TABLE_TAGS, $request) === false)
						$errors[] = sprintf(__("Failed inserting tag '%s'.", 'formbuilder'), $tag)
						 	. ' ' . sprintf(__('The error was: %s', 'formbuilder'), $wpdb->last_error);
				}
				
				unset($_POST['formbuilder']['tags']);
			}
			
			// Check to ensure that we can save the form data.  List an error message if not.
			if(false === $wpdb->update(FORMBUILDER_TABLE_FORMS, $_POST['formbuilder'], array('id'=>$form_id))) 
				$errors[] = __('ERROR.  Your form failed to save.', 'formbuilder')
					 . ' ' . sprintf(__('The error was: %s', 'formbuilder'), $wpdb->last_error);

			// Check to see if we have any form fields to save, while making sure there are no existing error messages.
			if(isset($_POST['formbuilderfields']) AND is_array($_POST['formbuilderfields']) AND !isset($errors))
			{
				// Iterate through the form fields, do verification and save them to the database.
				foreach($_POST['formbuilderfields'] as $key => $value)
				{
					// Verify that the field has appropriate data
					$value['field_name'] = clean_field_name($value['field_name']);
					
					// Verify that the field has a field name at all
					if(!$value['field_name'])
					{
						if($value['field_type'] != 'comments area'
						AND $value['field_type'] != 'page break'
						) 
						{
							$errors[] = sprintf(__("ERROR.  You have a field on your form with an empty field name.  The field is a '%s'  All fields MUST have a unique field name.", 'formbuilder'), $value['field_type']);
						}
					}
					
					// Check to ensure that the field name hasn't already been used.
					if($value['field_name'])
					{
						if(!isset($tmp_field_names[$value['field_name']]))
						{
							$tmp_field_names[$value['field_name']] = true;
						}
						else
						{
							$errors[] = __("ERROR.  You have a duplicate field '" . $value['field_name'] . "' on your form.  All field names must be unique.", 'formbuilder');
						}
					}
					
					$result = $wpdb->update(FORMBUILDER_TABLE_FIELDS, $value, array('id'=>$key));
					if(false === $result) 
						$errors[] = __("ERROR.  Problems were detected while saving your form fields.", 'formbuilder')
						. ' ' . sprintf(__('The error was: %s', 'formbuilder'), $wpdb->last_error);
				}
			}
			
			if(isset($_POST['fieldAction']) AND is_array($_POST['fieldAction']))
			{
				$fieldAction = $_POST['fieldAction'];
				$fieldKey = key($fieldAction);
				$fieldValue = current($fieldAction);

				if($fieldValue == __('Add New Field', 'formbuilder'))
				{
					if($fieldKey == "newField")
					{	// Create a new field at the end of the form.
						$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE form_id = $form_id ORDER BY display_order DESC;";
						$relatedRows = $wpdb->get_results($sql, ARRAY_A);
#						$relatedRows = $tableFields->search_rows("$form_id", "form_id", "display_order DESC");
						$actionRow = $relatedRows[0];
						$display_order = $actionRow['display_order'] + 1;

						$wpdb->insert(FORMBUILDER_TABLE_FIELDS, array(
							'form_id' => $form_id, 
							'display_order' => $display_order,
							'field_value' => '',
							'field_label' => '',
							'error_message' => '',
							'help_text' => ''
						));
						$rowID = $wpdb->insert_id;
#						$tableFields->save_row($rowID, array("form_id"=>"$form_id", "display_order"=>$display_order));
					}
					if(!isset($errors)) echo "<meta http-equiv='refresh' content='0;url=#field_$rowID' />";
				}
				if($fieldValue == __("Add Another", 'formbuilder'))
				{
					$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE id = $fieldKey ORDER BY display_order DESC;";
					$results = $wpdb->get_results($sql, ARRAY_A);
					$actionRow = $results[0];
					#$actionRow = $tableFields->load_row($fieldKey);


					$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE form_id = $form_id ORDER BY display_order DESC;";
					$relatedRows = $wpdb->get_results($sql, ARRAY_A);
					#$relatedRows = $tableFields->search_rows("$form_id", "form_id");

					foreach($relatedRows as $row)
					{
						#$row = $tableFields->load_row($tableRowID);
						$tableRowID = $row['id'];
						if($row['display_order'] >= $actionRow['display_order'])
						{
							$row['display_order'] = $row['display_order'] + 1;
							$wpdb->update(FORMBUILDER_TABLE_FIELDS, $row, array('id'=>$tableRowID));
							#$tableFields->save_row($tableRowID, $row);
						}
					}

					$wpdb->insert(FORMBUILDER_TABLE_FIELDS, array(
						'form_id' => $form_id, 
						'display_order' => $actionRow['display_order'],
						'field_value' => '',
						'field_label' => '',
						'error_message' => '',
						'help_text' => ''
					));
					$rowID = $wpdb->insert_id;

					#$rowID = $tableFields->create_row();
					#$tableFields->save_row($rowID, array("form_id"=>"$form_id", "display_order"=>$actionRow['display_order']));
					if(!isset($errors)) echo "<meta http-equiv='refresh' content='0;url=#field_$rowID' />";
				}
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
			if(isset($_POST['Save']) AND !isset($errors))
			{
				$message = sprintf(__("Your form has been saved.  %sYou may click here to return to the main FormBuilder options page.%s", 'formbuilder'), "<a href='" . FB_ADMIN_PLUGIN_PATH . "'>", "</a>");
			}

		}
		// End process submitted form results.
		
		
		/*
		 * Show any error messages that we need to show.
		 */
		if(isset($message)) echo "<div class='updated'><p><strong>$message</strong></p></div>"; 
		if(isset($errors)) 
		{
			foreach($errors as $error) 
			{
				echo "<div class='updated' style='background-color: #FFBBBB; border: 1px solid red; color: red;'><p><strong>$error</strong></p></div>";
			}
		} 

		
		/*
		 * Load the form fields from the database.
		 */
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FORMS . " WHERE id = '$form_id';";
		$results = $wpdb->get_results($sql, ARRAY_A);
		$form_fields = $results[0];
		
		/*
		 * Iterate through each field in the forms database for this form.  
		 * These are the generic form control fields such as name, subject, recipient.
		 */
		foreach($form_fields as $key=>$value)
		{
			// Create a new field array.
			$field = array();
			
			// Assign the key to the Field key
			$field['Field'] = $key;
			
			// If there is a POST value for the field, us it, otherwise use the $value variable which contains the value from the DB.
			if(!isset($_POST['formbuilder'][$key]))
				$field['Value'] = $value;
			else
				$field['Value'] = $_POST['formbuilder'][$key];
				
				
			// Add a brief explanation to specific fields of how to enter the data.
			if($field['Field'] == "name") {
				$field['Title'] = __('What do you want to call this contact form?', 'formbuilder');
				$field['HelpText'] = __('What do you want to call this contact form?', 'formbuilder');
				$field['Type'] = "varchar(255)";
			}

			if($field['Field'] == "subject") {
				$field['Title'] = __('The subject line for the email you receive from the form.', 'formbuilder');
				$field['HelpText'] = __('The subject line for the email you receive from the form.', 'formbuilder');
				$field['Type'] = "varchar(255)";
			}

			if($field['Field'] == "recipient") {
				$field['Title'] = __('What email address should the data from this contact form be mailed to?', 'formbuilder');
				$field['HelpText'] = __('What email address should the data from this contact form be mailed to?', 'formbuilder');
				$field['Type'] = "varchar(255)";
			}

			if($field['Field'] == "method") {
				$field['Title'] = __('How should this form post data?  If you are unsure, leave it on POST', 'formbuilder');
				$field['HelpText'] = __('How should this form post data?  If you are unsure, leave it on POST', 'formbuilder');
				$field['Type'] = "enum(POST,GET)";
			}

			if($field['Field'] == "action") {
				$field['Title'] = __('You may specify an alternate form processing system if necessary.  If you are unsure, leave it alone.', 'formbuilder');
				$field['HelpText'] = __('You may specify an alternate form processing system if necessary.  If you are unsure, leave it alone.', 'formbuilder');
				$field['Type'] = "enum('|" . __('Form to Email - Convert the form results to an email.', 'formbuilder') . "'";

				
				if(file_exists(FORMBUILDER_PLUGIN_PATH . "/modules"))
				{
					$d = dir(FORMBUILDER_PLUGIN_PATH . "/modules");
					while (false !== ($entry = $d->read())) {
					   if($entry != "." AND $entry != "..") {
					   	$module_filename = FORMBUILDER_PLUGIN_PATH . "/modules/$entry";
					   	if(!is_file($module_filename)) continue;
					   	$module_data = implode("", file($module_filename));

					   	if(eregi("\n\w*name\: ([^\r\n]+)", $module_data, $regs)) {
					   		$module_name = $regs[1];
					   	} else {
					   		$module_name = $entry;
					   	}
					   	$field['Type'] .= ",'$entry|$module_name'";
					   	
					   	if(eregi("\n\w*instructions\: ([^\r\n]+)", $module_data, $regs)) {
					   		$module_instructions = "\\n\\n" . addslashes($regs[1]);
					   	} else {
					   		$module_instructions = "";
					   	}
					   	$field['HelpText'] .= $module_instructions;
					   	
					   }
					}
					$d->close();
				}
				$field['Type'] .= ")";
			}

			if($field['Field'] == "thankyoutext") {
				$field['Title'] = __('What message would you like to show your visitors?', 'formbuilder');
				$field['HelpText'] = __('What message would you like to show your visitors when the successfully complete the form?', 'formbuilder');
				$field['Type'] = "text";
			}

			if($field['Field'] == "autoresponse") {
				$field['Title'] = __('You may specify an autoresponse to send back if necessary.', 'formbuilder');
				$field['HelpText'] = __('You may specify an autoresponse to send back if necessary.  You should have alread created them on the main FormBuilder Management page.', 'formbuilder');
				$field['Type'] = "enum('|'";

				$sql = "SELECT * FROM " . FORMBUILDER_TABLE_RESPONSES . ";";
				$response_ids = $wpdb->get_results($sql, ARRAY_A);
#				$response_ids = $tableResponses->list_rows();
				if($response_ids) foreach($response_ids as $response_data)
				{
#					$response_data = $tableResponses->load_row($response_id);
					$field['Type'] .= ",'" . $response_data['id'] . "|" . $response_data['name'] . "'";
				}
				$field['Type'] .= ")";
			}

			$fields[$key] = $field;
	
		}


		$all_field_types = formbuilder_get_field_types();
		$all_required_types = formbuilder_get_required_types();

		include(FORMBUILDER_PLUGIN_PATH . "html/options_edit_form.inc.php");
 	}
 	
 	function formbuilder_get_required_types()
 	{
 		$all_required_types = array(
			'any text'=>__("Requires some sort of text in this field.", 'formbuilder'),
			'name'=>__("Requires a name.", 'formbuilder'),
			'email address'=>__("Requires an email address.", 'formbuilder'),
			'confirm email'=>__("Requires an email address", 'formbuilder'),
			'phone number'=>__("Requires a phone number.", 'formbuilder'),
			'any number'=>__("Requires some sort of number.", 'formbuilder'),
			'valid url'=>__("Requires a valid URL.", 'formbuilder'),
			'single word'=>__("Requires a single word.", 'formbuilder'),
			'datestamp (dd/mm/yyyy)'=>__("Requires a date stamp in the form of (dd/mm/yyyy)", 'formbuilder'),
			'credit card number'=>__("Requires a valid credit card number.  Will attempt to determine whether the card number matches a valid pattern.  Does NOT check for card expiry or ownership.", 'formbuilder'),
		);
		ksort($all_required_types);
		return($all_required_types);
 	}
 	
 	function formbuilder_get_field_types()
 	{
 		$all_field_types = array(
			'checkbox'=>__("Single check box.", 'formbuilder'),
			'comments area'=>__("Special field just for text on the form.  Put the text in the field value.", 'formbuilder'),
			'single line text box'=>__("Standard single line text box.", 'formbuilder'),
			'small text area'=>__("Small multi-line text box.", 'formbuilder'),
			'large text area'=>__("Large multi-line text box.", 'formbuilder'),
			'password box'=>__("Used for password entry.  Characters are hidden.", 'formbuilder'),
			'datestamp'=>__("Date selection field.", 'formbuilder'),
			'unique id'=>__("Put a unique ID on your forms.", 'formbuilder'),
			'radio buttons'=>__("Radio selection buttons.  Enter one per line in the field value.", 'formbuilder'),
			'selection dropdown'=>__("Dropdown box.  Enter one value per line in the field value.", 'formbuilder'),
			'hidden field'=>__("A hidden field on the form.  The data will appear in the email.", 'formbuilder'),
			'followup page'=>__("Special field just for indicating a followup url, once the form has been submitted.  Put the url you want people to be redirected to in the field value.", 'formbuilder'),
			'recipient selection'=>__("A special selection dropdown allowing the visitor to specify an alternate form recipient.  Enter values in the form of email@domain.com|Destination Name.", 'formbuilder'),
			'spam blocker'=>__("Special field on the form.  Read more on the FormBuilder admin page.  Only needs a field name.", 'formbuilder'),
			'page break'=>__("Allows you to break your form into multiple pages.  Needs field name and field label.", 'formbuilder'),
			'reset button'=>__("Allows you to put a customized reset button anywhere on the form.  Needs field name and field label.", 'formbuilder'),
			'submit button'=>__("Allows you to put a customized submit button anywhere on the form.  Needs field name and field label.", 'formbuilder'),
			'submit image'=>__("Allows you to put a customized submit image anywhere on the form.  Needs field name and field label.  Field label must be the PATH TO THE IMAGE to be used for the submit button.", 'formbuilder'),
			'system field'=>__("Allows assigning variables to the form without having them displayed on the form itself.  Like hidden fields, but not shown even in the HTML code.", 'formbuilder'),
			'required checkbox'=>__("The same as a normal checkbox, but must be checked in order to submit the form.", 'formbuilder'),
			'required password'=>__("Forces the visitor to enter a predetermined required password in order to submit the form.  Enter the password they should use into the FIELD VALUE", 'formbuilder'),
			'wp user id'=>__("A hidden system field that automatically captures the visitor's WordPress username if they are logged in.", 'formbuilder'),
		);
		
		if(function_exists('imagecreate')) 
			$all_field_types['captcha field'] = __("Special field on the form for displaying CAPTCHAs.  Field name is used for identifying the field.  Field label is used to give the visitor further instruction on what to fill out.", 'formbuilder');
		
		ksort($all_field_types);
		return($all_field_types);
 	}
 	
 	function formbuilder_get_actions()
 	{
 		$actions = array();
 		
 		if(file_exists(FORMBUILDER_PLUGIN_PATH . "/modules"))
		{
			$d = dir(FORMBUILDER_PLUGIN_PATH . "/modules");
			while (false !== ($entry = $d->read())) 
			{
			   if($entry != "." AND $entry != "..") 
			   {
			   		$action = array();
				   	$module_filename = FORMBUILDER_PLUGIN_PATH . "/modules/$entry";
				   	if(!is_file($module_filename)) continue;
				   	$module_data = implode("", file($module_filename));
	
				   	if(eregi("\n\w*name\: ([^\r\n]+)", $module_data, $regs)) {
				   		$module_name = $regs[1];
				   	} else {
				   		$module_name = $entry;
				   	}
				   	$action['file'] = $entry;
				   	$action['name'] = $module_name;
				   	
				   	if(eregi("\n\w*instructions\: ([^\r\n]+)", $module_data, $regs)) {
				   		$module_instructions = "\\n\\n" . addslashes($regs[1]);
				   	} else {
				   		$module_instructions = "";
				   	}
				   	$action['help'] = $module_instructions;
				   	
			   		$actions[] = $action;
			   }
			}
			$d->close();
		}
		return($actions);
 	}
 	
 	function formbuilder_get_responses()
 	{
 		global $wpdb;
 		$responses = array();
 		
 		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_RESPONSES . ";";
		$response_ids = $wpdb->get_results($sql, ARRAY_A);
		if($response_ids) foreach($response_ids as $response_data)
		{
			$responses[] = $response_data;
		}
		
		return($responses);
 	}

 	function formbuilder_options_copyForm($form_id)
 	{
		global $wpdb, $formbuilder_admin_nav_options;
		
		if(!formbuilder_user_can('create'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
		_e("Copying form. Please wait...", 'formbuilder'); flush();
		
		// Duplicate the main form table row
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FORMS . " WHERE id = '$form_id' LIMIT 0,1;";
		$form_data = $wpdb->get_results($sql, ARRAY_A);
		$form_data = $form_data[0];
		
		unset($form_data['id']);
		$form_data['name'] .= __(" (COPY)", 'formbuilder');
		
		$result = $wpdb->insert(FORMBUILDER_TABLE_FORMS, $form_data);
		$new_form_id = $wpdb->insert_id;
		
		// Duplicate all fields on the form, assigning them to the newly created form table row
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE form_id = '$form_id';";
		$related = $wpdb->get_results($sql, ARRAY_A);
		foreach($related as $field)
		{
			unset($field['id']);
			$field['form_id'] = $new_form_id;
			$result = $wpdb->insert(FORMBUILDER_TABLE_FIELDS, $field);
		}
		
		// Duplicate all tags from one form to the other.
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_TAGS . " WHERE form_id = '$form_id';";
		$related = $wpdb->get_results($sql, ARRAY_A);
		foreach($related as $field)
		{
			unset($field['id']);
			$field['form_id'] = $new_form_id;
			$result = $wpdb->insert(FORMBUILDER_TABLE_TAGS, $field);
		}
		$editURL = formbuilder_build_url( array('fbaction'=>'editForm', 'fbid'=>$new_form_id), array('page', 'pageNumber', 'fbtag') );
		$editMSG = sprintf(__('The form has been copied.  You can edit it %shere%s.', 'formbuilder'), '<a href="' . $editURL . '">', '</a>');
		$homeURL = formbuilder_build_url( array('fbaction'=>'forms', 'fbmsg'=>$editMSG), array('page', 'pageNumber', 'fbtag') );
		echo "<meta HTTP-EQUIV='REFRESH' content='0; url=" . $homeURL . "'>";
 	}

	function formbuilder_options_removeForm($form_id)
	{
		global $wpdb, $formbuilder_admin_nav_options;
		
		if(!formbuilder_user_can('create'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
		_e("Deleting form.  Please wait...", 'formbuilder'); flush();
		
		$sql = "DELETE FROM " . FORMBUILDER_TABLE_FORMS . " WHERE id = '$form_id';";
		$wpdb->query($sql);
		
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE form_id = '$form_id';";
		$related = $wpdb->get_results($sql, ARRAY_A);
		if($related) foreach($related as $field)
		{
			$sql = "DELETE FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE id = '" . $field['id'] . "';";
			$wpdb->query($sql);
		}
		
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_TAGS . " WHERE form_id = '$form_id';";
		$related = $wpdb->get_results($sql, ARRAY_A);
		if($related) foreach($related as $field)
		{
			$sql = "DELETE FROM " . FORMBUILDER_TABLE_TAGS . " WHERE id = '" . $field['id'] . "';";
			$wpdb->query($sql);
		}
		
		$homeURL = formbuilder_build_url( array('fbaction'=>'forms', 'fbmsg'=>__('The form has been deleted.', 'formbuilder')), array('page', 'pageNumber', 'fbtag') );
		echo "<meta HTTP-EQUIV='REFRESH' content='0; url=" . $homeURL . "'>";
	}
	
	
	function formbuilder_options_settings()
 	{
 		global $wpdb, $formbuilder_admin_nav_options;
		$relative_path = FORMBUILDER_PLUGIN_URL;
		
		if(!formbuilder_user_can('manage'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
		include(FORMBUILDER_PLUGIN_PATH . "html/options_settings.inc.php");
 	}

	
	

	function formbuilder_options_strings()
 	{
 		global $wpdb, $formbuilder_admin_nav_options;
 		
		if(!formbuilder_user_can('manage'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
 		$formBuilderTextStrings = formbuilder_load_strings();
		
		if(isset($_POST['formbuilder_reset_all_text_strings']) AND $_POST['formbuilder_reset_all_text_strings'] == 'yes')
		{
			delete_option('formbuilder_text_strings');
			$formBuilderTextStrings = formbuilder_load_strings();
		}
		elseif($_POST) foreach($formBuilderTextStrings as $key=>$value)
		{
			if($_POST[$key])
			{
				$formBuilderTextStrings[$key] = htmlentities(stripslashes($_POST[$key]), ENT_QUOTES, get_option('blog_charset'));
			}
			update_option('formbuilder_text_strings', $formBuilderTextStrings);
		}
 		
		$relative_path = FORMBUILDER_PLUGIN_URL;
		include(FORMBUILDER_PLUGIN_PATH . "html/options_strings.inc.php");
 	}

	
	

	// Function to display individual form fields on an HTML page.  $field_info should contain an array describing the field, including any data associated with it.
	function formbuilder_display_form_field($field_info, $prefix = "formbuilder", $template_before = "<div style='padding: 1px 0 2px 20px;'>", $template_mid = ": ", $template_after = "</div>\n")
	{
		$field_name = strtoupper(str_replace("_", " ", $field_info['Field']));
		$field_data = htmlentities($field_info['Value'], ENT_QUOTES, get_option('blog_charset'));

		if(isset($field_info['HelpText'])) $helpText = ' <a href="javascript:;" onClick="alert(\'' . $field_info['HelpText'] . '\');">?</a> ';
		if(isset($helpText)) $template_after = $helpText . $template_after;
		
		if(!isset($field_info['Title'])) $field_info['Title'] = ""; 

		if(eregi("[a-z]+\(([0-9]+)\)", $field_info['Type'], $regs))
		{
			if($regs[1] > 50)  $size = 50;
			else $size = $regs[1];
			$field_details = "<input " .
						"name='" . $prefix . "[" . $field_info['Field'] . "]' " .
						"id='" . $field_info['Field'] . "' " .
						"type='text' " .
						"size='$size' " .
						"maxlength='$regs[1]' " .
						"value='$field_data' " .
						"alt='" . $field_info['Title'] . "' " .
						"title='" . $field_info['Title'] . "' " .
					"/>";
		}

		elseif(eregi("enum\((.+)\)", $field_info['Type'], $regs))
		{
			$enum_values = explode(",", $regs[1]);

			$field_details = "<select " .
						"name='" . $prefix . "[" . $field_info['Field'] . "]' " .
						"id='" . $field_info['Field'] . "' " .
						"alt='" . $field_info['Title'] . "' " .
						"title='" . $field_info['Title'] . "' " .
					">\n";
			foreach($enum_values as $value)
			{
				$value = str_replace("'", "", $value);

				// Check whether or not keys were passed along with the values.
				if(strpos($value, "|") !== false)
				{
					list($key, $value) = explode("|", $value);

				}
				else
					$key = $value;

				if($key == $field_data) $select = "selected";
				else $select = "";

				$field_details .= "<option value='$key' $select>$value</option>\n";
			}
			$field_details .= "</select>";
		}
		
		elseif(eregi("blob", $field_info['Type']) 
			OR eregi("text", $field_info['Type']) 
			OR eregi("longtext", $field_info['Type']))
		{
			$blob_cols = 52;

			$blob_rows =substr_count(wordwrap($field_data, $blob_cols), "\n");

			if($blob_rows > 30) $blob_rows = 30;
			if($blob_rows <= 2) $blob_rows = 2;

			$field_details = "<textarea " .
						"name='" . $prefix . "[" . $field_info['Field'] . "]' " .
						"id='" . $field_info['Field'] . "' " .
						"cols='$blob_cols' " .
						"rows='$blob_rows' " .
						"alt='" . $field_info['Title'] . "' " .
						"title='" . $field_info['Title'] . "' " .
					">\n$field_data</textarea>";
		}
		
		else
		{
			$field_details = "Field type not found!";
			print_r($field_info); echo $template_after;

		}

		// Output the actual field data
		echo $template_before . "<span class='formbuilderLabel'>" . $field_name . $template_mid . "</span>" . "<span class='formbuilderField'>$field_details</span>" . $template_after;
	}

	function formbuilder_array_stripslashes($slash_array = array())
	{
		if($slash_array)
		{
			foreach($slash_array as $key=>$value)
			{
				if(is_array($value))
				{
					$slash_array[$key] = formbuilder_array_stripslashes($value);
				}
				else
				{
					$slash_array[$key] = stripslashes($value);
				}
			}
		}
		return($slash_array);
	}
?>