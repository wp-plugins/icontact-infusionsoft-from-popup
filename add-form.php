<?php
if (!defined('ABSPATH')) die('ERROR');
//global $err,$msg,$wpdb,$optionss,$version;
$table_name = $wpdb->prefix . "shortcode_form";

$where = " form_name<>'' ";
if(is_numeric($_REQUEST['id']))
	$where .= " ANd id<>".$_REQUEST['id'];
$sql = "SELECT form_name FROM ".$table_name." WHERE $where ";
$frmname_info = $wpdb->get_results($sql);
$arr = array();
foreach($frmname_info as  $frmName)
{
	$arr[]=$frmName->form_name;
}

if (isset($_GET['mode'])) {
	if ( $_REQUEST['mode'] != '' and $_REQUEST['mode'] == 'edit' and  $_REQUEST['id'] != '' )
	{
		$page_title = 'Edit Form';
		$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
		$video_info = $wpdb->get_row($sql);
		array_walk($video_info, 'wsf_assign_value');
	}
}
else
{
	$page_title = 'Add New Form';
}
?>
<style type="text/css">
<?php
if(isset($video_info->form_type)) {
	if($video_info->form_type=='custom') { 
?>
.form_body{display:none;}
<?php 
	}
	else
	{ ?>
.admin_email,.display_phone,.lightbox_button_url{display:none;}
	<?php
	}
} 
else 
{ 
?>
.admin_email,.display_phone,.lightbox_button_url{display:none;}
<?php } ?>
span#form-name-msg{color:#ff0000;}
</style>
<script type="text/javascript">
var arrFormName = <?php echo json_encode($arr) ?>;
jQuery(document).ready(function(){

	jQuery('#form_name').focusout(function() {
		$res = jQuery.inArray(jQuery(this).val(), arrFormName);
		if($res!=-1)
		{
			jQuery('span#form-name-msg').html('Already used, please use different name');
		}
		else
		{
			jQuery('span#form-name-msg').html('');
		}
	});

	jQuery('select#form_type').change(function(){
		//alert(jQuery(this).val());
		if(jQuery(this).val()=='custom')
		{
			jQuery('tr.admin_email').show();
			jQuery('tr.display_phone').show();
			jQuery('tr.lightbox_button_url').show();
			jQuery('tr.form_body').hide();
		} 
		else
		{
			jQuery('tr.admin_email').hide();
			jQuery('tr.display_phone').hide();
			jQuery('tr.lightbox_button_url').hide();
			jQuery('tr.form_body').show();
		}
	})

})
</script>
<div class="wrap">
<?php
if($msg!='' or $err!='')
	echo '<div id="message" class="updated fade">'. $msg.$err.'</div>';
?>
<h2><?php echo $page_title;?></h2>
<form method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<table class="form-table">
			<?php
			foreach($optionss as $option)
			{
				$option_name = $option['option_name'];
			?>
			<tr valign="top" class="<?php echo $option_name ?>">
				<?php if($option['type']!='hr') { ?>
				<th scope="row"><?php echo $option['title'] ?></th>
				<?php } ?>
				<td <?php if($option['type']=='hr') { ?> colspan="2" <?php } ?>>
					<?php
					if($option['type']=='input') {
					?>
					<input type="text" name="<?php echo $option_name ?>" id="<?php echo $option_name ?>" class="<?php echo $option['class'] ?>" value="<?php echo $option['value'] ?>" <?php if($option_name=='width') echo 'readonly' ?>  />&nbsp;<em><?php echo $option['instruction'] ?></em>
					<?php
					}
					if($option['type']=='textarea') {
					?>
					<textarea style="height:250px;" name="<?php echo $option_name ?>" id="<?php echo $option_name ?>" class="<?php echo $option['class'] ?>"><?php echo $option['value'] ?></textarea>&nbsp;<em><?php echo $option['instruction'] ?></em>
					<?php
					}
					if($option['type']=='select') {
					?>
					<select name="<?php echo $option_name ?>" id="<?php echo $option_name ?>" class="<?php echo $option['class'] ?>">
					<?php 
					foreach ($option['option'] as $key=>$value)
					{
					?>
						<option <?php if( $option['value']==$key) echo 'selected="selected"'; ?> value="<?php echo $key ?>"><?php echo $value ?></option>
					<?php
					}
					?>
					</select>&nbsp;<em><?php echo $option['instruction'] ?></em>
					<?php
					}
					if($option['type']=='checkbox') {
					?>
					<input type="checkbox" name="<?php echo $option_name ?>" id="<?php echo $option_name ?>" class="<?php echo $option['class'] ?>" <?php if($option['value']==1) echo 'checked="checked"' ?> value="1" />&nbsp;<label for="<?php echo $option_name ?>"><?php echo $option['label'] ?></label>
					<?php
					}
					if($option['type']=='editor') {
						$content = $option['value'];
						$editor_id = $option_name;
						wp_editor( $content, $editor_id, $settings = array() );
					}
					if($option['type']=='file') {
					?>
					<input type="file" name="<?php echo $option_name ?>" id="<?php echo $option_name ?>" class="<?php echo $option['class'] ?>" value="" />&nbsp;<em><?php echo $option['instruction'] ?></em>
					<?php if($option['value']!='') { ?><img src="<?php echo $option['value'] ?>" /><?php } ?>
					<?php
					}
					if($option['type']=='info') {
						echo $option['instruction'];
					}
					if($option['type']=='hr') {
						echo '<hr style="border:#CCCCCC 1px dotted; margin:30px 0;" />';
					}
					?>
				</td>
			</tr>
			<?php 
			}
			?>
		</table>
		
		<p class="submit">
		<input type="submit" id="submit_button" name="submit_button" class="button-primary" value="<?php _e('Save Changes') ?>" />&nbsp;&nbsp;<a href="?page=wfs_form_shortcode_page"><input type="button" class="button-primary" value="Cancel" /></a>
		</p>
		
		<?php if (isset($_GET['mode']) ) { ?>
		<input type="hidden" name="action" value="edit" />
		<input type="hidden" name="id" id="id" value="<?php echo $_REQUEST['id'];?>" />
		<?php } else {?>
		<input type="hidden" name="action" value="update" />
		<?php } ?>
	</form>
</div>