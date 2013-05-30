<?php
/*
Plugin Name: iContact, InfusionSoft From Popup
Plugin URI: http://www.cybernetikz.com
Description: iContact, InfusionSoft, Custom From Popup
Version: 1.0
Author: cybernetikz
Author URI: http://www.cybernetikz.com
License: GPL2
*/

if (!defined('ABSPATH')) die('ERROR');

include('simple_html_dom.php');

$pluginsURI = plugins_url('/cn-form-popup/');
function wfs_my_script() {
	global $pluginsURI;
	wp_enqueue_script( 'jquery' );	
	wp_register_script('fancybox.js', $pluginsURI . 'fancybox/jquery.fancybox-1.3.4.pack.js', array(), '1.0' );
	wp_enqueue_script( 'fancybox.js' );	
	
	wp_register_style('fancybox.css', $pluginsURI . 'fancybox/jquery.fancybox-1.3.4.css', array(), '1.0' );
	wp_enqueue_style( 'fancybox.css' );	
}
add_action('init', 'wfs_my_script');

add_action('admin_menu', 'wfs_add_menu_pages');

function wfs_add_menu_pages() {
	add_menu_page('From Popup', 'From Popup', 'manage_options', 'wfs_form_shortcode_page', 'wfs_form_shortcode_page_fn',plugins_url('/images/scc-sc.gif', __FILE__) );
}

function wfs_create_table() {

   global $wpdb;
   $table_name = $wpdb->prefix . "shortcode_form";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
		$sql2 = "CREATE TABLE `$table_name` (
		`id` bigint(20) NOT NULL auto_increment,
		`form_name` varchar(255) default NULL,
		`form_type` varchar(20) default NULL,
		`form_header` text,
		`form_body` text,
		`form_footer` text,
		`onpage_button_url` varchar(255) default NULL,
		`lightbox_button_url` varchar(255) default NULL,
		`width` varchar(10) default NULL,
		`height` varchar(10) default NULL,
		`admin_email` varchar(255) default NULL,
		`display_phone` tinyint(4) NOT NULL,
		`created_at` varchar(50) default NULL,
		PRIMARY KEY  (`id`),
		UNIQUE KEY `form_name` (`form_name`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
		";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql2);
   }
}

function wfs_db_install($networkwide) {
    global $wpdb;
    if (function_exists('is_multisite') && is_multisite()) {
        if ($networkwide) {
            $old_blog = $wpdb->blogid;
            $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
				wfs_create_table();
                //call_user_func($pfunction, $networkwide);
            }
            switch_to_blog($old_blog);
            return;
        }   
    } else {
		wfs_create_table();
	}
    wfs_create_table();
}

function wfs_db_uninstall ($networkwide) {
	global $wpdb;
	$table_name = $wpdb->prefix."shortcode_form";
	$sql_delete = "drop table $table_name";
	$wpdb->query($sql_delete);
}

register_activation_hook(__FILE__,'wfs_db_install');
//register_deactivation_hook(__FILE__,'wfs_db_uninstall');

function senitize_form_name($s)
{
	$p = '/[^a-zA-Z0-9\s]/';
	$r = '';
	//return str_replace(' ','-',trim(preg_replace($p,$r,$s)));
	return trim(preg_replace($p,$r,$s));
}

function file_upload($file)
{
	global $msg,$err;
	$allow_file_ext = array('jpg','jpeg','png','gif');
	$max_size = 1024*1024*1; // 1MB
	$wp_upload_dir = wp_upload_dir();
	$file_upload_dir = $wp_upload_dir['basedir'];
	$file_upload_path = $wp_upload_dir['baseurl'];

	if($_FILES[$file]["name"]=='')
	{
		$err .= "Please input file". "<br />";
	}
	if($err!='') return;
	
	if ($_FILES[$file]["error"] > 0)
	{
		$err .= "Return Code: " . $_FILES[$file]["error"] . "<br />";
	}
	if($err!='') return;
	
	if (file_exists($file_upload_dir.'/'.$_FILES[$file]["name"]))
	{
		$err .= $_FILES[$file]["name"] . " already exists";
	}
	if($err!='') return;
	
	$current_file_ext = strtolower(pathinfo($_FILES[$file]["name"], PATHINFO_EXTENSION));
	if(!in_array($current_file_ext,$allow_file_ext))
	{
		$err .= "Invalid file type" . "<br />";
	}
	if($err!='') return;
	
	if($_FILES[$file]["size"] > $max_size)
	{
		$err .= "Invalid file type" . "<br />";
	}
	if($err!='') return;
	
	$file_name = time().'_'.$_FILES[$file]["name"];
	$status = move_uploaded_file($_FILES[$file]["tmp_name"], $file_upload_dir.'/'.$file_name);
	if($status)
	{
		$original_file_name = $file_upload_path.'/'.$file_name;
		//$msg .= "File upload successful"."<br />";
		return $original_file_name;
	}
}

