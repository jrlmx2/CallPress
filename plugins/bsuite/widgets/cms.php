<?php

/**
 * PostLoop widget class
 *
 */
class bSuite_Widget_PostLoop extends WP_Widget {

	function bSuite_Widget_PostLoop() {
		$widget_ops = array('classname' => 'widget_postloop', 'description' => __( 'Build your own post loop') );
		$this->WP_Widget('postloop', __('Post Loop'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title']);
	
		$templates = $this->get_templates();
	
		if( 'normal' == $instance['what'] ){
			global $wp_query;
			$ourposts = &$wp_query;
		}else{
			$criteria['suppress_filters'] = TRUE;
	
			if( in_array( $instance['what'], array( 'post', 'page', 'attachment' )))
				$criteria['post_type'] = $instance['what'];
	
			if( !empty( $instance['categories_in'] ))
				$criteria['category__'. ( in_array( $instance['categoriesbool'], array( 'in', 'and', 'not_in' )) ? $instance['categoriesbool'] : 'in' ) ] = array_keys( $instance['categories_in'] );

			if( !empty( $instance['categories_not_in'] ))
				$criteria['category__not_in'] = array_keys( $instance['categories_not_in'] );
	
			if( !empty( $instance['tags_in'] ))
				$criteria['tag__'. ( in_array( $instance['tagsbool'], array( 'in', 'and', 'not_in' )) ? $instance['tagsbool'] : 'in' ) ] = $instance['tags_in'];

			if( !empty( $instance['tags_not_in'] ))
				$criteria['tag__not_in'] = $instance['tags_not_in'];
	
			if( !empty( $instance['post__in'] ))
				$criteria['post__in'] = $instance['post__in'];
	
			if( !empty( $instance['post__not_in'] ))
				$criteria['post__not_in'] = $instance['post__not_in'];
	
			$criteria['showposts'] = $instance['count'];
	
			switch( $instance['order'] ){
				case 'age_new':
					$criteria['orderby'] = 'post_date';
					$criteria['order'] = 'DESC';
					break;
				case 'age_old':
					$criteria['orderby'] = 'post_date';
					$criteria['order'] = 'ASC';
					break;
				case 'pop_most':
				case 'pop_least':
				case 'comment_recent':
				case 'rand':
				default:
					$criteria['orderby'] = 'rand';
					break;
			}


			$ourposts = new WP_Query( $criteria );

	/*
	$options[$widget_number]['activity'] = in_array( $widget_var['activity'], array( 'pop_most', 'pop_least', 'pop_recent', 'comment_recent', 'comment_few') ) ? $widget_var['activity']: '';
	
	$options[$widget_number]['age'] = in_array( $widget_var['age'], array( 'after', 'before', 'around') ) ? $widget_var['age']: '';
	$options[$widget_number]['agestrtotime'] = strtotime( $widget_var['agestrtotime'] ) ? $widget_var['agestrtotime'] : '';
	
	$options[$widget_number]['relationship'] = in_array( $widget_var['relationship'], array( 'similar', 'excluding') ) ? $widget_var['relationship']: '';
	$options[$widget_number]['relatedto'] = array_filter( array_map( 'absint', $widget_var['relatedto'] ));
	*/
		}
	
		if( $ourposts->have_posts() ){
			while( $ourposts->have_posts() ){
				$ourposts->the_post();
	
				if( !isset( $instance['template'] ) || !include $templates[ $instance['template'] ]['fullpath'] ){
	?><!-- ERROR: the required template file is missing or unreadable. A default template is being used instead. -->
	<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
		<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
		<small><?php the_time('F jS, Y') ?> <!-- by <?php the_author() ?> --></small>
	
		<div class="entry">
			<?php the_content('Read the rest of this entry &raquo;'); ?>
		</div>
	
		<p class="postmetadata"><?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
	</div>
	<?php
				}
			}
		}
	

		if ( !empty( $out ) ) {
			echo $before_widget;
			if ( $title)
				echo $before_title . $title . $after_title;
		?>
		<ul>
			<?php echo $out; ?>
		</ul>
		<?php
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = wp_filter_nohtml_kses( $new_instance['title'] );
		$instance['what'] = in_array( $new_instance['what'], array( 'normal', 'post', 'page', 'attachment', 'any') ) ? $new_instance['what']: '';
		$instance['blog'] = absint( $new_instance['blog'] );

		$instance['categoriesbool'] = in_array( $new_instance['categoriesbool'], array( 'in', 'and', 'not_in') ) ? $new_instance['categoriesbool']: '';
		$instance['categories_in'] = array_filter( array_map( 'absint', $new_instance['categories_in'] ));
		$instance['categories_not_in'] = array_filter( array_map( 'absint', $new_instance['categories_not_in'] ));
		$instance['tagsbool'] = in_array( $new_instance['tagsbool'], array( 'in', 'and', 'not_in') ) ? $new_instance['tagsbool']: '';
		$tag_name = '';
		$instance['tags_in'] = array();
		foreach( array_filter( array_map( 'trim', array_map( 'wp_filter_nohtml_kses', explode( ',', $new_instance['tags_in'] )))) as $tag_name ){
			if( $temp = is_term( $tag_name, 'post_tag' ))
				$instance['tags_in'][] = $temp['term_id'];
		}
		$tag_name = '';
		$instance['tags_not_in'] = array();
		foreach( array_filter( array_map( 'trim', array_map( 'wp_filter_nohtml_kses', explode( ',', $new_instance['tags_not_in'] )))) as $tag_name ){
			if( $temp = is_term( $tag_name, 'post_tag' ))
				$instance['tags_not_in'][] = $temp['term_id'];
		}
		$instance['post__in'] = array_filter( array_map( 'absint', explode( ',', $new_instance['post__in'] )));
		$instance['post__not_in'] = array_filter( array_map( 'absint', explode( ',', $new_instance['post__not_in'] )));
		$instance['activity'] = in_array( $new_instance['activity'], array( 'pop_most', 'pop_least', 'pop_recent', 'comment_recent', 'comment_few') ) ? $new_instance['activity']: '';
		$instance['age'] = in_array( $new_instance['age'], array( 'after', 'before', 'around') ) ? $new_instance['age']: '';
		$instance['agestrtotime'] = strtotime( $new_instance['agestrtotime'] ) ? $new_instance['agestrtotime'] : '';
		$instance['relationship'] = in_array( $new_instance['relationship'], array( 'similar', 'excluding') ) ? $new_instance['relationship']: '';
		$instance['relatedto'] = array_filter( array_map( 'absint', $new_instance['relatedto'] ));
		$instance['count'] = absint( $new_instance['count'] );
		$instance['order'] = in_array( $new_instance['order'], array( 'age_new', 'age_old', 'pop_most', 'pop_least', 'relevance_most', 'comment_recent', 'rand' ) ) ? $new_instance['order']: '';
		$instance['template'] = wp_filter_nohtml_kses( $new_instance['template'] );
		$instance['columns'] = absint( $new_instance['columns'] );

		return $instance;
	}

	function form( $instance ) {
		//Defaults

		$instance = wp_parse_args( (array) $instance, 
			array( 
				'what' => 'normal', 
				'template' => 'a_default_full.php' 
				) 
			);
		$title = esc_attr( $instance['title'] );
	?>

		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p>
			<label for="<?php echo $this->get_field_id('what'); ?>"><?php _e( 'What to show:' ); ?></label>
			<select name="<?php echo $this->get_field_name('what'); ?>" id="<?php echo $this->get_field_id('what'); ?>" class="widefat">
				<option value="normal" <?php selected( $instance['what'], 'normal' ); ?>><?php _e('The default content'); ?></option>
				<option value="post" <?php selected( $instance['what'], 'post' ); ?>><?php _e('Posts'); ?></option>
				<option value="page" <?php selected( $instance['what'], 'page' ); ?>><?php _e('Pages'); ?></option>
				<option value="attachment" <?php selected( $instance['what'], 'attachment' ); ?>><?php _e('Attachments'); ?></option>
				<option value="any" <?php selected( $instance['what'], 'any' ); ?>><?php _e('Any content'); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('categoriesbool'); ?>"><?php _e( 'Categories:' ); ?></label>
			<select name="<?php echo $this->get_field_name('categoriesbool'); ?>" id="<?php echo $this->get_field_id('categoriesbool'); ?>" class="widefat">
				<option value="in" <?php selected( $instance['categoriesbool'], 'in' ); ?>><?php _e('Any of these categories'); ?></option>
				<option value="and" <?php selected( $instance['categoriesbool'], 'and' ); ?>><?php _e('All of these categories'); ?></option>
			</select>
			<ul><?php echo $this->control_categories( $instance , 'categories_in' ); ?></ul>
		</p>

		<p>
			Not in any of these categories
			<ul><?php echo $this->control_categories( $instance , 'categories_not_in' ); ?></ul>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('tagsbool'); ?>"><?php _e( 'Tags:' ); ?></label>
			<select name="<?php echo $this->get_field_name('tagsbool'); ?>" id="<?php echo $this->get_field_id('tagsbool'); ?>" class="widefat">
				<option value="in" <?php selected( $instance['tagsbool'], 'in' ); ?>><?php _e('Any of these tags'); ?></option>
				<option value="and" <?php selected( $instance['tagsbool'], 'and' ); ?>><?php _e('All of these tags'); ?></option>
			</select>

			<?php
			$tags_in = array();
			foreach( $instance['tags_in'] as $tag_id ){
				$temp = get_term( $tag_id, 'post_tag' );
				$tags_in[] = $temp->name;
			}
			?>
			<input type="text" value="<?php echo implode( ', ', (array) $tags_in ); ?>" name="<?php echo $this->get_field_name('tags_in'); ?>" id="<?php echo $this->get_field_id('tags_in'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Tags, separated by commas.' ); ?></small>
		</p>

		<p>
			With none of these tags
			<?php
			$tags_not_in = array();
			foreach( $instance['tags_not_in'] as $tag_id ){
				$temp = get_term( $tag_id, 'post_tag' );
				$tags_not_in[] = $temp->name;
			}
			?>
			<input type="text" value="<?php echo implode( ', ', (array) $tags_not_in ); ?>" name="<?php echo $this->get_field_name('tags_not_in'); ?>" id="<?php echo $this->get_field_id('tags_not_in'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Tags, separated by commas.' ); ?></small>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('post__in'); ?>"><?php _e( 'Matching any post ID:' ); ?></label> <input type="text" value="<?php echo implode( ', ', (array) $instance['post__in'] ); ?>" name="<?php echo $this->get_field_name('post__in'); ?>" id="<?php echo $this->get_field_id('post__in'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('post__not_in'); ?>"><?php _e( 'Excluding all these post IDs:' ); ?></label> <input type="text" value="<?php echo implode( ', ', (array) $instance['post__not_in'] ); ?>" name="<?php echo $this->get_field_name('post__not_in'); ?>" id="<?php echo $this->get_field_id('post__not_in'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Number of items to show:' ); ?></label>
			<select name="<?php echo $this->get_field_name('count'); ?>" id="<?php echo $this->get_field_id('count'); ?>" class="widefat">
			<?php for( $i = 1; $i < 51; $i++ ){ ?>
				<option value="<?php echo $i; ?>" <?php selected( $instance['count'], $i ); ?>><?php echo $i; ?></option>
			<?php } ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e( 'Ordered by:' ); ?></label>
			<select name="<?php echo $this->get_field_name('order'); ?>" id="<?php echo $this->get_field_id('order'); ?>" class="widefat">
					<option value="age_new" <?php selected( $instance['order'], 'age_new' ); ?>><?php _e('Newest first'); ?></option>
					<option value="age_old" <?php selected( $instance['order'], 'age_old' ); ?>><?php _e('Oldest first'); ?></option>
					<option value="rand" <?php selected( $instance['order'], 'rand' ); ?>><?php _e('Random'); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('template'); ?>"><?php _e( 'Template:' ); ?></label>
			<select name="<?php echo $this->get_field_name('template'); ?>" id="<?php echo $this->get_field_id('template'); ?>" class="widefat">
				<?php $this->control_template_dropdown( $instance['template'] ); ?>
			</select>
		</p>








<?php
	}


	function holding_area(){
?>


<!--
		<p>
			<label for="<?php echo $this->get_field_id('blog'); ?>"><?php _e( 'What to show:' ); ?></label>
			<select name="<?php echo $this->get_field_name('blog'); ?>" id="<?php echo $this->get_field_id('blog'); ?>" class="widefat">
		<?php
	//global $current_user;
	//get_blogs_of_user( $current_user->ID );
	// must allow for situations where the current user doesn't have access to the previously selected other blog
		?>
				<option value="any" <?php selected( $instance['blog'], 'any' ); ?>><?php _e('Any content'); ?></option>
			</select>
		</p>
-->




	
<!--	
<?php
//TODO: clean up these old fields
?>
		<fieldset class="bsuite-any-posts-activity">
		<p><label for="bsuite-any-posts-activity-<?php echo $number; ?>"><?php _e('Activity:'); ?>
		<select id="bsuite-any-posts-activity-<?php echo $number; ?>" name="bsuite-any-posts[<?php echo $number; ?>][activity]">
			<option value="pop_most" <?php selected( $options[$number]['activity'], 'pop_most' ) ?>>Most popular items</option>
			<option value="pop_least" <?php selected( $options[$number]['activity'], 'pop_least' ) ?>>Least popular</option>
	
			<option value="pop_recent" <?php selected( $options[$number]['activity'], 'pop_recent' ) ?>>Recently viewed</option>
	
			<option value="comment_recent" <?php selected( $options[$number]['activity'], 'comment_recent' ) ?>>Recently commented</option>
			<option value="comment_few" <?php selected( $options[$number]['activity'], 'comment_few' ) ?>>Fewest comments</option>
		</select>
		</label></p>
		<fieldset>

		<fieldset class="bsuite-any-posts-age">
		<p><label for="bsuite-any-posts-age-<?php echo $number; ?>"><?php _e('Post date:'); ?>
		<select id="bsuite-any-posts-age-<?php echo $number; ?>" name="bsuite-any-posts[<?php echo $number; ?>][age]">
			<option value="age_new" <?php selected( $options[$number]['age'], 'after' ) ?>>After</option>
			<option value="age_old" <?php selected( $options[$number]['age'], 'before' ) ?>>Before</option>
			<option value="age_from" <?php selected( $options[$number]['age'], 'around' ) ?>>Around</option>
		</select>
		</label> 
		<label for="bsuite-any-posts-agestrtotime-<?php echo $number; ?>"><input style="width: 150px" id="bsuite-any-posts-agestrtotime-<?php echo $number; ?>" name="bsuite-any-posts[<?php echo $number; ?>][agestrtotime]" type="text" value="<?php echo attribute_escape( $options[$number]['agestrtotime'] ); ?>" /></label></p>
		<fieldset>

		<?php if( $other_instances = $this->control_instances( $number, $options[$number]['relatedto']) ): ?>
			<fieldset class="bsuite-any-posts-relationship">
				<p><label for="bsuite-any-posts-relationship-<?php echo $number; ?>">
				<select id="bsuite-any-posts-relationship-<?php echo $number; ?>" name="bsuite-any-posts[<?php echo $number; ?>][relationship]">
					<option value="similar" <?php selected( $options[$number]['relationship'], 'similar' ) ?>>Similar to</option>
					<option value="excluding" <?php selected( $options[$number]['relationship'], 'excluding' ) ?>>Excluding those</option>
				</select>
				</label>
				<?php _e('items shown in:'); ?> <?php echo $other_instances; ?></p>
			<fieldset>
		<?php endif; ?>

		<p><label for="bsuite-any-posts-columns-<?php echo $number; ?>"><?php _e('Number of columns:'); ?> <input style="width: 25px; text-align: center;" id="bsuite-any-posts-columns-<?php echo $number; ?>" name="bsuite-any-posts[<?php echo $number; ?>][columns]" type="text" value="<?php echo attribute_escape( $options[$number]['columns'] ); ?>" /></label> <?php _e('(1 to 8)'); ?></p>
-->	
	


<?php
	}

	function control_categories( $instance , $whichfield = 'categories_in' ){
		$items = get_categories( array( 'style' => FALSE, 'echo' => FALSE, 'hierarchical' => FALSE ));
		foreach( $items as $item ){
			$list[] = '<li>
				<label for="'. $this->get_field_id( $whichfield .'-'. $item->term_id) .'"><input id="'. $this->get_field_id( $whichfield .'-'. $item->term_id) .'" name="'. $this->get_field_name( $whichfield ) .'['. $item->term_id .']" type="checkbox" value="1" '. ( isset( $instance[ $whichfield ][ $item->term_id ] ) ? 'checked="checked"' : '' ) .'/> '. $item->name .'</label>
			</li>';
		}
	
		return implode( "\n", $list );
	}
	
	function control_instances( $self = 0 , $selected = array() ){
		if ( !$options = get_option('bsuite_any_posts') )
			return FALSE;
	
			if( isset( $options[ $self ] ))
				unset( $options[ $self ] );
	
		$list = array();
		foreach( $options as $number => $option ){
			$list[] = '<label for="bsuite-any-posts-relatedto-'. $self .'-'. $number .'"><input class="checkbox" type="checkbox" value="'. $number .'" '.( in_array( $number, (array) $selected ) ? 'checked="checked"' : '' ) .' id="bsuite-any-posts-relatedto-'. $self .'-'. $number .'" name="bsuite-any-posts['. $self .'][relatedto][]" /> '. $option['title'] .'</label>';
		}
	
		return implode( ', ', $list );
	}
	
	function control_template_dropdown( $default = '' ) {
		$templates = $this->get_templates();
		foreach ($templates as $template => $info ) :
			if ( $default == $template )
				$selected = " selected='selected'";
			else
				$selected = '';
		echo "\n\t<option value=\"" .$info['file'] .'" '. $selected .'>'. $info['name'] .'</option>';
		endforeach;
	}
	
	function get_templates_readdir( $template_base ){
		$page_templates = array();
		$template_dir = @ dir( $template_base );
		if ( $template_dir ) {
			while ( ( $file = $template_dir->read() ) !== false ) {
				if ( preg_match('|^\.+$|', $file) )
					continue;
				if ( preg_match('|\.php$|', $file) ) {
					$template_data = implode( '', file( $template_base . $file ));
	
					$name = '';
					if ( preg_match( '|Template Name:(.*)$|mi', $template_data, $name ) )
						if( function_exists( '_cleanup_header_comment' ))
							$name = _cleanup_header_comment($name[1]);
						else
							$name = $name[1];
	
					if ( !empty( $name ) ) {
						$file = basename( $file );
						$page_templates[ $file ]['name'] = trim( $name );
						$page_templates[ $file ]['file'] = basename( $file );
						$page_templates[ $file ]['fullpath'] = $template_base . $file;
					}
				}
			}
			@ $template_dir->close();
		}
		return $page_templates;
	}
	
	function get_templates() {
		return array_merge( 
				(array) $this->get_templates_readdir( dirname( __FILE__ ) .'/templates-post/' ),
				(array) $this->get_templates_readdir( TEMPLATEPATH . '/templates-post/' ), 
				(array) $this->get_templates_readdir( STYLESHEETPATH . '/templates-post/' ) 
			);
	}
	

}// end bSuite_Widget_Pages


/**
 * Pages widget class
 *
 */
class bSuite_Widget_Pages extends WP_Widget {

	function bSuite_Widget_Pages() {
		$widget_ops = array('classname' => 'widget_pages', 'description' => __( 'A buncha yo blog&#8217;s WordPress Pages') );
		$this->WP_Widget('pages', __('Pages'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Pages' ) : $instance['title']);
		$sortby = empty( $instance['sortby'] ) ? 'menu_order' : $instance['sortby'];
		$exclude = empty( $instance['exclude'] ) ? '' : $instance['exclude'];
		$depth = isset( $instance['depth'] ) ? $instance['depth'] : 1;

		if ( $sortby == 'menu_order' )
			$sortby = 'menu_order, post_title';

		$out = wp_list_pages( array('title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude, 'depth' => $depth ));

		if( $instance['expandtree'] && is_page() ){
			global $post;

			// get the ancestor tree, including the current page
			$ancestors = $post->ancestors;
			$ancestors[] = $post->ID;
			$pages = get_pages( array( 'include' => implode( ',', $ancestors )));

			if ( !empty( $pages )){
				$subtree .= walk_page_tree( $pages, 0, $post->ID, array() );

				// get any children, insert them into the tree
				if( $children = wp_list_pages( array( 'child_of' => $post->ID, 'title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude, 'depth' => $depth ))){
					$subtree = preg_replace( '/current_page_item[^<]*<a([^<]*)/i', 'current_page_item"><a\1<ul>'. $children .'</ul>', $subtree );
				}

				// insert this extended page tree into the larger list
				if( !empty( $subtree )){
					$out = preg_replace( '/<li[^>]*page-item-'. ( count( $post->ancestors ) ? end( $post->ancestors ) : $post->ID ) .'[^>]*.*?<\/li>.*?($|<li)/si', $subtree .'\1', $out );
					reset( $post->ancestors );
				}
			}
		}

		if ( !empty( $out ) ) {
			echo $before_widget;
			if ( $title)
				echo $before_title . $title . $after_title;
		?>
		<ul>
			<?php echo $out; ?>
		</ul>
		<?php
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		if ( in_array( $new_instance['sortby'], array( 'post_title', 'menu_order', 'ID' ))) {
			$instance['sortby'] = $new_instance['sortby'];
		} else {
			$instance['sortby'] = 'menu_order';
		}
		$instance['depth'] = absint( $new_instance['depth'] );
		$instance['expandtree'] = absint( $new_instance['expandtree'] );
		$instance['exclude'] = strip_tags( $new_instance['exclude'] );

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'sortby' => 'post_title', 'title' => '', 'exclude' => '', 'depth' => 1, 'expandtree' => 1) );
		$title = esc_attr( $instance['title'] );
		$exclude = esc_attr( $instance['exclude'] );
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e( 'Sort by:' ); ?></label>
			<select name="<?php echo $this->get_field_name('sortby'); ?>" id="<?php echo $this->get_field_id('sortby'); ?>" class="widefat">
				<option value="post_title"<?php selected( $instance['sortby'], 'post_title' ); ?>><?php _e('Page title'); ?></option>
				<option value="menu_order"<?php selected( $instance['sortby'], 'menu_order' ); ?>><?php _e('Page order'); ?></option>
				<option value="ID"<?php selected( $instance['sortby'], 'ID' ); ?>><?php _e( 'Page ID' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e( 'Depth:' ); ?></label>
			<select name="<?php echo $this->get_field_name('depth'); ?>" id="<?php echo $this->get_field_id('depth'); ?>" class="widefat">
				<option value="1"<?php selected( $instance['depth'], '1' ); ?>><?php _e( '1' ); ?></option>
				<option value="2"<?php selected( $instance['depth'], '2' ); ?>><?php _e( '2' ); ?></option>
				<option value="3"<?php selected( $instance['depth'], '3' ); ?>><?php _e( '3' ); ?></option>
				<option value="4"<?php selected( $instance['depth'], '4' ); ?>><?php _e( '4' ); ?></option>
				<option value="0"<?php selected( $instance['depth'], '0' ); ?>><?php _e( 'All' ); ?></option>
			</select>
		</p>
		<p><input id="<?php echo $this->get_field_id('expandtree'); ?>" name="<?php echo $this->get_field_name('expandtree'); ?>" type="checkbox" value="1" <?php if ( $instance['expandtree'] ) echo 'checked="checked"'; ?>/>
		<label for="<?php echo $this->get_field_id('expandtree'); ?>"><?php _e('Expand current page tree?'); ?></label></p>
		<p>
			<label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e( 'Exclude:' ); ?></label> <input type="text" value="<?php echo $exclude; ?>" name="<?php echo $this->get_field_name('exclude'); ?>" id="<?php echo $this->get_field_id('exclude'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>
<?php
	}
}// end bSuite_Widget_Pages


// register these widgets
function bsuite_widgets_init() {
	unregister_widget('WP_Widget_Pages');
	register_widget( 'bSuite_Widget_Pages' );
	register_widget( 'bSuite_Widget_PostLoop' );
}
add_action('widgets_init', 'bsuite_widgets_init', 1);
