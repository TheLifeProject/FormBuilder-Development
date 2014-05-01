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

		add_action( 'save_post', 'formbuilder_save_options' );
		add_action( 'edit_post', 'formbuilder_save_options' );
		add_action( 'publish_post', 'formbuilder_save_options' );
		add_action( 'delete_post', 'formbuilder_delete_options' );

	function formbuilder_add_custom_box()
	{
		if(formbuilder_user_can('connect'))
		{
		// If we are capable of using meta boxes, use it.
		  if( function_exists( 'add_meta_box' )) {

		    add_meta_box( 'formbuilder_sectionid', __( 'FormBuilder', 'formbuilder_textdomain' , 'formbuilder'),
		                'formbuilder_post_options', 'post', 'normal', 'high' );

		    add_meta_box( 'formbuilder_sectionid', __( 'FormBuilder', 'formbuilder_textdomain' , 'formbuilder'),
		                'formbuilder_post_options', 'page', 'normal', 'high' );

		   } else {

	   		// Otherwise just use the old functions
			add_action( 'simple_edit_form', 'formbuilder_post_options' );
			add_action( 'edit_form_advanced', 'formbuilder_post_options' );
			add_action( 'edit_page_form', 'formbuilder_post_options' );

		  }
		}
	}





	/* Prints the inner fields for the custom post/page section */
	function formbuilder_inner_custom_box() {

	  // Use nonce for verification

	  echo '<input type="hidden" name="myplugin_noncename" id="myplugin_noncename" value="' .
	    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

	  // The actual fields for data entry

	  echo '<label for="myplugin_new_field">' . __("Description for this field", 'myplugin_textdomain' , 'formbuilder') . '</label> ';
	  echo '<input type="text" name="myplugin_new_field" value="whatever" size="25" />';
	}









	function formbuilder_post_options()
	{
		global $post, $wpdb;

		// Load the available forms
		$sql = "SELECT * FROM " . FORMBUILDER_TABLE_FORMS . " ORDER BY `name` ASC";
		$forms = $wpdb->get_results($sql, ARRAY_A);

		// If the post already has an id, determine whether or not there is a form already linked to it.
		if($post->ID)
		{
			// Determine if the post/page has a linked form.
			$sql = "SELECT * FROM " . FORMBUILDER_TABLE_PAGES . " WHERE `post_id` = '" . $post->ID . "';";
			$results = $wpdb->get_results($sql, ARRAY_A);
			if($results) {
				$pageDetails = $results[0];
			}
		}

		echo "<div id='formBuilderContactFormOptions'>\n" .
				"<p>" . __("If you wish to display a contact form that you have created using the FormBuilder plugin, please select it from the following options.", 'formbuilder') . "</p>\n" .
				"<select name='formbuilderFormSelection'>\n" .
				"<option value='noform'>" . __("Select Contact Form...", 'formbuilder') . "</option>\n";
		
		foreach($forms as $formDetails)
		{
			$form_id = $formDetails['id'];
			
			if($form_id == $pageDetails['form_id']) 
			{
				$selected = "selected";
				$form_data = $formDetails;
			}
			else 
				$selected = "";

			echo "<option value='$form_id' $selected>" . $formDetails['name'] . "</option>\n";
		}
		echo "</select>";
		
		if(isset($pageDetails) AND $pageDetails['form_id'] > 0)
		{
			$url = get_admin_url(null, '/tools.php?page=formbuilder.php&fbaction=editForm&fbid=' . $form_data['id']);
			echo "<br/><br/><strong>Edit This Form: <a href='{$url}'>" . $form_data['name'] . "</a></strong>";
		}
		
		echo "</div>\n";
		

	}

	function formbuilder_save_options($id)
	{
		global $wpdb;
		
		if(isset($_POST['formbuilderFormSelection']))
		{
		    if( !isset( $id ) )
		      $id = $_REQUEST[ 'post_ID' ];

			// Get any fb entries for the given page ID.
			$sql = "SELECT * FROM " . FORMBUILDER_TABLE_PAGES . " WHERE post_id = '" . $id . "';";
			$results = $wpdb->get_results($sql, ARRAY_A);
			
			// If page entries exist, 
			if($results)
			{
				$pageDetails = $results[0];
				$page = $pageDetails['id'];
			}
			
			if(!isset($pageDetails['form_id'])) $pageDetails['form_id'] = false;

			// Determine if the selected form ID is the same as the old form ID.
	    	if($_POST['formbuilderFormSelection'] != $pageDetails['form_id'])
	    	{
	    		if($_POST['formbuilderFormSelection'] == "noform")
	    		{	// The form was removed from the post, we should remove it completely from the db.
	    			if(isset($pageDetails['id']))
	    			{	// Only do this if we have an id... otherwise, we can assume the page wasn't in the table to begin with, and don't need to delete it.
	    				$sql = "DELETE FROM " . FORMBUILDER_TABLE_PAGES . " WHERE id = '" . $pageDetails['id'] . "' LIMIT 1;";
	    				$wpdb->query($sql);
	    			}
	    		}
	    		else
	    		{	// A form was added to the post.  Go ahead and add or modify it in the db.
	    			$pageDetails['post_id'] = addslashes($id);
	    			$pageDetails['form_id'] = addslashes($_POST['formbuilderFormSelection']);
	    			
					if(!$page)
					{
						$wpdb->insert(FORMBUILDER_TABLE_PAGES, $pageDetails);
					}
					else
					{
						$wpdb->update(FORMBUILDER_TABLE_PAGES, 
							$pageDetails, 
							array('id'=>$pageDetails['id'])
						);
					}
	    		}
	    	}
		}
	}
	function formbuilder_delete_options($id)
	{
		global $wpdb;
	    if( !isset( $id ) )
	      $id = $_REQUEST[ 'post_ID' ];

		$sql = "DELETE FROM " . FORMBUILDER_TABLE_PAGES . " WHERE post_id = '$id';";
		$wpdb->query($sql);
		
	}


?>