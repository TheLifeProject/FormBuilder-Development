<?php formbuilder_admin_nav('strings'); 
#echo "Strings: <pre>"; print_r($formBuilderTextStrings); echo "</pre>";
 ?>
<fieldset class="options metabox-holder">

	<div class="info-box-formbuilder postbox">
		<h3 class="info-box-title hndle"><?php _e('Translatable Strings', 'formbuilder'); ?></h3>
		<div class="inside">
		<p><?php _e('In some cases it can be useful to have text strings that appear on the site show up in another language without having to translate the entire WordPress interface.  This page allows you to translate or change all text that isn\'t form specific displayed to the visitor when filling out forms on the site.', 'formbuilder'); ?></p>
		
		<form action="" method="POST">
		<table class="widefat">
			<tr valign="top">
				<th><?php _e('Text String', 'formbuilder'); ?></th>
				<th><?php _e('Translated String', 'formbuilder'); ?></th>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Form Problem:", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='form_problem' style='width: 100%;'><?php echo $formBuilderTextStrings['form_problem']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("You have already submitted this form data once.", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='already_submitted' style='width: 100%;'><?php echo $formBuilderTextStrings['already_submitted']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("The CAPTCHA field below may not work due to cookies being disabled in your browser.  Please turn on cookies in order to fill out this form.", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='captcha_cookie_problem' style='width: 100%;'><?php echo $formBuilderTextStrings['captcha_cookie_problem']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Captcha functionality unavailable.  Please inform the website administrator.", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='captcha_unavailable' style='width: 100%;'><?php echo $formBuilderTextStrings['captcha_unavailable']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e('Previous', 'formbuilder'); ?></td>
				<td width="50%"><textarea name='previous' style='width: 100%;'><?php echo $formBuilderTextStrings['previous']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e('Next', 'formbuilder'); ?></td>
				<td width="50%"><textarea name='next' style='width: 100%;'><?php echo $formBuilderTextStrings['next']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Send!", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='send' style='width: 100%;'><?php echo $formBuilderTextStrings['send']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Success!", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='success' style='width: 100%;'><?php echo $formBuilderTextStrings['success']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Failed!", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='failed' style='width: 100%;'><?php echo $formBuilderTextStrings['failed']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Your message has been sent successfully.", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='send_success' style='width: 100%;'><?php echo $formBuilderTextStrings['send_success']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Your message has NOT been sent successfully.", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='send_failed' style='width: 100%;'><?php echo $formBuilderTextStrings['send_failed']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("You seem to have missed or had mistakes in the following required field(s).", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='send_mistakes' style='width: 100%;'><?php echo $formBuilderTextStrings['send_mistakes']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("ERROR!  Unable to display form!", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='display_error' style='width: 100%;'><?php echo $formBuilderTextStrings['display_error']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Error: Form processing failure.  Unable to store the form data in the database.", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='storage_error' style='width: 100%;'><?php echo $formBuilderTextStrings['storage_error']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("* It looks like an alternate destination_email field was defined for this form, but the email address it contained was invalid", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='bad_alternate_email' style='width: 100%;'><?php echo $formBuilderTextStrings['bad_alternate_email']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("TO Header Injection Detected!", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='hack_to' style='width: 100%;'><?php echo $formBuilderTextStrings['hack_to']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("SUBJECT Header Injection Detected!", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='hack_subject' style='width: 100%;'><?php echo $formBuilderTextStrings['hack_subject']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Mail server error.  Unable to send email.  Try switching FormBuilder to use Alternate Email Handling on the main configuration page.", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='mail_error_default' style='width: 100%;'><?php echo $formBuilderTextStrings['mail_error_default']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Mail server error.  Unable to send email using the built-in WordPress mail controls.  ", 'formbuilder'); ?></td>
				<td width="50%"><textarea name='mail_error_alternate' style='width: 100%;'><?php echo $formBuilderTextStrings['mail_error_alternate']; ?></textarea></td>
			</tr>
			
			<tr valign="top">
				<td width="50%"><?php _e("Reset all text strings to original English values.", 'formbuilder'); ?></td>
				<td width="50%"><input type="checkbox" name="formbuilder_reset_all_text_strings" value="yes" /></td>
			</tr>
			
			<tr valign="top">
				<td width="50%">&nbsp;</td>
				<td width="50%"><input type="submit" name="Submit" value="<?php _e('Save', 'formbuilder'); ?>" /></td>
			</tr>
			
		</table>
		</form>
		</div>
	</div>
	
	<div class='clear' />

</fieldset>
