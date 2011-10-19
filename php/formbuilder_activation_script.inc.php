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


	/**
	 * Main FormBuilder Activation Function
	 * 
	 * This script should run each time that the formbuilder is activated.
	 * The upgrades should run from lowest to highest, each one upgrading
	 * the one before until the current version is reached.
	 */
	function formbuilder_activation() {
		
		global $wpdb;
		$charset_collate = formbuilder_getCharSet();
		
		// Run this in the event that no previous or current version of formBuilder is installed.
		if(get_option('formbuilder_version'))
		{
			// FormBuilder must already be installed, make sure to upgrade previous versions.

			// Upgrade to version 0.11
			if(get_option('formbuilder_version') < "0.11")
			{
				// Changes:
					// Installed spam blocker
					// Installed css enabled form fields.

				// Set the spam blocker default variable
				update_option('formbuilder_spam_blocker', "formBuilderCap");

				// Set the version number
				update_option('formbuilder_version', "0.11");

			}

			// Upgrade to version 0.12
			if(get_option('formbuilder_version') < "0.12")
			{
				// Requested Features and Bugs to Fix:
					// Alternate css for required fields
					// "Save Form" button at top as well
					// Comments with no input box field
					// Hidden Fields need to be installed
					// Email confirm field

				// Changes:
					// Added alternate css for required fields.
					// Added second "Save Form" button at top of fields.
					// Upgraded database to use new field types
					// Installed hidden fields
					// Installed comment fields
					// Fixed bugs in the email creation functions to not send spam blocker or comment results
					// Installed email confirm fields


			}

			// Upgrade to version 0.122
			if(get_option('formbuilder_version') < "0.122")
			{
				// Requested Features and Bugs to Fix:
					// Bug in upgrade sql for 0.12

				// Changes:
					// Fixed bug in upgrade sql

				$sql = 'ALTER TABLE `' . FORMBUILDER_TABLE_FIELDS . '` CHANGE `field_type` `field_type` ENUM(\'single line text box\',\'small text area\',\'large text area\',\'password box\',\'checkbox\',\'radio buttons\',\'selection dropdown\',\'hidden field\',\'comments area\',\'spam blocker\'), CHANGE `required_data` `required_data` ENUM(\'none\',\'any text\',\'email address\',\'confirm email\')';
				if($wpdb->query($sql) !== false)
				{
					echo "Upgraded FormBuilder to 0.122<br/>";
					update_option('formbuilder_version', "0.122");
				}
				else
					echo "Error: Unable to upgrade formbuilder plugin.";

			}

			// Upgrade to version 0.13
			if(get_option('formbuilder_version') < "0.13")
			{
				// Requested Features and Bugs to Fix:
					// Bug in form comments.  They stop displaying after an error on the form.
					// Specific id's for each input area

				// Changes:
					// Fixed comments field bug.
					// Specific id's for each input area

				update_option('formbuilder_version', "0.13");
				echo "Upgraded FormBuilder to 0.13<br/>";
			}

			// Upgrade to version 0.2Click="form1.SubmitButton.disabled=tr0
			if(get_option('formbuilder_version') < "0.20")
			{
				// Requested Features and Bugs to Fix:

				// Changes:
					// Added additional instructions on how to use the form building controls.
					// Installed ability to enable or disabled built-in CSS

				update_option('formBuilder_custom_css', 'Enabled');
				update_option('formbuilder_version', "0.20");
				echo "Upgraded FormBuilder to 0.20<br/>";
			}

			// Upgrade to version 0.21
			if(get_option('formbuilder_version') < 0.21)
			{
				// Requested Features and Bugs to Fix:
					// Add the option to customize the "thank you" text.
					// Updated database to allow for custom thankyou text
					// Repaired bug in new installation script.

				// Changes:
				$sql = 'ALTER TABLE ' . FORMBUILDER_TABLE_FORMS . ' ADD thankyoutext TEXT NOT NULL ;';
				if($wpdb->query($sql) !== false)
				{
					echo "Upgraded FormBuilder to 0.21<br/>";
					update_option('formbuilder_version', "0.21");
				}
				else
					echo "Error: Unable to upgrade formbuilder plugin.";

			}

			// Upgrade to version 0.22
			if(get_option('formbuilder_version') < 0.22)
			{
				// Requested Features and Bugs to Fix:

				// Changes:
					// Installed ajaxified on-the-fly field checking.

				update_option('formbuilder_version', "0.22");
				echo "Upgraded FormBuilder to 0.22<br/>";
			}

			// Upgrade to version 0.30
			if(get_option('formbuilder_version') < 0.30)
			{
				// Requested Features and Bugs to Fix:
					// Autoresponse capability

				// Changes:
					// Created Responses table in DB.
					// Updated upgrade scripts
					//

				/* Enable this when enabling version 0.30 */
				$sql = 'CREATE TABLE ' . FORMBUILDER_TABLE_RESPONSES . ' ('
					. ' `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY, '
					. ' `name` VARCHAR(255) NOT NULL, '
					. ' `subject` VARCHAR(255) NOT NULL, '
					. ' `message` TEXT NOT NULL, '
					. ' `from_name` VARCHAR(255) NOT NULL,'
					. ' `from_email` VARCHAR(255) NOT NULL'
					. ' );';


				$wpdb->query($sql);

				$sql = 'ALTER TABLE ' . FORMBUILDER_TABLE_FORMS . ' ADD autoresponse BIGINT NOT NULL ;';
				$wpdb->query($sql);





				echo "Upgraded FormBuilder to 0.30<br/>";
				update_option('formbuilder_version', "0.30");
			}

			// Upgrade to version 0.31
			if(get_option('formbuilder_version') < 0.31)
			{
				// Requested Features and Bugs to Fix:
					// Fix allow forms to have subject line.

				// Changes:
					// Replaced form description with subject line/

				$sql = 'ALTER TABLE `' . FORMBUILDER_TABLE_FORMS . '` CHANGE `description` `subject` VARCHAR( 255 ) NOT NULL';
				$wpdb->query($sql);

				echo "Upgraded FormBuilder to 0.31<br/>";
				update_option('formbuilder_version', "0.31");
			}

			// Upgrade to version 0.32
			if(get_option('formbuilder_version') < 0.32)
			{
				// Requested Features and Bugs to Fix:
					// Fix apostrophies in form labels to not break db calls.
					// Install export/import utility for creating new forms.
					// Install duplicate/copy form button
					// Put confirmation dialog on delete form.

				// Changes:
					// Confirmation on form delete.


				echo "Upgraded FormBuilder to 0.32<br/>";
				update_option('formbuilder_version', "0.32");
			}

			// Upgrade to version 0.33
			if(get_option('formbuilder_version') < 0.33)
			{
				// Requested Features and Bugs to Fix:
					// Allow for HTML in comments and thankyou text.

				// Changes:
					// Enabled HTML in comments and thankyou text.


				echo "Upgraded FormBuilder to 0.33<br/>";
				update_option('formbuilder_version', "0.33");
			}

			// Upgrade to version 0.34
			if(get_option('formbuilder_version') < 0.34)
			{
				// Requested Features and Bugs to Fix:
					// Emails display with variable names instead of labels.

				// Changes:
					//  display with variable names instead of labels.


				echo "Upgraded FormBuilder to 0.34<br/>";
				update_option('formbuilder_version', "0.34");
			}

			// Upgrade to version 0.35
			if(get_option('formbuilder_version') < 0.35)
			{
				// Requested Features and Bugs to Fix:
					// Include page url and referring url in email

				// Changes:
					//


				echo "Upgraded FormBuilder to 0.35<br/>";
				update_option('formbuilder_version', "0.35");
			}

			// Upgrade to version 0.40
			if(get_option('formbuilder_version') < 0.40)
			{
				// Requested Features and Bugs to Fix:
					// Allow for added modules

				// Changes:
					// Allowed for added modules


				echo "Upgraded FormBuilder to 0.40<br/>";
				update_option('formbuilder_version', "0.40");
			}

			// Upgrade to version 0.41
			if(get_option('formbuilder_version') < 0.41)
			{
				// Requested Features and Bugs to Fix:
					// Forms would be removed from posts, when comments were made.

				// Changes:
					// FIXED: Forms would be removed from posts, when comments were made.


				echo "Upgraded FormBuilder to 0.41<br/>";
				update_option('formbuilder_version', "0.41");
			}

			// Upgrade to version 0.42
			if(get_option('formbuilder_version') < 0.42)
			{
				// Changes:
					// Various minor bug fixes


				echo "Upgraded FormBuilder to 0.42<br/>";
				update_option('formbuilder_version', "0.42");
			}

			// Upgrade to version 0.43
			if(get_option('formbuilder_version') < 0.43)
			{
				// Changes:
					// Added ability to seperate label and value for radio buttons and select boxes with "value|label"
					// Changed required data field type from enum to text to allow greater flexability.
					// Added "any number" required data field.

				$sql = 'ALTER TABLE `' . FORMBUILDER_TABLE_FIELDS . '` CHANGE `required_data` `required_data` VARCHAR(255) NOT NULL';
				if($wpdb->query($sql) !== false)
				{
					update_option('formbuilder_version', "0.43");
					echo "Upgraded FormBuilder to 0.43<br/>";
				}
				else
					echo "Error: Unable to upgrade formbuilder plugin.";

			}

			// Upgrade to version 0.44
			if(get_option('formbuilder_version') < 0.44)
			{
				// Changes:
					// Various minor bug fixes and additions


				echo "Upgraded FormBuilder to 0.44<br/>";
				update_option('formbuilder_version', "0.44");
			}

			// Upgrade to version 0.45
			if(get_option('formbuilder_version') < 0.45)
			{
				// Changes:
					// Many CSS Changes


				echo "Upgraded FormBuilder to 0.45<br/>" .
						"Features:<br/>" .
						"- Reworked CSS for better form display.<br/>" .
						"- Added additional classes to form divs to allow better CSS management.<br/>";
				update_option('formbuilder_version', "0.45");
			}

			// Upgrade to version 0.46
			if(get_option('formbuilder_version') < 0.46)
			{
				// Changes:
					// Added ability to load a custom CSS file which will over-ride the default css options.


				echo "Upgraded FormBuilder to 0.46<br/>" .
						"Features:<br/>" .
						"- Added ability to load custom CSS file which will over-ride the default FB CSS.  Filename is additional_styles.css and must be located in the FB plugin folder.<br/>" .
						"- Added javascript to disable submit button on click to prevent duplicates.<br/>";
				update_option('formbuilder_version', "0.46");
			}

			// Upgrade to version 0.50
			if(get_option('formbuilder_version') < 0.50)
			{
				// Changes:
					// Final changes for 0.50 release

				echo "Upgraded FormBuilder to 0.50<br/>";
				update_option('formbuilder_version', "0.50");
			}

			// Upgrade to version 0.51
			if(get_option('formbuilder_version') < 0.51)
			{
				// Changes:
					// Bug fix.  Database was modified on upgrade for version 0.43, but the fresh install was not
					// configured with the same database modification.

				$sql = 'ALTER TABLE `' . FORMBUILDER_TABLE_FIELDS . '` CHANGE `required_data` `required_data` VARCHAR(255) NOT NULL';
				if($wpdb->query($sql) !== false)
				{
					update_option('formbuilder_version', "0.51");
					echo "Upgraded FormBuilder to 0.51 - Bug fix in the database tables.  More info on <a href='http://truthmedia.com/category/formbuilder/'>our site</a>.<br/>";
				}
				else
					echo "Error: Unable to upgrade formbuilder plugin to 0.51.";

			}

			// Upgrade to version 0.52
			if(get_option('formbuilder_version') < 0.52)
			{
				// Changes:
					// Final changes for 0.52 release

				echo "Upgraded FormBuilder to 0.52<br/>";
				update_option('formbuilder_version', "0.52");
			}

			// Upgrade to version 0.53
			if(get_option('formbuilder_version') < 0.53)
			{
				// Changes:
					// Added ability to supress errors.  Simply include &suppress_errors=true in the URL.
					// Fixed bug in Protected posts to disable display of form if correct password has not been entered.

				echo "Upgraded FormBuilder to 0.53<br/>";
				echo " - Added ability to supress errors.  Simply include &amp;suppress_errors=true in the URL.<br/>";
				echo " - Fixed bug in Protected posts to disable display of form if correct password has not been entered.<br/>";
				update_option('formbuilder_version', "0.53");
			}

			// Upgrade to version 0.54
			if(get_option('formbuilder_version') < 0.54)
			{
				// Changes:
					// Fixed bug in deactivation script.
					// Repaired CSS for better radio button display.
					// Removed background color presets from CSS

				echo "Upgraded FormBuilder to 0.54<br/>";
				echo " - Fixed bug in deactivation script.<br/>";
				echo " - Repaired CSS for better radio button display.<br/>";
				echo " - Removed background color presets from CSS.<br/>";
				update_option('formbuilder_version', "0.54");
			}

			// Upgrade to version 0.55
			if(get_option('formbuilder_version') < 0.55)
			{
				// Changes:
					// Repaired small bug introduced when sites use default permalinks.

				echo "Upgraded FormBuilder to 0.55<br/>";
				echo " - Repaired small bug introduced when sites use default permalinks.<br/>";
				update_option('formbuilder_version', "0.55");
			}

			// Upgrade to version 0.57
			if(get_option('formbuilder_version') < 0.57)
			{
			// Ensure the tables exist.  This helps people who installed v. 0.55 or lower on wp 2.5 to upgrade without problems.
			$wpdb->query("CREATE TABLE IF NOT EXISTS " . FORMBUILDER_TABLE_FORMS . " (
  id bigint(20) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  subject varchar(255) NOT NULL,
  recipient varchar(255) NOT NULL,
  method enum('POST','GET') NOT NULL,
  `action` varchar(255) NOT NULL,
  `thankyoutext` text NOT NULL,
  `autoresponse` bigint(20) NOT NULL,
  UNIQUE KEY id (id)
);");

			$wpdb->query("CREATE TABLE IF NOT EXISTS " . FORMBUILDER_TABLE_FIELDS . " (
  id bigint(20) NOT NULL auto_increment,
  form_id bigint(20) NOT NULL,
  display_order int(11) NOT NULL,
  field_type enum('single line text box','small text area','large text area','password box','checkbox','radio buttons','selection dropdown','hidden field','comments area','spam blocker','submit button','submit image') NOT NULL,
  field_name varchar(255) NOT NULL,
  field_value text NOT NULL,
  field_label varchar(255) NOT NULL,
  required_data varchar(255) NOT NULL,
  error_message varchar(255) NOT NULL,
  UNIQUE KEY id (id)
);");

			$wpdb->query("CREATE TABLE IF NOT EXISTS " . FORMBUILDER_TABLE_PAGES . " (
  id bigint(20) NOT NULL auto_increment,
  post_id bigint(20) NOT NULL,
  form_id bigint(20) NOT NULL,
  UNIQUE KEY id (id)
);");

				$wpdb->query('CREATE TABLE IF NOT EXISTS ' . FORMBUILDER_TABLE_RESPONSES . ' ('
					. ' `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY, '
					. ' `name` VARCHAR(255) NOT NULL, '
					. ' `subject` VARCHAR(255) NOT NULL, '
					. ' `message` TEXT NOT NULL, '
					. ' `from_name` VARCHAR(255) NOT NULL, '
					. ' `from_email` VARCHAR(255) NOT NULL '
					. ' );');

				$sql = 'ALTER TABLE `' . FORMBUILDER_TABLE_FIELDS . '` CHANGE `field_type` `field_type` ENUM(\'single line text box\',\'small text area\',\'large text area\',\'password box\',\'checkbox\',\'radio buttons\',\'selection dropdown\',\'hidden field\',\'comments area\',\'spam blocker\',\'submit button\',\'submit image\')';
				$wpdb->query($sql);

				echo "Upgraded FormBuilder to 0.57<br/>";
				echo " - Repaired bugs introduced by WP 2.5.<br/>";
				echo " - Changed required fields from being highlighted in RED by default, simply to being BOLD by default.  Error messages on the form are also now bold instead of red.<br/>";
				echo " - Added functions necessary for uninstallation.  <a href='http://truthmedia.com/wordpress/formbuilder/documentation/uninstall/'>See our blog for more information on how to uninstall FormBuilder.</a><br/>";
				echo " - Linked checkboxes and radio buttons to their labels, making them clickable.<br/>";
				echo " - Added ability to create customized submit buttons and submit button images anywhere in the form.<br/>";
				update_option('formbuilder_version', "0.57");
			}

			// Upgrade to version 0.58
			if(get_option('formbuilder_version') < 0.58)
			{
				echo "Upgraded FormBuilder to 0.58<br/>";
				echo " - Small bug fix for compliance with the HTML spec (section 4.2 at http://www.ietf.org/rfc/rfc2396.txt)<br/>";
				echo " - Small change to included file structure for additional_styles.css.<br/>";
				update_option('formbuilder_version', "0.58");
			}

			// Upgrade to version 0.59
			if(get_option('formbuilder_version') < 0.59)
			{
				echo "Upgraded FormBuilder to 0.59<br/>";
				echo " - Updated error reporting to be more clear to the end user, when they forget to enter information in required fields.<br/>";
				update_option('formbuilder_version', "0.59");
			}

			// Upgrade to version 0.60
			if(get_option('formbuilder_version') < 0.60)
			{
				$sql = array();

				$sql[1] = 'ALTER TABLE `' . FORMBUILDER_TABLE_FIELDS . '` CHANGE `field_value` `field_value` BLOB NOT NULL ,
CHANGE `field_label` `field_label` BLOB NOT NULL ,
CHANGE `error_message` `error_message` BLOB NOT NULL;';

				$sql[2] = 'ALTER TABLE `' . FORMBUILDER_TABLE_FORMS . '` CHANGE `name` `name` BLOB NOT NULL ,
CHANGE `subject` `subject` BLOB NOT NULL ,
CHANGE `recipient` `recipient` BLOB NOT NULL ,
CHANGE `thankyoutext` `thankyoutext` BLOB NOT NULL;';

				$sql[3] = ' ALTER TABLE `' . FORMBUILDER_TABLE_RESPONSES . '` CHANGE `name` `name` BLOB NOT NULL ,
CHANGE `subject` `subject` BLOB NOT NULL ,
CHANGE `message` `message` BLOB NOT NULL ,
CHANGE `from_name` `from_name` BLOB NOT NULL ';

				$sql[4] = 'ALTER TABLE `' . FORMBUILDER_TABLE_FIELDS . '` CHANGE `field_type` `field_type` VARCHAR(255) NOT NULL';

				$db_errors = "";
				foreach($sql as $key=>$query) {
					if($wpdb->query($query) === false) {
						$db_errors .= "Unable to run query #$key: $query<br/>\n";
					}
				}

				if(!$db_errors)
				{
					echo "Upgraded FormBuilder to 0.60<br/>";
					echo " - Feature: Numerous database modifications to support internationalization of characters.<br/>";
					echo " - Feature: Added new form field type to allow for redirection to a followup URL on successful form completion.<br/>";
					echo " - Feature: Added basic duplicate form submission checking.<br/>";
					echo " - Feature: Added a new module (form action) to simply submit the form data without sending an email.  Useful for directing the visitor to another page if necessary.<br/>";
					echo " - Bug Fix: Fixed problem with FormBuilder when WordPress is installed in a subdirectory<br/>";
					echo " - Bug Fix: Updated to use the WordPress built-in version of the prototype.js library, rather than bundling it.<br/>";
					update_option('formbuilder_version', "0.60");
				}
				else
					echo "Error: Unable to upgrade formbuilder plugin to 0.60. <br/>$db_errors";
			}

			// Upgrade to version 0.61
			if(get_option('formbuilder_version') < 0.61)
			{
				echo "Upgraded FormBuilder to 0.61<br/>";
				echo " - Feature: Added the ability to include forms anywhere in the content or on the template.  See the blog for more info.  <a href='http://truthmedia.com/wordpress/formbuilder'>http://truthmedia.com/wordpress/formbuilder</a><br/>";
				echo " - Feature: Updated to allow for multiple forms to be displayed per page.<br/>";
				echo " - Feature: Enabled better randomization in the form redirection module.<br/>";
				echo " - Feature: Added documentation on <a href='http://truthmedia.com/2008/06/27/feature-reverse-captcha/' title='Read More: FormBuilder Spam Blocking' target='_blank'>reverse captcha spam blocking system</a>.<br/>";
				echo " - Bug Fix: Repaired bugs introduced in the form redirection process.<br/>";
				update_option('formbuilder_version', "0.61");
			}

			// Upgrade to version 0.63
			if(get_option('formbuilder_version') < 0.63)
			{
				echo "Upgraded FormBuilder to 0.63<br/>";
				echo " - Feature: Upgraded CSS and links for compatibility with WP 2.7<br/>";
				echo " - Bug Fix: Repaired help text to be accurate for form redirects.<br/>";
				echo " - Bug Fix: Fixed support for PHP4.  However, PHP5+ is still your better option.<br/>";
				update_option('formbuilder_version', "0.63");
			}

			// Upgrade to version 0.70
			if(get_option('formbuilder_version') < 0.70)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to 0.70", 
					" - Feature: Added Alternate Action module to allow form submission to other form processing systems.<br/>" .
					" - Feature: Uninstall feature added to FB Dashboard.<br/>" .
					" - Tweak: Some code streamlining for faster response times.");
				update_option('formbuilder_version', "0.70");
			}

			// Upgrade to version 0.71
			if(get_option('formbuilder_version') < 0.71)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to 0.71", "Bug Fix: A small but serious bug was found in the built-in WordPress wp_mail function " .
						"which prevents responses from coming from the proper email address.  The implications of " .
						"this are that if you attempted to reply to an email from the site it might have been sent " .
						"to wordpress@yourdomain.com instead.  We have switched back to using the regular built-in " .
						"PHP mail command instead.");
				update_option('formbuilder_version', "0.71");
			}

			// Upgrade to version 0.72
			if(get_option('formbuilder_version') < 0.72)
			{
				unset($db_errors);
				$sql = 'CREATE TABLE ' . FORMBUILDER_TABLE_RESULTS . ' (
					`id` BIGINT UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`form_id` BIGINT UNSIGNED ZEROFILL NOT NULL ,
					`timestamp` BIGINT UNSIGNED ZEROFILL NOT NULL ,
					`xmldata` LONGBLOB NOT NULL ,
					INDEX ( `form_id` , `timestamp` )
				);';
				if($wpdb->query($sql) === false) {
						$db_errors = "Unable to run query $sql<br/>\n";
				}
				
				if(!$db_errors)
				{
					update_option('formbuilder_db_xml', '0');
					formbuilder_admin_alert("Upgraded FormBuilder to 0.72", 
							" - Feature: Enabled new field type CAPTCHA to allow for human form verification.<br/>" .
							" - Feature: New forms are created with default contact fields.<br/>" .
							" - Feature: Allow for form data to be saved to the database in addition to being processed by the form processing module.<br/>" .
							" - Bug Fix: Repaired call-time pass-by-reference error.");
					update_option('formbuilder_version', "0.72");
				}
				else
					formbuilder_admin_alert("FormBuilder Upgrade Error: $db_errors");
			}

			// Upgrade to version 0.73
			if(get_option('formbuilder_version') < 0.73)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to 0.73", 
					" - Bug Fix: Minor bug-fix to CAPTCHA functions which caused failure to work on some systems.");
				update_option('formbuilder_version', "0.73");
			}

			// Upgrade to version 0.74
			if(get_option('formbuilder_version') < 0.74)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to 0.74", 
					" - Feature: Enabled multi-page forms with the new 'page break' form field type.  Simply add a 'page break' where you want a new page of form fields to start.<br/>" .
					" - Feature: Database form result saving and exporting enabled.  You can now view all forms that have come in to your site simply by viewing the database backup page.  You can also export a selection of results to a CSV file.");
				update_option('formbuilder_version', "0.74");
			}

			// Upgrade to version 0.75
			if(get_option('formbuilder_version') < 0.75)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to 0.75", 
					" - Feature: Enabled the ability to add a \"recipient selection\" field type.  <a href='http://truthmedia.com/wordpress/formbuilder/documentation/setting-a-selectable-form-recipient/'>more</a><br/>" .
					" - Bug Fix: Minor bugs related to the export of CSV data.<br/>" .
					" - Code Cleanup: Extensive code cleanup to better comply with STRICT PHP standards.");
				update_option('formbuilder_version', "0.75");
			}

			// Upgrade to version 0.76
			if(get_option('formbuilder_version') < 0.76)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to 0.76", 
					" - Feature: Added ability to capture IP address of form submitter.  Disabled by default.<br/>" .
					" - Bug Fix: Minor HTML tweak to allow for better CSS compatability.<br/>" . 
					" - Code Cleanup: Cleaned up the FormBuilder management dashboard for better viewing and usability.");
				update_option('formbuilder_version', "0.76");
			}

			// Upgrade to version 0.77
			if(get_option('formbuilder_version') < 0.77)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.77", 
					"Feature: Enabled Internationalization of the FormBuilder plugin.  This means that we will now be able to provide translators with a special POT file which can be used to create alternate language translations for the FormBuilder interface.<br/>" .
					"Feature: Enabled exporting of results from a single form in more standardized CSV format.<br/>" . 
					"");
					
				update_option('formbuilder_version', "0.77");
			}
			
			// Upgrade to version 0.80
			if(get_option('formbuilder_version') < 0.80)
			{
				// I know this could all be done in a single command/line but I like it organized, not nerdy.
				$sql = array();
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FORMS . "` CHANGE `action` `action` BLOB NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FORMS . "` " . $charset_collate . ";";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FORMS . "` CHANGE `action` `action` varchar(255) NOT NULL default '';";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FORMS . "` CHANGE `name` `name` varchar(255) NOT NULL default '';";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FORMS . "` CHANGE `subject` `subject` text NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FORMS . "` CHANGE `recipient` `recipient` text NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FORMS . "` CHANGE `thankyoutext` `thankyoutext` text NOT NULL;";
				
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `field_type` `field_type` BLOB NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `field_name` `field_name` BLOB NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `required_data` `required_data` BLOB NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` " . $charset_collate . ";";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `field_type` `field_type` varchar(255) NOT NULL default '';";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `field_name` `field_name` varchar(255) NOT NULL default '';";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `required_data` `required_data` varchar(255) NOT NULL default '';";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `field_value` `field_value` text NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `field_label` `field_label` text NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `error_message` `error_message` text NOT NULL;";
				
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_PAGES . "` " . $charset_collate . ";";
				
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_RESULTS . "` " . $charset_collate . ";";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_RESULTS . "` CHANGE `xmldata` `xmldata` LONGTEXT NOT NULL;";
				
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_RESPONSES . "` CHANGE `from_email` `from_email` BLOB NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_RESPONSES . "` " . $charset_collate . ";";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_RESPONSES . "` CHANGE `from_email` `from_email` varchar(255) NOT NULL default '';";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_RESPONSES . "` CHANGE `name` `name` varchar(255) NOT NULL default '';";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_RESPONSES . "` CHANGE `subject` `subject` text NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_RESPONSES . "` CHANGE `message` `message` text NOT NULL;";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_RESPONSES . "` CHANGE `from_name` `from_name` varchar(255) NOT NULL default '';";
				
				$special_collate = "";
				if ( ! empty($wpdb->charset) )
					$special_collate = "CHARACTER SET $wpdb->charset";
				if ( ! empty($wpdb->collate) )
					$special_collate .= " COLLATE $wpdb->collate";
				
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FORMS . "` CHANGE `method` `method` ENUM( 'POST', 'GET' ) $special_collate NOT NULL DEFAULT 'POST'";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FORMS . "` CHANGE `autoresponse` `autoresponse` BIGINT( 20 ) NOT NULL DEFAULT '0'";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `form_id` `form_id` BIGINT( 20 ) NOT NULL DEFAULT '0'";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` CHANGE `display_order` `display_order` INT( 11 ) NOT NULL DEFAULT '0'";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_PAGES . "` CHANGE `post_id` `post_id` BIGINT( 20 ) NOT NULL DEFAULT '0'";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_PAGES . "` CHANGE `form_id` `form_id` BIGINT( 20 ) NOT NULL DEFAULT '0'";
				$sql[] = "ALTER TABLE `" . FORMBUILDER_TABLE_RESULTS . "` CHANGE `form_id` `form_id` BIGINT( 20 ) UNSIGNED ZEROFILL NOT NULL DEFAULT '0'";
				
				foreach($sql as $query)
				{
					$result = $wpdb->query($query);
					if($result === false)
					{
						formbuilder_admin_alert("Failed running query: $query");
						$error_status = true;
					}
				}
			
				update_option('formbuilder_alternate_email_handling', 'Disabled');
				update_option('formbuilder_blacklist', 'Disabled');
				
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.80", 
					"Feature: Alternate Email Processing - Allows you to select whether to use the standar PHP mail command (default) or the WordPress WP_Mail command.<br/>\n" .
					"Feature: Form Data in AutoResponses - You can now make reference to submitted form data in autoresponses using variables like ~variable~ where the variable name matches the field names on your form.<br/>\n" .
					"Feature: Blacklist Form Checking - You can enable checking of submitted form data against the WordPress discussion blacklist.  Set blacklisted items in the WordPress discussion settings.<br/>\n" .
					"Feature: DB Subject and Recipient - Forms stored in the database will now include subject and recipient.<br/>\n" .
					"Feature: More Required Types - Added Link and Single Word the the required field types.<br/>\n" .
					"Bug Fix: Attempted to fix link problems on windows hosting solutions.<br/>\n" .
					"Bug Fix: Added Reply-To field to email headers.<br/>\n");
					
				update_option('formbuilder_version', "0.80");
			}
			
			// Upgrade to version 0.81
			if(get_option('formbuilder_version') < 0.81)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.81", 
					"Feature: Configured FB to automatically scroll back to the location of the form on the page when submitted.<br/>" . 
					"Feature: Enabled ability to add Reset button to form if necessary.<br/>" . 
					"Feature: Added page, referrer and optional IP to XML Email module, as well as XML database storage.<br/>" . 
					"Feature: Enabled grey list checking based on moderation words found in the WordPress discussion options.<br/>" . 
					"Feature: Excessive link checking based on link limits found in the WordPress discussion options.<br/>" . 
					"Bug Fix: Allowed editors to export form results as CSV.<br/>\n" .
					"Code Cleanup: Switch all code to use WordPress native database access model.<br/>" .
					"");
					
				update_option('formbuilder_version', "0.81");
			}
			
			// Upgrade to version 0.82
			if(get_option('formbuilder_version') < 0.82)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.82", 
					"Feature: Added ability to export or delete specific forms from the XML backup database.<br/>" . 
					"Feature: Added ability to translate specific front-end strings without translating the whole application.<br/>" . 
					"Feature: Slight navigation and design reorganization for easier navigation.<br/>" . 
					"Feature: Updated alternate_action with more robust code checking for curl library first.<br/>" . 
					"Bug Fix: Fixed more Windows path related problems.<br/>\n" .
					"Bug Fix: Enabled setting checkboxes, dropdowns and radio buttons as required fields.<br/>\n" .
					"");
					
				update_option('formbuilder_version', "0.82");
			}
			
			// Upgrade to version 0.821
			if(get_option('formbuilder_version') < 0.821)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.821", 
					"Feature: Akismet spam checking.  Forms to be checked must have at least one 'name' required field and at least one 'email' required field.<br/>" . 
					"Feature: New required field type: 'name'  Essentially the same as 'any text' but used specifically for the Akismet spam checking.<br/>" . 
					"");
					
				update_option('formbuilder_version', "0.821");
			}
			
			// Upgrade to version 0.822
			if(get_option('formbuilder_version') < 0.822)
			{
				$referrer_info = get_option('formBuilder_referrer_info');
				if(!$referrer_info) {
					$referrer_info = 'Enabled';
					update_option('formBuilder_referrer_info', $referrer_info);
				}
				
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.822", 
					"Feature: Spammer IP checking installed, checking IP's against http://www.stopforumspam.com/apis.<br/>" . 
					"Feature: New field type: unique id.<br/>" . 
					"Feature: New permissions system installed, allowing for form controls to be customized for certain user levels.<br/>" . 
					"Bug Fix: URL validation was only partially working.<br/>" . 
					"Bug Fix: Enabled better field name checking.<br/>" . 
				"");
					
				update_option('formbuilder_version', "0.822");
			}
			
			// Upgrade to version 0.823
			if(get_option('formbuilder_version') < 0.823)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.823", 
					"Bug Fix: Major permissions problem prevented any FormBuilder access on upgrades and new installs.<br/>" . 
					"");
					
				update_option('formbuilder_version', "0.823");
			}
			
			// Upgrade to version 0.824
			if(get_option('formbuilder_version') < 0.824)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.824", 
					"Overhaul: Complete overhaul of the javascript processing systems, replacing jQuery with a smaller, lighter library.<br/>" . 
					"");
					
				update_option('formbuilder_version', "0.824");
			}
			
			// Upgrade to version 0.825
			if(get_option('formbuilder_version') < 0.825)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.825", 
					"Feature: Better database export controls which should solve some of the timeout problems, as well as adding paginated form results and the ability to mass-delete database records.<br/>" . 
					"");
					
				update_option('formbuilder_version', "0.825");
			}
			
			// Upgrade to version 0.83
			if(get_option('formbuilder_version') < 0.83)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.83",  
					"");
					
				update_option('formbuilder_version', "0.83");
			}
			
			// Upgrade to version 0.84
			if(get_option('formbuilder_version') < 0.84)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.84",  
					"Feature: Enabled autodetection of forms to cut down on HTML bloat.<br/>\n" .
					"Clean Up: Sorted field types and required field types alphabetically when editing forms.<br/>\n" .
					"Bug Fix: Fixed CAPTCHA bug.<br/>\n" .
					"Bug Fix: Removed requirement for field name on comments and page breaks.<br/>\n" .
				"");
					
				update_option('formbuilder_version', "0.84");
			}
			
			// Upgrade to version 0.85
			if(get_option('formbuilder_version') < 0.85)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.85",  
					"Feature: New SYSTEM FIELD type. Allows assigning variables to the form without having them displayed on the form itself.  Like hidden fields, but not shown even in the HTML code.<br/>\n" .
					"Feature: Credit Card Required Field Type. Will do BASIC credit card number validation.  (ensures it looks like a valid CC number)<br/>\n" .
					"Feature: Ability to resend emails from DB backup if necessary.<br/>\n" .
					"Bug Fix: Small problem with session creation affecting confirmation email address checking.<br/>\n" .
					"Bug Fix: Issue with improper error processing when unable to do spammer IP checking.<br/>\n" .
				"");
					
				update_option('formbuilder_version', "0.85");
			}
			
			// Upgrade to version 0.852
			if(get_option('formbuilder_version') < 0.852)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.852",  
					"Bug Fix: Upgrade alert fixed.<br/>\n" .
					"Bug Fix: Small REQUEST_URI problem fixed.<br/>\n" .
				"");
					
				update_option('formbuilder_version', "0.852");
			}
			
			
			// Upgrade to version 0.86
			if(get_option('formbuilder_version') < 0.86)
			{
				if(get_option('formbuilder_extensions') === false)
					update_option('formbuilder_extensions', array());
				
				formbuilder_admin_alert("Upgrading FormBuilder to version 0.86",  
					"Bug Fix: Fixed problem with DB_COLLATE and DB_CHARSET variables not being set.<br/>\n" .
					"Clean Up: Changed post-to-form attachment box to list forms available alphabetically.<br/>\n" .
					"Clean Up: New forms will now be named 'A New Form' so as to appear at the top of the forms list.<br/>\n" .
					"Clean Up: Creating a new form will automatically load the form editor.<br/>\n" .
				"");
					
				$installWasSuccessful = formbuilder_createTables();
				
				if($installWasSuccessful)
				{
					update_option('formbuilder_version', "0.86");
				}
				else
				{
	
					formbuilder_admin_alert("ERRORS INSTALLING FORMBUILDER " . FORMBUILDER_VERSION_NUM, 
						"FormBuilder seems to have encountered some errors while trying to install.  Please contact us on the FormBuilder help page at: <br/>
						<a href='http://truthmedia.com/wordpress/formbuilder/request-help/' target='_blank'>http://truthmedia.com/wordpress/formbuilder/request-help/</a><br/><br/>
						When contacting us, we will need to know the following information: <br/>
						<ul>
						<li>Your website hosting provider</li>
						<li>WordPress version</li>
						<li>FormBuilder version</li>
						<li>PHP version</li>
						<li>MySQL version</li>
						<li>The error message you received</li>
						<li>The steps you took leading up to the problem</li>
						</ul>
						");
					
				}
			}
			
			// Upgrade to version 0.87
			if(get_option('formbuilder_version') < 0.87)
			{
				$charset_collate = formbuilder_getCharSet();
				$sql = "ALTER TABLE `" . FORMBUILDER_TABLE_FIELDS . "` ADD `help_text` TEXT NOT NULL";
				$result = $wpdb->query($sql);
				if($result === false)
				{
					formbuilder_admin_alert("Failed running query: $sql");
					$error_status = true;
				}
				
				$sql = "CREATE TABLE IF NOT EXISTS `" . FORMBUILDER_TABLE_TAGS . "` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `form_id` bigint(20) NOT NULL,
  `tag` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`)
) $charset_collate;";
				$result = $wpdb->query($sql);
				if($result === false)
				{
					formbuilder_admin_alert("Failed running query: $sql");
					$error_status = true;
				}
				
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.87",  
					"Feature: New help text field added.<br/>\n" .
					"Feature: Tags for forms.<br/>\n" .
					"Feature: Paginated list of forms.<br/>\n" .
					"Feature: Better internationalization support.<br/>\n" .
					"Bug Fixing: Added more error information during the dreaded 'Form not saved' problem.<br/>\n" .
				"");
					
				update_option('formbuilder_version', "0.87");
			}
			
		
			
			// Upgrade to version 0.88
			if(get_option('formbuilder_version') < 0.88)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.88",  
					"Feature: Ability to search for forms.<br/>\n" .
					"Feature: New field type: required checkbox.<br/>\n" .
					"Feature: New field type: required password.<br/>\n" .
					"Feature: Special field to capture logged in WordPress usernames.<br/>\n" .
					"Feature: Ability to edit the form from the live site using a link in the admin bar.<br/>\n" .
					"Feature: Ability to detect logged in WordPress users and pre-fill things like name and email.<br/>\n" .
					"Bug Fix: Datestamp field typo fixed.<br/>\n" .
					"Bug Fix: Repaired problem with showing thankyou text after XML email sending.<br/>\n" .
				"");
					
				update_option('formbuilder_version', "0.88");
			}
			
		
			
			// Upgrade to version 0.89
			if(get_option('formbuilder_version') < 0.89)
			{
				formbuilder_admin_alert("Upgraded FormBuilder to version 0.89", nl2br("
* Feature: Allow ~variable~ fields in thankyou text.
* Feature: Option to show all fields in autoresponder.
* Feature: Allow admin bar to show all forms on the current page/index.
* Bug Fix: Quotes in ThankYou text remain encoded which breaks HTML
* Bug Fix: Name/Email matching was too broad.
* Bug Fix: Fixed ability to create new forms.
* Bug Fix: Form search lost when switching pages.
* Bug Fix: Fixed forms not displaying / processing properly on some themes due to the_content being processed multiple times.
* Bug Fix: Forms with followup_url fields now bounce straight to the followup url without re-showing the original page first.
					"));
					
				update_option('formbuilder_version', "0.89");
			}
			
			
			
			
			
			/* For a future version
			 */
			// TODO: Marker.
			
			
		}
		else
		{
			
			// Formbuilder was not previously installed, therefore install the required tables.
			$installWasSuccessful = formbuilder_createTables();
			
			if($installWasSuccessful)
			{
			
				if(!$error_status)
				{
					formbuilder_createOptions();
				}
				
				// Set the version number
				update_option('formbuilder_version', FORMBUILDER_VERSION_NUM);
	
				formbuilder_admin_alert("Finished Installing FormBuilder " . FORMBUILDER_VERSION_NUM, 
					"Thanks for installing FormBuilder.  We hope you like it.  Feel free to visit our blog " .
					"if you have any comments or questions at " .
					"<a href='http://truthmedia.com/wordpress/formbuilder/'>http://truthmedia.com/wordpress/formbuilder/</a>");
			
			}
			else
			{

				formbuilder_admin_alert("ERRORS INSTALLING FORMBUILDER " . FORMBUILDER_VERSION_NUM, 
					"FormBuilder seems to have encountered some errors while trying to install.  Please contact us on the FormBuilder help page at: <br/>
					<a href='http://truthmedia.com/wordpress/formbuilder/request-help/' target='_blank'>http://truthmedia.com/wordpress/formbuilder/request-help/</a><br/><br/>
					When contacting us, we will need to know the following information: <br/>
					<ul>
					<li>Your website hosting provider</li>
					<li>WordPress version</li>
					<li>FormBuilder version</li>
					<li>PHP version</li>
					<li>MySQL version</li>
					<li>The error message you received</li>
					<li>The steps you took leading up to the problem</li>
					</ul>
					");
				
			}
		}
	}

	
	/**
	 * Create the string used to define MySQL charset and collation if necessary.
	 */
	function formbuilder_getCharSet()
	{
		global $wpdb;
		$charset_collate = '';
		
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";

		// Determine database collation.
		if ( $charset_collate == ''  
			AND defined(DB_CHARSET) 
			AND trim( DB_CHARSET ) != '' 
		) 
		{
			$charset_collate = "DEFAULT CHARACTER SET " . DB_CHARSET;
			if ( defined(DB_COLLATE) AND trim( DB_COLLATE ) != '' )
				$charset_collate .= " COLLATE " . DB_COLLATE;
		}
		
		return($charset_collate);
	}
	
	
	/**
	 * Contains the actual code for running the install.
	 */
	function formbuilder_createTables()
	{
			
		global $wpdb;
		$charset_collate = formbuilder_getCharSet();
		
			formbuilder_admin_alert('Creating necessary FormBuilder tables.', '');
		
			$error_status = false;
			
			// Run the table creation querys.
			$sql = "CREATE TABLE IF NOT EXISTS `" . FORMBUILDER_TABLE_FIELDS . "` (
  `id` bigint(20) NOT NULL auto_increment,
  `form_id` bigint(20) NOT NULL default '0',
  `display_order` int(11) NOT NULL default '0',
  `field_type` varchar(255) NOT NULL default '',
  `field_name` varchar(255) NOT NULL default '',
  `field_value` text NOT NULL,
  `field_label` text NOT NULL,
  `required_data` varchar(255) NOT NULL default '',
  `error_message` text NOT NULL,
  `help_text` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) $charset_collate;";
			$result = $wpdb->query($sql);
			if($result === false)
			{
				formbuilder_admin_alert("Failed running query: $sql");
				$error_status = true;
			}
			
			
			$sql = "CREATE TABLE IF NOT EXISTS `" . FORMBUILDER_TABLE_FORMS . "` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `subject` text NOT NULL,
  `recipient` text NOT NULL,
  `method` enum('POST','GET') NOT NULL default 'POST',
  `action` varchar(255) NOT NULL default '',
  `thankyoutext` text NOT NULL,
  `autoresponse` bigint(20) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) $charset_collate;";
			$result = $wpdb->query($sql);
			if($result === false)
			{
				formbuilder_admin_alert("Failed running query: $sql");
				$error_status = true;
			}


			$sql = "CREATE TABLE IF NOT EXISTS `" . FORMBUILDER_TABLE_PAGES . "` (
  `id` bigint(20) NOT NULL auto_increment,
  `post_id` bigint(20) NOT NULL default '0',
  `form_id` bigint(20) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) $charset_collate;";
			$result = $wpdb->query($sql);
			if($result === false)
			{
				formbuilder_admin_alert("Failed running query: $sql");
				$error_status = true;
			}


			$sql = "CREATE TABLE IF NOT EXISTS `" . FORMBUILDER_TABLE_RESPONSES . "` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `subject` text NOT NULL,
  `message` text NOT NULL,
  `from_name` varchar(255) NOT NULL default '',
  `from_email` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) $charset_collate;";
			$result = $wpdb->query($sql);
			if($result === false)
			{
				formbuilder_admin_alert("Failed running query: $sql");
				$error_status = true;
			}


			$sql = "CREATE TABLE IF NOT EXISTS `" . FORMBUILDER_TABLE_RESULTS . "` (
  `id` bigint(20) unsigned zerofill NOT NULL auto_increment,
  `form_id` bigint(20) unsigned zerofill NOT NULL default '00000000000000000000',
  `timestamp` bigint(20) unsigned zerofill NOT NULL,
  `xmldata` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`,`timestamp`)
) $charset_collate;";
			$result = $wpdb->query($sql);
			if($result === false)
			{
				formbuilder_admin_alert("Failed running query: $sql");
				$error_status = true;
			}
				
			
				
				$sql = "CREATE TABLE IF NOT EXISTS `" . FORMBUILDER_TABLE_TAGS . "` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `form_id` bigint(20) NOT NULL,
  `tag` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`)
) $charset_collate;";
			$result = $wpdb->query($sql);
			if($result === false)
			{
				formbuilder_admin_alert("Failed running query: $sql");
				$error_status = true;
			}
			
			return(!$error_status);
	}
	
	
	/**
	 * Function to create various wordpress option variables used by FormBuilder.
	 */
	function formbuilder_createOptions()
	{
		// Set the spam blocker default variable
		update_option('formbuilder_spam_blocker', "formBuilderCap");
	
		// Set Custom CSS default variable
		update_option('formBuilder_custom_css', 'Enabled');
		
		// Set default alternate email handling value to DisAbled
		update_option('formbuilder_alternate_email_handling', 'Disabled');
		
		// Set blacklist checking to be disabled.
		update_option('formbuilder_blacklist', 'Disabled');
		
		// Set blacklist checking to be disabled.
		update_option('formbuilder_greylist', 'Disabled');
		
		// Set blacklist checking to be disabled.
		update_option('formbuilder_excessive_links', 'Disabled');
		
		// Set blacklist checking to be disabled.
		update_option('formbuilder_spammer_ip_checking', 'Disabled');
		
		// Set blacklist checking to be disabled.
		update_option('formbuilder_akismet', 'Disabled');
		
		// Set referrer info to be collected by default.
		update_option('formBuilder_referrer_info', 'Enabled');
	}
	
	
	/**
	 * This script should be run in the event that the user wants to remove all formbuilder related tables from the database.
	 */
	function formbuilder_cleaninstall($confirm=false) {

		global $userdata, $table_prefix, $wpdb;
		echo " &gt; <a href='" . $_SERVER['REQUEST_URI'] . "'>Uninstall</a>";
		$varname = $table_prefix . "capabilities";
		$caps = $userdata->$varname;
		if(!$caps['administrator'])
		{
			?>
			<fieldset class="options">
				<h3><?php _e('UnInstall FormBuilder', 'formbuilder'); ?></h3>
				<p><?php _e('You must have administrator privilages to uninstall FormBuilder.', 'formbuilder'); ?></p>
			</fieldset>
			<?php
			return;
		}

		if($confirm != false)
		{
			// Remove formbuilder options in the WP db.
			delete_option('formbuilder_version');
			delete_option('formbuilder_spam_blocker');
			delete_option('formBuilder_custom_css');
			delete_option('formBuilder_duplicate_hash');
			delete_option('formbuilder_db_xml');
			delete_option('formbuilder_alternate_email_handling');
			delete_option('formbuilder_blacklist');
			delete_option('formbuilder_greylist');
			delete_option('formbuilder_permissions');
			delete_option('formBuilder_referrer_info');
			delete_option('formbuilder_spammer_ip_checking');
			delete_option('formbuilder_akismet');
			delete_option('formbuilder_excessive_links');
			delete_option('formBuilder_IP_Capture');
			delete_option('formBuilder_javascript_compat');
			delete_option('formbuilder_db_export_ids');
			
			

			// Remove formbuilder tables
			$sql = 'DROP TABLE `' . 
				FORMBUILDER_TABLE_FIELDS . '`, `' . 
				FORMBUILDER_TABLE_FORMS . '`, `' . 
				FORMBUILDER_TABLE_PAGES . '`, `' . 
				FORMBUILDER_TABLE_RESULTS . '`, `' . 
				FORMBUILDER_TABLE_TAGS . '`, `' . 
				FORMBUILDER_TABLE_RESPONSES . '`;';
			$wpdb->query($sql);
			?>
			<fieldset class="options">
				<div class="info-box-formbuilder">
					<h3 class="info-box-title"><?php _e('FormBuilder UnInstalled!', 'formbuilder'); ?></h3>
					<p><?php _e('FormBuilder has been uninstalled from your blog.  You should now deactivate the plugin on your plugins page.  If you do not do so, and instead return to the FormBuilder administration page, the plugin will be reinstalled with a fresh set of databases. (ie. if you want to start over with a clean install)', 'formbuilder'); ?></p>
					<p><a href="./plugins.php"><?php _e('Click here to go to your plugins page and deactivate FormBuilder', 'formbuilder'); ?></a></p>
				</div>
			</fieldset>
			<?php
		}
		else
		{
			?>
			<fieldset class="options">
				<div class="info-box-formbuilder">
					<h3 class="info-box-title"><?php _e('UnInstall FormBuilder', 'formbuilder'); ?></h3>
					<p><?php _e('Do you really want to completely uninstall the FormBuilder plugin?  This will completely remove all FormBuilder related data from the database.', 'formbuilder'); ?>  <strong><?php _e('BE VERY SURE YOU WANT TO DO THIS!', 'formbuilder'); ?></strong></p>
					<a href="<?php echo FB_ADMIN_PLUGIN_PATH; ?>"><?php _e('No thanks!  Take me away from here.', 'formbuilder'); ?></a><br/>
					<br/>
					<a href="<?php echo FB_ADMIN_PLUGIN_PATH; ?>&fbaction=uninstall&confirm=yes"
						onclick="return(confirm('<?php _e("Last chance to turn back... Are you sure you want to remove FormBuilder and all it\\'s data from your blog?", 'formbuilder'); ?>'));"
						><?php _e('YES, PLEASE REMOVE FORMBUILDER FROM MY BLOG!', 'formbuilder'); ?></a>
				</div>
			</fieldset>
			<?php
		}

	}

?>