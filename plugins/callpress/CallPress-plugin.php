<?php
/**
 * @package CallPress
 * @version 0.5
 */
/*
Plugin Name: CallPress
Plugin URI: http://wordpress.org/#
Description: This is the plugin that, paired with the CallPress theme, will provide a solid platform for a ticketing system that is very easily modifiable.
Author: James Lemieux
Version: 0.5
*/

include 'includes/callpress.inc.php';

add_action( 'init', 'CallPress::init' );
add_action( 'wp_enqueue_scripts', 'CallPress::scripts' );
?>
