<?php

class NineSpotCore
{
	public $is_x_opts = array(
		'is_archive',
		'is_attachment',
		'is_author',
		'is_category',
		'is_tag',
		'is_tax',
		'is_date',
		'is_day',
		'is_front_page',
		'is_home',
		'is_month',
		'is_page',
		'is_paged',
		'is_search',
		'is_single',
		'is_singular',
		'is_time',
		'is_year',
		'is_404',
	);

	public $grid = 16;
	public $min_block_size = 1;

	public $layout_page = '9spot-settings';
	public $layout_prefix = 'nines-layout-';

	public $layout_default = 'nines-layout-default';	
	public $layout_new = 'nines-new';
	public $option_layouts = 'nines-layouts';
	public $option_rules = 'nines-rules';

/**
 * Constructor for NineSpot (9spot)
 *
 * @access public
 */
	function __construct()
	{
		global $ninespot_dir, $nines_layout_default;
		
		$this->dir = $ninespot_dir;

		// @TODO: get the defaults based on the default layout
		if( isset( $nines_layout_default ) && is_array( $nines_layout_default ))
		{
			$this->default_structure = 
				array(
					'name' => __( 'New Layout', 'nines' ),
					'slug' => $this->layout_new,
					'spots' => &$nines_layout_default['spots'],
					'copy_spots' => &$nines_layout_default['copy_spots'],
					'widgets' => &$nines_layout_default['widgets'],
				);
		}
		else
		{
			$this->default_structure = array(
				'name' => 'New Layout',
				'slug' => $this->layout_new,
				'spots' => array(
					'head' => array(
						1 => 16,
					),
					'nav' => array(
						1 => 16,
					),
					'avant-body' => array(
					),
					'body' => array(
						1 => 12,
						2 => 4,
					),
					'apres-body' => array(
					),
					'foot' => array(
						1 => 16,
					),
					'apres-foot' => array(
						1 => 16,
					),
				),

				'copy_spots' => array(
					'head' =>$this->layout_new,
					'nav' => $this->layout_new,
					'avant-body' => 0,
					'body' => 0,
					'apres-body' => 0,
					'foot' => $this->layout_new,
					'apres-foot' => $this->layout_new,
				),

				'widgets' => array(
					'head' => array(
						'text' => array(
							'title' => '',
							'text' => 'Put a widget header here, or modify this text widget as your header.',
							'filter' => '',
						),
					),
					'body-1' => array(
						'text' => array(
							'title' => '',
							'text' => 'This is one of the four available vertical widget sections in 9spot. You can make this a sidebar, or move things around and put a post loop here.',
							'filter' => '',
						),
					),
					'body-2' => array(
						'text' => array(
							'title' => '',
							'text' => 'This is one of the four available vertical widget sections in 9spot. You can make this a sidebar, or move things around and put a post loop here.',
							'filter' => '',
						),
					),
					'foot' => array(
						'text' => array(
							'title' => '',
							'text' => 'This is your footer. Put a widget header here, or modify this text widget.',
							'filter' => '',
						),
					),
				),
			);
		}
		
		$this->structure_sections = array('head','nav','avant-body','body','apres-body','foot','apres-foot');
		$this->widget_sections = array('head','nav','avant-body','body-1','body-2','body-3','body-4','apres-body','foot','apres-foot');

		$this->layouts = get_option( $this->option_layouts );

		// initialize layouts
		if( is_array( $this->layouts ))
		{
			foreach( $this->layouts as $layout )
			{
				// only register widget areas for layouts if show_layout 
				// is unset OR if show_layout matches the layout slug
				
				// @todo Get widget areast to NOT purge other widget areas
				//$register_widget_areas = (!$_GET['show_layout'] || $_GET['show_layout'] == $layout);
				$register_widget_areas = true;

				$this->$layout = new NineSpotLayout( $layout , $register_widget_areas);
			}//end foreach
		}//end if
				
		add_action('admin_menu', array(&$this, 'admin_menu_add'));
	}//end constructor

/**
 * init a default layout.
 *
 */
	function initDefaults()
	{
		global $ninespot_admin;
	
		$ninespot_admin->addNewLayout( 'nines-layout-default',
			array(
				'spots' => array(
					'head' => array(
						1 => 8,
						2 => 8,
						3 => 0,
						4 => 0,
					),
					'nav' => array(
						1 => 16,
						2 => 0,
						3 => 0,
						4 => 0,
					),
					'avant-body' => array(
						1 => 0,
						2 => 0,
						3 => 0,
						4 => 0,
					),
					'body' => array(
						1 => 4,
						2 => 8,
						3 => 4,
						4 => 0,
					),
					'apres-body' => array(
						1 => 0,
						2 => 0,
						3 => 0,
						4 => 0,
					),
					'foot' => array(
						1 => 16,
						2 => 0,
						3 => 0,
						4 => 0,
					),
					'apres-foot' => array(
						1 => 16,
						2 => 0,
						3 => 0,
						4 => 0,
					),
				),
				'widgets' => array(
					'head' => array(
						'text' => array( 'title' => 'This is a text widget' , 'text' => 'Edit me in the dashboard' ),
					),
					'body-1' => array(
						'pages' => array( 'expandtree' => 1 , 'homelink' => 'Home' ),
					),
					'body-2' => array(
						'breadcrumbs' => array( 'homelink' => get_option('name') , 'maxchars' => 35 ),
						'categorydescription' => array( 'title' => '%term_name% Archives' ),
						'pagednav' => array(),
						'postloop' => array( 'title' => 'Primary Post Loop', 'what' => 'normal', 'template' => 'a_default_full.php', ),
						'pagednav' => array(),
					),
					'body-3' => array(
						'postloop' => array( 'title' => 'Around the site', 'title_show' => 1, 'what' => 'post', 'age_bool' => 'newer' , 'age_num' => 2 , 'age_unit' => 'year' , 'count' => 3 , 'order' => 'rand', 'template' => 'c_default_tiny.php', ),
						'text' => array( 'title' => 'Above this is a post loop widget' , 'text' => 'Use multiple post loop widgets to feature stories anywhere on the page. This widget shows stories published in the past year, but you can select stories by category, tag, or other criteria.' ),
					),
				),
			)
		);
	}// end initDefaults

	
/**
 * Adds a layout with defined spot sizes and widgets
 *
 */
	public function addNewLayout( $layout, $default_structure = FALSE )
	{
		if( !in_array( $layout, (array) $this->layouts ))
		{
			if( !is_array( $default_structure ))
				$default_structure = $this->default_structure;

			$this->layouts[] = $layout;
			update_option( $this->option_layouts , $this->layouts , NULL , 'no' );

			// is this layout already saved (as when called from updateLayout())?
			if( !get_option( $layout ))
			{
				$default_structure['name'] = ucfirst( str_replace('nines-layout-', '', $layout ));
				$default_structure['slug'] = $layout;

				// save the layout if it doesn't exist
				update_option( $layout, $default_structure , NULL , 'no' );

				// update the rules
				if( is_array( $default_structure['rules'] ))
					$this->updateRules();
			}

			// do we have a list of default widgets?
			if( is_array( $default_structure['widgets'] ))
			{

				// get WP's list of active widgets
				$sidebars_widgets = get_option( 'sidebars_widgets' );
				
				foreach( $this->widget_sections as $section )
				{
					if( is_array( $default_structure['widgets'][ $section ] ) && count( $default_structure['widgets'][ $section ] ) )
					{
						// initialize this section, clearing out any previously defined widgets
						$sidebars_widgets[ $layout .'-'. $section ] = array();

						// loop over default widgets
						foreach( $default_structure['widgets'][ $section ] as $widget => $options )
						{
							//get the settings for all instances of this widget
							$widget_options = get_option( 'widget_'. $widget );
							if( ! is_array( $widget_options ))
							{
								$widget_options = array( '_multiwidget' => 1, 0 => $options );
								$widget_id = 0;
							}
							else
							{
								// create a new instance of the widget, get the ID of it
								$widget_options[] = $options;
								end( $widget_options );
								$widget_id = key( $widget_options );
							}

							// save the widget settings
							update_option( 'widget_'. $widget , $widget_options );

							// add this one to the list of active widgets
							$sidebars_widgets[ $layout .'-'. $section ][] = $widget .'-'. $widget_id;
						} // end foreach
					} // end if
				} // end foreach

				// save the list of active widgets
				update_option( 'sidebars_widgets' , $sidebars_widgets );
			} // end if

			// initialize the layout
			$this->$layout = new NineSpotLayout( $layout, TRUE );

		}//end if
	}//end addNewLayout

/**
 * Adds an administration menu for 9spot
 *
 */
	public function admin_menu_add() 
	{
		add_submenu_page(
			'themes.php'
			, __('9spot Settings', '9spot')
			, __('Theme Layouts', '9spot')
			, 0
			, $this->layout_page
			, array(&$this, 'admin_layout')
		);
		
		foreach($this->layouts as $layout)
		{
			add_submenu_page(
				'themes.php'
				, __(' - ' . $this->$layout->name . ' Widgets', '9spot')
				, __(' - ' . $this->$layout->name . ' Widgets', '9spot')
				, 0
				, 'widgets.php?show_layout=' . $this->$layout->slug
			);
		}//end foreach
	}// end admin_menu_add

/**
 * Outputs the layout administration page
 *
 */
	public function admin_layout()
	{
		echo '<link rel="stylesheet" href="' . get_bloginfo('template_directory').'/admin/grid.css"/>';
		echo '<link rel="stylesheet" href="' . get_bloginfo('template_directory').'/admin/style.css"/>';
		
		$selected_layout = ($_GET['layout']) ? sanitize_title( $_GET['layout'] ) : $this->layout_default;
		
		// is an action being done on a layout?
		if($_GET['action'] || $_POST)
		{
			// is a layout being deleted?
			if($_GET['action'] == 'delete')
			{
				// delete the layout
				$this->deleteLayout( $selected_layout );
				
				// set the selected layout to the default
				$selected_layout = $this->layout_default;
			}//end if
			elseif($_POST) // form was posted
			{
				$selected_layout = $this->updateLayout( $selected_layout, $_POST );
			}//end else
			
			echo '<script type="text/javascript">document.location = "', get_option('siteurl'), '/wp-admin/themes.php?page=', $this->layout_page, '&layout=',$selected_layout,'";</script>';
		}//end if
		
?>
<script type="text/javascript">
	var ninespot_max_slots = <?php echo $this->$selected_layout->grid; ?>;
</script>
<script type="text/javascript" src="<?php echo get_bloginfo('template_directory'); ?>/js/admin.js"></script>

<div id="ninespot-settings">
<ul id="ninespot-tabs">
	<li class="<?php echo ($selected_layout == $this->layout_default ) ? 'active' : ''; ?>"><a href="?page=<?php echo $this->layout_page; ?>&layout=<?php echo $this->layout_default; ?>">Default</a></li>
<?php
	foreach($this->layouts as $layout):
		if($layout != $this->layout_default): 
?>
			<li class="<?php echo ($selected_layout == $layout) ? 'active' : ''; ?>"><a href="?page=<?php echo $this->layout_page; ?>&layout=<?php echo $layout; ?>"><?php echo $this->$layout->name; ?></a></li>
<?php 
		endif;
	endforeach;
	
	if($selected_layout == $this->layout_new):
?>
			<li class="active"><a href="?page=<?php echo $this->layout_page; ?>&layout=<?php echo $this->layout_new; ?>">New Layout</a></li>
<?php else: ?>
	<li><a href="?page=<?php echo $this->layout_page; ?>&layout=<?php echo $this->layout_new; ?>" class="ninespot-add">( + )</a></li>
<?php endif; ?>
</ul>
<?php
	if($selected_layout == $this->layout_new)
	{
		$this->$selected_layout = new NineSpotLayout($this->default_structure);
	}//end if

	$new_slug = ($selected_layout == $this->layout_new) ? 'The slug will be created when you save this layout.' : $selected_layout;
	include $this->dir . '/admin/theme_settings.php';
?>
</div>
<?php
	}//end admin_layout

/**
 * Deletes a layout
 *
 */
	public function deleteLayout($layout)
	{
		if($layout != $this->layout_default && in_array($layout, $this->layouts))
		{
			// delete the layout object
			unset($this->$layout);
			
			// remove the layout from registered layouts
			$this->layouts = array_diff( $this->layouts, array( $layout ) );
			
			// update registered layouts in options
			update_option( $this->option_layouts, $this->layouts , NULL , 'no' );
	
			// delete the layout from options
			delete_option( sanitize_title( $layout ) );
			
			// @todo: delete layout from rules
		}//end if
	}//end deleteLayout

/**
 * Generates a unique name if the provided name already exists
 *
 * @param $name \b Layout name to have uniqueness forced on it
 * @return string
 */
	public function uniqueName($name)
	{
		$name_attempt = $name;
		$match = true;
		
		// build slug/number combos until an unused slug is found
		for($i = 1; $match; $i++)
		{
			$match = false;
			
			if($i > 1) $name_attempt = $name.' ('.$i.')';
			else $name_attempt = $name;
			
			foreach($this->layouts as $layout)
			{
				if($this->$layout->name == $name_attempt)
				{
					$match = true;
				}//end if
			}//end if
		}//end while
		
		// when we get to here, a valid slug was built.  Assign to slug.
		$name = $name_attempt;
		
		// unset temp variable
		unset($name_attempt);
		
		return $name;
	}//end uniqueName

/**
 * Generates a unique slug if the provided slug already exists
 *
 * @param $slug \b Layout slug to have uniqueness forced on it
 * @return string
 */
	public function uniqueSlug($slug)
	{
		$slug_attempt = $slug;
		
		// build slug/number combos until an unused slug is found
		for($i = 1; in_array($slug_attempt, $this->layouts); $i++)
		{
			$slug_attempt = $slug.'-'.$i;
		}//end while
		
		// when we get to here, a valid slug was built.  Assign to slug.
		$slug = $slug_attempt;
		
		// unset temp variable
		unset($slug_attempt);
		
		return $slug;
	}//end uniqueSlug

/**
 * Updates/Creates layouts based on posted data ($_POST)
 *
 * @param $selected_layout \b Layout associated with the the current update
 * @param $post_data \b _POST array
 * @return string
 */
	public function updateLayout($selected_layout, $post_data)
	{
		// is this a new layout?
		$new_layout = trim($post_data['slug']) == $this->layout_new;
		
		$reslugged_layout = trim($post_data['slug']) != $selected_layout;

		// if this is a new layout, sanitize the layout name, otherwise grab the posted slug
		$slug = ($new_layout) ? sanitize_title($post_data['name']) : $post_data['slug'];
		
		// ensure the proper prefix is added to the layout slug
		if( !preg_match('/^'.$this->layout_prefix.'/', $slug) )
		{
			$slug = $this->layout_prefix . $slug;
		}//end if
		
		$name = $post_data['name'];
		
		// if this is a new slug or a renamed slug, make sure 
		// it doesn't overlap with a pre-existing one
		if($new_layout || $reslugged_layout)
		{
			$slug = $this->uniqueSlug($slug);
			$name = $this->uniqueName($name);
		}//end if

		// prepare the layout structure
		$layout = array(
			'name' => $name,
			'slug' => $slug,
			'spots' => array(),
			'copy_spots' => array(),
			'rules' => array(
				'is_x' => array_values( array_intersect( $this->is_x_opts , array_keys( $post_data['rule_is_x'] ))),
				'id' => array_filter( array_map( 'absint', explode( ',', $post_data['rule_post_id'] ))),
				'parent_id' => array_filter( array_map( 'absint', explode( ',', $post_data['rule_parent_id'] ))),
			),
		);

		// add all posted sections to the layout structure
		foreach( $this->structure_sections as $section )
		{

			$layout['spots'][$section] = $post_data[$section];

			if( 'body' == $section )
			{
				for($i = 1; $i < 5; $i++)
				{
					if( in_array( $post_data['copy_spots'][$section][$i] , $this->layouts ))
					{
						$layout['copy_spots'][$section][$i] = $post_data['copy_spots'][$section][$i];
						$layout['copy_spots'][$section .'-'. $i] = $post_data['copy_spots'][$section][$i];
					}
					else
					{
						$layout['copy_spots'][$section][$i] = 0;
					}
				}
			}
			else
			{
				if( in_array( $post_data['copy_spots'][$section] , $this->layouts ))
					$layout['copy_spots'][$section] = $post_data['copy_spots'][$section];
				else
					$layout['copy_spots'][$section] = 0;
			}

		}//end foreach

		// add the layout structure to options
		update_option( $slug, $layout , NULL , 'no' );

		// add the layout structure to options
		update_option( $slug, $layout , NULL , 'no' );

		// is this a renamed slug?
		if( $reslugged_layout )
		{
			$this->deleteLayout( $selected_layout );
		}//end if

		$this->updateRules();

		// set the selected layout to the slug of the updated layout
		$selected_layout = $layout['slug'];
		
		// if this is a new layout, add the layout to the registered layouts
		if($new_layout || $reslugged_layout)
		{
			$this->addNewLayout( $selected_layout );
		}//end if
		
		return $selected_layout;
	}//end updateLayout

	public function updateRules()
	{

		// reset the layout list
		$this->layouts = get_option( $this->option_layouts );

		$the_rules = array();

		if( is_array( $this->layouts ))
		{
			foreach( $this->layouts as $layout_name )
			{
				if( $layout = get_option( $layout_name ))
				{
					// add the new layout rules to the rule list
					foreach( $layout['rules'] as $rule_type => $rules )
					{
						foreach( $rules as $rule )
						{
							$the_rules[ $rule_type ][ $rule ] = $layout['slug'];
						}
					}
				}
			}
		}

		update_option( $this->option_rules , $the_rules , NULL , 'no' );
	}

}//end class NineSpotCore
