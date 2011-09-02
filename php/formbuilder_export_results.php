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
	$basestart = strpos(__FILE__, 'wp-content');
	$basepath = substr(__FILE__, 0, $basestart);
	$wp_load = $basepath . 'wp-load.php';
	$wp_conf = $basepath . 'wp-config.php';

	if(file_exists($wp_load)) include_once($wp_load);
	elseif(file_exists($wp_conf)) include_once($wp_conf);
	else die("Unable to include WordPress configuration files.");

	// Ensure that only editors or higher can access this page.
	get_currentuserinfo() ;
	global $user_level;

	if ($user_level >= 7 OR $userdata->wp_capabilities['administrator'] == 1) {
		include(FORMBUILDER_PLUGIN_PATH . "extensions/formbuilder_xml_db_results.class.php");
		if(!isset($fb_xml_stuff)) $fb_xml_stuff = new formbuilder_xml_db_results();
		$fb_xml_stuff->export_csv();
	}
	else
		die(__("You must be logged in as an editor or higher to access this page.", 'formbuilder'));
	
?>