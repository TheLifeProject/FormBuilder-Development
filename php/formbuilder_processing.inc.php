<?php

	/**
	 * This is run before the template is shown in order to process any form post data
	 * and define what the form should look like on the page.
	 */
	function formbuilder_checkPOSTData()
	{
		global $wp_query, $_SERVER, $wpdb, $formbuilder_formDisplayArray;
		
		$formbuilder_formDisplayArray = array();

		$version = get_option('formbuilder_version');
		if(!$version) return;
		
		foreach($wp_query->posts as $post)
		{
		
			$content = $post->post_content;
	
			if($post->post_password != '' AND strpos($content, 'wp-pass.php')) return($content);
	
			// Check to determine whether or not we have a form manually entered into the content of the post
			// Manual entries in the form of [formbuilder:5] where 5 is the ID of the form to be displayed.
			$content_form_ids = formbuilder_check_content($content);
	
			// Go through the content and process the form data for the tag.
			foreach($content_form_ids as $form_id)
			{
				if(!isset($formbuilder_formDisplayArray[$form_id['id']]))
					$formbuilder_formDisplayArray[$form_id['id']] = formbuilder_process_form($form_id['id']);
			}
	
	
			$excerpt = strpos($post->post_content, "<!--more-->");
			$show = false;
			if(is_single() OR is_page() OR !$excerpt) $show = true;
	
			if($show)
			{
				$post_id = $post->ID;
				
				$sql = "SELECT form_id FROM " . FORMBUILDER_TABLE_PAGES . " WHERE post_id = '$post_id';";
				$results = $wpdb->get_results($sql, ARRAY_A);
				
				if($results)
				{
					$page = $results[0];
	
					if(!isset($formbuilder_formDisplayArray[$page['form_id']]))
						$formbuilder_formDisplayArray[$page['form_id']] = formbuilder_process_form($page['form_id']);
				}
			}
		}
		
	}
	
	
	/**
	 * Main action on the_content()
	 * @param unknown_type $content
	 */
	function formbuilder_main($content = '') {
		global $post, $_SERVER, $wpdb, $formbuilder_formDisplayArray;

		$version = get_option('formbuilder_version');
		if(!$version) return($content);

		$module_status = false;

		if($post->post_password != '' AND strpos($content, 'wp-pass.php')) return($content);


		// Check to determine whether or not we have a form manually entered into the content of the post
		// Manual entries in the form of [formbuilder:5] where 5 is the ID of the form to be displayed.
		$content_form_ids = formbuilder_check_content($content);

		foreach($content_form_ids as $form_id)
		{
			$formDisplay = $formbuilder_formDisplayArray[$form_id['id']];
			$content = str_replace($form_id['tag'], $formDisplay, $content);
		}


		$excerpt = strpos($post->post_content, "<!--more-->");
		$show = false;
		if(is_single() OR is_page() OR !$excerpt) $show = true;

		if($show)
		{
			$post_id = $post->ID;
			
			$sql = "SELECT form_id FROM " . FORMBUILDER_TABLE_PAGES . " WHERE post_id = '$post_id';";
			$results = $wpdb->get_results($sql, ARRAY_A);
			
			if($results)
			{
				$page = $results[0];

				$formDisplay = $formbuilder_formDisplayArray[$page['form_id']];
			
				// Do not show the post content if FORMBUILDER_HIDE_POST_AFTER is true.
				if(FORMBUILDER_HIDE_POST_AFTER)
				{
					if(stripos($formDisplay, '<form') === false)
						$content = '';
				}
				
				
				$content = $content . "$formDisplay\n";
			}
		}
		return($content);
	}

	// Function to display and process the actual form.
	function formbuilder_process_form($form_id, $data=false)
	{
		global $wpdb;
		
		$formBuilderTextStrings = formbuilder_load_strings();
		
		$siteurl = get_option('siteurl');
		$relative_path = str_replace(ABSOLUTE_PATH, "/", FORMBUILDER_PLUGIN_PATH);
		$page_path = $siteurl . $relative_path;

		// Pull the form data from the db for the selected form ID.
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FORMS . " WHERE id='$form_id';";
		$results = $wpdb->get_results($sql, ARRAY_A);
		if($results) $form = $results[0];
		
		if(!$form) return("");
		
		// Pass the form through a filter that converts all fields to proper htmlentities.
		$form = formbuilder_array_htmlentities($form);
		$allFields = array();

		// If no form action is defined, use a blank string.  (indicates standard process)
		if(!isset($form['action']))
			$form['action'] = "";
		
		$module_status = false;

		// Load the Form Action module, if different than the standard.
		if($form['action'] != "") {
			if(include_once(FORMBUILDER_PLUGIN_PATH . "modules/" . $form['action'])) {
				$startup_funcname = "formbuilder_startup_" . eregi_replace("\..+", "", $form['action']);
				$processor_funcname = "formbuilder_process_" . eregi_replace("\..+", "", $form['action']);

				if(function_exists("$startup_funcname"))
					$module_status = $startup_funcname($form);
			}
		}
		else
			$module_status = true;
			
		if(!isset($form['action_target'])) $form['action_target'] = "";

		$formID = clean_field_name($form['name']);
		$formCSSID = "formBuilderCSSID$formID";
		if(!$form['action_target'] OR $form['action_target'] == "")
			$form['action_target'] = $_SERVER['REQUEST_URI']. "#$formCSSID";
		
		$session_id = session_id();
		$sessName   = session_name();

		if(SID != "" AND strpos($form['action_target'], $sessName) === false)
		{
			if(strpos($form['action_target'], "?") === false)
				$form['action_target'] .= "?" . htmlspecialchars(SID);
			else
				$form['action_target'] .= "&amp;" . htmlspecialchars(SID);
		}

		if($module_status !== false)
		{
			// Retrieve the tags for the form and use as additional CSS classes in order to allow forms with specific tags to use alternate stylesheets.
			$formTags = array();
			$sql = "SELECT * FROM " . FORMBUILDER_TABLE_TAGS . " WHERE form_id = '{$form_id}' ORDER BY tag ASC;";
			$results = $wpdb->get_results($sql, ARRAY_A);
			foreach($results as $r)
			{
				$formTags[] = preg_replace('/[^a-z0-9]/isU', '', $r['tag']);
			}
			$formTags = implode(' FormBuilder', $formTags);

			$formDisplay = "\n<form class='formBuilderForm $formTags' id='formBuilder$formID' " .
					"action='" . $form['action_target'] . "' method='" . strtolower($form['method']) . "' onsubmit='return fb_disableForm(this);'>" .
					"<input type='hidden' name='formBuilderForm[FormBuilderID]' value='" . $form_id . "' />";

			
			// Paged form related controls for CSS and Javascript
			$page_id = 1;
			$new_page = false;
			$formDisplay .= "<div id='formbuilder-{$form_id}-page-$page_id'>";
			
			$formDisplay .= '<script type="text/javascript">

function toggleVis(boxid)
{
	if(document.getElementById(boxid).isVisible == "true")
	{
		toggleVisOff(boxid);
	}
	else
	{
		toggleVisOn(boxid);
	}
}

function toggleVisOn(boxid) 
{
		document.getElementById(boxid).setAttribute("class", "formBuilderHelpTextVisible");
		document.getElementById(boxid).isVisible = "true";
}

function toggleVisOff(boxid) 
{
		document.getElementById(boxid).setAttribute("class", "formBuilderHelpTextHidden");
		document.getElementById(boxid).isVisible = "false";
}

			</script>';


			// The $module_status variable is considered to be an error, if it contains a string.  
			if(is_string($module_status))
				$formDisplay .= $module_status;

			// Get the fields for the form.
			$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FIELDS . " WHERE form_id = '" . $form['id'] . "' ORDER BY display_order ASC;";
			$related = $wpdb->get_results($sql, ARRAY_A);

			$submit_button_set = false;

			// Check for duplicate form submissions.
			if(isset($_POST['formBuilderForm']['FormBuilderID']) 
			AND $_POST['formBuilderForm']['FormBuilderID'] == $form_id) 
			{
				
				$duplicate_check_hash = $form_id . formbuilder_get_hash();
				$old_hash = get_option('formBuilder_duplicate_hash');
				
				if($duplicate_check_hash == $old_hash) {
					$post_errors = true;

					$tmp_msg = "\n<div class='formBuilderFailure'><h4>" . $formBuilderTextStrings['form_problem'] . "</h4><p>" . $formBuilderTextStrings['already_submitted'] . "</p>";
					$tmp_msg .= "\n</div>\n" . $formDisplay;

					$formDisplay = $tmp_msg;
					
				}
				else {
					update_option('formBuilder_duplicate_hash', $duplicate_check_hash);
				}
			}


			// Begin going through each field on the form and checking it against the submitted data.
			if(count($related) > 0)
			{
				foreach($related as $field)
				{
					$error_msg = "";
					
					$divClass = "formBuilderField " . eregi_replace("[^a-z0-9]", "_", $field['field_type']);
					$divID = "formBuilderField" . clean_field_name($field['field_name']);

					$lb = "<br/>";
					$visibility = "";

					// Define short versions of the more used form variables.
					$field['name'] = "formBuilderForm[" . $field['field_name'] . "]";
					
					// If the field type is a checkbox with no predefined field value, give it a field value of "checked".
					if($field['field_type'] == "checkbox" AND $field['field_value'] == "")
					{
						$field['field_value'] = "checked";
					}
					if($field['field_type'] == "required checkbox" AND $field['field_value'] == "")
					{
						$field['field_value'] = "checked";
					}
					
					// Fill unset POST vars with empty strings.  Not sure what this was used for, but it is now disabled so as not to mess with other plugins that may also check _POST data. (James: Oct. 19, 2011)
					//if(!isset($_POST['formBuilderForm'][$field['field_name']])) $_POST['formBuilderForm'][$field['field_name']] = "";
				
					// Determine what submitted value to give to the field values. 
					if($field['field_type'] == 'system field')
					{
						// Manually assign value to system fields before anything else.
						$field['value'] = $field['field_value'];
					}
					// Determine what submitted value to give to the field values. 
					elseif($field['field_type'] == 'wp user id')
					{
						// Manually assign value to system fields before anything else.
						$wpuser = wp_get_current_user();
						if($wpuser->id != 0)
						{
							$field['value'] = $wpuser->user_login;
						}
						$wpuser = null;
					}
					elseif(isset($_POST['formBuilderForm']['FormBuilderID']) AND $_POST['formBuilderForm']['FormBuilderID'] == $form_id)
					{
						// If there is a POST value, assign it to the field.
						$field['value'] = htmlentities(stripslashes($_POST['formBuilderForm'][$field['field_name']]), ENT_QUOTES, get_option('blog_charset'));
					}
					elseif(isset($_GET[$field['field_name']]))
					{
						// If there is a GET value, assign it to the field.
						$field['value'] = htmlentities(stripslashes($_GET[$field['field_name']]), ENT_QUOTES, get_option('blog_charset'));
					}
					else
					{
						// Required passwords should not display the default field value.
						if($field['field_type'] != 'required password')
						{
							// In this case, there is neither a POST nor a GET value, therefore we assign the field value to be whatever the default value was for the field.
							$field['value'] = $field['field_value'];
						}
						else
						{
							$field['value'] = "";
						}
					}



					// Validate POST results against validators.
					if(isset($_POST['formBuilderForm']['FormBuilderID']) AND $_POST['formBuilderForm']['FormBuilderID'] == $form_id)
					{
						$duplicate_check_hash .= md5($field['value']);
						
						if($field['field_type'] == "spam blocker")
						{	// Check Spam Blocker for any submitted data.
							if(trim($field['value']) != "") {
								$post_errors = true;
							}
						}
						
						elseif($field['field_type'] == "recipient selection")
						{	// Check to ensure we have been given a valid recipient selection
							$options = explode("\n", $field['field_value']);
							
							if(strpos($options[$field['value']], "|") !== false)
								list($option_value, $option_label) = explode("|", $options[$field['value']], 2);
							else
								$option_value = $option_label = $options[$field['value']];
							
							if(!eregi(FORMBUILDER_PATTERN_EMAIL, $option_value))
							{
								$error_msg = $field['error_message'];
								$post_errors = true;
								$missing_post_fields[$divID] = $field['field_label'];
							}
						}
						
						elseif($field['field_type'] == "captcha field" AND function_exists('imagecreate'))
						{	// Check CAPTCHA to ensure it is correct
							if( isset($_SESSION['security_code']) AND $_SESSION['security_code'] == $field['value'] && !empty($_SESSION['security_code'] ) ) {
								// Insert you code for processing the form here, e.g emailing the submission, entering it into a database. 
								unset($_SESSION['security_code']);
							} else {
								if( !isset( $_SERVER['HTTP_COOKIE'] ) ) 
								{
									$post_errors = true;
									$missing_post_fields[$divID] = $formBuilderTextStrings['captcha_cookie_problem'];
								}
								else
								{
									// Insert your code for showing an error message here
									$post_errors = true;
									$error_msg = $field['error_message'];
									$missing_post_fields[$divID] = $field['field_label'];
								}
							}
						}
						
						elseif($field['field_type'] == 'required password')
						{
							if($field['value'] != $field['field_value'])
							{
								$post_errors = true;
								if(!$field['error_message'])
									$field['error_message'] = __("The password you entered is incorrect.", 'formbuilder');
									
								$error_msg = $field['error_message'];
								$missing_post_fields[$divID] = $field['field_label'];
							}
						}
						
						else
						{	// Check the values of any other required fields.
							if(!formbuilder_validate_field($field))
							{
								$error_msg = $field['error_message'];
								$post_errors = true;
								$missing_post_fields[$divID] = $field['field_label'];
							}
							
						}
					}
					
					
					
					// Prepopulate fields with user details if available
					if($field['value'] == "")
					{
						$wpuser = wp_get_current_user();
						if($wpuser->id != 0)
						{
							// User is logged in.  Prepopulate with data.
							if(preg_match('#^(yourname|name|your_name|display_name|nickname)$#i', $field['field_name'], $regs))
								$field['value'] = $wpuser->display_name;
							
							if(preg_match('#^(firstname|first_name)$#i', $field['field_name'], $regs))
								$field['value'] = $wpuser->first_name;
							
							if(preg_match('#^(lastname|last_name)$#i', $field['field_name'], $regs))
								$field['value'] = $wpuser->last_name;
							
							if((preg_match('#^(email)$#i', $field['field_name'], $regs)))
								$field['value'] = $wpuser->user_email;
							
							if((preg_match('#^(full_name|fullname)$#i', $field['field_name'], $regs)))
								$field['value'] = trim($wpuser->first_name . " " . $wpuser->last_name);
						}
						$wpuser = null;
					}
					

					// Display any necessary error msgs.
					if($error_msg) {
						$formError = "<div class='formBuilderError'>$error_msg</div>";
					}
					else
						$formError = "";

					// Check for required fields, and change the class label details if necessary
					if(isset($field['required_data']) AND $field['required_data'] != "none" AND $field['required_data'] != "")
					{
						$formLabelCSS = "formBuilderLabelRequired";
					}
					else
					{
						$formLabelCSS = "formBuilderLabel";
					}
					
					// Determine if we need to show help text.
					if($field['help_text'])
					{
						$formHelp = "<div class='formBuilderHelpText' id='formBuilderHelpText$divID'>" . $field['help_text'] . "</div>";
						$formHelpJava = "<a href='javascript:;' "
							. "class='formBuilderHelpTextToggle' "
							. "onClick='toggleVis(\"formBuilderHelpText$divID\");' "
							. ">?</a>$formHelp";
					}
					else
					{
						$formHelpJava = "";
						$formHelp = "";
					}
					
					// Display assorted form fields depending on the type of field.
					switch($field['field_type'])
					{
						case "comments area":
							$formLabel = "";
							$formInput = "<div class='formBuilderCommentsField'>" . decode_html_entities($field['field_value'], ENT_NOQUOTES, get_option('blog_charset')) . "</div> $formHelpJava";
							$divClass = "formBuilderComment";
						break;

						case "hidden field":
							$formLabel = "";
							$formInput = "<div class='formBuilderHiddenField'><input type='hidden' name='" . $field['name'] . "' value='" . $field['value'] . "' /></div>";
							$divClass = "formBuilderHidden";
						break;

						case "small text area":
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " $formHelpJava</div>";
							$formInput = "<div class='formBuilderSmallTextarea'><textarea name='" . $field['name'] . "' rows='4' cols='50' " .
									"id='field$divID' onblur=\"fb_ajaxRequest('" . $page_path . "php/formbuilder_parser.php', " .
									"'formid=" . $form['id'] . "&amp;fieldid=" . $field['id'] . "&amp;val='+document.getElementById('field$divID').value, 'formBuilderErrorSpace$divID')\" >" .
									$field['value'] . "</textarea></div>";
						break;

						case "large text area":
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " $formHelpJava</div>";
							$formInput = "<div class='formBuilderLargeTextarea'><textarea name='" . $field['name'] . "' rows='10' cols='80' " .
									"id='field$divID' onblur=\"fb_ajaxRequest('" . $page_path . "php/formbuilder_parser.php', " .
									"'formid=" . $form['id'] . "&amp;fieldid=" . $field['id'] . "&amp;val='+document.getElementById('field$divID').value, " .
									"'formBuilderErrorSpace$divID')\" >" . $field['value'] . "</textarea></div>";
						break;

						case "password box":
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
							$formInput = "<div class='formBuilderInput'><input type='password' name='" . $field['name'] . "' value='" . $field['value'] . "' id='field$divID' onblur=\"fb_ajaxRequest('" . $page_path . "php/formbuilder_parser.php', 'formid=" . $form['id'] . "&amp;fieldid=" . $field['id'] . "&amp;val='+document.getElementById('field$divID').value, 'formBuilderErrorSpace$divID')\" /> $formHelpJava</div>";
						break;

						case "required password":
							$formLabel = "<div class='formBuilderLabelRequired'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
							$formInput = "<div class='formBuilderInput'><input type='password' name='" . $field['name'] . "' value='' id='field$divID' /> $formHelpJava</div>";
						break;

						case "checkbox":
							if(isset($_POST['formBuilderForm'][$field['field_name']]) AND htmlentities(stripslashes($_POST['formBuilderForm'][$field['field_name']]), ENT_NOQUOTES, get_option('blog_charset')) == $field['field_value']) $selected = "checked";
								else $selected = "";
							$formLabel = "<div class='$formLabelCSS'><label for='field$divID'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </label></div>";
							$formInput = "<div class='formBuilderInput'><input type='checkbox' name='" . $field['name'] . "' id='field$divID' value='" . $field['field_value'] . "' $selected /> <span class='formBuilderCheckboxDescription'>";

							if($field['field_value'] != "checked") 
							{
								$formInput .= "<label for='field$divID'>"
								 . decode_html_entities($field['field_value'], ENT_NOQUOTES, get_option('blog_charset'))
								 . "</label>";
							}

							$formInput .= "</span> $formHelpJava</div>";
						break;

						case "required checkbox":
							if(isset($_POST['formBuilderForm'][$field['field_name']]) AND htmlentities(stripslashes($_POST['formBuilderForm'][$field['field_name']]), ENT_NOQUOTES, get_option('blog_charset')) == $field['field_value']) $selected = "checked";
								else $selected = "";
							$formLabel = "<div class='formBuilderLabelRequired'><label for='field$divID'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </label></div>";
							$formInput = "<div class='formBuilderInput'><input type='checkbox' name='" . $field['name'] . "' id='field$divID' value='" . $field['field_value'] . "' $selected /> <span class='formBuilderCheckboxDescription'>";

							if($field['field_value'] != "checked") 
							{
								$formInput .= "<label for='field$divID'>"
								 . decode_html_entities($field['field_value'], ENT_NOQUOTES, get_option('blog_charset'))
								 . "</label>";
							}

							$formInput .= "</span> $formHelpJava</div>";
						break;

						case "radio buttons":
							$options = explode("\n", $field['field_value']);
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " $formHelpJava</div>";
							$formInput = "<div class='formBuilderInput'>";
							foreach($options as $option_value=>$roption)
							{
								// Check for a pipe, and if it exists, split the value into value, label.
								if(strpos($roption, "|")) 
									list($option_original_value, $option_label) = explode("|", $roption, 2);
								else 
									$option_label = $roption;

								$option_label = trim(stripslashes($option_label));
								$option_label = str_replace("<", "&lt;", $option_label);
								$option_label = str_replace(">", "&gt;", $option_label);

								if(isset($_POST['formBuilderForm'][$field['field_name']]) AND htmlentities(stripslashes($_POST['formBuilderForm'][$field['field_name']]), ENT_QUOTES, get_option('blog_charset')) == $option_value) $selected = "checked";
								else $selected = "";

								$formInput .= "<div class='formBuilderRadio'><label><input type='radio' name='" . $field['name'] . "' value='$option_value' $selected /> $option_label</label></div>";
							}
							$formInput .= "</div>";
						break;

						case "selection dropdown":
							$options = explode("\n", $field['field_value']);
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
							$formInput = "<div class='formBuilderSelect'>\n<select name='" . $field['name'] . "'>";
							foreach($options as $option_value=>$roption)
							{
								// Check for a pipe, and if it exists, split the value into value|label.
								if(strpos($roption, "|")) 
								{
									list($option_original_value, $option_label) = explode("|", $roption, 2);
								}
								else 
								{
									$option_label = $roption;
								}
								
								$option_label = trim(stripslashes($option_label));
								$option_label = str_replace("<", "&lt;", $option_label);
								$option_label = str_replace(">", "&gt;", $option_label);

								// Check to see if the posted data is the same as the value.
								if(isset($_POST['formBuilderForm'][$field['field_name']]) AND htmlentities(stripslashes($_POST['formBuilderForm'][$field['field_name']]), ENT_QUOTES, get_option('blog_charset')) == $option_value) 
									$selected = "selected = 'selected'";
								elseif($field['value'] == $option_value)  
									$selected = "selected = 'selected'";
								else 
									$selected = "";
								
								$formInput .= "\n<option value='$option_value' $selected>$option_label</option>";
							}
							$formInput .= "\n</select>\n $formHelpJava</div>";
						break;

						case "captcha field":
							if(function_exists('imagecreate')) {
								$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
								$formInput = "<div class='formBuilderInput'><div class='formBuilderCaptcha'>" .
										"<img src='" . FORMBUILDER_PLUGIN_URL . "captcha/display.php?" . SID . "' " .
											 "alt='" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . "' />" .
										"<br/><input type='text' name='" . $field['name'] . "' value=''/> $formHelpJava</div></div>";
							}
							else
							{
								$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
								$formInput = "<div class='formBuilderInput'>" . $formBuilderTextStrings['captcha_unavailable'] . "</div>";
							}
						break;

						case "spam blocker":
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
							$formInput = "<div class='formBuilderInput'><input type='text' name='" . $field['name'] . "' value=''/> $formHelpJava</div>";
							$divClass = get_option('formbuilder_spam_blocker');
						break;

						case "followup page":
							$formLabel = "";
							$formInput = "";
						break;
						
						case "recipient selection":
							$formLabelCSS = "formBuilderLabelRequired";
							$options = explode("\n", $field['field_value']);
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
							$formInput = "<div class='formBuilderSelect'>\n<select name='" . $field['name'] . "'>";
							foreach($options as $option_value=>$roption)
							{
								// Check for a pipe, and if it exists, split the value into value|label.
								if(strpos($roption, "|")) 
								{
									list($option_original_value, $option_label) = explode("|", $roption, 2);
								}
								else 
								{
									$option_label = $roption;
								}
								
								$option_label = trim(stripslashes($option_label));
								$option_label = str_replace("<", "&lt;", $option_label);
								$option_label = str_replace(">", "&gt;", $option_label);

								// Check to see if the posted data is the same as the value.
								if(isset($_POST['formBuilderForm'][$field['field_name']]) AND htmlentities(stripslashes($_POST['formBuilderForm'][$field['field_name']]), ENT_QUOTES, get_option('blog_charset')) == $option_value) 
									$selected = "selected = 'selected'";
								elseif($field['value'] == $option_value)  
									$selected = "selected = 'selected'";
								else 
									$selected = "";
								
								$formInput .= "\n<option value='$option_value' $selected>$option_label</option>";
							}
							$formInput .= "\n</select>\n $formHelpJava</div>";
						break;

						case "page break":
							$new_page = true;
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
							$formInput = "<div class='formBuilderPageBreak'>";
							
							$previous_page_insert = "";
							if($page_id > 1)
							$previous_page_insert = "<input type='button' name='formbuilder_page_break' value='" . $formBuilderTextStrings['previous'] . "' onclick=" . '"   fb_toggleLayer(\'formbuilder-' . $form_id . '-page-' . $page_id . '\');  fb_toggleLayer(\'formbuilder-' . $form_id . '-page-' . ($page_id - 1) . '\');  "' . " />";
							
							$formInput .= "$previous_page_insert <input type='button' name='formbuilder_page_break' value='" . $formBuilderTextStrings['next'] . "' onclick=" . '"  fb_toggleLayer(\'formbuilder-' . $form_id . '-page-' . $page_id . '\');  fb_toggleLayer(\'formbuilder-' . $form_id . '-page-' . ($page_id + 1) . '\');  "' . " />" .
									"</div>";

							$page_id++;
						break;

						case "reset button":
							$formLabel = "";
							$formInput = "<div class='formBuilderSubmit'>$previous_page_insert<input type='reset' name='" . $field['name'] . "' value='" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . "' /> $formHelpJava</div>";
						break;

						case "submit button":
							$formLabel = "";

							$previous_page_insert = "";
							if($page_id > 1)
							$previous_page_insert = "<input type='button' name='formbuilder_page_break' value='" . $formBuilderTextStrings['previous'] . "' onclick=" . '"   fb_toggleLayer(\'formbuilder-' . $form_id . '-page-' . $page_id . '\');  fb_toggleLayer(\'formbuilder-' . $form_id . '-page-' . ($page_id - 1) . '\');  "' . " />";
							
							$formInput = "<div class='formBuilderSubmit'>$previous_page_insert<input type='submit' name='" . $field['name'] . "' value='" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . "' /> $formHelpJava</div>";

							$submit_button_set = true;
						break;

						case "submit image":
							$formLabel = "";

							$previous_page_insert = "";
							if($page_id > 1)
							$previous_page_insert = "<input type='button' name='formbuilder_page_break' value='" . $formBuilderTextStrings['previous'] . "' onclick=" . '"   fb_toggleLayer(\'formbuilder-' . $form_id . '-page-' . $page_id . '\');  fb_toggleLayer(\'formbuilder-' . $form_id . '-page-' . ($page_id - 1) . '\');  "' . " /> $formHelpJava";
							
							$formInput = "<div class='formBuilderSubmit'>$previous_page_insert<input type='image' name='" . $field['name'] . "' src='" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . "' value='" . $field['value'] . "' alt='" . $field['value'] . "' /></div>";

							$submit_button_set = true;
						break;

						case "datestamp":
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
							if(isset($calendar_loaded) AND $calendar_loaded == true) 
							{
								$calendar_loading_code = "";
							}
							else
							{
								$calendar_loading_code = "<script src=\"" . $page_path . "js/calendar.js\" type=\"text/javascript\"></script>";
								$calendar_loaded = true;
							}
							$formInput = "<div class='formBuilderDateStamp'><input type='text' name='" . $field['name'] . "' value='" . $field['value'] . "' id='field$divID' />
								$calendar_loading_code
								<script type=\"text/javascript\">
								fb_calendar.set(\"field$divID\");
								</script> $formHelpJava
							</div>";
							
							break;

						case "unique id":
							$unique = uniqid();
							$formLabel = "";
							$formInput = "<div class='formBuilderHiddenField'><input type='hidden' name='" . $field['name'] . "' value='" . uniqid() . "' /></div>";
							$divClass = "formBuilderHidden";
						break;

						case "system field":
							$formLabel = "";
							$formInput = "";
						break;

						case "wp user id":
							$formLabel = "";
							$formInput = "";
						break;

						default:
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
							$formInput = "<div class='formBuilderInput'><input type='text' "
																			. "name='" . $field['name'] . "' "
																			. "value='" . $field['value'] . "' "
																			. "id='field$divID' "
																			. "onblur=\"fb_ajaxRequest('" . $page_path . "php/formbuilder_parser.php', 'formid=" . $form['id'] . "&amp;fieldid=" . $field['id'] . "&amp;val='+document.getElementById('field$divID').value, 'formBuilderErrorSpace$divID')\"/> $formHelpJava</div>";
						break;
					}
					
					if($field['field_type'] != 'system field' && $field['field_type'] != 'wp user id')
					{
						$formDisplay .= "\n<div class='$divClass' id='$divID' title='" . $field['error_message'] . "' $visibility><a name='$divID'></a>";

						if(isset($_POST['formBuilderForm']['FormBuilderID']) AND $_POST['formBuilderForm']['FormBuilderID'] == $form_id) 
							$formDisplay .= "\n<span id='formBuilderErrorSpace$divID'>$formError</span>";
						elseif(!isset($_GET['supress_errors']) AND !isset($_GET['suppress_errors'])) 
							$formDisplay .= "\n<span id='formBuilderErrorSpace$divID'>$formError</span>";
	
						$formDisplay .= "\n$formLabel";
						$formDisplay .= "\n$formInput";
						$formDisplay .= "\n</div>";
					}
					
					// Check for new page of form details.
					if($new_page == true)
					{
						$formDisplay .= "</div><div id='formbuilder-{$form_id}-page-$page_id' title='formbuilder-{$form_id}-page-$page_id' style='display:none;'>";
					}
					$new_page = false;

					$allFields[] = $field;
				}
			}
			
			
			
			
			
			$referrer_info = get_option('formBuilder_referrer_info');
			if($referrer_info == 'Enabled')
			{
				// Hidden fields to include referer, and page uri
				if(isset($_SERVER['HTTP_REFERER'])) $formDisplay .= "<input type='hidden' name='REFERER' value='" . $_SERVER['HTTP_REFERER'] . "' />";
				if(isset($_SERVER['HTTP_HOST']) AND isset($_SERVER['REQUEST_URI'])) $formDisplay .= "<input type='hidden' name='PAGE' value='http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "' />";
			}
			
			// Submit Button
			if(!$submit_button_set) {
				$previous_page_insert = "";
				if($page_id > 1)
				$previous_page_insert = "<input type='button' name='formbuilder_page_break' " .
					"value='" . $formBuilderTextStrings['previous'] . "' " .
					"onclick=" . '"   fb_toggleLayer(\'formbuilder-' . $form_id . '-page-' . $page_id . '\');  ' .
					'  fb_toggleLayer(\'formbuilder-' . $form_id . '-page-' . ($page_id - 1) . '\');  "' .	" />";
	
				$formDisplay .= "\n<div class='formBuilderSubmit'>$previous_page_insert<input type='submit' name='Submit' value='" . $formBuilderTextStrings['send'] . "' /></div>";
			}
			else
				$formDisplay .= "";

			$formDisplay .= "\n</div>\n</form>";	// End of paged CSS
			
			
			// Check posted form data to ensure that we don't have any blacklisted material
			$blacklist_enabled = get_option('formbuilder_blacklist');
			if($blacklist_enabled == "Enabled")
			{
				if(isset($_POST['formBuilderForm']['FormBuilderID'])) 
				{
					foreach($_POST['formBuilderForm'] as $key=>$value)
					{
						if(formbuilder_on_blacklist($value))
						{
							$post_errors = "There is a word in your form submission that the owners of this site deem to be probable spam.";
							break;
						}
					}
				}
			}
			
			// Check posted form data to ensure that we don't have any greylisted material
			$greylist_enabled = get_option('formbuilder_greylist');
			if($greylist_enabled == "Enabled")
			{
				if(isset($_POST['formBuilderForm']['FormBuilderID'])) 
				{
					foreach($_POST['formBuilderForm'] as $key=>$value)
					{
						if(formbuilder_on_greylist($value))
						{
							$form['subject'] = "POSSIBLE SPAM: " . $form['subject'];
							break;
						}
					}
				}
			}
			
			// Check posted form data to ensure that we don't have any greylisted material
			$excessive_links_enabled = get_option('formbuilder_excessive_links');
			if($excessive_links_enabled == "Enabled")
			{
				if(isset($_POST['formBuilderForm']['FormBuilderID'])) 
				{
					foreach($_POST['formBuilderForm'] as $key=>$value)
					{
						if(formbuilder_excessive_links($value))
						{
							$form['subject'] = "POSSIBLE SPAM: " . $form['subject'];
							break;
						}
					}
				}
			}
			
			// Check posted form data to ensure that we don't have any greylisted material
			$formbuilder_spammer_ip_checking = get_option('formbuilder_spammer_ip_checking');
			if($formbuilder_spammer_ip_checking == "Enabled")
			{
				if(isset($_POST['formBuilderForm']['FormBuilderID'])) 
				{
					$response = formbuilder_check_spammer_ip($_SERVER['REMOTE_ADDR']);
					if($response > 0)
					{
						$form['subject'] = "POSSIBLE SPAMMER IP: " . $form['subject'];
					}
				}
			}
			
			// Check posted form data for Akismet Spam
			$akismet_enabled = get_option('formbuilder_akismet');
			if($akismet_enabled == "Enabled" AND function_exists('akismet_http_post'))
			{
				if(isset($_POST['formBuilderForm']['FormBuilderID'])) 
				{
					
					if(formbuilder_check_akismet($allFields) == 'true')
					{
						$form['subject'] = "POSSIBLE AKISMET SPAM: " . $form['subject'];
					}

				}
			}
			
			// Process Form Results if necessary
			if(!isset($post_errors) 
			&& isset($_POST['formBuilderForm']['FormBuilderID']) 
			&& $_POST['formBuilderForm']['FormBuilderID'] == $form_id)
			{
			
			
			
				// Convert numeric selection values to the real form values
				// Iterate through the form fields to add values to the email sent to the recipient.
				foreach($allFields as $key=>$field)
				{
					// If select box or radio buttons, we need to translate the posted value into the real value.
					if(
						$field['field_type'] == "recipient selection" OR
						$field['field_type'] == "selection dropdown" OR
						$field['field_type'] == "radio buttons"
						)
					{
						$options = explode("\n", $field['field_value']);
						$roption = $options[$field['value']];
						// Check for a pipe, and if it exists, split the value into value|label.
						if(strpos($roption, "|")) 
						{
							list($option_value, $option_label) = explode("|", $roption, 2);
						}
						else 
						{
							$option_value = $option_label = $roption;
						}
						
						$allFields[$key]['value'] = trim($option_value);
					}
				}
				
					
				
				
				$msg = "";
				// If enabled, put backup copies of the form data into a database.
				if(get_option('formbuilder_db_xml') != '0')
				{
					$msg = formbuilder_process_db($form, $allFields);
				}
				
				// Check if an alternate form processing system is used.
				// Otherwise just use the default which sends an email to the recipiant.
				if($form['action'] != "") {
						if(function_exists("$processor_funcname"))
						{
							$msg = $processor_funcname($form, $allFields);
							$func_run = true;
						}
						else
							$msg = formbuilder_process_email($form, $allFields);
				}
				else
					$msg = formbuilder_process_email($form, $allFields);
					
				// Check for and process any redirections at this point.
				formbuilder_check_redirection($form, $allFields);

				if(!isset($func_run))
				{
					if(!$msg)
					{
						if(!$form['thankyoutext']) $form['thankyoutext'] = "<h4>" 
							. $formBuilderTextStrings['success'] 
							. "</h4><p>" 
							. $formBuilderTextStrings['send_success'] 
							. "</p>";
						
						// Populate ~variable~ tags in the autoresponse with values submitted by the user.
						$txtAllFields = ""; 
						foreach($allFields as $field)
						{
							if(
								trim($field['field_name']) != "" AND
								$field['field_type'] != "recipient selection" AND
								$field['field_type'] != "comments area" AND
								$field['field_type'] != "followup page" AND
								$field['field_type'] != "spam blocker" AND
								$field['field_type'] != "page break" AND
								$field['field_type'] != "reset button" AND
								$field['field_type'] != "submit button" AND
								$field['field_type'] != "submit image" AND
								$field['field_type'] != "captcha field"
								)
							{
								$key = $field['field_name'];
								$value = $field['value'];
								
								$form['thankyoutext'] = str_replace("~" . $key . "~", $value, $form['thankyoutext']);
								$txtAllFields .= $key . ": " . $value . "\n";
							}
						}
						$form['thankyoutext'] = str_replace("~FullForm~", nl2br(trim($txtAllFields)), $form['thankyoutext']);
									
						$formDisplay = "\n<div class='formBuilderSuccess'>" 
							. decode_html_entities($form['thankyoutext'], ENT_QUOTES, get_option('blog_charset')) 
							. "</div>";
					}
					else
						$formDisplay = "\n<div class='formBuilderFailure'><h4>" . $formBuilderTextStrings['failed'] . "</h4><p>" . $formBuilderTextStrings['send_failed'] . "<br/>$msg</p></div>";
				}
				elseif($msg)
					$formDisplay = "\n<div class='formBuilderFailure'><h4>" . $formBuilderTextStrings['failed'] . "</h4><p>$msg</p></div>$formDisplay";
				else
					$formDisplay = $msg;
			}
			else
			{
				if(isset($post_errors) AND isset($missing_post_fields) AND $post_errors AND $missing_post_fields)
				{
					$msg = "\n<div class='formBuilderFailure'><h4>" . $formBuilderTextStrings['form_problem'] . "</h4><p>" . $formBuilderTextStrings['send_mistakes'] . "</p>";
					$msg .= "\n<ul>";
					foreach($missing_post_fields as $idValue=>$field_label) {
						$msg .= "\n<li><a href='#$idValue'>$field_label</a></li>";
					}
					$msg .= "\n</ul></div>\n" . $formDisplay;

					$formDisplay = $msg;
				}
				elseif(isset($post_errors) AND is_string($post_errors))
				{
					$msg = "\n<div class='formBuilderFailure'><h4>" . $formBuilderTextStrings['form_problem'] . "</h4>";
					$msg .= "\n<p>$post_errors</p></div>\n" . $formDisplay;

					$formDisplay = $msg;
				}
			}

			return("<div id='$formCSSID'>$formDisplay</div>");

		}
		else
			return($formBuilderTextStrings['display_error']);
	}
	

	/**
	 * Process form redirections if necessary.
	 * @param unknown_type $form
	 * @param unknown_type $fields
	 */
	function formbuilder_check_redirection($form, $fields)
	{
		// Iterate through the form fields to add values to the email sent to the recipient.
		foreach($fields as $field)
		{
			// Add the followup page redirect, if it exists.
			if($field['field_type'] == "followup page" AND trim($field['field_value']) != "")
			{
				//echo "<meta HTTP-EQUIV='REFRESH' content='0; url=" . $field['field_value'] . "'>";
				header("Location: " . $field['field_value']);
			}

		}
	}




	// Function to validate submitted form fields against the required regex.
	function formbuilder_validate_field($field)
	{
		static $last_email_address;
		$post_errors = false;
		
		if($field['field_type'] == 'selection dropdown' 
			OR $field['field_type'] == 'recipient selection' 
			OR $field['field_type'] == 'radio buttons'
		)
		{
			$options = explode("\n", $field['field_value']);
			$roption = trim($options[$field['value']])	;
			
			if(strpos($roption, "|")) 
			{
				list($option_value, $option_label) = explode("|", $roption, 2);
			}
			else 
			{
				$option_label = $option_value = $roption;
			}
			
			$field['value'] = trim($option_value);
		}
		
		if($field['field_type'] == 'required checkbox')
		{
			$field['required_data'] = 'any text';
		}

		switch($field['required_data']) 
		{
			case "name":
			case "any text":
				$pattern = ".+";
			break;
	
			case "email address":
				$pattern = FORMBUILDER_PATTERN_EMAIL;
				if(eregi($pattern, $field['value']))
				{
					$last_email_address = $field['value'];
					$_SESSION['formbuilder']['last_email_address'] = $last_email_address;
				}
			break;
	
			case "confirm email":
				$pattern = FORMBUILDER_PATTERN_EMAIL;
				if(isset($_SESSION['formbuilder']['last_email_address'])) $last_email_address = $_SESSION['formbuilder']['last_email_address']; 
				if($field['value'] != $last_email_address)
				{
					$post_errors = true;
				}
			break;
	
			case "any number":
				$pattern = "^[0-9\.-]+$";
			break;
			
			case "phone number":
				$pattern = FORMBUILDER_PATTERN_PHONE;
			break;
	
			case "valid url":
				$pattern = '^\s*(http|https|ftp)://([^:/]+)\.([^:/\.]{2,7})(:\d+)?(/?[^\#\s]+)?(\#(\S*))?\s*$';
			break;
			
			case "single word":
				$pattern = "^\s*[0-9a-z\-]+\s*$";
			break;
			
			case "datestamp (dd/mm/yyyy)":
				$pattern = "^([0-9]{2}/[0-9]{2}/[0-9]{4})|([0-9]{4}\-[0-9]{2}\-[0-9]{2})$";
			break;
			
			case "credit card number":
				$pattern = "^.*$";
				require_once(FORMBUILDER_PLUGIN_PATH . "php/phpcreditcard.php");
				$errornum = false;
				$errortext= false;
				$post_errors = !(formbuilder_checkCreditCard($field['value'], '', $errornum, $errortext));
			break;
			
			default:
				$pattern = ".*";
			break;
		}
	
		if(!preg_match("#" . $pattern . "#isu", $field['value']))
		{
			$post_errors = true;
		}
		
		
		return(!$post_errors);
	}



	// This function will take the submitted form fields and store than in a database blob in XML format.
	function formbuilder_process_db($form, $fields)
	{
		global $_POST;
		
		$formBuilderTextStrings = formbuilder_load_strings();
		
		$xml_container = "form";
		
		$xml = '<?xml version="1.0" encoding="' . get_option('blog_charset') . '" ?>';
		$xml .= "\r\n<$xml_container>";

		$xml .= "\r\n<FormSubject>" . decode_html_entities($form['subject'], ENT_QUOTES, get_option('blog_charset')) . "</FormSubject>";
		$xml .= "\r\n<FormRecipient>" . $form['recipient'] . "</FormRecipient>";

		// Iterate through the form fields to add values to the email sent to the recipient.
		foreach($fields as $field)
		{
			// Add the comments to the email message, if they are appropriate.
			if(
				trim($field['field_name']) != "" AND
				$field['field_type'] != "comments area" AND
				$field['field_type'] != "followup page" AND
				$field['field_type'] != "spam blocker" AND
				$field['field_type'] != "page break" AND
				$field['field_type'] != "reset button" AND
				$field['field_type'] != "submit button" AND
				$field['field_type'] != "submit image" AND
				$field['field_type'] != "captcha field"
				)
			{
				$xml .= "\r\n<" . $field['field_name'] . ">" . $field['value'] . "</" .	$field['field_name'] . ">";
			}

		}

		// Add IP if enabled.
		$ip_capture = get_option('formBuilder_IP_Capture');
		if($ip_capture == 'Enabled' AND isset($_SERVER['REMOTE_ADDR'])) $xml .= "\r\n<IP>" . $_SERVER['REMOTE_ADDR'] . "</IP>";

		$referrer_info = get_option('formBuilder_referrer_info');
		if($referrer_info == 'Enabled')
		{
			// Add Page and Referer urls to the bottom of the email.
			if(isset($_POST['PAGE'])) $xml .= "\r\n<Page>" . $_POST['PAGE'] . "</Page>";
			if(isset($_POST['REFERER'])) $xml .= "\r\n<Referrer>" . $_POST['REFERER'] . "</Referrer>";
		}

		$xml .= "\r\n</$xml_container>";
		
		global $wpdb;
		
		$insertData = array();
		$insertData['form_id'] = $form['id'];
		$insertData['timestamp'] = time();
		$insertData['xmldata'] = addslashes($xml);
		
		$result = $wpdb->insert(FORMBUILDER_TABLE_RESULTS, $insertData);
		
		if($result === false) 
			return($formBuilderTextStrings['storage_error']);
	}


	// The function that takes the post results and turns them into an email.
	function formbuilder_process_email($form, $fields)
	{
		global $_POST, $wpdb;

		$formBuilderTextStrings = formbuilder_load_strings();
		

		$email_msg = "";
		$autoresponse_required = false;
		$source_email = "";

		// Iterate through the form fields to add values to the email sent to the recipient.
		foreach($fields as $field)
		{
			// Add the comments to the email message, if they are appropriate.
			if(
				trim($field['field_name']) != "" AND
				$field['field_type'] != "recipient selection" AND
				$field['field_type'] != "comments area" AND
				$field['field_type'] != "followup page" AND
				$field['field_type'] != "spam blocker" AND
				$field['field_type'] != "page break" AND
				$field['field_type'] != "reset button" AND
				$field['field_type'] != "submit button" AND
				$field['field_type'] != "submit image" AND
				$field['field_type'] != "captcha field"
				)
			{
				$email_msg .= strtoupper(decode_html_entities($field['field_name'], ENT_QUOTES, get_option('blog_charset'))) . ": " . decode_html_entities($field['value'], ENT_QUOTES, get_option('blog_charset')) . "\r\n\r\n";
				$field_values[$field['field_name']] = decode_html_entities($field['value'], ENT_QUOTES, get_option('blog_charset'));
			}
			elseif($field['field_type'] == "recipient selection")
			{
				// If we have a recipient selection field, change the form recipient to the selected value.
				if( eregi(FORMBUILDER_PATTERN_EMAIL, trim($field['value'])) )
				{
					$form['recipient'] = trim($field['value']);
				}
				else
					$email_msg .= $formBuilderTextStrings['bad_alternate_email'] . " [" . trim($field['value']) . "]\n\n";
			}

			// Get source email address, if exists.  Will use the first email address listed in the form results, as the source email address.
			if($field['required_data'] == "email address" AND !$source_email)
			{
				$source_email = $field['value'];
			}

		}

		// Add IP if enabled.
		$ip_capture = get_option('formBuilder_IP_Capture');
		if($ip_capture == 'Enabled' AND isset($_SERVER['REMOTE_ADDR'])) $email_msg .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\r\n";

		$referrer_info = get_option('formBuilder_referrer_info');
		if($referrer_info == 'Enabled')
		{
			// Add Page and Referer urls to the bottom of the email.
			if(isset($_POST['PAGE'])) $email_msg .= "PAGE: " . $_POST['PAGE'] . "\r\n";
			if(isset($_POST['REFERER'])) $email_msg .= "REFERER: " . $_POST['REFERER'] . "\r\n";
		}


		// Set autoresponse information if required and send it out.
		if($source_email AND $form['autoresponse'] != false AND $autoresponse_required == false)
		{
			$sql = "SELECT * FROM " . FORMBUILDER_TABLE_RESPONSES . " WHERE id='" . $form['autoresponse'] . "';";
			$results = $wpdb->get_results($sql, ARRAY_A);
			$response_details = $results[0];

			$response_details['destination_email'] = $source_email;

			if($response_details['from_email'] AND $response_details['subject'] AND $response_details['message'] AND $response_details['destination_email'])
			{
				if($response_details['from_name']) 
					$response_details['from_email'] = '"' . $response_details['from_name'] . '"<' . $response_details['from_email'] . '>';
			}
			
			// Populate ~variable~ tags in the autoresponse with values submitted by the user.
			$txtAllFields = ""; 
			foreach($field_values as $key=>$value)
			{
				$response_details['subject'] = str_replace("~" . $key . "~", $value, $response_details['subject']);
				$response_details['message'] = str_replace("~" . $key . "~", $value, $response_details['message']);
				$txtAllFields .= $key . ": " . $value . "\n";
			}
			$response_details['subject'] = str_replace("~FullForm~", trim($txtAllFields), $response_details['subject']);
			$response_details['message'] = str_replace("~FullForm~", trim($txtAllFields), $response_details['message']);
			
			$result = formbuilder_send_email($response_details['destination_email'], 
				decode_html_entities($response_details['subject'], ENT_QUOTES, get_option('blog_charset')), 
				$response_details['message'], 
				"From: " . $response_details['from_email'] . "\nReply-To: " . $response_details['from_email'] . "\n");
			if($result) die($result);
		}

		if(!$source_email) $source_email = get_option('admin_email');
		return(formbuilder_send_email(
			$form['recipient'], 
			decode_html_entities($form['subject'], ENT_QUOTES, get_option('blog_charset')), 
			$email_msg, 
			"From: " . $source_email . "\nReply-To: " . $source_email . "\n"));

	}

	// Function to send an email
	function formbuilder_send_email($to, $subject, $message, $headers="")
	{
		$formBuilderTextStrings = formbuilder_load_strings();
		
		// Check to and subject for header injections
		$badStrings = array("Content-Type:",
		                     "MIME-Version:",
		                     "Content-Transfer-Encoding:",
		                     "bcc:",
		                     "cc:");
		foreach($badStrings as $v2){
		    if(strpos(strtolower($to), strtolower($v2)) !== false){
		        $error = $formBuilderTextStrings['hack_to'];
		    }
		    if(strpos(strtolower($subject), strtolower($v2)) !== false){
		        $error = $formBuilderTextStrings['hack_subject'];
		    }
		}

		// If no errors are detected, send the message and return the response of the mail command.
		if(!isset($error)) {
			$headers = trim(trim($headers) . "\nContent-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n");
			
			if(get_option('formbuilder_alternate_email_handling') != 'Enabled')
			{
				if(mail($to, $subject, $message, $headers))
					return(false);
				else
					return($formBuilderTextStrings['mail_error_default']);
			}
			else
			{
				if(wp_mail($to, $subject, $message, ''))
					return(false);
				else
					return($formBuilderTextStrings['mail_error_alternate']);
			}
			
		}
		else
		{
			return($error);
		}
	}
