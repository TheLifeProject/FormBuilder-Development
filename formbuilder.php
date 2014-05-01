<?php
/*
Plugin Name: FormBuilder
Plugin URI: http://truthmedia.com/wordpress/formbuilder
Description: The FormBuilder plugin allows the administrator to create contact forms of a variety of types for use on their WordPress blog.  The FormBuilder has built-in spam protection and can be further protected by installing the Akismet anti-spam plugin.  Uninstall instructions can be found <a href="http://truthmedia.com/wordpress/formbuilder/documentation/uninstall/">here</a>.  Forms can be included on your pages and posts either by selecting the appropriate form in the dropdown below the content editing box, or by adding them directly to the content with [formbuilder:#] where # is the ID number of the form to be included.
Author: TruthMedia Internet Group
Version: 0.92
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
	
	define("FORMBUILDER_VERSION_NUM", "0.92");

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
	if( isset($GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI']) 
	AND $_SERVER['REQUEST_URI'] != $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'])
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
	define("FORMBUILDER_PLUGIN_URL", plugins_url() . '/' . rawurlencode(basename(dirname(__FILE__))) . '/');
  
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
	add_action('admin_bar_init', 'formbuilder_admin_bar_init');

	function formbuilder_admin_menu()
	{
		// Add admin management pages
		add_management_page(__('FormBuilder Management', 'formbuilder'), __('FormBuilder', 'formbuilder'), 'publish_posts', FORMBUILDER_FILENAME, 'formbuilder_options_page');

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
	
			if(session_id() == '') session_start();
			
			// Check to see if we have POST data to process.
			formbuilder_checkPOSTData();
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
	
	/**
	 * Determine if the current post has an attached form.
	 * @return int id of the form or false on no form.
	 * 
	 */
	function formbuilder_page_has_form()
	{
		global $post, $wpdb;
		
		$post_id = $post->ID;
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_PAGES . " " 
			 . "LEFT JOIN " . FORMBUILDER_TABLE_FORMS . " ON " . FORMBUILDER_TABLE_PAGES . ".form_id = " . FORMBUILDER_TABLE_FORMS . ".id "
			 . "WHERE post_id = '$post_id';";
			 
		$results = $wpdb->get_results($sql, ARRAY_A);
		if($results) return($results[0]);
		
		$form_ids = formbuilder_check_content($post->post_content);
		if(count($form_ids) > 0) return($form_id[0]);
		
		return false;
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

	/**
	 * Convert all applicable characters in each field of the array to htmlentities.
	 * @param array $slash_array
	 */
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
		$_GET += array('fbaction' => NULL);
		if(!$fb_permissions AND $_GET['fbaction'] != 'uninstall') 
		{
			$fb_permissions['level_10'] = array(
				'connect' => 'yes',
				'create' => 'yes',
				'manage' => 'yes'
			);
			
			$fb_permissions['level_7'] = array(
				'connect' => 'yes',
				'create' => 'yes',
				'manage' => 'no'
			);
			
			$fb_permissions['level_2'] = array(
				'connect' => 'yes',
				'create' => 'no',
				'manage' => 'no'
			);
			
			$fb_permissions['level_0'] = array(
				'connect' => 'no',
				'create' => 'no',
				'manage' => 'no'
			);
			
			update_option('formbuilder_permissions', $fb_permissions);
		}
	
		if(current_user_can('create_users'))
			$level = 'level_10';
		elseif(current_user_can('publish_pages'))
			$level = 'level_7';
		elseif(current_user_can('publish_posts'))
			$level = 'level_2';
		else
			$level = 'level_0';
		
		if(isset($fb_permissions[$level][$capability]) && $fb_permissions[$level][$capability] == 'yes')
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
			@$tmp[] = "$key=" . urlencode($q[$key]);
		
		foreach($add as $key=>$value)
			@$tmp[] = "$key=" . urlencode($value);
		
		// Create a new query string for the URL of the page to look at.
		$qvars = implode("&", $tmp);
		
		// Create the new URL for this page.
		$new_url = $url . '?' . $qvars;
		
		return($new_url);
	}
	
	/**
	 * Get a list of all forms on this page.
	 */
	function formbuilder_get_all_forms()
	{
		global $wp_query, $wpdb;
		
		$formIDs = array();
		
		if(isset($wp_query))
		{
		
			echo "TESTING FOR FORMS..";
			foreach($wp_query->posts as $post)
			{
				$content = $post->post_content;
				$content_form_ids = formbuilder_check_content($content);
			
				foreach($content_form_ids as $form_id)
				{
					$formIDs[] = $form_id['id'];
				}
				
				$post_id = $post->ID;
				
				$sql = "SELECT form_id FROM " . FORMBUILDER_TABLE_PAGES . " WHERE post_id = '$post_id';";
				$results = $wpdb->get_results($sql, ARRAY_A);
				
				if($results)
				{
					$page = $results[0];
					$formIDs[] = $page['form_id'];
				}
			}
		}
		
		var_dump($formIDs);
		
		if(count($formIDs) > 0)
		{
			$insert = implode(',', $formIDs);
			$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FORMS . " WHERE id IN ({$insert});";
			$results = $wpdb->get_results($sql, ARRAY_A);
			return($results);
		}
		else
		{
			return(array());
		}
	}
	
	/**
	 * Admin bar link.  Code from:
	 * http://www.problogdesign.com/wordpress/add-useful-links-to-wordpress-admin-bar/
	 */
	
	/**
	 * Adds links to the bar.
	 */
	function formbuilder_admin_bar_links() {
		global $wp_admin_bar, $formbuilder_formDisplayArray, $wpdb;
		
		// Only show if there is a form attached to the page.
		$formIDs = array();
		if(isset($formbuilder_formDisplayArray) AND is_array($formbuilder_formDisplayArray))
		{
			foreach($formbuilder_formDisplayArray as $formID=>$result)
			{
				$formIDs[] = $formID;
			}
		}
		
		if(count($formIDs) > 0)
		{
			$insert = implode(',', $formIDs);
			$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FORMS . " WHERE id IN ({$insert});";
			$forms = $wpdb->get_results($sql, ARRAY_A);

			if(count($forms) > 0)
			{
				// Add the Parent link.
				$wp_admin_bar->add_menu( array(
					'title' => 'Edit Form',
					'id' => 'formbuilder_forms'
				));
			}
			
			foreach($formIDs as $id)
			{
				foreach($forms as $form)
				{
					if($form['id'] != $id) continue;
					$url = get_admin_url(null, '/tools.php?page=formbuilder.php&fbaction=editForm&fbid=' . $form['id']);
					$wp_admin_bar->add_menu( array(
						'parent' => 'formbuilder_forms',
						'title' => $form['name'],
						'href' => $url,
						'id' => 'formbuilder_form_' . $form['id']
					));
				}
			}
		}
		
	}	

	
	/**
	 * Checks if we should add links to the bar.
	 */
	function formbuilder_admin_bar_init() {
		// Is the user sufficiently leveled, or has the bar been disabled?
		if (!is_super_admin() || !is_admin_bar_showing() )
			return;
	 
		// Good to go, lets do this!
		add_action('admin_bar_menu', 'formbuilder_admin_bar_links', 35);
	}
	
	
	
	