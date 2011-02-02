<?php
require_once 'includes/NineSpotLayout.class.php';
require_once 'includes/NineSpotCore.class.php';

$ninespot_dir = dirname(__FILE__);

if ( function_exists('register_sidebars') )
{	
	add_action( 'init', 'nines_init' );
}//end if

// activate widgets if bSuite hasn't already activated them
if( ! is_object( $bsuite ) )
{
	require_once( dirname( __FILE__) .'/components/cms-widgets.php' );
	require_once( dirname( __FILE__) .'/components/wijax.php' );
} // end if

/**
 * Conditinally initializes the 9spot objects (if is_admin) 
 * or adds a hook to render for public display.
 *
 */
function nines_init()
{
	
	if( is_admin() )
	{
		global $ninespot_admin;
		$ninespot_admin = new NineSpotCore();

		// check for layouts, init them if missing
		if( empty( $ninespot_admin->layouts ))
		{
			// allow child themes to set some defaults
			do_action( 'nines_init_defaults' );

			// if there are still no layouts, let's go create one ourselves
			if( empty( $ninespot_admin->layouts ))
				$ninespot_admin->initDefaults();

		}//end if
	}
	else
	{
		add_action( 'template_redirect', 'nines_templateredirect' );
	}// end else
}

/**
 * Does rule matching and initializes the matching layout for public display.
 *
 */
function nines_templateredirect()
{
	global $wp_query, $ninespot;

	$rules = get_option( 'nines-rules' );

/* 	all the possible rulesets: id, parent, category, tag, is_x
	$rules = array( 
		'id' => array(),
		'parent_id' => array(),
		'category' => array(),
		'tag' => array(),
		'is_x' => array(
			'is_home' => 'home',
		),
	);

	// the rules array should not contain empty rulesets
	$rules = array( 
		'id' => array(
			'1' => 'nines-layout-front',
		),
		'parent_id' => array(
			'1' => 'nines-layout-front',
		),
		'is_x' => array(
			'is_home' => 'nines-layout-front',
		),
	);
	//TODO: get_option() the above array
*/

	// some rules only work on singular
	if( $wp_query->is_singular )
	{
		// do the post id rules
		if( 
			isset( $rules['id'][ $wp_query->post->ID ] ) && 
			!( empty( $rules['id'][ $wp_query->post->ID ] ))
		)
		{
			$ninespot = new NineSpotLayout( $rules['id'][ $wp_query->post->ID ] );
			return;
		}// end if

		// do the post ancestor id rules
		if( 
			is_array( $rules['parent_id'] ) && 
			is_array( $wp_query->post->ancestors )
		)
		{
			if( $matched = array_shift( array_intersect( $wp_query->post->ancestors , array_keys( $rules['parent_id'] ))))
			{
				$ninespot = new NineSpotLayout( $rules['parent_id'][ $matched ] );
				return;
			}
		}// end if
	}// end if is_singular

	// do the is_x rules
	if( is_array( $rules['is_x'] ))
	{
		if( 
			( $rule = array_intersect_key( (array) $wp_query , $rules['is_x'] )) &&
			( $rule = array_filter( $rule )) &&
			count( $rule )
		)
		{
			$ninespot = new NineSpotLayout( $rules['is_x'][ key( $rule ) ] );
			return;
		}// end if
	}// end if


	// do the default layout
	$ninespot = new NineSpotLayout( 'nines-layout-default' );

	return;
}// end nines_templateredirect
