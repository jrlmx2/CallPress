<?php
class NineSpotLayout
{
	public $grid = 16;
	public $min_block_size = 1;

/**
 * Returns the size of a block
 *
 * @param string $block_id Identifier of the block to be sized
 * @return int
 */
	public function blockSize($section, $spot, $default_when_0 = true)
	{
		$size = $this->spots[$section]['size'][$spot];

		if(!$size || $size < $this->min_block_size) 
			$size = ($default_when_0) ? $this->min_block_size : 0;
		return $size;
	}//end blockSize


/**
 * runs WP's built-in body_class(), adding the 9spot layout name
 *
 */
	public function body_class()
	{
		$columns_class = $this->sectionClassSlug( 'body' );
		body_class( $this->slug .' '. $this->slug .'-'. $columns_class .' nines-layout-'. $columns_class );
	}//end body_class

/**
 * Adds a new "spot" to the list of possible areas
 *
 * @param string $id Identifier of the "spot"
 * @param string $name Name of "spot"
 */
	public function createSpot($id, $name, $sizing)
	{
		$full_id = $this->slug . '-' . $id;

		$this->spots[ $id ] = array(
			'id' => $full_id,
			'short_id' => $id,
			'name' => $name . ' (' . $this->short_slug .')',
			'size' => $sizing
		);
	}//end createSpot

/**
 * Registers "spots" as widget areas.
 *
 */
	public function loadSpots()
	{
		foreach( $this->spots as $id => $spot )
		{
			if( $this->copy_spots[ $id ] )
			{
				if( !is_admin() || 'body' == $id )
					$this->registerSpot( $spot['short_id'] , $this->copy_spots[ $spot['short_id'] ] );
			}
			else
			{
				$this->registerSpot($id);
			}
		}//end foreach
	}//end loadSpots

/**
 * Given a column number, returns a column letter
 *
 */
	public function numberToColumn($num)
	{
		switch($num)
		{
			case 1:
				$letter = 'a';
			break;
			case 2:
				$letter = 'b';
			break;
			case 3:
				$letter = 'c';
			break;
			case 4:
				$letter = 'd';
			break;
			default:
				$letter = 'invalid_column';
			break;
		}//end switch

		return $letter;
	}//end numberToColumn

/**
 * Prepares widgets for output
 *
 * @param string $id Identifier of the "spot"
 */
	public function prepWidgets($id, $before = '', $after = '')
	{
		global $wp_registered_sidebars, $wp_registered_widgets;
	
		$widgets = array();

		if ( is_int($id) ) 
		{
			$id = "sidebar-$id";
		}//end if
		else 
		{
			$id = str_replace( $this->slug, '', $id );
			if( $this->copy_spots[ $id ] )
				$id = $this->copy_spots[ $id ] .'-'. $id;
			else
				$id = $this->slug .'-'. $id;
		}//end else

		if( !( $sidebar = $wp_registered_sidebars[ $id ] ))
			return $widgets;

		foreach((array) $this->widgets[$id] as $widget)
		{
			if ( !isset($wp_registered_widgets[$widget]) ) continue;

			$params = array_merge(
				array( 
					array_merge( 
						$sidebar, 
						array(
							'widget_id' => $widget, 
							'widget_name' => $wp_registered_widgets[$widget]['name']
						)
					)
				),
				(array) $wp_registered_widgets[$widget]['params']
			);

			// Substitute HTML id and class attributes into before_widget
			$classname_ = '';
			foreach ( (array) $wp_registered_widgets[$widget]['classname'] as $cn ) 
			{
				if ( is_string($cn) )
					$classname_ .= '_' . $cn;
				elseif ( is_object($cn) )
					$classname_ .= '_' . get_class($cn);
			}//end foreach
			$classname_ = ltrim($classname_, '_');

			$params = apply_filters( 'dynamic_sidebar_params', $params );

			$callback = $wp_registered_widgets[$widget]['callback'];

			if ( is_callable($callback) ) 
			{
				$widgets[] = array(
					'widget' => $widget,
					'callback' => $callback, 
					'params' => $params,
					'class' => $classname_);
			}//end if
		}//end foreach

		return $widgets;
	}//end prepWidgets

/**
 * Registers "spots" as widget areas.
 *
 * @param string $id Identifier of the "spot"
 */
	public function registerSpot( $id , $base = '' )
	{
		if( empty( $base ) )
			$base = $this->slug;

		if( $id == 'body' )
		{
			for($i = 1; $i <= 4; $i++)
			{
				$params = $this->spot_params;
				if( is_array( $base ) && $base[ $i ] )
					$params['id'] = $base[ $i ] . '-body-' . $i;
				else
					$params['id'] = $this->slug . '-body-' . $i;

				$params['name'] = 'Body Column ' . $i .' ('.$this->short_slug.')';

				if( $this->render_widget_areas && $this->spots[$id]['size'][$i] )
					register_sidebar($params);

			}//end for
		}//end if
		else
		{
			$params = $this->spot_params;
			$params['id'] = $base .'-'. $id;
			$params['name'] = $this->spots[$id]['name'];

			if( $this->render_widget_areas && $this->spotEnabled( $id ))
				register_sidebar( $params );
		}//end else
	}//end registerSpot

/**
 * Renders a body spot area
 *
 * @param string $id Identifier of the "spot"
 */
	public function renderBody()
	{
		$slots = 0;
		$blocks = array();

		for($i = 1; $i <= ($this->grid / $this->min_block_size); $i++)
		{
			if($slots >= $this->grid) break;

			$id = 'body-' . $i;

			if($slot = $this->blockSize( 'body', $i ))
			{
				$blocks[] = array(
					'id' => $id,
					'size' => $slot
				);

				$slots += $slot;
			}//end if
		}//end for

		foreach($blocks as $key => $block)
		{
			$extra_classes = '';

			if($key == 0)
			{
				$extra_classes = 'alpha';
			}//end if
			elseif($key+1 == sizeof($blocks))
			{
				$extra_classes = 'omega';
			}//end elseif

			echo '<div class="grid_' . $block['size'] . ' nines-' . $block['id'] . ' ' . $extra_classes . '">'."\n";

			$widgets = (array) $this->prepWidgets( $block['id'] );
			if(empty($widgets))
			{
				echo '&nbsp;';
			}//end if
			else
			{
				foreach($widgets as $wkey => $widget_data)
				{
					$slot = $block['size'];
					$widget_data['params'][0]['before_widget'] = sprintf($widget_data['params'][0]['before_widget'], $widget_data['widget'], $widget_data['class']);

					call_user_func_array($widget_data['callback'], $widget_data['params']);
					echo "\n". $this->timer( 'body-'. $key .' widget '. $wkey , 'widget' ) ."\n";
				}//end foreach
			}//end else
			echo "</div>\n". $this->timer( 'body-'. $key , 'area' ) ."\n";
		}//end foreach
	}//end renderBody

/**
 * Renders a spot area
 *
 * @param string $id Identifier of the "spot"
 */
	public function renderSpot($id)
	{

		echo '<div id="'.$id.'-wrapper">'."\n";
		echo '<div id="'.$id.'" class="container_16 ' . $this->sectionClass($id, false) . '">'."\n";
		echo '	<div class="inner grid_16">'."\n";

		if($id == 'body')
		{
			$this->renderBody();
		}
		else
		{
			$slots = 0;
			$widgets = array_slice((array) $this->prepWidgets($id), 0, 4);

			$blocks = array();

			foreach($widgets as $key => $widget_data)
			{

				if($slots >= $this->grid) break;

				if($slot = $this->blockSize( $id, $key + 1 ))
				{
					$widget_data['size'] = $slot;
					$widget_data['id'] = 'nines-' . $id . '_' . ($key + 1);
					$blocks[] = $widget_data;
					$slots += $slot;
				}
			}//foreach

			if(empty($blocks))
			{
				echo '&nbsp;';
			}//end if
			else
			{
				foreach($blocks as $key => $widget_data)
				{
					$extra_classes = '';
					if($key == 0)
					{
						$extra_classes = 'alpha';
					}//end if
					elseif($key == sizeof($blocks)-1)
					{
						$extra_classes = 'omega';
					}//end elseif

					$widget_data['params'][0]['before_widget'] = sprintf($widget_data['params'][0]['before_widget'], $widget_data['widget'], 'grid_' . $widget_data['size'] . ' ' .$widget_data['class'] . ' ' . $widget_data['id'] . ' ' . $extra_classes);

					call_user_func_array($widget_data['callback'], $widget_data['params']);
					echo "\n". $this->timer( $id .' widget '. $key , 'widget' ) ."\n";
				}//end foreach
			}//end else
			echo $this->timer( $id , 'area' ) ."\n";
		}//end else

		echo '		<div class="clear">&nbsp;</div>'."\n";
		echo '	</div>'."\n";
		echo '</div>'."\n";
		echo '</div>'."\n";
	}//end renderSpot

