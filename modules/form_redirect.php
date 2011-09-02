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
		global $_POST;

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