if (isset($_GET['delete'])) {
	
	if ($_REQUEST['id'] != '')
	{
	
		$table_name = $wpdb->prefix . "shortcode_form";
		/*$image_file_path = "../wp-content/uploads/";
		$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
		$video_info = $wpdb->get_results($sql);
		
		if (!empty($video_info))
		{
			@unlink($image_file_path.$video_info[0]->image_url);
		}*/
		$delete = "DELETE FROM ".$table_name." WHERE id = ".$_REQUEST['id']." LIMIT 1";
		$results = $wpdb->query( $delete );
		$msg = "Delete Successful"."<br />";
	}

}

if (isset($_POST['submit_button'])) {

	if ($_POST['action'] == 'update')
	{
		$err = "";
		$msg = "";
		
		//$image_file_path = "../wp-content/uploads/";
		
		$onpage_button_url = '';
		if($_FILES['onpage_button_url']['name']!='')
			$onpage_button_url = file_upload('onpage_button_url');
			
		$lightbox_button_url = '';	
		if($_FILES['lightbox_button_url']['name']!='')	
			$lightbox_button_url = file_upload('lightbox_button_url');
		
		if ($err == '')
		{
			$table_name = $wpdb->prefix . "shortcode_form";
			if(isset($_REQUEST['display_phone']))
				$display_phone = 1;
			else
				$display_phone = 0;
	
			$insert = "INSERT INTO " . $table_name .
			" ( 
			`id` ,
			`form_name` ,
			`form_type` ,
			`form_header` ,
			`form_body` ,
			`form_footer` ,
			`onpage_button_url` ,
			`lightbox_button_url` ,
			`width` ,
			`height` ,
			`admin_email` ,
			`display_phone` ,
			`created_at` ) " .
			"VALUES (NULL,'" . 
			senitize_form_name($_POST['form_name']) . "','" . 
			$wpdb->escape( $_REQUEST['form_type']) . "','" . 
			$wpdb->escape( $_REQUEST['form_header']) . "','" . 
			$wpdb->escape( $_REQUEST['form_body']) . "','" . 
			$wpdb->escape( $_REQUEST['form_footer']) . "','" . 
			$onpage_button_url . "','" . 
			$lightbox_button_url . "','" . 
			$wpdb->escape( $_REQUEST['width']) . "','" . 
			$wpdb->escape( $_REQUEST['height']) . "','" . 
			$wpdb->escape( $_REQUEST['admin_email']) . "'," . 
			$display_phone . ",'" . 
			time() . "'" . 
			")";
			//echo $insert;
			$results = $wpdb->query( $insert );
			
			if (!$results)
				$err .= "Fail to update database" . "<br />";
			else
				$msg .= "Update Successful" . "<br />";
		
		}
	}// end if update
	
	if ( $_REQUEST['action'] == 'edit' and $_REQUEST['id'] != '' )
	{
		$err = "";
		$msg = "";

		//$image_file_path = "../wp-content/uploads/";
		$table_name = $wpdb->prefix . "shortcode_form";
		
		if(isset($_REQUEST['display_phone']))
			$display_phone = 1;
		else
			$display_phone = 0;
		
		$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
		$video_info = $wpdb->get_row($sql);
		$onpage_button_url_old = $video_info->onpage_button_url;
		$lightbox_button_url_old = $video_info->lightbox_button_url;
		
		$update = "";
		
		$onpage_button_url = $onpage_button_url_old;
		if($_FILES['onpage_button_url']['name']!=''){
			@unlink($onpage_button_url_old);
			$onpage_button_url = file_upload('onpage_button_url');
		}
			
		$lightbox_button_url = $lightbox_button_url_old;	
		if($_FILES['lightbox_button_url']['name']!=''){
			@unlink($lightbox_button_url_old);
			$lightbox_button_url = file_upload('lightbox_button_url');
		}
		
		$update = "UPDATE " . $table_name . " SET " . 
		"form_name='" .senitize_form_name($_POST['form_name']) . "'," . 
		"form_type='" .$_POST['form_type'] . "'," . 
		"form_header='" .$wpdb->escape( $_POST['form_header']) . "'," . 
		"form_body='" .$wpdb->escape( $_POST['form_body']) . "'," . 
		"form_footer='" .$wpdb->escape( $_POST['form_footer']) . "'," . 
		"onpage_button_url='" .$onpage_button_url. "'," . 
		"lightbox_button_url='" .$lightbox_button_url. "'," . 
		"width='" .$_POST['width']. "'," . 
		"height='" .$_POST['height']. "'," . 
		"display_phone=" .$display_phone . "" . 
		" WHERE id=" . $_POST['id'];
		if ($err == '')
		{
			$results3 = $wpdb->query( $update );
			
			if (!$results3){
				$err .= "";
			}
			else
			{
				$msg = "Update Successful". "<br />";
			}
		}
		
	} // end edit
	
}

