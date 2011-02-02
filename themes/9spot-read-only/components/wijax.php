<?php
/**
 * Wijax class
 *
 */
class bSuite_Wijax {
	function bSuite_Wijax()
	{
		global $bsuite;

		$this->path_web = is_object( $bsuite ) ? $bsuite->path_web : get_template_directory_uri();

		add_action( 'init', array( &$this, 'init' ));
		add_action( 'widgets_init', array( &$this , 'widgets_init' ) , 1 );
	}

	function init()
	{
		add_rewrite_endpoint( 'wijax' , EP_ALL );
		add_filter( 'request' , array( &$this, 'request' ));

		if( ! is_admin())
			add_filter( 'print_footer_scripts', array( &$this, 'print_js' ));
	}

	function widgets_init() {
		register_widget( 'Wijax_Widget' );

		register_sidebar( array(
			'name' => __( 'Wijax Widgets', 'Bsuite' ),
			'id' => 'wijax-area',
			'description' => __( 'Place widgets here to configure them for lazy loading using the Wijax widget.', 'Bsuite' ),
		) );
	}

	public function request( $request )
	{
		if( isset( $request['wijax'] ))
		{
			add_filter( 'template_redirect' , array( &$this, 'redirect' ), 0 );
			define( IS_WIJAX , TRUE );
			do_action( 'do_wijax' );
		}

		return $request;
	}

	function redirect()
	{
		global $wp_registered_widgets;

		$requested_widgets = array_filter( array_map( 'trim' , (array) explode( ',' , get_query_var('wijax') )));

		if( 1 > count( $requested_widgets ))
			return;

		foreach( $requested_widgets as $key )
		{
			if( ! $widget_data = $wp_registered_widgets[ $key ] )
				continue;

			preg_match( '/\-([0-9]+)$/' , $key , $instance_number );
			$instance_number = absint( $instance_number[1] );
			if( ! $instance_number )
				continue;

			$widget_data['widget'] = $key;
	
			$widget_data['params'][0] = array(
				'name' => $wp_registered_widgets[ $key ]['name'],
				'id' => $key,
				'before_widget' => '<div id="widget-%1$s" class="widget %2$s"><div class="widget-inner">'."\n",
				'after_widget'  => '</div></div>'."\n",
				'before_title'  => '<h2 class="widgettitle">',
				'after_title'   => "</h2>\n",
				'widget_id' => $key,
				'widget_name' => $wp_registered_widgets[ $key ]['name'],
			);

//print_r( $widget_data['callback'][0]->number );

			$widget_data['params'][1] = array(
				'number' => absint( $instance_number ),
			);
	
			$widget_data['params'][0]['before_widget'] = sprintf($widget_data['params'][0]['before_widget'], $widget_data['widget'], 'grid_' . $widget_data['size'] . ' ' .$widget_data['class'] . ' ' . $widget_data['id'] . ' ' . $extra_classes);

			ob_start();			
			call_user_func_array( $widget_data['callback'], $widget_data['params'] );
			$params['text'] = ob_get_clean();
			$params['callback'] = 'jQuery.wijax.channelLoad';
			if($_GET['js_callback']) $params['js_callback'] = $_GET['js_callback'];
			$params['channel_id'] = $_GET['channel_id'];
			Wijax_Encode::out( 'callback' , $params );

		}//end foreach

/*	
		if($_GET['output'] == 'js')
		{
			$params = array(
				'callback' => '$.my.channelLoad',
				'channel_id' => $_GET['channel_id']
			);
			if($_GET['js_callback']) $params['js_callback'] = $_GET['js_callback'];
			Channel::out('callback', $params);
		}//end if
*/
		die;
	}

	function print_js(){
		?>
		<script type="text/javascript">	
		;(function($){
			$(document).ready(function(){
				$(window).bind('scroll', function(event){
					setTimeout(function() {
						var gobe = document.createElement('script'); gobe.type = 'text/javascript'; gobe.async = true;
						gobe.src = '<?php echo $this->path_web . '/components/js/wijax-library.js'; ?>';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(gobe, s);      
					}, 1);
					if(typeof $.go != 'undefined'){
						$.go.channelInit( $('#secondary-content'), '<?php home_url(); ?>/wijax/go-brand-explorer-2');
						$(this).unbind(event);
					}
				});
			});
		})(jQuery);
		</script>
<?php
	}

} //end bSuite_Wijax

