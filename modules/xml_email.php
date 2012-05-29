<?php

/*

Name: XML Email - Send email formatted in XML

 */

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


	function formbuilder_startup_xml_email($form)
	{
		return(true);
	}


	function formbuilder_process_xml_email($form, $fields)
	{
		global $_POST, $wpdb, $formBuilderTextStrings;

		$xml_container = "form";

		$email_msg = "<$xml_container>\r\n";
		$autoresponse_required = false;
		$source_email = "";

		// Iterate through the form fields to add values to the email sent to the recipient.
		foreach($fields as $field)
		{
			// Add the comments to the email message, if they are appropriate.
			if(
				$field['field_type'] != "comments area" AND
				$field['field_type'] != "followup page" AND
				$field['field_type'] != "spam blocker" AND
				$field['field_type'] != "page break" AND
				$field['field_type'] != "submit button" AND
				$field['field_type'] != "submit image" AND
				$field['field_type'] != "captcha field"
				)
			{
				$email_msg .= "<" . decode_html_entities($field['field_name'], ENT_QUOTES, get_option('blog_charset')) . ">" . decode_html_entities($field['value'], ENT_QUOTES, get_option('blog_charset')) . "</" . decode_html_entities($field['field_name'], ENT_QUOTES, get_option('blog_charset')) . ">\r\n";
			}

			// Get source email address, if exists.  Will use the first email address listed in the form results, as the source email address.
			if($field['required_data'] == "email address" AND !$source_email)
			{
				$source_email = $field['value'];
			}

			// Add IP if enabled.
			$ip_capture = get_option('formBuilder_IP_Capture');
			if($ip_capture == 'Enabled' AND isset($_SERVER['REMOTE_ADDR'])) $email_msg .= "<ip>" . $_SERVER['REMOTE_ADDR'] . "</ip>\r\n";
	
			// Add Page and Referer urls to the bottom of the email.
			if(isset($_POST['PAGE'])) $email_msg .= "<page>" . $_POST['PAGE'] . "</page>\r\n";
			if(isset($_POST['REFERER'])) $email_msg .= "<referer>" . $_POST['REFERER'] . "</referer>\r\n";
	
	
		}

		$email_msg .= "</$xml_container>";

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

		if(!$source_email) $source_email = get_option('admin_email');
		$sendResult = formbuilder_send_email($form['recipient'], $form['subject'], $email_msg, "From: " . $source_email . "\n");
		
		if(!$sendResult)
		{
			if(!$form['thankyoutext']) $form['thankyoutext'] = "<h4>" . $formBuilderTextStrings['success'] . "</h4><p>" . $formBuilderTextStrings['send_success'] . "</p>";
			echo "\n<div class='formBuilderSuccess'>" . decode_html_entities($form['thankyoutext'], ENT_NOQUOTES, get_option('blog_charset')) . "</div>";
		}
		
		return($sendResult);
	}

?>