	public function renderSpotIfEnabled($section)
	{
		if($this->spotEnabled( $section ))
			$this->renderSpot( $section );
	}//end renderSpotIfEnabled

/**
 * Returns the calculated section class
 *
 * Possible sections are: head, nav, avant-body, body, apres-body, foot, apres-foot
 *
 * @param $id \b Identifier of the "section"
 * @param $echo \b specifies whether to echo the result rather than return it.  Defaults to true (for echo)
 * @return boolean
 */
	public function sectionClass( $section, $echo = true)
	{
		$class = 'nines-'. $section .' nines-'. $section .'-'. $this->sectionClassSlug( $section );

		if($echo) echo $class;
		else return $class;
	}//end sectionClass

/**
 * Returns a calculated slug identifying the layout of a section
 *
 * Possible sections are: head, nav, avant-body, body, apres-body, foot, apres-foot
 *
 * @param $id \b Identifier of the "section"
 * @return string
 */
	public function sectionClassSlug( $section )
	{
		$class = '';
		$area_width = 0;
		foreach((array) $this->spots[ $section ]['size'] as $sub_section => $size)
		{
			if($area_width + $size <= 16)
			{
				$area_width += $size;

				$class .= $this->numberToColumn( $sub_section ).$size;
			}//end if
		}//end foreach

		return $class;
	}//end sectionClass

/**
 * Returns whether or not the given "spot" is enabled.
 *
 * @param string $id Identifier of the "spot"
 * @return boolean
 */
	public function spotEnabled( $id )
	{
		$total_size = array_sum((array) $this->spots[$id]['size']);
		return $total_size > 0;
	}//end spotEnabled

