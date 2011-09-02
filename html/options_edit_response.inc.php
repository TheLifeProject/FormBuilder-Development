<?php formbuilder_admin_nav('edit response'); ?>
		<form name="form1" method="post" class="formBuilderForm formBuilderAdminForm" action="<?php echo FB_ADMIN_PLUGIN_PATH; ?>&fbaction=editResponse&fbid=<?php echo $response_id; ?>">
			<h3 class="info-box-title"><?php _e('Current Autoresponses', 'formbuilder'); ?></h3>
			<fieldset class="options">
				<p><?php _e('You may use these controls to modify an autoresponse on your blog.  When linked to a form, the autoresponse you create here will be emailed to the first email address listed on the form in question.  In other words, you must specify at least one field on the form as requiring an email address.', 'formbuilder'); ?></p>

				<table width="100%" cellspacing="2" cellpadding="5" class="widefat">
					<tr valign="top">
						<td>
						<h4><?php _e('Autoresponse Controls', 'formbuilder'); ?>:</h4>

						<?php
							foreach($fields as $field)
							{
								if($field['Field'] != "id") formbuilder_display_form_field($field);
							}
						?>
						</td>
					</tr>
					<tr valign="top">
						<td>
							<input type="submit" name="Save" value="<?php _e('Save', 'formbuilder'); ?>">
						</td>
					</tr>

				</table>

			</fieldset>

		</form>