$i=0;
$optionss = 
array(
	array(
		'option_name'=>'form_name',
		'type'=>'input',
		'class'=>'regular-text',
		'title'=>'Form Name',
		'instruction'=>'<span id="form-name-msg"></span>',
		'value'=>'',
	),
	array(
		'option_name'=>'form_type',
		'type'=>'select',
		'class'=>'',
		'title'=>'Form type',
		'instruction'=>'',
		'option'=>array('icontact'=>'iContact','inf'=>'InfusionSoft','custom'=>'Custom'),
		'value'=>'',
	),
	array(
		'option_name'=>'form_header',
		'type'=>'editor',
		'class'=>'large-text',
		'title'=>'Form Content',
		'instruction'=>'',
		'value'=>'',
	),
	array(
		'option_name'=>'form_body',
		'type'=>'textarea',
		'class'=>'large-text',
		'title'=>'Form Code',
		'instruction'=>'Paste the form html code here',
		'value'=>'',
	),
	array(
		'option_name'=>'form_footer',
		'type'=>'editor',
		'class'=>'large-text',
		'title'=>'Form Footer',
		'instruction'=>'',
		'value'=>'',
	),
	array(
		'option_name'=>'onpage_button_url',
		'type'=>'file',
		'class'=>'regular-text',
		'title'=>'Url for on page button',
		'instruction'=>'<br />',
		'value'=>'',
	),
	array(
		'option_name'=>'lightbox_button_url',
		'type'=>'file',
		'class'=>'regular-text',
		'title'=>'Url for lightbox button',
		'instruction'=>'<br />',
		'value'=>'',
	),
	array(
		'option_name'=>'width',
		'type'=>'input',
		'class'=>'small-text',
		'title'=>'Popup width',
		'instruction'=>'px',
		'value'=>'350',
	),
	array(
		'option_name'=>'height',
		'type'=>'input',
		'class'=>'small-text',
		'title'=>'Popup height',
		'instruction'=>'px',
		'value'=>'',
	),
	
	array(
		'option_name'=>'admin_email',
		'type'=>'input',
		'class'=>'regular-text',
		'title'=>'Admin Email',
		'instruction'=>'email address who will receive the email',
		'value'=>'',
	),
	array(
		'option_name'=>'display_phone',
		'type'=>'checkbox',
		'class'=>'',
		'title'=>'Display phone',
		'instruction'=>'',
		'label'=>'Enable phone number',
		'value'=>'',
	),
	
);

function wsf_assign_value($item, $key)
{
	global $i,$optionss;
	$exfld = array('id','shortcode','created_at');
	if($key=='id') $i=0;
	
	if(!in_array($key,$exfld)) {
		$optionss[$i]['value'] = stripslashes($item);
		$i++;
	}
}

