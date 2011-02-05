<?php
class CallPress {
	//This will remain an empty construct until I find a need for it.
	function __constuct() {}
	
	/* init makes all the necessary declarations and defanitions and runs on the wordpress init action. The add_action function for this function is located at the bottom of this function.*/
	public static function init() {

		// The ticket labels are defined here. 
		$ticket_labels = array(
			'name' => __('Tickets'),
			'singular_name' => __('Ticket'),
			'add_new' => _x('Add New', 'ticket'),
			'add_new_item' => __('Add New Ticket'),
			'edit_item' => __('Edit Ticekts'),
			'new_item' => __('New Ticket'),
			'view_item' => __('View Ticket'),
			'search_items' => __('Search tickets'),
			'not_found' => __('Ticket not found'),
			'not_found_in_trash' => __('Ticket not in trash'),
			'parent_item_colon' => __('Ticket'),
			'menu_name' => __('Ticket Menu')
		);//end var ticket_labels
		
		// This array sets the diffults for the post type ticket. It is highly recomended that you do not edit defaults form here but rely on the action after this array delcartion 
		$ticket_args = array( 
			'label' => __('Tickets'),
			'labels' => $ticket_labels,
			'description' => __( 'Tickets are the core building blocks of CallPress. The ticket structure is an asysclic directed graph. Tickets are hierarchical and can also be merged into other tickets. This puts duplication control in the users hands.' ),
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 5,
			'menu_icon' => false, //todo
			//'capability_type' //WTF?
			//'capability' => ,  //todo maybe?
			'map_meta_cap' => true,
			'hierarchical' => true,
			//'supports' => '',
			//'taxonomies' => //todo
			//'permalink_epmask' => //wtf
			'rewrite' => false,
			'query_var' => 'ticket',
			'can_export' => true,
			'show_in_nav_menus' => true,
		);// end var ticket args

		// Callpress action ticket_before_register alls for rewriting defaults.
		do_action( 'callpress_ticket_before_register' );

		//Register the type ticket with wordpress with all of the arguments defined above.
		register_post_type( 'ticket', $ticket_args );

		do_action( 'callpress_init' );
	}//end init

}//end CallPress

?>
