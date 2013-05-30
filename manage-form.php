<?php
if (!defined('ABSPATH')) die('ERROR');

if ( is_multisite() ) { 

	echo '<div style="display:none">'.$table_name = $wpdb->prefix . "shortcode_form";
	$dbname = $wpdb->dbname;
	$sql = "SHOW TABLES FROM $dbname";
	$result = mysql_query($sql);
	if (!$result) {
		echo "DB Error, could not list tables\n";
		echo 'MySQL Error: ' . mysql_error();
		exit;
	}
	while ($row = mysql_fetch_row($result)) {
		echo "Table: {$row[0]}"."<br />";
	}
	mysql_free_result($result);
	echo '</div>';
}

$table_name = $wpdb->prefix . "shortcode_form";
$sql = "SELECT * FROM ".$table_name." WHERE 1 ";
$video_info = $wpdb->get_results($sql);
?>
<script type="text/javascript">
function show_confirm(title, id)
{
	var rpath1 = "";
	var rpath2 = "";
	var r=confirm('Are you confirm to delete "'+title+'"');
	if (r==true)
	{
		rpath1 = '<?php echo admin_url('?page=wfs_form_shortcode_page'); ?>';
		rpath2 = '&delete=y&id='+id;
		//alert(rpath1+rpath2);
		window.location = rpath1+rpath2;
	}
}
</script>
<div class="wrap">
<h2>Manage Form</h2>
<a href="?page=wfs_form_shortcode_page&amp;view=add"><input type="button" class="button button-primary" value="Add Form" /></a>
	<table class="widefat page fixed" cellspacing="0" style="margin:12px 0;">
	
		<thead>
		<tr valign="top">
			<th class="manage-column column-title" scope="col" width="150">Form Name</th>
			<th class="manage-column column-title"  scope="col" width="100">Form Type</th>
			<th class="manage-column column-title" scope="col">Form Content</th>
			<th class="manage-column column-title" scope="col">Short Code</th>
			<th class="manage-column column-title" scope="col" width="50">Edit</th>
			<th class="manage-column column-title" scope="col" width="50">Delete</th>
		</tr>
		</thead>
		
		<tbody>
		<?php foreach($video_info as $vdoinfo){ ?>
		<tr valign="top">
			<td>
				<?php echo $vdoinfo->form_name;?>
			</td>
			<td>
				<?php echo $form_type = $vdoinfo->form_type=='inf'?'infusion':$vdoinfo->form_type; ?>
			</td>
			<td>
				<?php echo $vdoinfo->form_header;?>
			</td>
			<td><input type="text" class="regular-text" value="[CN_Popforms type=&quot;<?php echo $form_type;?>&quot; name=&quot;<?php echo $vdoinfo->form_name;?>&quot;]" /></td>
			
			<td>
				<a href="?page=wfs_form_shortcode_page&view=add&mode=edit&id=<?php echo $vdoinfo->id;?>"><strong>Edit</strong></a>
			</td>
			<td>
				<a onclick="show_confirm('<?php echo $vdoinfo->form_type?>','<?php echo $vdoinfo->id;?>');" href="#delete"><strong>Delete</strong></a>
			</td>
			
		</tr>
		<?php }?>
		</tbody>
		<tfoot>
		<tr valign="top">
			<th class="manage-column column-title" scope="col">Form Name</th>
			<th class="manage-column column-title" scope="col">Form Type</th>
			<th class="manage-column column-title" scope="col">Form Content</th>
			<th class="manage-column column-title" scope="col">Short Code</th>
			<th class="manage-column column-title" scope="col" width="50">Edit</th>
			<th class="manage-column column-title" scope="col" width="50">Delete</th>
		</tr>
		</tfoot>
	</table>
<a href="?page=wfs_form_shortcode_page&amp;view=add"><input type="button" class="button button-primary" value="Add Form" /></a>
</div>