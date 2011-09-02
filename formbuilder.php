<?php
/*
Plugin Name: FormBuilder
Plugin URI: http://truthmedia.com/wordpress/formbuilder
Description: The FormBuilder plugin allows the administrator to create contact forms of a variety of types for use on their WordPress blog.  The FormBuilder has built-in spam protection and can be further protected by installing the Akismet anti-spam plugin.  Uninstall instructions can be found <a href="http://truthmedia.com/wordpress/formbuilder/documentation/uninstall/">here</a>.  Forms can be included on your pages and posts either by selecting the appropriate form in the dropdown below the content editing box, or by adding them directly to the content with [formbuilder:#] where # is the ID number of the form to be included.
Author: TruthMedia Internet Group
Version: 0.87
Author URI: http://truthmedia.com/


Created by the TruthMedia Internet Group
(website: truthmedia.com       email : editor@truthmedia.com)

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
	
	define("FORMBUILDER_VERSION_NUM", "0.87");

	// Define FormBuilder Related Tables
	global $table_prefix;
	define("FORMBUILDER_TABLE_FORMS", $table_prefix . "formbuilder_forms");
	define("FORMBUILDER_TABLE_FIELDS", $table_prefix . "formbuilder_fields");
	define("FORMBUILDER_TABLE_PAGES", $table_prefix . "formbuilder_pages");
	define("FORMBUILDER_TABLE_RESPONSES", $table_prefix . "formbuilder_responses");
	define("FORMBUILDER_TABLE_RESULTS", $table_prefix . "formbuilder_results");
	define("FORMBUILDER_TABLE_TAGS", $table_prefix . "formbuilder_tags");
	
	
	/*
	 Place the following in the wp-config.php file to force FB to remain
	 active.  You should only need to do this if you have added FB
	 to the template manually.
	 
	if(!defined("FORMBUILDER_IN_TEMPLATE"))
		define("FORMBUILDER_IN_TEMPLATE", true);
	 
	 */
	if(!defined("FORMBUILDER_IN_TEMPLATE"))
		define("FORMBUILDER_IN_TEMPLATE", false);
	
	/*
	 * Define this as 'true' in the wp-config.php file if you want formbuilder
	 * to hide the post body after a successful submission of the form.
	 * In this case, it will only show the thankyou text, or the redirect.
	 */
	if(!defined('FORMBUILDER_HIDE_POST_AFTER'))
		define('FORMBUILDER_HIDE_POST_AFTER', false);

	// Check to see if we have an accurate Request URI.  
	// Can help with certain apache configurations.
	if($_SERVER['REQUEST_URI'] != $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI']
		AND isset($GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI']) )
	{
		$_SERVER['REQUEST_URI'] = $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'];
	}

	// IIS compatibility
	if(!isset($_SERVER['REQUEST_URI'])) 
	{
		if(isset($_SERVER['PHP_SELF']))
			$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
		elseif(isset($_SERVER['SCRIPT_NAME']))
			$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
		else
			trigger_error("FormBuilder Server URL Problem.", E_USER_WARNING);

		if($_SERVER['QUERY_STRING'])
			$_SERVER['REQUEST_URI'] .=  '?'.$_SERVER['QUERY_STRING'];
	}
		
	//Dawnings' Fix to correct ABSPATH for Windows Machines
	$ColonSlash = substr(ABSPATH, 1, 2);	//If Windows, this may be from "C:\" or another letter
	$SlashSlash = substr(ABSPATH, 0, 2);	//If Windows, this could be "\\" for a network path
	$IfWin_ColSlash = ':\\';
	$IfWin_SlashSlash = '\\\\';

	//Okay, is this a Windows Path?
	if (($ColonSlash == $IfWin_ColSlash) || ($SlashSlash == $IfWin_SlashSlash) )
	{
		//Indeed, this is a Windows path. Correct ABSPATH to remove that trailing "/"
		$IS_WINDOWS = true;	//Handy for some other places

		$new_abs = substr(ABSPATH, 0, (strlen(ABSPATH)-1));
		$new_abs = str_replace("\\", "/", $new_abs);

	} else {
		//This looks like the typical case... Carry on as normal..
		$new_abs = ABSPATH;
		$IS_WINDOWS = false;	//Handy for some other places
	}
	
	if(substr($new_abs, -1, 1) != '/') $new_abs = $new_abs . '/';
	define ( "ABSOLUTE_PATH", $new_abs );

	// Define FormBuilder Paths and Directories
	define("FORMBUILDER_FILENAME", basename(__FILE__));
	
	define("FORMBUILDER_PLUGIN_KEY", basename(dirname(__FILE__)) . '/' . basename(__FILE__));
	
	

	if ($IS_WINDOWS) {
		$temp = str_replace(FORMBUILDER_FILENAME, "", __FILE__);
		$temp = str_replace("\\", "/", $temp);	//switch direction of slashes
		define("FORMBUILDER_PLUGIN_PATH", $temp);
	} else {
		define("FORMBUILDER_PLUGIN_PATH", str_replace(FORMBUILDER_FILENAME, "", __FILE__));
	}
	
	// Pre-2.6 compatibility
	if ( ! defined( 'WP_CONTENT_URL' ) )
	      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
	if ( ! defined( 'WP_CONTENT_DIR' ) )
	      define( 'WP_CONTENT_DIR', ABSOLUTE_PATH . 'wp-content' );
	if ( ! defined( 'WP_PLUGIN_URL' ) )
	      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
	if ( ! defined( 'WP_PLUGIN_DIR' ) )
	      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

	// Determine whether we're in HTTPS mode or not, and change URL's accordingly.
	if(isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on') 
	{
		define('FORMBUILDER_SITE_URL', str_replace('http://', 'https://', get_bloginfo('url')));
		define('FORMBUILDER_BLOG_URL', str_replace('http://', 'https://', get_bloginfo('wpurl')));
	}
	else 
	{
		define('FORMBUILDER_SITE_URL', get_bloginfo('url'));
		define('FORMBUILDER_BLOG_URL', get_bloginfo('wpurl'));
	}
	define("FORMBUILDER_PLUGIN_URL", FORMBUILDER_BLOG_URL . str_replace(ABSOLUTE_PATH, "/", FORMBUILDER_PLUGIN_PATH));
  
	// Define Regular Expressions used throughout.
	define("FORMBUILDER_CONTENT_TAG", '\[ *formbuilder *\: *([0-9]+) *\]');
	define("FORMBUILDER_PATTERN_EMAIL", '^[A-Za-z0-9\._-]+@[A-Za-z0-9\._-]+\.[a-zA-Z]+$');
	define("FORMBUILDER_PATTERN_PHONE", '^[0-9 \(\)\-]+$');
	
	// Require all necessary files.
	require_once(FORMBUILDER_PLUGIN_PATH . "requiredFiles.php");

	// Set default for JS.
	$fb_do_js_manually = false;

	// Global Filters and Actions
	add_filter('template_redirect', 'formbuilder_init');

	// Admin Specific Filters and Actions
	add_action('admin_menu', 'formbuilder_admin_menu');
	add_action('admin_menu', 'formbuilder_add_custom_box');

	function formbuilder_admin_menu()
	{
		// Add admin management pages
		add_management_page(__('FormBuilder Management', 'formbuilder'), __('FormBuilder', 'formbuilder'), 2, FORMBUILDER_FILENAME, 'formbuilder_options_page');

		// Additional Filters and Actions
		add_filter('admin_head', 'formbuilder_admin_head');
		add_action('activate_formbuilder/formbuilder.php', 'formbuilder_activation');
		
		if(!session_start())
			die("FormBuilder admin can't start session.");

		$plugin_dir = basename(dirname(__FILE__));
		$lang_dir = $plugin_dir . '/lang';
		load_plugin_textdomain( 'formbuilder', 'wp-content/plugins/' . $lang_dir, $lang_dir );
	}

	function formbuilder_admin_head() {
		// Include additional function for admin system.
		include_once(FORMBUILDER_PLUGIN_PATH . "php/formbuilder_admin_functions.php");
		include_once(FORMBUILDER_PLUGIN_PATH . "php/formbuilder_admin_pages.inc.php");
		
		// Display the admin related CSS
		formbuilder_admin_css();
	}

	//
	function formbuilder_plugin_notice( $plugin ) {
		$version = get_option('formbuilder_version');
		if($version != FORMBUILDER_VERSION_NUM)
		{
		 	if( $plugin == FORMBUILDER_PLUGIN_KEY && function_exists( "admin_url" ) )
				echo '<td colspan="5" class="formbuilder-plugin-update">FormBuilder must be configured. Go to <a href="' . admin_url( 'tools.php?page=formbuilder.php' ) . '">the admin page</a> to enable and configure the plugin.</td>';
		}
	}
	add_action( 'after_plugin_row', 'formbuilder_plugin_notice' );
	
	function formbuilder_plugin_links( $links, $file ) {
		if( $file == FORMBUILDER_PLUGIN_KEY && function_exists( "admin_url" ) ) {
			$manage_link = '<a href="' . admin_url( 'tools.php?page=formbuilder.php' ) . '">' . __('Manage') . '</a>';
			array_unshift( $links, $manage_link );
		}
		return $links;
	}
	add_filter( 'plugin_action_links', 'formbuilder_plugin_links', 10, 2 );
	



	/**
	 * FormBuilder initialization function.  Set's up javascripts as well as determining what components to activate.
	 * @return unknown_type
	 */
	function formbuilder_init() {
		global $fb_do_js_manually, $wp_version;
		$plugin_dir = basename(dirname(__FILE__));
		$lang_dir = $plugin_dir . '/lang';
		load_plugin_textdomain( 'formbuilder', 'wp-content/plugins/' . $lang_dir, $lang_dir );
		
		if(fb_is_active())
		{

			add_filter('the_content', 'formbuilder_main');
			add_filter('the_content_rss', 'formbuilder_strip_content');
			add_filter('the_excerpt', 'formbuilder_strip_content');
			add_filter('the_excerpt_rss', 'formbuilder_strip_content');
		
			add_filter('wp_head', 'formbuilder_css');
	
			if(function_exists('wp_enqueue_script') AND $wp_version >= '2.6')
			{
				wp_enqueue_script( 
					'jx_compressed.js', 
					FORMBUILDER_PLUGIN_URL . 'js/jx_compressed.js', 
					array(), 
					FORMBUILDER_VERSION_NUM
				);
				
				wp_enqueue_script( 
					'formbuilder_js', 
					FORMBUILDER_PLUGIN_URL . 'js/compat-javascript.js', 
					array(), 
					FORMBUILDER_VERSION_NUM
				);
			}
			else
			{
				$fb_do_js_manually = true;
			}
	
			session_start();
		}

	}
	
	/**
	 * Function to determine whether FB should run or not.
	 * @return unknown_type
	 */
	function fb_is_active()
	{
		global $wp_query, $wpdb, $FB_ACTIVE;
		if($FB_ACTIVE == true) return(true);
		
		// Allows placing of a constant variable in the wp-config.php file
		// named FORMBUILDER_IN_TEMPLATE in order to force FB to remain active.
		if(defined("FORMBUILDER_IN_TEMPLATE") 
		AND FORMBUILDER_IN_TEMPLATE == true) return(true);
		
		// Detect whether or not there are forms to be displayed on the page.
		$FB_ACTIVE = false;
		$results = $wpdb->get_results("SELECT * FROM " . FORMBUILDER_TABLE_PAGES, ARRAY_A);
		if($results) 
		{
			foreach($results as $formPage)
			{
				$formPages[$formPage['post_id']] = $formPage['form_id'];
			}
		}
		
		foreach($wp_query->posts as $p)
		{
			if(isset($formPages[$p->ID]) 
			OR formbuilder_check_content($p->post_content)) {
				$FB_ACTIVE = true;
				return(true);
			}
		}
		
		return(false);
	}

	function formbuilder_main($content = '') {
		global $post, $_SERVER, $wpdb;

		$version = get_option('formbuilder_version');
		if(!$version) return($content);

		$module_status = false;

		if($post->post_password != '' AND strpos($content, 'wp-pass.php')) return($content);


		// Check to determine whether or not we have a form manually entered into the content of the post
		// Manual entries in the form of [formbuilder:5] where 5 is the ID of the form to be displayed.
		$content_form_ids = formbuilder_check_content($content);

		foreach($content_form_ids as $form_id)
		{
			$formDisplay = formbuilder_process_form($form_id['id']);
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

				$formDisplay = formbuilder_process_form($page['form_id']);
			
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

	function formbuilder_get_hash()
	{
		// Create a hash for the given form, based on the information submitted, combined with the IP address of the submitter.
		// Should provide basic protection against the same person submitting the same form repeatedly.

		if(isset($_POST['formBuilderForm'])) {
			$hash = $_SERVER['REMOTE_ADDR'] . "|" . $_SERVER['REQUEST_URI'] . "|";
			$hash .= htmlentities(serialize($_POST));
			return($hash);
		}

		return(false);
	}

	function formbuilder_css()
	{
		?>
		<!-- FORMBUILDER CSS CUSTOMIZATION -->
		<?php
		$custom_css = get_option('formBuilder_custom_css');
		$relative_path = FORMBUILDER_PLUGIN_URL;

		// Only load the custom css if it is enabled.
		if($custom_css == "Enabled")
		{
			$css_path = FORMBUILDER_PLUGIN_URL . "css/formbuilder_styles.css";
			?>
			<link rel='stylesheet' href='<?php echo $css_path; ?>' type='text/css' media='all' />
			<?php
		}


		// Load any additional css needed for the site.
		if(file_exists(WP_CONTENT_DIR . "/additional_styles.css"))
		{
			$css_path = WP_CONTENT_URL . "/additional_styles.css";
			?>
			<!-- ADDITIONAL CSS CUSTOMIZATION -->
			<link rel='stylesheet' href='<?php echo $css_path; ?>' type='text/css' media='all' />
			<!-- END ADDITIONAL CSS CUSTOMIZATION -->
			<?php
		}
		elseif(file_exists(FORMBUILDER_PLUGIN_PATH . "additional_styles.css"))
		{
			$css_path = FORMBUILDER_PLUGIN_URL . "additional_styles.css";
			?>
			<!-- ADDITIONAL CSS CUSTOMIZATION -->
			<link rel='stylesheet' href='<?php echo $css_path; ?>' type='text/css' media='all' />
			<!-- END ADDITIONAL CSS CUSTOMIZATION -->
			<?php
		}

		// Load the spam blocker css regardless of custom options.
		$spam_blocker = get_option('formbuilder_spam_blocker');
		?>
		<style type='text/css' media='screen'>
		.<?php echo $spam_blocker; ?> {
			visibility: hidden;
			padding: 0;
			margin: 0;
			border: 0;
			position: absolute;
		}
		</style>
		<!-- END FORMBUILDER CSS CUSTOMIZATION -->
	<?php
	
		// Determine if we need to load the javascript components manually or not.
	
		global $fb_do_js_manually;
		if($fb_do_js_manually)
		{
			?>
			<script type='text/javascript' src='<?php echo FORMBUILDER_PLUGIN_URL; ?>js/jx_compressed.js'></script>
			<script type='text/javascript' src='<?php echo FORMBUILDER_PLUGIN_URL; ?>js/compat-javascript.js'></script>
			<?php
		}

	
	}

	// This function should take any string of text and convert it to a readable variable name.
	function clean_field_name($text)
	{
		$text = str_replace(" ", "_", $text);
		$text = eregi_replace("[^a-z0-9_]", "", $text);
		return($text);
	}

	
	function formbuilder_array_htmlentities($slash_array = array())
	{
		if(!$slash_array) return($slash_array);
		foreach($slash_array as $key=>$value)
		{
			if(is_array($value))
			{
				$slash_array[$key] = formbuilder_array_htmlentities($value);
			}
			else
			{
				$slash_array[$key] = htmlentities($value, ENT_QUOTES, get_option('blog_charset'));
			}
		}
		return($slash_array);
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



	// Function to display and process the actual form.
	function formbuilder_process_form($form_id, $data=false)
	{
		global $wpdb;
		
		$formBuilderTextStrings = formbuilder_load_strings();
		
		$siteurl = get_option('siteurl');
		$relative_path = str_replace(ABSOLUTE_PATH, "/", FORMBUILDER_PLUGIN_PATH);
		$page_path = $siteurl . $relative_path;

		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FORMS . " WHERE id='$form_id';";
		$results = $wpdb->get_results($sql, ARRAY_A);
		if($results) $form = $results[0];
		
		if(!$form) return("");
		$form = formbuilder_array_htmlentities($form);
		$allFields = array();

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


			if(is_string($module_status))
				$formDisplay .= $module_status;

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
					
					// Fill unset POST vars with empty strings. 
					if(!isset($_POST['formBuilderForm'][$field['field_name']])) $_POST['formBuilderForm'][$field['field_name']] = "";

					// Determine what submitted value to give to the field values. 
					if($field['field_type'] == 'system field')
					{
						// Manually assign value to system fields before anything else.
						$field['value'] = $field['field_value'];
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
						// In this case, there is neither a POST nor a GET value, therefore we assign the field value to be whatever the default value was for the field.
						$field['value'] = $field['field_value'];
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
							if(!$calendar_loaded) 
							{
								$calendar_loading_code = "<script src=\"" . $page_path . "js/calendar.js\" type=\"text/javascript\"></script>";
								$calendar_loaded = true;
							}
							else
							{
								$calendar_loading_code = "";
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

						default:
							$formLabel = "<div class='$formLabelCSS'>" . decode_html_entities($field['field_label'], ENT_NOQUOTES, get_option('blog_charset')) . " </div>";
							$formInput = "<div class='formBuilderInput'><input type='text' "
																			. "name='" . $field['name'] . "' "
																			. "value='" . $field['value'] . "' "
																			. "id='field$divID' "
																			. "onblur=\"fb_ajaxRequest('" . $page_path . "php/formbuilder_parser.php', 'formid=" . $form['id'] . "&amp;fieldid=" . $field['id'] . "&amp;val='+document.getElementById('field$divID').value, 'formBuilderErrorSpace$divID')\"/> $formHelpJava</div>";
						break;
					}
					
					if($field['field_type'] != 'system field')
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

				if(!isset($func_run))
				{
					if(!$msg)
					{
						if(!$form['thankyoutext']) $form['thankyoutext'] = "<h4>" . $formBuilderTextStrings['success'] . "</h4><p>" . $formBuilderTextStrings['send_success'] . "</p>";
						$formDisplay = "\n<div class='formBuilderSuccess'>" . decode_html_entities($form['thankyoutext'], ENT_NOQUOTES, get_option('blog_charset')) . "</div>";
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
		
		$sql = "INSERT INTO " . FORMBUILDER_TABLE_RESULTS . " (`form_id`, `timestamp`, `xmldata`) " .
				"VALUES ('" . $form['id'] . "', '" . time() . "', '" . addslashes($xml) . "');";
		
		if($wpdb->query($sql) === false) 
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

			// Add the followup page redirect, if it exists.
			if($field['field_type'] == "followup page" AND trim($field['field_value']) != "")
				echo "<meta HTTP-EQUIV='REFRESH' content='0; url=" . $field['field_value'] . "'>";


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
			foreach($field_values as $key=>$value)
			{
				$response_details['subject'] = str_replace("~" . $key . "~", $value, $response_details['subject']);
				$response_details['message'] = str_replace("~" . $key . "~", $value, $response_details['message']);
			}
			
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
	








	// Function to return a list of form ID's located inside the content.  Also returns the tag related to each, in order that they can be replaced with the actual form content.
	function formbuilder_check_content($content) {
		$form_ids = array();
		$counter = 0;
		$tmp = $content;

		while(eregi(FORMBUILDER_CONTENT_TAG, $tmp, $regs)) {
			$form_ids[$counter]['id'] = trim($regs[1]);
			$form_ids[$counter]['tag'] = $regs[0];
			$tmp = str_replace($regs[0], "", $tmp);
			$counter++;
		}

		return($form_ids);
	}

	// Function to strip all form tags from any content.
	function formbuilder_strip_content($content) {
		while(eregi(FORMBUILDER_CONTENT_TAG, $content, $regs)) {
			$content = str_replace($regs[0], "", $content);
		}
		return($content);
	}

	// Function to decode HTML entities when necessary, if PHP5 is installed.
	function decode_html_entities($string, $quote_style = 0, $charset = 'ISO-8859-1')
	{
		if(!($return = @html_entity_decode($string, $quote_style, $charset)))
		{
			$return = @html_entity_decode($string, $quote_style);
		}
		return($return);
	}
	
	function formbuilder_on_greylist($content)
	{
		$mod_keys = trim(get_option('moderation_keys'));
		if ( !empty($mod_keys) ) {
			$words = explode("\n", $mod_keys );
	
			foreach ( (array) $words as $word) {
				$word = trim($word);
	
				// Skip empty lines
				if ( empty($word) )
					continue;
	
				// Do some escaping magic so that '#' chars in the
				// spam words don't break things:
				$word = preg_quote($word, '#');
	
				$pattern = "#$word#i";
				if ( preg_match($pattern, $content) ) return true;
			}
		}
		return(false);
	}
	
	function formbuilder_on_blacklist($content)
	{
		$mod_keys = trim( get_option('blacklist_keys') );
		if ( '' == $mod_keys )
			return false; // If moderation keys are empty
		$words = explode("\n", $mod_keys );
	
		foreach ( (array) $words as $word ) {
			$word = trim($word);
	
			// Skip empty lines
			if ( empty($word) ) { continue; }
	
			// Do some escaping magic so that '#' chars in the
			// spam words don't break things:
			$word = preg_quote($word, '#');
	
			$pattern = "#$word#i";
			if ( preg_match($pattern, $content) ) return true;
		}
		return false;
	}
	
	function formbuilder_excessive_links($content)
	{
		$maxLinks = get_option('comment_max_links');
		
		if ( $maxLinks )
		{
			$links_found = preg_match_all("/<a[^>]*href[^=]*=/i", $content, $out);
			
			if($links_found > $maxLinks)
				return true;
		}
		return(false);
	}
	
	function formbuilder_check_spammer_ip($ip)
	{
		$url = "http://www.stopforumspam.com/api?ip=" . $ip;
		$response = wp_remote_request($url);
		
		if(is_array($response))
		{
			$xml = $response['body'];
			return(preg_match('|<response success="true".+<appears>yes</appears>|isu', $xml));
		}
		else
		{
			return(0);
		}
	}
	
	function formbuilder_check_akismet($allFields = array())
	{
		// Code largely taken from Akismet WordPress plugin.
		global $wpdb, $akismet_api_host, $akismet_api_port, $wpcom_api_key;
		if(!function_exists('akismet_http_post') OR !$wpcom_api_key) return(false);
		
		$c = array();

		$ignore = array( 'HTTP_COOKIE' );
		foreach ( $_SERVER as $key => $value )
			if ( !in_array( $key, $ignore ) )
				$c["$key"] = $value;

		$c['user_ip']    = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
		$c['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$c['referrer']   = $_SERVER['HTTP_REFERER'];
		$c['permalink']   = get_permalink();
		$c['comment_type']   = 'comment form';
		$c['blog']       = get_option('home');
		$c['blog_lang']  = get_locale();
		$c['blog_charset'] = get_option('blog_charset');
		
		$c['comment_content'] = '';
		foreach($allFields as $key=>$field)
		{
			if(!$c['comment_author'] AND (strtolower($field['field_name']) == 'name' OR $field['required_data'] == "name"))
			{
				$c['comment_author'] = $field['value'];
			}
			
			if(!$c['comment_author_email'] AND (strtolower($field['field_name']) == 'email' OR $field['required_data'] == "email address"))
			{
				$c['comment_author_email'] = $field['value'];
			}
			
			if($field['field_type'] == 'small text area' 
			OR $field['field_type'] == 'large text area')
			{
				$c['comment_content'] .= $field['value'] . "\n";
			}
		}
		$c['comment_content'] = trim($c['comment_content']);
		
		if(!$c['comment_author'] OR !$c['comment_author_email'] OR !$c['comment_content']) return(false);
		
#		echo "<pre>Query Vars: "; print_r($c); echo "<br/></pre>";
		$query_string = '';
		foreach ( $c as $key => $data )
		$query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';
		
#		echo "<pre>Query: $query_string<br/>\n</pre>";
	
		$response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
#		echo "<pre>Response: "; print_r($response); echo "<br/></pre>";
		return $response[1];
	}
	
	/**
	 * Function to load many of the text strings used in FB
	 * @return unknown_type
	 */
	function formbuilder_load_strings()
	{

		// Text strings displayed to visitors
		$formBuilderTextStrings = get_option('formbuilder_text_strings');
		if(!$formBuilderTextStrings)
		{
			$formBuilderTextStrings['form_problem'] = __("Form Problem:", 'formbuilder');
			$formBuilderTextStrings['already_submitted'] = __("You have already submitted this form data once.", 'formbuilder');
			$formBuilderTextStrings['captcha_cookie_problem'] = __("The CAPTCHA field below may not work due to cookies being disabled in your browser.  Please turn on cookies in order to fill out this form.", 'formbuilder');
			$formBuilderTextStrings['captcha_unavailable'] = __("Captcha functionality unavailable.  Please inform the website administrator.", 'formbuilder');
			$formBuilderTextStrings['previous'] = __('Previous', 'formbuilder');
			$formBuilderTextStrings['next'] = __('Next', 'formbuilder');
			$formBuilderTextStrings['send'] = __("Send!", 'formbuilder');
			$formBuilderTextStrings['success'] = __("Success!", 'formbuilder');
			$formBuilderTextStrings['failed'] = __("Failed!", 'formbuilder');
			$formBuilderTextStrings['send_success'] = __("Your message has been sent successfully.", 'formbuilder');
			$formBuilderTextStrings['send_failed'] = __("Your message has NOT been sent successfully.", 'formbuilder');
			$formBuilderTextStrings['send_mistakes'] = __("You seem to have missed or had mistakes in the following required field(s).", 'formbuilder');
			$formBuilderTextStrings['display_error'] = __("ERROR!  Unable to display form!", 'formbuilder');
			$formBuilderTextStrings['storage_error'] = __("Error: Form processing failure.  Unable to store the form data in the database.", 'formbuilder');
			$formBuilderTextStrings['bad_alternate_email'] = __("* It looks like an alternate destination_email field was defined for this form, but the email address it contained was invalid", 'formbuilder');
			$formBuilderTextStrings['hack_to'] = __("TO Header Injection Detected!", 'formbuilder');
			$formBuilderTextStrings['hack_subject'] = __("SUBJECT Header Injection Detected!", 'formbuilder');
			$formBuilderTextStrings['mail_error_default'] = __("Mail server error.  Unable to send email.  Try switching FormBuilder to use Alternate Email Handling on the main configuration page.", 'formbuilder');
			$formBuilderTextStrings['mail_error_alternate'] = __("Mail server error.  Unable to send email using the built-in WordPress mail controls.  ", 'formbuilder');
		}
		
		return($formBuilderTextStrings);
	}
	
	function formbuilder_user_can($capability)
	{
		$fb_permissions = get_option('formbuilder_permissions');
		
		if(!$fb_permissions AND $_GET['fbaction'] != 'uninstall') 
		{
			$fb_permissions[level_10] = array(
				'connect' => 'yes',
				'create' => 'yes',
				'manage' => 'yes'
			);
			
			$fb_permissions[level_7] = array(
				'connect' => 'yes',
				'create' => 'yes',
				'manage' => 'no'
			);
			
			$fb_permissions[level_2] = array(
				'connect' => 'yes',
				'create' => 'no',
				'manage' => 'no'
			);
			
			update_option('formbuilder_permissions', $fb_permissions);
		}
	
		if(current_user_can('level_10'))
			$level = 'level_10';
		elseif(current_user_can('level_7'))
			$level = 'level_7';
		elseif(current_user_can('level_2'))
			$level = 'level_2';
		else
			$level = 'level_0';
		
		if($fb_permissions[$level][$capability] == 'yes')
			return(true);
		else
			return(false);
	}

	/**
	 * Get a paginated navigation bar
	 * 
	 * This function will create and return the HTML for a paginated navigation bar
	 * based on the total number of results passed in $num_results, and the value 
	 * found in $_GET['pageNumber'].  The programmer simply needs to call this function
	 * with the appropriate value in $num_results, and use the value in $_GET['pageNumber']
	 * to determine which results should be shown.
	 * Creates a list of pages in the form of:
	 * 1 .. 5 6 7 .. 50 51 .. 100
	 * (in this case, you would be viewing page 6)
	 * 
	 * @global   int     $_GET['pageNumber'] is the current page of results being displayed.
	 * @param    int     $num_results is the total number of results to be paged through.
	 * @param    int     $num_per_page is the number of results to be shown per page.
	 * @param    bool    $show set to true to write output to browser.
	 * 
	 * @return   string  Returns the HTML code to display the nav bar. 
	 * 
	 */
	function fb_get_paged_nav($num_results, $num_per_page=10, $show=false)
	{
	    // Set this value to true if you want all pages to be shown,
	    // otherwise the page list will be shortened.
	    $full_page_list = false; 
	        
	    // Get the original URL from the server.
	    $url = $_SERVER['REQUEST_URI'];
	    
	    // Initialize the output string.
	    $output = '';
	    
	    // Remove query vars from the original URL.
	    if(preg_match('#^([^\?]+)(.*)$#isu', $url, $regs))
	        $url = $regs[1];
	    
	    // Shorten the get variable.
	    $q = $_GET;
	    
	    // Determine which page we're on, or set to the first page.
	    if(isset($q['pageNumber']) AND is_numeric($q['pageNumber'])) $page = $q['pageNumber'];
	    else $page = 1;
	    
	    // Determine the total number of pages to be shown.
	    $total_pages = ceil($num_results / $num_per_page);
	    
	    // Begin to loop through the pages creating the HTML code.
	    for($i=1; $i<=$total_pages; $i++)
	    {
	        // Assign a new page number value to the pageNumber query variable.
	        $q['pageNumber'] = $i;
	        
	        // Initialize a new array for storage of the query variables.
	        $tmp = array();
	        foreach($q as $key=>$value)
	            $tmp[] = "$key=$value";
	        
	        // Create a new query string for the URL of the page to look at.
	        $qvars = implode("&amp;", $tmp);
	        
	        // Create the new URL for this page.
	        $new_url = $url . '?' . $qvars;
	        
	        // Determine whether or not we're looking at this page.
	        if($i != $page)
	        {
	            // Determine whether or not the page is worth showing a link for.
	            // Allows us to shorten the list of pages.
	            if($full_page_list == true
	                OR $i == $page-1
	                OR $i == $page+1
	                OR $i == 1
	                OR $i == $total_pages
	                OR $i == floor($total_pages/2)
	                OR $i == floor($total_pages/2)+1
	                )
	                {
	                    $output .= "<a href='$new_url'>$i</a> ";
	                }
	                else
	                    $output .= '. ';
	        }
	        else
	        {
	            // This is the page we're looking at.
	            $output .= "<strong>$i</strong> ";
	        }
	    }
	    
	    // Remove extra dots from the list of pages, allowing it to be shortened.
	    $output = ereg_replace('(\. ){2,}', ' .. ', $output);
	    
	    // Determine whether to show the HTML, or just return it.
	    if($show) echo $output;
	    
	    return($output);
	}
	
		/**
	 * Build a url based on permitted query vars passed to the function.
	 * 
	 * @param $add array containing query vars to add to the query request.
	 * @param $qvars array containing query vars to keep from the old query request.
	 * 
	 * @return string containing the new URL
	 */
	function formbuilder_build_url($add = array(), $qvars = array())
	{
		// Get the original URL from the server.
		$url = $_SERVER['REQUEST_URI'];
		
		// Remove query vars from the original URL.
		if(preg_match('#^([^\?]+)(.*)$#isu', $url, $regs))
		$url = $regs[1];
		
		// Shorten the get variable.
		$q = $_GET;
		
		// Initialize a new array for storage of the query variables.
		$tmp = array();
		foreach($qvars as $key)
			$tmp[] = "$key=" . urlencode($q[$key]);
		
		foreach($add as $key=>$value)
			$tmp[] = "$key=" . urlencode($value);
		
		// Create a new query string for the URL of the page to look at.
		$qvars = implode("&", $tmp);
		
		// Create the new URL for this page.
		$new_url = $url . '?' . $qvars;
		
		return($new_url);
	}