	public function credit()
	{
		$credit_array = array(
			'Proudly powered by <a href="http://wordpress.org/">WordPress</a> and <a href="http://code.google.com/p/9spot/">9spot</a>, by <a href="http://borkweb.com/">Matthew Batchelder</a> & <a href="http://maisonbisson.com/">Casey Bisson</a>.',

			'Created with <a href="http://wordpress.org/">WordPress</a> and <a href="http://code.google.com/p/9spot/">9spot</a>, a coproduction of <a href="http://maisonbisson.com/">Casey Bisson</a> and <a href="http://borkweb.com/">Matthew Batchelder</a>.',

			'Powered by <a href="http://wordpress.org/">WordPress</a> and <a href="http://code.google.com/p/9spot/">9spot</a>, a <a href="http://borkweb.com/">Matthew</a> & <a href="http://maisonbisson.com/">Casey</a> joint.',

			'Themed with <a href="http://code.google.com/p/9spot/">9spot</a>, by <a href="http://borkweb.com/">Matthew Batchelder</a> & <a href="http://maisonbisson.com/">Casey Bisson</a>.',

			'Sweetened  with <a href="http://code.google.com/p/9spot/">9spot</a>, a <a href="http://maisonbisson.com/">Casey Bisson</a> and <a href="http://borkweb.com/">Matthew Batchelder</a> production.',

			'Made awesome with <a href="http://code.google.com/p/9spot/">9spot</a>, a <a href="http://borkweb.com/">Matthew Batchelder</a> & <a href="http://maisonbisson.com/">Casey Bisson</a> production.',

			'Erected with <a href="http://code.google.com/p/9spot/">9spot</a>, a <a href="http://maisonbisson.com/">Casey Bisson</a> and <a href="http://borkweb.com/">Matthew Batchelder</a> production.',

			'Spiffied up with <a href="http://code.google.com/p/9spot/">9spot</a>, a <a href="http://borkweb.com/">Matthew Batchelder</a> & <a href="http://maisonbisson.com/">Casey Bisson</a> production.',

			'This site has gone groovy with <a href="http://code.google.com/p/9spot/">9spot</a>, a <a href="http://maisonbisson.com/">Casey Bisson</a> and <a href="http://borkweb.com/">Matthew Batchelder</a> production.',
		);
?>
		<div id="ninescredit" role="contentinfo">
			<?php echo $credit_array[ array_rand( $credit_array ) ]; ?>
		</div>
<?php
	}//end spotEnabled


