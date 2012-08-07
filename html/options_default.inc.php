<?php
formbuilder_admin_nav('forms');
$_GET += array('pageNumber' => '');
?>
<fieldset class="options metabox-holder">

	<div class="info-box-formbuilder postbox">
		<h3 class="info-box-title hndle"><?php _e('Current Forms', 'formbuilder'); ?></h3>
		<div class="inside">
		<style>
			.formSearch {
			 	display: block;
			 	width: 200px;
			 	float: right;
			 	text-align: right;
			 	padding: 6px;
			}
			.formSearch input {
				width: 120px;
			}
			.formSearch input.searchButton {
				width: auto;
			}
		</style>
		<?php 
			if(isset($_GET['formSearch']) && $_GET['formSearch'] != "")
			{
				$formSearch = preg_replace("#[^a-z0-9 _-]#i", "", $_GET['formSearch']);
			}
			else
			{
				$formSearch = "";
			}
		?>
		<form class='formSearch' name="formSearch" method="GET" action="<?php echo FB_ADMIN_PLUGIN_PATH; ?>">
			<input name='page' type="hidden" value="<?php echo $_GET['page']; ?>" />
			<input name='pageNumber' type="hidden" value="<?php echo $_GET['pageNumber']; ?>" />
			<input name='formSearch' type="text" size="10" value="<?php echo $formSearch; ?>" />
			<input class='searchButton' name='Search' type="submit" value="Search" />
		</form>
		
		<p>
			<?php _e('These are the forms that you currently have running on your blog.', 'formbuilder'); ?>
			<a href="<?php echo FB_ADMIN_PLUGIN_PATH; ?>&fbaction=newForm"><?php printf(__('Click here%s to create a new form', 'formbuilder'), '</a>'); ?>.
			<a href="<?php echo FB_ADMIN_PLUGIN_PATH; ?>&fbaction=importForm"><?php printf(__('Click here%s to %simport%s a form', 'formbuilder'), '</a>', '<strong>', '</strong>'); ?>.
		</p>
		
		<?php 
			// Get a list of any tags associated with the forms.
			$sql = "SELECT DISTINCT `tag` FROM " . FORMBUILDER_TABLE_TAGS . " ORDER BY `tag`;";
			$tags = $wpdb->get_results($sql, ARRAY_A);
			
			if(count($tags) > 0)
			{
				$html = array();
				foreach($tags as $tag)
				{
					$tag = trim($tag['tag']);
					$url = formbuilder_build_url(array('fbtag'=>$tag), array('page', 'fbaction'));
					$html[] = "<a href='{$url}'>{$tag}</a>";
				}
				$html = implode(", ", $html);
				$allurl = formbuilder_build_url(array(), array('page', 'fbaction', 'pageNumber'));
				?>
				
				<div class="inside">
					Form Tags: 
					<a href='<?php echo $allurl; ?>'>All</a>, 
					<?php echo $html; ?>
					<br/><br/>
				</div>
				
				<?php
			}
		
			// Build the list of current forms:
			if($formSearch)
			{
				$formSearchInsert = " AND ("
					. FORMBUILDER_TABLE_FORMS . ".name LIKE '%$formSearch%'"
					. " OR " . FORMBUILDER_TABLE_FORMS . ".subject LIKE '%$formSearch%'"
					. " OR " . FORMBUILDER_TABLE_FORMS . ".recipient LIKE '%$formSearch%'"
					. ") ";
			}
			else
			{
				$formSearchInsert = "";
			}
			
			if(isset($_GET['fbtag']) AND $_GET['fbtag'] != "")
			{
				
				$tag = $_GET['fbtag'];
				$tag = preg_replace("/[^A-Za-z0-9 _-]/isU", "", $tag);
				$sql = "SELECT " . FORMBUILDER_TABLE_FORMS . ".id,name,subject,recipient  FROM " . FORMBUILDER_TABLE_FORMS . " "
				. " LEFT JOIN " . FORMBUILDER_TABLE_TAGS . " ON " . FORMBUILDER_TABLE_FORMS . ".id = " . FORMBUILDER_TABLE_TAGS . ".form_id "
				. " WHERE " . FORMBUILDER_TABLE_TAGS . ".tag LIKE '{$tag}' "
				. $formSearchInsert
				. " ORDER BY " . FORMBUILDER_TABLE_FORMS . ".name ASC";
			}
			else
			{
				$sql = "SELECT " . FORMBUILDER_TABLE_FORMS . ".id,name,subject,recipient FROM " . FORMBUILDER_TABLE_FORMS . " WHERE 1=1 " . $formSearchInsert . " ORDER BY `name` ASC";
			}
			
			$objForms = $wpdb->get_results($sql);
			$alt = false;
			$itemLimit = 20;

			if(is_array($objForms)) 
			{
				$numForms = count($objForms);
					
				$nav = __('Page', 'formbuilder') . ': ' . fb_get_paged_nav($numForms, $itemLimit, false);
				
				if(isset($_GET['pageNumber']))
					$page = $_GET['pageNumber'];
				else
					$page = "";
					
				if(!is_numeric($page))
					$page = 0;
				else
					$page--;
				
				if( ($page * $itemLimit) > $numForms )
					$page = 0;
			
				if($numForms < $itemLimit)
					$nav = "";
		?>

		<table class="widefat">
			<tr valign="top">
				<th><?php _e('ID #', 'formbuilder'); ?></th>
				<th><?php _e('Name', 'formbuilder'); ?></th>
				<th><?php _e('Subject', 'formbuilder'); ?></th>
				<th>
					<?php _e('Recipient', 'formbuilder'); ?>
					<div width='125' style='float: right; text-align: right;'>
						<?php echo $nav; ?>
					</span>
				</th>
			</tr>
			<?php
					
					$start = $page * $itemLimit;
					$limit = $start + $itemLimit;
					if($limit >= $numForms)
						$limit = $numForms;
					
					for($i=$start; $i<$limit; $i++)
					{
						$form = $objForms[$i];
						if($alt == false) {
							$alt = true;
							$class = "alternate";
						}
						else
						{
							$class = "";
							$alt = false;
						}
				?>
				<tr valign="top" class="<?php echo $class; ?> hoverlite" onClick="jQuery('#formRow<?php echo $form->id; ?>').show();">
					<td><acronym title="<?php printf(__("Manually include this form with %s in the page/post content.", 'formbuilder'), "[formbuilder:" . $form->id . "]"); ?>"><?php echo $form->id; ?></acronym></td>
					<td><a href='javascript:;' onClick="jQuery('#formRow<?php echo $form->id; ?>').show();"><?php echo $form->name; ?></a></td>
					<td><a href='javascript:;' onClick="jQuery('#formRow<?php echo $form->id; ?>').show();"><?php echo $form->subject; ?></a></td>
					<td><a href='javascript:;' onClick="jQuery('#formRow<?php echo $form->id; ?>').show();"><?php echo $form->recipient; ?></a></td>
				</tr>
				<tr id="formRow<?php echo $form->id; ?>" style="display: none; background-color: #dddddd;">
					<td>
						<a href="javascript:;" onClick="jQuery('#formRow<?php echo $form->id; ?>').hide();">^</a>
					</td>
					<td colspan="3" style="padding-bottom: 20px;">
						<a href="<?php echo formbuilder_build_url(array('fbaction'=>'editForm', 'fbid'=>$form->id), array('page')); ?>"><?php _e('Edit', 'formbuilder'); ?></a>
						 |
						<a href="<?php echo formbuilder_build_url(array('fbaction'=>'copyForm', 'fbid'=>$form->id), array('page', 'fbtag', 'pageNumber')); ?>"><?php _e('Copy', 'formbuilder'); ?></a>
						 |
						<a href="<?php echo formbuilder_build_url(array('fbaction'=>'exportForm', 'fbid'=>$form->id), array('page', 'fbtag', 'pageNumber')); ?>"><?php _e('Export', 'formbuilder'); ?></a>
						 |
						<a href="<?php echo formbuilder_build_url(array('fbaction'=>'removeForm', 'fbid'=>$form->id), array('page', 'fbtag', 'pageNumber')); ?>" onclick="return(confirm('<?php _e('Are you sure you want to delete this form?', 'formbuilder'); ?>'));"><?php _e('Remove', 'formbuilder'); ?></a>
					</td>
				</tr>
			<?php } ?>
			
			<tr valign="top">
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td width='125' align='right' style='text-align: right;'><?php echo $nav; ?></td>
			</tr>
			
		</table>
		<?php } ?>
		</div>
	</div>
	
	<div class="info-box-formbuilder postbox">
		<h3 class="info-box-title hndle"><?php _e('Current Autoresponses', 'formbuilder'); ?></h3>
		<div class="inside">
		<p><?php _e('These are the autoresponses that you have available to use with your forms.', 'formbuilder'); ?>
		<a href="<?php echo FB_ADMIN_PLUGIN_PATH; ?>&fbaction=newResponse"><?php printf(__('Click here%s to create a new autoresponse.', 'formbuilder'), '</a>'); ?></p>

		<table class="widefat">
			<tr valign="top">
				<th><?php _e('Name', 'formbuilder'); ?></th>
				<th><?php _e('Subject', 'formbuilder'); ?></th>
				<th><?php _e('Actions', 'formbuilder'); ?></th>
			</tr>
			<?php
				// Build the list of current forms:
				$sql = "SELECT * FROM " . FORMBUILDER_TABLE_RESPONSES . " ORDER BY `name` ASC";
				$objResponses = $wpdb->get_results($sql);

				if(is_array($objResponses)) foreach($objResponses as $autoresponse)
				{
					if($alt == false) {
						$alt = true;
						$class = "alternate";
					}
					else
					{
						$class = "";
						$alt = false;
					}

			?>
			<tr valign="top" class="<?php echo $class; ?> hoverlite">
				<td><?php echo $autoresponse->name; ?></td>
				<td><?php echo $autoresponse->subject; ?></td>
				<td>
					<a href="<?php echo FB_ADMIN_PLUGIN_PATH; ?>&fbaction=editResponse&fbid=<?php echo $autoresponse->id; ?>"><?php _e('Edit', 'formbuilder'); ?></a>
					 |
					<a href="<?php echo FB_ADMIN_PLUGIN_PATH; ?>&fbaction=copyResponse&fbid=<?php echo $autoresponse->id; ?>"><?php _e('Copy', 'formbuilder'); ?></a>
					 |
					<a href="<?php echo FB_ADMIN_PLUGIN_PATH; ?>&fbaction=removeResponse&fbid=<?php echo $autoresponse->id; ?>" onclick="return(confirm('<?php _e('Are you sure you want to delete this autoresponse?', 'formbuilder'); ?>'));"><?php _e('Remove', 'formbuilder'); ?></a>
				</td>
			</tr>
			<?php } ?>
		</table>
		</div>
	</div>
	
	<div class='clear' />

</fieldset>
