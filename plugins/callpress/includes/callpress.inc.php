<?php

	/**
	 * Purely for the readability of the plugin file. Merely includes all items in includes
	 **/
	
	require_once( 'cp.class.php' ); 
	require_once( 'cp_general.class.php' );

	$cp_general = new cp_general();

	require_once( 'cp_ticket.class.php' );
	
	add_action( 'wp_ajax_add_ticket', 'cp_ticket::add_ticket' ); // file: cp_ticket.class.php function: add_ticket search: T1
?>