/*function wfs_form_shortcode_add_fn() {

	global $err,$msg,$wpdb,$optionss,$version;

	if (isset($_GET['mode'])) {
		if ( $_REQUEST['mode'] != '' and $_REQUEST['mode'] == 'edit' and  $_REQUEST['id'] != '' )
		{
			$page_title = 'Edit Form';
			$table_name = $wpdb->prefix . "shortcode_form";
			$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
			$video_info = $wpdb->get_row($sql);
			array_walk($video_info, 'wsf_assign_value');
		}
	}
	else
	{
		$page_title = 'Add New Form';
	}
//print_r($optionss);
?>
<style>
<?php
if(isset($video_info->form_type)) {
if($video_info->form_type=='custom') { ?>
.form_body{display:none;}
<?php } } else { ?>
.admin_email,.display_phone{display:none;}
<?php } ?>
</style>
<script>
jQuery(document).ready(function(){
	jQuery('select#form_type').change(function(){
		//alert(jQuery(this).val());
		if(jQuery(this).val()=='custom')
		{
			jQuery('tr.admin_email').show();
			jQuery('tr.display_phone').show();
			jQuery('tr.form_body').hide();
		} 
		else
		{
			jQuery('tr.admin_email').hide();
			jQuery('tr.display_phone').hide();
			jQuery('tr.form_body').show();
		}
	})

})
</script>
<div class="wrap">
<?php
$handle = @fopen("http://cybernetikz.com/wsf_version.txt", "r");
if ($handle) {
	$c=0;
	$str = '';
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
		if($c==0)
		{
			if(trim($buffer)!==trim($version))
				$str .= 'New version available ';
		}
		if($c==1)
		{
			if($str!='') {
				$str .= '<a target="_blank" href="'.$buffer.'">'.'click here</a> to update.';
				$msg = $str;
			}
		}
		$c++;
    }
    fclose($handle);
}
?>
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
					<input type="text" name="<?php echo $option_name ?>" id="<?php echo $option_name ?>" class="<?php echo $option['class'] ?>" value="<?php echo $option['value'] ?>" />&nbsp;<em><?php echo $option['instruction'] ?></em>
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
		<input type="submit" id="submit_button" name="submit_button" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
		
		<?php if (isset($_GET['mode']) ) { ?>
		<input type="hidden" name="action" value="edit" />
		<input type="hidden" name="id" id="id" value="<?php echo $_REQUEST['id'];?>" />
		<?php } else {?>
		<input type="hidden" name="action" value="update" />
		<?php } ?>
		
	</form>

</div>
<?php 
} */

function wfs_form_shortcode_page_fn() {

	global $err,$msg,$wpdb,$optionss;
	
	if(isset($_GET['view']))
	{
		include_once('add-form.php');
	}
	else
	{
		include_once('manage-form.php');
	}
}

add_filter('widget_text', 'do_shortcode');

if(isset($_POST['wpfs_submit'])){
	
	// multiple recipients
	
	$to = get_option('wpfs_admin_email');
	
	if($to!=''){
		// subject
		$subject = 'WP Short-code plugins - WebForm request';
		
		// message
		$message = '';
		$message .= 'Name : '.$_POST['ContactName'];
		$message .= '<br />';
		$message .= 'Email : '.$_POST['ContactEmail'];
		$message .= '<br />';
		$message .= 'Phone : '.$_POST['ContactPhone'];
		
		// To send HTML mail, the Content-type header must be set
		$headers ="";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		
		// Additional headers
		$headers .= 'To: Admin <'.$to.'>' . "\r\n";
		$headers .= 'From: '.$_POST['ContactName'].' <'.$_POST['ContactEmail'].'>' . "\r\n";
		
		//echo $message;
		// Mail it
		mail($to, $subject, $message, $headers);
	}

}

