<?php

	// Require support files.
	require_once(FORMBUILDER_PLUGIN_PATH . "php/formbuilder_processing.inc.php");
	require_once(FORMBUILDER_PLUGIN_PATH . "php/formbuilder_autoresponse_functions.inc.php");
	require_once(FORMBUILDER_PLUGIN_PATH . "php/formbuilder_post_metabox.inc.php");
	require_once(FORMBUILDER_PLUGIN_PATH . "php/formbuilder_activation_script.inc.php");
	require_once(FORMBUILDER_PLUGIN_PATH . "captcha/CaptchaSecurityImages.php");
	require_once(FORMBUILDER_PLUGIN_PATH . "extensions/formbuilder_xml_db_results.class.php");
	
	// Activate debugging object.
#	require_once(FORMBUILDER_PLUGIN_PATH . "class/FBObject.class.php");
#	require_once(FORMBUILDER_PLUGIN_PATH . "class/FBField.class.php");
#	require_once(FORMBUILDER_PLUGIN_PATH . "class/FBForm.class.php");
#	require_once(FORMBUILDER_PLUGIN_PATH . "class/FBFormEditor.class.php");
#	require_once(FORMBUILDER_PLUGIN_PATH . "class/FBTemplatizer.class.php");
