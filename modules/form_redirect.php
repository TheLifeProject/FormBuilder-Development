<?php

/*

Name: Form Redirect - Redirect visitor to alternate page on submit.  (no email)
Instructions: If you are using the Form Redirect module, create a 'followup page' type form field, and put the url you wish to redirect people to in the field value.


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


	function formbuilder_startup_form_redirect($form)
	{
		$random_hash = "<input type='hidden' name='formBuilderForm[randomizer]' value='" . uniqid("", time()) . "' />";
		return($random_hash);
	}


	function formbuilder_process_form_redirect($form, $fields)
	{
		global $_POST, $wpdb;
		$autoresponse_required = false;
		$source_email = "";
		
		foreach($fields as $field)
		{
			// Get source email address, if exists.  Will use the first email address listed in the form results, as the source email address.
			if($field['required_data'] == "email address" AND !$source_email)
			{
				$source_email = $field['value'];
			}
		}
		
		// Set autoresponse information if required and send it out.
		if($source_email AND $form['autoresponse'] != false AND $autoresponse_required == false)
		{
			$sql = "SELECT * FROM " . FORMBUILDER_TABLE_RESPONSES . " WHERE id = '" . $form['autoresponse'] . "';";
			$results = $wpdb->get_results($sql, ARRAY_A);
			$response_details = $results[0];

			$response_details['destination_email'] = $source_email;

			if($response_details['from_email'] AND $response_details['subject'] AND $response_details['message'] AND $response_details['destination_email'])
			{
				if($response_details['from_name']) $response_details['from_email'] = "\"" . $response_details['from_name'] . "\"<" . $response_details['from_email'] . ">";
			}
			$result = formbuilder_send_email($response_details['destination_email'], decode_html_entities($response_details['subject'], ENT_QUOTES, get_option('blog_charset')), $response_details['message'], "From: " . $response_details['from_email']);
			if($result) die($result);
		}

		foreach($fields as $field)
		{
			if($field['field_type'] == "followup page") {
				echo "<meta HTTP-EQUIV='REFRESH' content='0; url=" . $field['field_value'] . "'>";
				break;
			}
		}

		return(false);
	}

?>