add_shortcode('CN_Popforms', 'wfs_form_fn');
function wfs_form_fn($attr){ 

	global $wpdb;
	$form_name = $attr['name'];
	
	//if(is_numeric($id))
	if($form_name!=''){
	
	$table_name = $wpdb->prefix . "shortcode_form";
	//$sql = "SELECT * FROM ".$table_name." WHERE id=$id ";
	$sql = "SELECT * FROM ".$table_name." WHERE form_name='$form_name' ";
	//echo $sql; 
	$video_info = $wpdb->get_row($sql);
	$form_type = $video_info->form_type;
	$id = $video_info->id;

ob_start();
?>
<script type="text/javascript">
function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
	//"
}
jQuery(document).ready(function() {
	jQuery("#various<?php echo $id ?>").fancybox();
	
	<?php if($form_type=='custom') { ?>
	jQuery('form.lead<?php echo $id ?>').submit(function() {
		if(jQuery('#wpfs_name<?php echo $id ?>').val()==''){
			alert('Please input name');
			return false;
		}
		if(!validateEmail(jQuery('#wpfs_email<?php echo $id ?>').val() ) ) {
			alert('Please input valid email');
			return false;
		}
	});
	<?php } ?>
	
	
});
</script>
<div style="display: none;">
	<div id="inline<?php echo $id ?>" class="wpsf-form-container" style="width:<?php echo $video_info->width; ?>px;height:<?php echo $video_info->height; ?>px; overflow:auto;">
		<?php 
		echo '<div style="float:left; width:100%; margin-bottom:10px;">';
		echo stripslashes($video_info->form_header);
		echo '</div>';
		$icform = stripslashes($video_info->form_body);
		$html = str_get_html($icform);
		if($form_type=='icontact' or $form_type=='inf') {
		foreach($html->find('form') as $e) {
			$input = '<div style="float:left"><form ';
			foreach ( $e->getAllAttributes() as $k=>$v) {
				$input .= $k.'="'.$v.'" ';
			}	
			$input .= '>';
			echo $input;
		}
		
		foreach($html->find('input') as $e) {
			$input = '<input ';
			foreach ( $e->getAllAttributes() as $k=>$v) {
				if($k=='name')
				{
					
					if($form_type=='icontact')
						$repword = 'fields_';
					else
						$repword = 'inf_field_';
					$pos = strpos($v,$repword);
					if($pos!==false)
						$input .= 'placeholder="'.ucwords(str_replace($repword,'',$v)).'" ';
				}
				$input .= $k.'="'.$v.'" ';
			}
			$input .= '/>';
			$input .= "\n";
			echo $input;
		}
		foreach($html->find('button') as $e) {
			$input = '<button ';
			foreach ( $e->getAllAttributes() as $k=>$v) {
				$input .= $k.'="'.$v.'" ';
			}	
			$input .= '>';
			if( $e->innertext !='' ) {
				$input .= $e->innertext;
			}
			$input .= '</button>';
			echo $input;
		}
		
		echo '</form>';
		
		foreach($html->find('script') as $e) {
			$input = '<script ';
			foreach ( $e->getAllAttributes() as $k=>$v) {
				$input .= $k.'="'.$v.'" ';
			}	
			$input .= '>';
			$input .= "\n";
			if( $e->innertext !='' ) {
				$input .= str_replace('<![CDATA[',"<![CDATA[\n", $e->innertext);
			}
			$input .= "\n";
			$input .= '</script></div>';
			echo $input;
		} 
		} else {
		?>
		<div style="float:left">
		<form class="lead<?php echo $id ?>" method="post" action="">
			<input type="text" placeholder="Name*" id="wpfs_name<?php echo $id ?>" class="required" name="ContactName">
			<?php if($video_info->display_phone==1) { ?>
			<input type="text" placeholder="Phone" id="wpfs_phone<?php echo $id ?>" class="" name="ContactPhone">
			<?php } ?>
			<input type="text" placeholder="Email*" id="wpfs_email<?php echo $id ?>" class="required" name="ContactEmail">
			<input type="hidden" name="wpfs_submit" value="1" />
			<span class="required-text">*Required</span><input style="width:auto; float:right; border:none;" type="image" src="<?php echo $video_info->lightbox_button_url ; ?>" />
		</form>
		</div>
		<?php
		}
		echo '<div style="float:left; width:100%; margin-top:10px;">';
		echo stripslashes($video_info->form_footer);
		echo '</div>';
		//echo strip_tags($icform,'<form><input><textarea><script>');
		?>
	</div>
</div>
<a id="various<?php echo $id ?>" href="#inline<?php echo $id ?>"><img style="border-radius:none; box-shadow:none;" src="<?php echo $video_info->onpage_button_url; ?>" border="0" /></a>
<?php
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
	}
}