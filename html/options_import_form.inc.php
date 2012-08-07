<?php formbuilder_admin_nav('import form'); ?>

<form name="formImport" method="POST" action="<?php echo FB_ADMIN_PLUGIN_PATH; ?>&fbaction=importForm">
	<h3 class="info-box-title"><?php _e('Form Import', 'formbuilder'); ?></h3>
	<p><?php _e('Enter the exported form data here and press Save.', 'formbuilder'); ?></p>
	<textarea style="width: 700px; height: 500px;" name="formData"><?php echo $formData; ?></textarea>
	<br/><input type="submit" name="submit" value="Save" />
</form>