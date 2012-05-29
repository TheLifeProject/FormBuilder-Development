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

 	function formbuilder_options_newResponse()
 	{
		global $wpdb;

		if(!formbuilder_user_can('create'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
		$data['name'] = __("New AutoResponse", 'formbuilder');
		$data['subject'] = __("This is a new, empty autoresponse that you can now modify to suit your needs.", 'formbuilder');
		$data['from_email'] = get_option('admin_email');
		$data['message'] = '';

		$wpdb->insert(FORMBUILDER_TABLE_RESPONSES, $data);

		formbuilder_options_default();
 	}

 	function formbuilder_options_editResponse($response_id)
 	{
 		global $wpdb, $formbuilder_admin_nav_options;

 		if(!formbuilder_user_can('create'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
 		// Make a connection to the database.
		$message = "";

		if(isset($_POST['formbuilder']) AND is_array($_POST['formbuilder']))
		{
			$_POST['formbuilder'] = formbuilder_array_stripslashes($_POST['formbuilder']);

			// Verify the data that was posted.
			$data = $_POST['formbuilder'];
			
			if(!preg_match('/^.+$/isu', $data['name']))
				$message = "\n " . __("You must enter a name for this autoresponse.", 'formbuilder');

			if(!preg_match('/^.+$/isu', $data['subject']))
				$message = "\n " . __("You must enter a subject for this autoresponse.", 'formbuilder');

			if(!preg_match('/^.+$/isu', $data['message']))
				$message = "\n " . __("You must enter a message for this autoresponse.", 'formbuilder');

			if(!preg_match('/^.+$/isu', $data['from_name']))
				$message = "\n " . __("You must enter a from name for this autoresponse.", 'formbuilder');

			if(!preg_match('/^.+$/isu', $data['from_email']))
				$message = "\n " . __("You must enter a from email address for this autoresponse.", 'formbuilder');

			// Check to ensure that we can save the form data.  List an error message if not.
			if($_POST['Save'] AND !$message)
			{
				if(!$wpdb->update(FORMBUILDER_TABLE_RESPONSES, $_POST['formbuilder'], array('id'=>$response_id))) 
					$message = __("ERROR.  Your response failed to save.", 'formbuilder');
				else
					$message = sprintf(__("Your autoresponse has been saved.  %sYou may click here to return to the main FormBuilder options page.%s", 'formbuilder'), "<a href='" . FB_ADMIN_PLUGIN_PATH . "'>", "</a>");
			}

		}

		if($message) echo "<div class='updated'><p><strong>$message</strong></p></div>"; 

		$result = $wpdb->get_results("SELECT * FROM " . FORMBUILDER_TABLE_RESPONSES . " WHERE id = '" . $response_id . "';", ARRAY_A);
		$response_fields = $result[0];
		
		foreach($response_fields as $key=>$value)
		{
			$field = array();
			
			$field['Field'] = $key;
			
			if(!isset($data[$key]) OR !$data[$key])
				$field['Value'] = $value;
			else
				$field['Value'] = $data[$key];
				
			
			// Add a brief explanation to specific fields of how to enter the data.
			if($field['Field'] == "name") {
				$field['Title'] = __("What do you want to call this autoresponse?", 'formbuilder');
				$field['HelpText'] = __("What do you want to call this autoresponse?  This will appear on the main FormContact Management page, as well as in a dropdown list of related autoresponses when editing the forms.", 'formbuilder');
				$field['Type'] = "varchar(255)";
			}
	
			if($field['Field'] == "subject") {
				$field['Title'] = __("What do you want the subject line of this autoresponse email to be?", 'formbuilder');
				$field['HelpText'] = __("What do you want the subject line of this autoresponse email to be?", 'formbuilder');
				$field['Type'] = "varchar(255)";
			}
	
			if($field['Field'] == "message") {
				$field['Title'] = __("What should the autoresponse email say?", 'formbuilder');
				$field['HelpText'] = __("What should the autoresponse email say?", 'formbuilder');
				$field['Type'] = "text";
			}
	
			if($field['Field'] == "from_email") {
				$field['Title'] = __("What email address should the data from this contact form be mailed from?", 'formbuilder');
				$field['HelpText'] = __("What email address should the data from this contact form be mailed from?", 'formbuilder');
				$field['Type'] = "varchar(255)";
			}
	
			if($field['Field'] == "from_name") {
				$field['Title'] = __("What name should the data from this contact form be mailed from?", 'formbuilder');
				$field['HelpText'] = __("What name should the data from this contact form be mailed from?", 'formbuilder');
				$field['Type'] = "varchar(255)";
			}
			
			$fields[$key] = $field;
	
		}

		include(FORMBUILDER_PLUGIN_PATH . "html/options_edit_response.inc.php");
 	}

 	function formbuilder_options_copyResponse($response_id)
 	{
 		global $wpdb;
 		
		if(!formbuilder_user_can('create'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
 		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_RESPONSES . " WHERE id = '" . $response_id . "';";
 		$results = $wpdb->get_results($sql, ARRAY_A);
 		$fields = $results[0];

		unset($fields['id']);
		$fields['name'] .= __(" (COPY)", 'formbuilder');
		
		$wpdb->insert(FORMBUILDER_TABLE_RESPONSES, $fields);

		formbuilder_options_default();
 	}

	function formbuilder_options_removeResponse($response_id)
	{
		global $wpdb;
		if(!formbuilder_user_can('create'))
		{
			formbuilder_admin_alert('You do not have permission to access this area.');
			return;
		}
		
		$wpdb->query("DELETE FROM " . FORMBUILDER_TABLE_RESPONSES . " WHERE id = '" . $response_id . "';");
	}

?>