// initialize that class
new bSuite_Wijax();



/**
 * Wijax widget class
 *
 */
class Wijax_Widget extends WP_Widget {

	function Wijax_Widget() {
		$widget_ops = array('classname' => 'widget_wijax', 'description' => __( 'Lazy load widgets after DOMDocumentReady') );
		$this->WP_Widget('wijax', __('Wijax Widget Lazy Loader'), $widget_ops);

		add_filter( 'wijax-base-current' , array( $this , 'base_current' ) , 5 );
		add_filter( 'wijax-base-home' , array( $this , 'base_home' ) , 5 );
	}

	function widget( $args, $instance ) {
		extract( $args );
		//print_r( $instance );

		$base = apply_filters( 'wijax-base-'. $instance['base'] , '' );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
//echo $base . $instance['widget'];
		echo $before_widget;
		if ( ! empty( $category_description ) )
			echo '<div class="archive-meta">' . $category_description . '</div>';
		echo '<div class="clear"></div>';
		echo $after_widget;

	}

	function base_home( $base )
	{
		return home_url() .'/wijax/';
	}

	function base_current( $base )
	{
		return esc_url_raw( home_url() . $_SERVER['REQUEST_URI'] .'/wijax/' );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['widget'] = sanitize_title( $new_instance['widget'] );
		$instance['widget-custom'] = esc_url_raw( $new_instance['widget-custom'] );
		$instance['base'] = sanitize_title( $new_instance['base'] );

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, 
			array( 
				'title' => '', 
				'homelink' => get_option('blogname'),
				'maxchars' => 35,
			)
		);

		$title = esc_attr( $instance['title'] );
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /><br />
			<small>For convenience, not shown publicly</small
		</p>
<?php
		echo $this->control_widgets( $instance );
		echo $this->control_base( $instance );
	}

	function control_widgets( $instance , $whichfield = 'widget' )
	{
		// get the available widgets
		$sidebars_widgets = wp_get_sidebars_widgets();
		$list = '';
		foreach( (array) $sidebars_widgets['wijax-area'] as $item )
		{
			if( $number == $this->number )
				continue;

			$list .= '<option value="'. $item .'" '. selected( $instance[ $whichfield ] , $item , FALSE ) .'>'. $item .'</option>';
		}
		$list .= '<option value="custom" '. selected( $instance[ $whichfield ] , 'custom' , FALSE ) .'>Custom</option>';

		return '<p><label for="'. $this->get_field_id( $whichfield ) .'">Widget</label><select name="'. $this->get_field_name( $whichfield ) .'" id="'. $this->get_field_id( $whichfield ) .'" class="widefat">'. $list . '</select></p><p><label for="'. $this->get_field_id( $whichfield .'-custom' ) .'">Custom Widget Path</label><input name="'. $this->get_field_name( $whichfield .'-custom' ) .'" id="'. $this->get_field_id( $whichfield .'-custom' ) .'" class="widefat" type="text" value="'. esc_url( $instance[ $whichfield .'-custom' ] ).'"></p>';
	}

	function control_base( $instance , $whichfield = 'base' )
	{

		$bases = apply_filters( 'wijax-bases' , array(
			'current' => 'The currently requested URL',
			'home' => 'The blog home URL',
		));

		foreach( (array) $bases as $k => $v )
			$list .= '<option value="'. $k .'" '. selected( $instance[ $whichfield ] , $k , FALSE ) .'>'. $v .'</option>';

		return '<p><label for="'. $this->get_field_id( $whichfield ) .'">Base URL</label><select name="'. $this->get_field_name( $whichfield ) .'" id="'. $this->get_field_id( $whichfield ) .'" class="widefat">'. $list . '</select><br /><small>The base URL affects widget content and caching</small></p>';
	}

}// end Wijax_Widget

