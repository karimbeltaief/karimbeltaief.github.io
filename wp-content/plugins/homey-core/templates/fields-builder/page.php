<?php 
if(isset($_GET['action']) && ( $_GET['action'] == 'add-new' || $_GET['action'] == 'homey-edit-field')) {
	load_template( HOMEY_TEMPLATES . '/fields-builder/fields-form.php' );
} else {
	load_template( HOMEY_TEMPLATES . '/fields-builder/index.php' );
}
?>