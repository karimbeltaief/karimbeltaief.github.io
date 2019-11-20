<?php
if(isset($_GET['action']) && ( $_GET['action'] == 'add-new' || $_GET['action'] == 'edit-currency')) {
	load_template( FCC_TEMPLATES . '/currency/form.php' );
} else {
	load_template( FCC_TEMPLATES . '/currency/currency-list.php' );
}
?>