/**
 *
 * Code for simplifying channel creation
 *
 * @module		channel.class.php
 * @author		Vasken Hauri
 * 
 * This code relies heavily upon the channel class created by 
 * Zachary Tirrell <zbtirrell@plymouth.edu> and  Matthew Batchelder <mtbatchelder@plymouth.edu>
 */ 

class Wijax_Encode
{
	/**
	 * callback
	 *
	 * Generate a callback function for the given text
	 *
	 * @since		version 2.0.0
	 * @access	public
	 * @param  	string $text Text to output
	 * @param  	string $callback Callback function
	 * @param   mixed $params Parameters to be appended to the JS callback
	 */
	public function callback($text, $callback, $params)
	{
		//callback is being passed in separately...ensure that its corresponding params entry is unset
		unset($params['callback']);
		
		//since the text to output is now part of params, unset it before the callback
		unset($params['text']);

		$find = array(
			"'",
			"\n",
			"\r",
			"\t",
			"document.write('');\n"
		);
		
		$replace = array(
			"\'",
			"'+\n'",
			'',
			'',
			''
		);
		
		//create a variable to put the page content into
		$text="var the_text_to_output='".str_replace($find,$replace,$text)."';\n";
		
		//begin the callback
		$output = $text.strip_tags($callback).'(the_text_to_output';
	
		//im not really a fan of this...i'll bet there's a better way
		rsort($params);
	
		//are there parameters set?
		if(is_array($params) && !empty($params))
		{
			//yup!  implode those puppies and append to the output
			$output .= ',"'.implode('","',$params).'"';
		}//end if
		
		//finish off the output
		$output .= ');';
		
		return $output;
	}//end callback

	/**
	 * out
	 *
	 * A utility function that outputs the channel content returned by Channel::text
	 *
	 * @since		version 2.0.0
	 * @access	public
	 * @param  	string $type Type of output (callback or write)
	 * @param   mixed $params Parameters to be appended to the JS callback
	 */
	public static function out($type = 'write', $params = false)
	{
		//echo the return value of text
		echo self::text($type, $params);		
	}//end out

	/**
	 * start
	 *
	 * Prepares the page for channel output
	 *
	 * @since		version 2.0.0
	 * @access	public
	 */
	public function start()
	{
		ob_start();
	}//end start

	/**
	 * text
	 *
	 * Return the channel in a specified format
	 *
	 * @since		version 2.0.0
	 * @access	public
	 * @param  	string $type Type of output (callback or write)
	 * @param   mixed $params Parameters to be appended to the JS callback
	 */
	public function text($type = 'write', $params = false)
	{
		if($type!='html') header('Content-type: text/javascript');
		
		extract($params);
		
		if($type == 'callback')
		{
			$text = self::callback($text, $params['callback'],$params);
		}//end if
		elseif($type == 'html')
		{
			$text = self::html($text, $params);
		}//end elseif
		else
		{
			$text = self::write($text);
		}//end else
		return $text;
	}//end text

	/**
	 * write
	 *
	 * Formats the given text as a series of document.writes
	 *
	 * @since		version 2.0.0
	 * @access	public
	 * @param  	string $text Text to output
	 */
	public function write($text)
	{
		$find = array("'","\n","\r","\t","document.write('');\n");
		$replace = array("\'","');\ndocument.write('",'','','');
		$text="document.write('".str_replace($find,$replace,$text)."');";
		return $text;
	}//end write

	/**
	 * makeJSFriendly
	 *
	 * @since		version 1.0.0
	 */
	public function makeJSFriendy($text,$callback='',$vars='')
	{
		if($callback)
		{
			$find = array('\\',"'","\n","\r","\t","document.write('');\n");
			$replace = array("&#92;","\'","'+\n'",'','','');
			$text="var the_text_to_output='".str_replace($find,$replace,$text)."';";
			return ''.$text."\n".$callback.'(the_text_to_output'.$vars.');';
		}
		else
		{
			$find = array("'","\n","\r","\t","document.write('');\n");
			$replace = array("\'","');\ndocument.write('",'','','');
			$text="document.write('".str_replace($find,$replace,$text)."');";
			return $text;
		}//end else
	}//end makeJSFriendly
}//end class Channel