	public function timer( $name='' , $group = 'widget' )
	{
		// credit for this function belongs to Mark Jaquith http://coveredwebservices.com/ 
		if( ! isset( $this->last_timer->$group ))
			$this->last_timer->$group = 0;
		$current_timer = timer_stop( 0 );
		$change = $current_timer - $this->last_timer->$group;
		$this->last_timer->$group = $current_timer;
		return "<!-- Total Time: $current_timer | {$name}: $change -->";
	}

	public function __construct($layout, $render_widget_areas = true)
	{
		// TODO: should get_option() the prefs for this layout and revert to default if the named layout doesn't exist.

		$this->spot_params = array(
			'before_widget' => '<div id="widget-%1$s" class="widget %2$s"><div class="widget-inner">'."\n",
			'after_widget'  => '</div></div>'."\n",
			'before_title'  => '<h2 class="widgettitle">',
			'after_title'   => '</h2>'."\n"
		);

		$this->render_widget_areas = $render_widget_areas;

		if(!is_array($layout))
		{
			$layout = get_option( $layout );
		}//end else

		$this->slug = empty( $layout['slug'] ) ? 'nines-layout-default' : sanitize_title( $layout['slug'] );
		$this->short_slug = str_replace('nines-layout-', '' , $this->slug);
		$this->name = $layout['name'];
		$this->spots = array();

		if( $this->slug <> 'nines-layout-default' )
		{
			$this->copy_spots = $layout['copy_spots'];
			$this->rules = $layout['rules'];
		}

		foreach( (array) $layout['spots'] as $spot => $data )
		{
/*
			if( 'body' == $spot )
			{
				for($i = 1; $i < 5; $i++)
				{
					if( $this->copy_spots['copy_spots'][$spot][$i] )
					{
						$copied_layout = get_option( $this->copy_spots['copy_spots'][ $spot ][$i] );
						$data = $copied_layout['spots'][ $spot ][$i];
					
					}
				}
			}
			elseif( $this->copy_spots['copy_spots'][$spot] )
			{
				$copied_layout = get_option( $this->copy_spots['copy_spots'][ $spot ] );
				$data = $copied_layout['spots'][ $spot ];

			}

*/
			switch($spot)
			{
				case 'head': $name = 'Head'; break;
				case 'nav': $name = 'Nav'; break;
				case 'avant-body': $name = 'Avant Body'; break;
				case 'body': $name = 'Body'; break;
				case 'apres-body': $name = 'Apres Body'; break;
				case 'foot': $name = 'Foot'; break;
				case 'apres-foot': $name = 'Apres Foot'; break;
			}//end switch

			$this->createSpot($spot, $name, $data);
		}//end foreach
		$this->loadSpots();

		$this->widgets = (array) wp_get_sidebars_widgets();
	}//end constructor
}//end class NineSpotLayout
