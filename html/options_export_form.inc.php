<?php formbuilder_admin_nav('export form'); ?>

<h3 class="info-box-title"><?php _e('Form Export', 'formbuilder'); ?></h3>
<p><?php _e('The following text contains the data required to move this form to another site.  To move it, copy the entire text and paste it into the Import Form box on your secondary site.', 'formbuilder'); ?></p>
<textarea style="width: 700px; height: 500px;"><?php echo $formData; ?></textarea>