=== FormBuilder ===
Contributors: warkior
Tags: form, forms, email, comments, contact, input, spam, form to email, email form, contact form
Requires at least: 2.7
Tested up to: 3.9
Stable tag: 0.93

Allows WordPress bloggers to easily create customised contact forms for use on pages or posts.

== Description ==
The Form Builder Plugin for WordPress allows you to build contact forms in
the WordPress administrative interface without needing to know PHP or HTML.
We've tried to make it simple enough for anyone to make them, yet customisable
enough to satisfy the needs of experienced web developers.

** Features: **

* Easily build complex contact forms without any HTML or coding.
* Pre-made contact forms for quick and easy setup.
* Put forms anywhere on your site.
* Put multiple forms on a single page.
* Split forms into multiple pages.
* Set up personalised autoresponses to send to the visitor.
* Optionally store all form data in the database.
* Built-in anti-spam capabilities.
* Optional CAPTCHA technology.
* Translatable.
* Multi-language forms support.
* Akismet and spammer IP checking
* Permissions control
* and LOTS more...

Programming and Design by [James Warkentin](http://www.warkior.com/)

== Installation ==
1.	Upload the /formbuilder/ folder and files to your WordPress plugins folder,
	located in your WordPress install under /wp-content/plugins/

2.	Browse in the WordPress admin interface to the plugin activation page and
	activate the FormBuilder plugin.

3.	The plugin should now be installed and activated.  The first time you visit
	management page, the appropriate tables will be created in the database
	if necessary.  In versions of WordPress greater than 2.7.0, the management
	page is located under the Tools menu on the left hand side.

4.	You may create new contact forms by using the controls found on the
	Tools > FormBuilder page.

5.	Once you have created one or more contact forms, you should see a dropdown
	list with your form names below the text editing area on the Write Post
	administrative page.

6.	If you need to modify the CSS in order to make forms display better on your
	site, you may add additional CSS information in the additional_styles.css
	file.  Styles in this file will cascade and over-ride the preset defaults.

7.	Have fun, enjoy using the FormBuilder plugin.

8.  For technical support and bleeding edge versions, find FormBuilder on GitHub:
    https://github.com/warkior/FormBuilder-Development

== Screenshots ==

1. The administration screen on a new FormBuilder install. 
2. Creating new forms.
3. Adding new form fields.

== Changelog ==

= 0.93 =
* Ownership Change: TruthMedia will no longer be maintaining this plugin. Ongoing development will be handled by James Warkentin.
* Better Email Handling: Switching forms to send from predefined email address, rather than from the visitor. This avoids many spam false positives and complies properly with new DMARK policy rules.
WARNING! This update will change how the email FROM address is created. You may adjust the default on the settings page.

= 0.92 =
* Cleanup: Cleaning up small bugs and deprecated code in more recent versions of WordPress.
* Bug Fix: Switched referrer field to populate using JS rather than PHP to allow better functionality on cached sites.

= 0.91 =
* Security Fix: Resolved XSS vulnerability with the referer functionality.

= 0.90 =
* Feature: Allow ~variable~ fields in email subject lines.  Generously contributed by maihde in github.
* Clean Up: Quite a few bug fixes and tidying changes generously contributed by outis in github.
* Bug Fix: Allow showing of thankyou text when using modules.

= 0.892 =
* Bug Fix: Additional minor bug fixes

= 0.891 =
* Bug Fix: Fixed warning that was appearing on pages.

= 0.89 =
* Feature: Allow ~variable~ fields in thankyou text.
* Feature: Option to show all fields in autoresponder.
* Feature: Allow admin bar to show all forms on the current page/index.
* Feature: Included new sample CSS for right-to-left languages.
* Bug Fix: Quotes in ThankYou text remain encoded which breaks HTML
* Bug Fix: Name/Email matching was too broad.
* Bug Fix: Fixed ability to create new forms.
* Bug Fix: Form search lost when switching pages.
* Bug Fix: Fixed forms not displaying / processing properly on some themes due to the_content being processed multiple times.
* Bug Fix: Forms with followup_url fields now bounce straight to the followup url without re-showing the original page first.


= 0.881 =
* Bug Fix: Catchable fatal error in FormBuilder repaired.

= 0.880 =
* Feature: Ability to search for forms.
* Feature: New field type: required checkbox.
* Feature: New field type: required password.
* Feature: Special field to capture logged in WordPress usernames.
* Feature: Ability to edit the form from the live site using a link in the admin bar.
* Feature: Ability to detect logged in WordPress users and pre-fill things like name and email.
* Bug Fix: Datestamp field typo fixed.
* Bug Fix: Repaired problem with showing thankyou text after XML email sending.

= 0.870 =
* Feature: New help text field added.
* Feature: Tags for forms.
* Feature: Paginated list of forms.
* Feature: Better internationalization support.
* Bug Fixing: Added more error information during the dreaded 'Form not saved' problem.

= 0.860 =
* Bug Fix: Fixed problem with DB_COLLATE and DB_CHARSET variables not being set.
* Bug Fix: Changed duplicate form checking method.
* Clean Up: Changed post-to-form attachment box to list forms available alphabetically.
* Clean Up: New forms will now be named 'A New Form' so as to appear at the top of the forms list.
* Clean Up: Creating a new form will automatically load the form editor.
* Clean Up: Updated for compatibility with WordPress 3.0.1.

= 0.852 =
* Bug Fix: Upgrade alert fixed.
* Bug Fix: Small REQUEST_URI problem fixed.

= 0.851 =
* Bug Fix: Issue with improper error processing when unable to do spammer IP checking.
* Bug Fix: Restored multi-language capabilities.
* Feature: Updated translation text to include latest features.

= 0.85 =
* Feature: New SYSTEM FIELD type. Allows assigning variables to the form without having them displayed on the form itself.  Like hidden fields, but not shown even in the HTML code.
* Feature: Re-send emails from db backup. Allows re-sending .
* Bug Fix: Small problem with session creation affecting confirmation email address checking.

= 0.84 =
* Feature: Enabled autodetection of forms to cut down on HTML bloat.
* Clean Up: Sorted field types and required field types alphabetically when editing forms.
* Bug Fix: Fixed CAPTCHA bug.
* Bug Fix: Removed requirement for field name on comments and page breaks.

= 0.83 (unstable) =
* Alphabetization of forms in list of forms on site.
* Release update wrapping up all previously developed functionality.

= 0.825 (unstable) =
* Feature: Better database export controls which should solve some of the timeout problems, as well as adding paginated form results and the ability to mass-delete database records.

= 0.824 (unstable) = 
* Overhaul: Complete overhaul of the javascript processing systems, replacing jQuery with a smaller, lighter library.

= 0.823 (unstable) = 
* Bug Fix: Major permissions problem prevented any FormBuilder access on upgrades and new installs.

= 0.822 (unstable) = 
* Feature: Spammer IP checking installed, checking IP's against http://www.stopforumspam.com/apis.
* Feature: New field type: unique id.
* Feature: New permissions system installed, allowing for form controls to be customized for certain user levels.
* Bug Fix: URL validation was only partially working.
* Bug Fix: Enabled better field name checking.

= 0.821 (unstable) = 
* Feature: Akismet spam checking.  Forms to be checked must have at least one 'name' required field and at least one 'email' required field.
* Feature: New required field type: 'name'  Essentially the same as 'any text' but used specifically for the Akismet spam checking.

= 0.82 =
* Feature: Added ability to export or delete specific forms from the XML backup database.
* Feature: Added ability to translate specific front-end strings without translating the whole application.
* Feature: Slight navigation and design reorganization for easier navigation.
* Feature: Updated alternate_action with more robust code checking for curl library first.
* Bug Fix: Fixed more Windows path related problems.
* Bug Fix: Enabled setting checkboxes, dropdowns and radio buttons as required fields.

= 0.81 =
* Feature: Configured FB to automatically scroll back to the location of the form on the page when submitted.
* Feature: Enabled ability to add Reset button to form if necessary.
* Feature: Added page, referrer and optional IP to XML Email module, as well as XML database storage.
* Feature: Enabled grey list checking based on moderation words found in the WordPress discussion options.
* Feature: Excessive link checking based on link limits found in the WordPress discussion options.
* Bug Fix: Allowed editors to export form results as CSV.
* Code Cleanup: Switch all code to use WordPress native database access model.

== LICENSE ==
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

== BETA SOFTWARE WARNING==
Please be aware as you use this software that it is still considered to be
BETA SOFTWARE and as such may function in unexpected ways.  Of course, we
do try our best to make sure it is as stable as possible and try to address
problems as quickly as possible when they come up, but just be aware that
there may still be bugs.

In the event that you DO experience any problems with this software, we would
like to hear about it and will do our best to fix the problem.  You can let us
know about bugs by commenting on [our blog](http://truthmedia.com/category/formbuilder/ "TruthMedia FormBuilder Blog")

