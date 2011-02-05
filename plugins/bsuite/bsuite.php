<?php
/*
Plugin Name: bSuite
Plugin URI: http://maisonbisson.com/bsuite/
Description: Stats tracking, improved sharing, related posts, CMS features, and a kitchen sink. <a href="http://maisonbisson.com/bsuite/">Documentation here</a>.
Version: 4.0.7
Author: Casey Bisson
Author URI: http://maisonbisson.com/blog/
*/

class bSuite {

	function bSuite(){

		global $wpdb;
		$this->search_table = $wpdb->prefix . 'bsuite4_search';

		$this->hits_incoming = $wpdb->prefix . 'bsuite4_hits_incoming';
		$this->hits_terms = $wpdb->prefix . 'bsuite4_hits_terms';
		$this->hits_targets = $wpdb->prefix . 'bsuite4_hits_targets';
		$this->hits_searchphrases = $wpdb->prefix . 'bsuite4_hits_searchphrases';
//		$this->hits_searchwords = $wpdb->prefix . 'bsuite4_hits_searchwords';
		$this->hits_sessions = $wpdb->prefix . 'bsuite4_hits_sessions';
		$this->hits_shistory = $wpdb->prefix . 'bsuite4_hits_shistory';
		$this->hits_pop = $wpdb->prefix . 'bsuite4_hits_pop';
		
		$this->loadavg = $this->get_loadavg();

		// establish web path to this plugin's directory
		$this->path_web = plugins_url( basename( dirname( __FILE__ )));

		$this->is_quickview = FALSE;

		// register and queue javascripts
		wp_register_script( 'bsuite', $this->path_web . '/js/bsuite.js', array('jquery'), '20080503' );
		wp_enqueue_script( 'bsuite' );

		// jQuery text highlighting plugin http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html
		wp_register_script( 'highlight', $this->path_web . '/js/jquery.highlight-1.js', array('jquery'), '1' );
		wp_enqueue_script( 'highlight' );

		// is this wpmu?
		if( function_exists( 'is_site_admin' ))
			$this->is_mu = TRUE;
		else
			$this->is_mu = FALSE;

		if ( isset( $_GET['bsuite_mycss'] ) && !is_admin() )
			add_action( 'init', array( &$this, 'bsuite_mycss_printstyles' ));


		//
		// register hooks
		//

		// shortcodes
		add_shortcode('pagemenu', array(&$this, 'shortcode_list_pages'));
		add_shortcode('list_pages', array(&$this, 'shortcode_list_pages'));
		add_shortcode('innerindex', array(&$this, 'shortcode_innerindex'));
		add_shortcode('include', array(&$this, 'shortcode_include'));
		add_shortcode('icon', array(&$this, 'shortcode_icon'));
		add_shortcode('feed', array(&$this, 'shortcode_feed'));

		// filter the_excerpt and x_rss through do_shortcode(). wish this was in core
		// http://trac.wordpress.org/ticket/7093
		add_filter('the_content_rss', 'do_shortcode', 11);
		add_filter('the_excerpt', 'do_shortcode', 11);
		add_filter('the_excerpt_rss', 'do_shortcode', 11);
		add_filter('widget_text', 'do_shortcode', 11);

		// bsuite post icons
		add_action('wp_ajax_bsuite_icon_form', array( &$this, 'icon_ajax_form' ));
		add_action('wp_ajax_bsuite_icon_upload', array( &$this, 'icon_ajax_upload' ));
		add_action('wp_ajax_bsuite_icon_delete', array( &$this, 'icon_ajax_delete' ));
		$this->icon_sizes_default(); // initialize default icons

		// tokens
		// tokens are deprecated. please use shortcode functionality instead.
		add_filter('bsuite_tokens', array(&$this, 'tokens_default'));
		add_filter('the_content', array(&$this, 'tokens_the_content'), 0);
		add_filter('the_content_rss', array(&$this, 'tokens_the_content_rss'), 0);
		add_filter('the_excerpt', array(&$this, 'tokens_the_excerpt'), 0);
		add_filter('the_excerpt_rss', array(&$this, 'tokens_the_excerpt_rss'), 0);
		add_filter('get_the_excerpt ', array(&$this, 'tokens_the_excerpt'), 0);
		add_filter('widget_text', array(&$this, 'tokens'), 0);

		//innerindex
		add_filter('content_save_pre', array(&$this, 'innerindex_nametags'));
		add_filter('save_post', array(&$this, 'innerindex_delete_cache'));
		$this->kses_allowedposttags(); // allow IDs on H1-H6 tags

		// bsuggestive related posts
		add_filter('save_post', array(&$this, 'bsuggestive_delete_cache'));
		if( get_option( 'bsuite_insert_related' )){
			add_filter('the_content', array(&$this, 'bsuggestive_bypageviews_the_content'), 5);
			add_filter('the_content', array(&$this, 'bsuggestive_the_content'), 5);
		}

		// sharelinks
		if( get_option( 'bsuite_insert_sharelinks' ))
			add_filter('the_content', array(&$this, 'sharelinks_the_content'), 6);

		// searchsmart
		if( get_option( 'bsuite_searchsmart' )){
			add_filter('posts_request', array(&$this, 'searchsmart_posts_request'), 10);
			add_filter('content_save_pre', array(&$this, 'searchsmart_edit'));
		}
		add_filter('template_redirect', array(&$this, 'searchsmart_direct'), 8);
		add_filter('post_link', array(&$this, 'searchsmart_post_link_direct'), 11, 2);

		// default CSS
		if( get_option( 'bsuite_insert_css' )){
			add_action('wp_head', 'wp_print_styles', 9);
			wp_register_style( 'bsuite-default', $this->path_web .'/css/default.css' );
			wp_enqueue_style( 'bsuite-default' );
		}

		// bstat
		add_action('get_footer', array(&$this, 'bstat_js'));

		// cron
		add_filter('cron_schedules', array(&$this, 'cron_reccurences'));
		if( $this->loadavg < get_option( 'bsuite_load_max' )){ // only do cron if load is low-ish
			add_filter('bsuite_interval', array(&$this, 'bstat_migrator'));
			if( get_option( 'bsuite_searchsmart' ))
				add_filter('bsuite_interval', array(&$this, 'searchsmart_upindex_passive'));
		}

		// machine tags
		add_action('save_post', array(&$this, 'machtag_save_post'), 2, 2);

		// cms goodies
		add_filter('user_has_cap', array(&$this, 'edit_current_user_can'), 10, 3);
		add_filter('save_post', array(&$this, 'edit_publish_page'));
//		add_action('dbx_page_advanced', array(&$this, 'edit_insert_excerpt_form'));
//		add_action('dbx_page_sidebar', array(&$this, 'edit_insert_category_form'));
//		add_action('edit_form_advanced', array(&$this, 'edit_post_form'));
//		add_action('edit_page_form', array(&$this, 'edit_page_form'));

		add_action('widgets_init', array(&$this, 'widgets_register'));

/*
		// user-contributed tags
		add_action('preprocess_comment', array(&$this, 'uctags_preprocess_comment'), 1);
*/


		// activation and menu hooks
		register_activation_hook(__FILE__, array(&$this, 'activate'));
		add_action('admin_menu', array(&$this, 'admin_menu_hook'));
		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('init', array(&$this, 'init'));
		// end register WordPress hooks


	}

	function admin_init(){
/*
		// set things up so authors can edit their own pages
		$role = get_role('author');
		if ( ! empty($role) ) {
			$role->add_cap('edit_pages');
			$role->add_cap('edit_published_pages');
		}
*/

//		add_filter( 'whitelist_options', array(&$this, 'mu_options' ));

		register_setting( 'bsuite-options', 'bsuite_insert_related', 'absint' );
		register_setting( 'bsuite-options', 'bsuite_insert_sharelinks', 'absint' );
		register_setting( 'bsuite-options', 'bsuite_searchsmart', 'absint' );
		register_setting( 'bsuite-options', 'bsuite_swhl', 'absint' );
		register_setting( 'bsuite-options', 'bsuite_who_can_edit' );
		register_setting( 'bsuite-options', 'bsuite_managefocus_month', 'absint' );
		register_setting( 'bsuite-options', 'bsuite_managefocus_author', 'absint' );
		register_setting( 'bsuite-options', 'bsuite_insert_css', 'absint' );
		register_setting( 'bsuite-options', 'bsuite_migration_interval', 'absint' );
		register_setting( 'bsuite-options', 'bsuite_migration_count', 'absint' );
		register_setting( 'bsuite-options', 'bsuite_load_max', 'absint' );
	}

	function init(){
		if( get_option( 'bsuite_mycss_replacethemecss' ) && !is_admin() ){
			add_filter( 'stylesheet_uri', array( &$this, 'bsuite_mycss_hidesstylesheet' ), 11 );
			add_filter( 'locale_stylesheet_uri', array( &$this, 'bsuite_mycss_hidesstylesheet' ), 11 );
		}

		if(( get_option( 'bsuite_mycss' ) || get_option( 'bsuite_mycss_replacethemecss' )) && !is_admin() ){
			wp_register_style( 'bsuite-mycss', get_option('home') .'/index.php?bsuite_mycss=print' );
			wp_enqueue_style( 'bsuite-mycss' );
		}

		if( 0 < get_option( 'bsuite_mycss_maxwidth' ))
			$GLOBALS['content_width'] = absint( get_option( 'bsuite_mycss_maxwidth' ));
		if( !isset( $GLOBALS['content_width'] ))
			$GLOBALS['content_width'] = 500;

		load_plugin_textdomain( 'bsuite', FALSE, basename( dirname( __FILE__ )) .'/lang/' );

/*
		// handle user-contributed tags via comments
		if( strpos( $_SERVER['PHP_SELF'], 'wp-comments-post.php' ) && ( !empty( $_REQUEST['bsuite_uctags'] )))
			$_REQUEST['comment'] = 'BSUITE_UCTAG';
*/

//		add_rewrite_endpoint( 'quickview', EP_PERMALINK ); // this doesn't quite work as I want it to
	}

	function admin_menu_hook() {
		if( (( 'edit.php' == basename( $_SERVER['PHP_SELF'] )) || ( 'edit-pages.php' == basename( $_SERVER['PHP_SELF'] ))) && ( !count( $_GET )) ){
			global $current_user;
			if( !current_user_can( 'edit_others_posts' ) )
				die( wp_redirect( admin_url( basename( $_SERVER['PHP_SELF'] ) .'?author='. $current_user->id . ( ( get_option( 'bsuite_managefocus_month' ) && ( 'edit-pages.php' <> basename( $_SERVER['PHP_SELF'] )) ) ? '&m='. date( 'Ym' ) : '') )));

			die( wp_redirect( admin_url( basename( $_SERVER['PHP_SELF'] ) .'?s'. ( get_option( 'bsuite_managefocus_author' ) ? '&author='. $current_user->id : '' ) . ( ( get_option( 'bsuite_managefocus_month' ) && ( 'edit-pages.php' <> basename( $_SERVER['PHP_SELF'] )) ) ? '&m='. date( 'Ym' ) : '') )));
		}

/*
		// the machine tags js and style
		wp_register_script( 'bsuite-machtags', $this->path_web . '/js/bsuite-machtags.js', array('jquery-ui-sortable'), '1' );
		wp_enqueue_script( 'bsuite-machtags' );
		wp_register_style( 'bsuite-machtags', $this->path_web .'/css/machtags.css' );
		wp_enqueue_style( 'bsuite-machtags' );
*/

/*
		// add the sweet categories and tags JS from the post editor to the page editor
		wp_register_script( 'edit_page', $this->path_web . '/js/edit_page.js', array('jquery'), '1' ); 
		if( strpos( $_SERVER['REQUEST_URI'], 'admin/page' )){
			wp_enqueue_script( 'edit_page' );
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_script( 'suggest' );
			wp_enqueue_script( 'ajaxcat' );
		}
*/

		// add the options page
		add_options_page('bSuite Settings', 'bSuite', 'manage_options', plugin_basename( dirname( __FILE__ )) .'/ui_options.php' );

		// the bstat reports are handled in a seperate file
		add_submenu_page('index.php', 'bSuite bStat Reports', 'bStat Reports', 'edit_posts', plugin_basename( dirname( __FILE__ )) .'/ui_stats.php' );

		// the custom css page
		add_theme_page( __('Custom CSS'), __('Custom CSS'), 'switch_themes', plugin_basename( dirname( __FILE__ )) .'/ui_mycss.php' );

		// add the post icon widget to the post and page editors
		add_meta_box('bsuite_post_icon', __('bSuite Post Icon'), array( &$this, 'icon_editor_iframe' ), 'post', 'advanced', 'high');
		add_meta_box('bsuite_post_icon', __('bSuite Post Icon'), array( &$this, 'icon_editor_iframe' ), 'page', 'advanced', 'high');
	}



	//
	// shortcode functions
	//
	function shortcode_list_pages( $arg ){
		// [pagemenu ]
		global $id;

		$arg = shortcode_atts( array(
			'title' => 'Contents',
			'div_class' => 'contents pagemenu list_pages',
			'ul_class' => 'contents pagemenu list_pages',
			'ol_class' => FALSE,
			'excerpt'   => FALSE,
			'icon'   => FALSE,
			'echo' => 0,
			'child_of' => $id,
			'depth' => 1,
			'sort_column' => 'menu_order, post_title',
			'title_li' => '',
			'show_date'   => '',
			'date_format' => get_option('date_format'),
			'exclude'     => '',
			'authors'     => '',
		), $arg );

		$prefix = $suffix = '';
		if( $arg['div_class'] ){
			$prefix .= '<div class="'. $arg['div_class'] .'">';
			$suffix .= '</div>';
			if( $arg['title'] )
				$prefix .= '<h3>'. $arg['title'] .'</h3>';
			if( $arg['ul_class'] ){
				$prefix .= '<ul>';
				$suffix = '</ul>'. $suffix;
			}else if( $arg['ol_class'] ){
				$prefix .= '<ol>';
				$suffix = '</ol>'. $suffix;
			}
		}else{
			if( $arg['title'] )
				$prefix .= '<h3 class="'. $arg['ul_class'] . $arg['ol_class'] .'">'. $arg['title'] .'</h3>';
			if( $arg['ul_class'] ){
				$prefix .= '<ul class="'. $arg['ul_class'] .'">';
				$suffix = '</ul>'. $suffix;
			}else if( $arg['ol_class'] ){
				$prefix .= '<ol class="'. $arg['ol_class'] .'">';
				$suffix = '</ol>'. $suffix;
			}
		}

		if(( $arg['excerpt'] ) || ( $arg['icon'] )){
			$this->list_pages->show_excerpt = $arg['excerpt'];
			$this->list_pages->show_icon = $arg['icon'];
			return( $prefix . preg_replace_callback( '/<li class="page_item page-item-([0-9]*)"><a(.*)<\/a>/i', array( &$this, 'shortcode_list_pages_callback'), wp_list_pages( $arg )) . $suffix );
		}
		return( $prefix . wp_list_pages( $arg ) . $suffix );
	}

	function shortcode_list_pages_callback( $arg ){
		global $id, $post;

		if( $this->list_pages->show_excerpt ){
			$post_orig = unserialize( serialize( $post )); // how else to prevent passing object by reference?
			$id_orig = $id;

			$post = get_post( $arg[1] );
			$id = $post->ID;

			$content = ( $this->list_pages->show_icon ? '<a href="'. get_permalink( $arg[1] ) .'" class="bsuite_post_icon_link" rel="bookmark" title="Permanent Link to '. attribute_escape( get_the_title( $arg[1] )) .'">'. $this->icon_get_h( $arg[1] , 's' ) .'</a>' : '' ) . apply_filters( 'the_content', get_post_field( 'post_excerpt', $arg[1] ));

			$post = $post_orig;
			$id = $id_orig;

			if( 5 < strlen( $content ))
				return( $arg[0] .'<ul><li class="page_excerpt page_excerpt-'. $arg[1] .'">'. $content .'</li></ul>' );
			return( $arg[0] );

		}else{
			$content = apply_filters( 'the_content', get_post_field( 'post_excerpt', $arg[1] ));
			return( $arg[0] .'<ul><li class="page_icon page_icon-'. $arg[1] .'"><a href="'. get_permalink( $arg[1] ) .'" class="bsuite_post_icon_link" rel="bookmark" title="Permanent Link to '. attribute_escape( get_the_title( $arg[1] )) .'">'. $this->icon_get_h( $arg[1] , 's' ) .'</a></li></ul>' );

		}

	}

	function shortcode_icon( $arg ){
		// [innerindex ]
		global $id;

		$arg = shortcode_atts( array(
			'post_id' => $id,
			'size' => 's',
			'width' => 0,
			'height' => 0,
		), $arg );

		return( $this->icon_get_h( $arg['post_id'], $arg['size'], $arg['width'], $arg['height'] ));
	}

	function shortcode_innerindex( $arg ){
		// [innerindex ]
		global $id;

		$arg = shortcode_atts( array(
			'title' => 'Contents',
			'div_class' => 'contents innerindex',
		), $arg );

		$prefix = $suffix = '';
		if( $arg['div_class'] ){
			$prefix .= '<div class="'. $arg['div_class'] .'">';
			$suffix .= '</div>';
			if( $arg['title'] )
				$prefix .= '<h3>'. $arg['title'] .'</h3>';
		}else{
			if( $arg['title'] )
				$prefix .= '<h3>'. $arg['title'] .'</h3>';
		}

		if ( !$menu = wp_cache_get( $id, 'bsuite_innerindex' )) {
			$menu = $this->innerindex_build( get_post_field( 'post_content', $id ));
			wp_cache_add( $id, $menu, 'bsuite_innerindex', 864000 );
		}

		return( $prefix . str_replace( '%%the_permalink%%', get_permalink( $id ), $menu ) . $suffix );
	}

	function shortcode_include( $arg ){
		// [include ]
		global $id, $post;
		$arg = shortcode_atts( array(
			'post_id' => FALSE,
			'url' => FALSE,
			'field' => 'post_excerpt',
		), $arg );

		if( !( $arg[ 'post_id' ] || $arg[ 'url' ] ))
			return( FALSE );

		if( isset( $arg[ 'url' ] ))
			$include_id = url_to_postid( $arg[ 'url' ] );

		if( (int) $arg[ 'post_id' ] )
			$include_id = (int) $arg[ 'post_id' ];

		if( !$include_id || ( $id == $include_id ))
			return( FALSE );

		$post_orig = unserialize( serialize( $post )); // how else to prevent passing object by reference?
		$id_orig = $id;

		$post = get_post( $arg[ 'post_id' ] );
		$id = $post->ID;

		if( ( 'post_excerpt' == $arg[ 'field' ] ) && !( get_post_field( $arg[ 'field' ], $include_id )))
			$arg[ 'field' ] = 'post_content';

		$content = apply_filters( 'the_content', get_post_field( $arg[ 'field' ], $include_id ));

		$post = $post_orig;
		$id = $id_orig;

		return( $content );
	}

	function shortcode_feed( $arg ){
		// [feed ]

		$arg = shortcode_atts( array(
			'title' => FALSE,
			'div_class' => FALSE,
			'ul_class' => 'feed',
			'ol_class' => FALSE,
			'feed_url' => FALSE,
			'count' => 5,
			'template' => '<li><h4><a href="%%link%%">%%title%%</a></h4><p>%%content%%</p></li>',
		), $arg );

		if( ! $arg[ 'feed_url' ] )
			return( FALSE );

		$prefix = $suffix = '';
		if( $arg['div_class'] ){
			$prefix .= '<div class="'. $arg['div_class'] .'">';
			$suffix .= '</div>';
			if( $arg['title'] )
				$prefix .= '<h3>'. $arg['title'] .'</h3>';
			if( $arg['ul_class'] ){
				$prefix .= '<ul>';
				$suffix = '</ul>'. $suffix;
			}else if( $arg['ol_class'] ){
				$prefix .= '<ol>';
				$suffix = '</ol>'. $suffix;
			}
		}else{
			if( $arg['title'] )
				$prefix .= '<h3 class="'. $arg['ul_class'] . $arg['ol_class'] .'">'. $arg['title'] .'</h3>';
			if( $arg['ul_class'] ){
				$prefix .= '<ul class="'. $arg['ul_class'] .'">';
				$suffix = '</ul>'. $suffix;
			}else if( $arg['ol_class'] ){
				$prefix .= '<ol class="'. $arg['ol_class'] .'">';
				$suffix = '</ol>'. $suffix;
			}
		}

		return( $prefix . $this->get_feed( $arg['feed_url'], $arg['count'], $arg['template'], TRUE) . $suffix );
	}

	//
	// token functions
	// tokens are [[token]] in the content of a post.
	//
	function tokens_get(){
		// establish list of tokens
		static $tokens = FALSE;
		if($tokens)
			return($tokens);

		$tokens = array();
		$tokens = apply_filters('bsuite_tokens', $tokens);

		return($tokens);
	}

	function tokens_fill($thing) {
		// match tokens
		$return = $thing[0];
		$thing = explode('|', trim($thing[0], '[]'), 2);
		$tokens = &$this->tokens_get();

		if($tokens[$thing[0]])
			$return = call_user_func_array($tokens[$thing[0]], $thing[1]);

		return($return);
	}

	function tokens($content) {
		// find tokens in the page
		$content = preg_replace_callback(
			'/\[\[([^\]\]])*\]\]/',
			array(&$this, 'tokens_fill'),
			$content);
		return($content);
	}

	function tokens_the_content($content) {
		$this->is_content = TRUE;
		$content = $this->tokens($content);
		$this->is_content = FALSE;
		return($content);
	}

	function tokens_the_content_rss($content) {
		$this->is_content = TRUE;
		$this->is_rss = TRUE;
		$content = $this->tokens($content);
		$this->is_content = FALSE;
		$this->is_rss = FALSE;
		return($content);
	}

	function tokens_the_excerpt($content) {
		$this->is_excerpt = TRUE;
		$content = $this->tokens($content);
		$this->is_excerpt = FALSE;
		return($content);
	}

	function tokens_the_excerpt_rss($content) {
		$this->is_excerpt = TRUE;
		$this->is_rss = TRUE;
		$content = $this->tokens($content);
		$this->is_excerpt = FALSE;
		$this->is_rss = FALSE;
		return($content);
	}



	function tokens_default($tokens){
		// setup some default tokens
		$tokens['date'] = array(&$this, 'token_get_date');
		$tokens['pagemenu'] = array(&$this, 'token_get_pagemenu');
		$tokens['innerindex'] = array(&$this, 'innerindex');
		$tokens['feed'] = array(&$this, 'token_get_feed');
		$tokens['redirect'] = array(&$this, 'token_get_redirect');

		return($tokens);
	}

	function token_get_date($stuff = 'F j, Y, g:i a'){
		// [[date|options]]
		return(date($stuff));
	}

	function token_get_pagemenu($stuff = NULL){
		// [[pagemenu|depth|extra]]
		// [[pagemenu|1|sort_column=post_date&sort_order=DESC]]
		global $id;
		$stuff = explode('|', $stuff);
		return(wp_list_pages("child_of=$id&depth=1&echo=0&sort_column=menu_order&title_li=&$stuff[0]"));
	}

	function token_get_redirect($stuff){
		// [[redirect|$url]]
		if(!headers_sent())
			header("Location: $stuff");
		return('redirect: <a href="'. $stuff .'">'. $stuff .'</a>');
	}

	function token_get_feed($stuff){
		// [[feed|feed_url|count]]
		$stuff = explode('|', $stuff);
		if(!$stuff[1])
			$stuff[1] = 5;
		if(!$stuff[2])
			$stuff[2] = '<li><a href="%%link%%">%%title%%</a><br />%%content%%</li>';
		return($this->get_feed($stuff[0], $stuff[1], $stuff[2], TRUE));
	}
	// end token-related functions



	//innerindex
	function innerindex($title = 'Contents:'){
		global $id, $post_cache;

		if ( !$menu = wp_cache_get( $id, 'bsuite_innerindex' )) {
			$menu = $this->innerindex_build( get_post_field( 'post_content', $id ));
			wp_cache_add( $id, $menu, 'bsuite_innerindex', 864000 );
		}

		if($this->is_excerpt){
			return( str_replace( '%%the_permalink%%', get_permalink( $id ), $menu ));
		}else{
			return( '<div class="innerindex"><h3>'. $title .'</h3>'. str_replace( '%%the_permalink%%', get_permalink( $id ), $menu ) .'</div>' );
		}
	}

	function innerindex_build($content){
		// find <h*> tags with IDs in the content and build an index of them
		preg_match_all(
			'|<h[^>]+>[^<]+</h[^>]+>|U',
			$content,
			$things
			);

		$menu = '<ol>';
		$closers = $count = 0;
		foreach($things[0] as $thing){
			preg_match('|<h([0-9])|U', $thing, $h);
			preg_match('|id="([^"]*)"|U', $thing, $anchor);

			if(!$last)
				$last = $low = $h[1];

			if($anchor[1]){
				if($h[1] > $last){
					$menu .= '<ol>';
					$closers++;
				}else if($count){
					$menu .= '</li>';
				}

				if(($h[1] < $last) && ($h[1] >= $low)){
					$menu .= '</ol></li>';
					$closers--;
				}

				$last = $h[1];

				$menu .= '<li><a href="%%the_permalink%%#'. $anchor[1] .'">'. strip_tags($thing) .'</a>';
				$count++;
			}
		}
		$menu .= '</li>'. str_repeat('</ol></li>', $closers) . '</ol>';
		return($menu);
	}

	function innerindex_delete_cache($id) {
		$id = (int) $id;
		wp_cache_delete( $id, 'bsuite_innerindex' );
	}

	function innerindex_nametags($content){
		// find <h*> tags in the content
		$content = preg_replace_callback(
			"/(\<h([0-9])?([^\>]*)?\>)(.*?)(\<\/h[0-9]\>)/",
			array(&$this,'innerindex_nametags_callback'),
			$content
			);
		return($content);
	}

	function innerindex_nametags_callback($content){
		// receive <h*> tags and insert the ID
		static $slugs;
		$slugs[] = $slug = substr(sanitize_title_with_dashes($content[4]), 0, 20);
		$content = "<h{$content[2]} id=\"{$_POST['post_ID']}_{$slug}_". count(array_keys($slugs, $slug)) .'" '. trim(preg_replace('/id[^"]*"[^"]*"/', '', $content[3])) .">{$content[4]}{$content[5]}";
		return($content);
	}
	// end innerindex-related



	//
	// post icons
	//
	function icon_sizes_default(){
		$this->icon_sizes = array( 
			's' => array( 
				'file' => dirname( __FILE__ ) .'/img/post_icon_default/s.jpg',
				'url' => $this->path_web .'/img/post_icon_default/s.jpg',
				'w' => '100',
				'h' => '100',
				), 
			'm' => array( 
				'file' => dirname( __FILE__ ) .'/img/post_icon_default/m.jpg',
				'url' => $this->path_web .'/img/post_icon_default/m.jpg',
				'w' => '250',
				'h' => '200',
				), 
			'l' => array( 
				'file' => dirname( __FILE__ ) .'/img/post_icon_default/l.jpg',
				'url' => $this->path_web .'/img/post_icon_default/l.jpg',
				'w' => '500',
				'h' => '375',
				), 
			'b' => array( 
				'file' => dirname( __FILE__ ) .'/img/post_icon_default/b.jpg',
				'url' => $this->path_web .'/img/post_icon_default/b.jpg',
				'w' => '1280',
				'h' => '975',
				), 
			);
	}

	function icon_is_editing( $post_id ) {
		global $current_user;

		if ( !$post = get_post( $post_id ) )
			return false;

		$lock = get_post_meta( $post->ID, '_edit_lock', true );
		$last = get_post_meta( $post->ID, '_edit_last', true );

		$time_window = apply_filters( 'wp_check_post_lock_window', AUTOSAVE_INTERVAL * 2 );

		if ( $lock && $lock > time() - $time_window && $last == $current_user->ID )
			return( TRUE );
		return( FALSE );
	}

	function icon_handle_upload() {
		$_FILES['import']['name'] = substr( md5( uniqid( microtime())), 0, 4 ) . strrchr( $_FILES['import']['name'] , '.' );
		$overrides = array( 'test_form' => false );
		$file = wp_handle_upload( $_FILES['import'], $overrides );

		return( $file );
	}

	function icon_form( $post_id = 0 ) {
		require_once( ABSPATH .'/wp-admin/includes/template.php');

		if( 0 >= (int) $post_id )
			return( FALSE );
?>
<html><head></head>
<?php

		$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
		$size = wp_convert_bytes_to_hr( $bytes );
		if( $img = get_post_meta( $post_id, 'bsuite_post_icon', TRUE )){
			if( is_string( $img ))
				$img = unserialize( $img );

			echo '<div style="width:'. $img['s']['w'] .'px; height:'. $img['s']['h'] .'px; background: #bbbbbb url( \''. $img['s']['url'] .'\' ) no-repeat scroll center; float: left; padding: 3px; margin-right: 25px;">';
			$img = array_pop( $img );
?>
			<form enctype="multipart/form-data" name="import-delete-form" id="import-delete-form" method="post" action="<?php echo bloginfo( 'wpurl' ) .'/wp-admin/admin-ajax.php' ?>">
			<?php wp_nonce_field('bsuite-icon-upload'); ?>
			<input type="hidden" name="post_ID" value="<?php echo (int) $_REQUEST['post_ID'] ?>" />
			<input type="hidden" name="action" value="bsuite_icon_delete" />
			<input type="image" src="<?php echo $this->path_web .'/img/silk_icons/delete.png' ?>" onclick="if ( confirm('You are about to delete this image.\n\'Cancel\' to stop, \'OK\' to delete.') ) { return true;}return false;"  title="Delete icon"/>
			<a id="icon_info" href="<?php echo $img['url'] ?>" title="View larger icon" target="_blank"><img src="<?php echo $this->path_web .'/img/silk_icons/magnifier_zoom_in.png' ?>" width="16" height="16" alt="zoom in on the icon." /></a>
			</form></div>
<?php
	}
?>
	<form enctype="multipart/form-data" name="import-upload-form" id="import-upload-form" method="post" action="<?php echo bloginfo( 'wpurl' ) .'/wp-admin/admin-ajax.php' ?>">
	<?php wp_nonce_field('bsuite-icon-upload'); ?>
	<input type="file" id="upload" name="import" size="20" /> 
	<input type="hidden" name="post_ID" value="<?php echo (int) $_REQUEST['post_ID'] ?>" />
	<input type="hidden" name="action" value="bsuite_icon_upload" />
	<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
	<input type="submit" class="button" value="<?php _e( 'Upload New' ); ?>" />
	(<?php printf( __('%s max' ), $size ); ?>)
	</form></html>
<?php
	}

	function icon_ajax_delete( ){
		if (!current_user_can('upload_files'))
			wp_die(__('You do not have permission to upload files.'));

		if( 0 >= (int) $_REQUEST['post_ID'] )
			die(0);

		if( !$this->icon_is_editing( (int) $_REQUEST['post_ID'] ))
			wp_die(__('You cannot add an icon to a post you`re not currently editing.'));

		$img = get_post_meta( (int) $_REQUEST['post_ID'], 'bsuite_post_icon', TRUE );
		$this->unlink_recursive( dirname( $img['s']['file'] ), TRUE );
		delete_post_meta( (int) $_REQUEST['post_ID'], 'bsuite_post_icon' );

		die( $this->icon_form( (int) $_REQUEST['post_ID'] ));
	}

	function icon_ajax_form( ){
		if (!current_user_can('upload_files'))
			wp_die(__('You do not have permission to upload files.'));

		if( 0 >= (int) $_REQUEST['post_ID'] )
			die(0);

		if( !$this->icon_is_editing( (int) $_REQUEST['post_ID'] ))
			wp_die(__('You cannot add an icon to a post you`re not currently editing.'));

		die( $this->icon_form( (int) $_REQUEST['post_ID'] ));
	}

	function icon_ajax_upload( ){

		// security checks
		check_admin_referer('bsuite-icon-upload');
		if (!current_user_can('upload_files'))
			wp_die(__('You do not have permission to upload files.'));

		// make sure we have a post ID
		if( 0 >= (int) $_REQUEST['post_ID'] )
			die('form is incomplete');

		if( !$this->icon_is_editing( (int) $_REQUEST['post_ID'] ))
			wp_die(__('You cannot add an icon to a post you`re not currently editing.'));

		// get the file that's being uploaded
		$file = $this->icon_handle_upload();

		// make sure that we've got an image
		if( 'image' === strtolower( substr( $file['type'], 0, strpos( $file['type'], '/' )))){

			// get the post id
			$post_id = (int) $_REQUEST['post_ID'];

			if( $this->icon_resize( $file['file'], $post_id ) ){

				sleep(1); // give the database a moment to process the input

				// die with the editor form
				die( $this->icon_form( $post_id ) );
			}
			die(0);

		}else{
			// don't know what to do with it, so delete it and die
			@unlink( $file['file'] );
			die(0);
		}
	}

	function icon_resize( $image_ori , $post_id , $freshen = FALSE ){

		// set a new directory for the file
		$uploads = wp_upload_dir( 'icon-'. substr( str_pad( $post_id, 2, '0', STR_PAD_LEFT ), -2) );
		$uploads['path'] .= '/'. $post_id;

		// create the directory, remove any previous if existing
		if( is_dir( $uploads['path'] )){
			if( preg_match( '/^http/i', $image_ori )){	
				$headers = wp_remote_head( $image_ori );
				if( filectime( $uploads['path'] ) > strtotime( $headers['headers']['last-modified'] ) && !$freshen )
					return( get_post_meta( $post_id, 'bsuite_post_icon', TRUE ) );
			}

			$this->unlink_recursive( $uploads['path'] );
			mkdir( $uploads['path'], 0775, TRUE );

		}else{
			mkdir( $uploads['path'], 0775, TRUE );
		}

		// set the destination path of the file
		$uploads['path'] .= '/o'. strrchr( basename( $image_ori ) , '.' );

		// check if it's a remote image, get it if it is
		if( preg_match( '/^http/i', $image_ori )){

			// fetch the remote url and write it to the placeholder file
			$headers = wp_get_http( $image_ori, $uploads['path'] );

//print_r( $headers );

			//Did we get a result?
			if( !$headers ) {
				@unlink( $uploads['path'] );
				return FALSE;
			}elseif ( $headers['response'] != '200' ) {
				@unlink( $uploads['path'] );
				return FALSE;
			}elseif ( isset( $headers['content-length'] ) && filesize( $uploads['path'] ) != $headers['content-length'] ) {
				@unlink( $uploads['path'] );
				return FALSE;
			}
		}else{
			// move the uploaded file into that directory, delete the old file (redundent, i know)
			rename( $image_ori, $uploads['path']);
			@unlink( $image_ori );
		}

		// set base paths and urls
		$image_ori = $uploads['path'];
		$url_base = $uploads['url'] .'/'. $post_id .'/';

		// okay, let's process that image
		$img = array();
		// make the square version
		if( $img_tmp = image_resize( $image_ori, 150, 150, TRUE, 's' )){
			$img_dims = @getimagesize( $img_tmp );
			$img_scale = wp_crop_image( $img_tmp, ( $img_dims[0] / 2 ) - 50 , ( $img_dims[1] / 2 ) - 50, 100, 100, 100, 100, FALSE, $img_tmp );

			if( $img_tmp <> $img_scale )
				@unlink( $img_tmp );
			rename( $img_scale, str_replace( basename( $img_scale ), substr( basename( $img_scale ), 2), $img_scale ));
			$img_scale = str_replace( basename( $img_scale ), substr( basename( $img_scale ), 2), $img_scale );

			$img['s']['file'] = $img_scale;
			$img['s']['url'] = $url_base . basename( $img_scale );
			list( $img['s']['w'],$img['s']['h'] ) = getimagesize( $img_scale );
		}

		// make the medium version
		if( $img_tmp = image_resize( $image_ori, 278, 278, FALSE, 'm' )){
			$img_dims = @getimagesize( $img_tmp );
			$img_scale = wp_crop_image( $img_tmp, $img_dims[0] * .05 , $img_dims[1] * .05, $img_dims[0] * .9, $img_dims[1] * .9, $img_dims[0] * .9, $img_dims[1] * .9, FALSE, $img_tmp );

			if( $img_tmp <> $img_scale )
				@unlink( $img_tmp );
			rename( $img_scale, str_replace( basename( $img_scale ), substr( basename( $img_scale ), 2), $img_scale ));
			$img_scale = str_replace( basename( $img_scale ), substr( basename( $img_scale ), 2), $img_scale );

			$img['m']['file'] = $img_scale;
			$img['m']['url'] = $url_base . basename( $img_scale );
			list( $img['m']['w'],$img['m']['h'] ) = getimagesize( $img_scale );

		}

		// make the large version
		if( $img_scale = image_resize( $image_ori, 500, 500, FALSE, 'l' )){

			rename( $img_scale, str_replace( basename( $img_scale ), substr( basename( $img_scale ), 2), $img_scale ));
			$img_scale = str_replace( basename( $img_scale ), substr( basename( $img_scale ), 2), $img_scale );

			$img['l']['file'] = $img_scale;
			$img['l']['url'] = $url_base . basename( $img_scale );
			list( $img['l']['w'],$img['l']['h'] ) = getimagesize( $img_scale );
		}

		// make the big version
		if( $img_scale = image_resize( $image_ori, 1280, 1280, FALSE, 'b' )){

			rename( $img_scale, str_replace( basename( $img_scale ), substr( basename( $img_scale ), 2), $img_scale ));
			$img_scale = str_replace( basename( $img_scale ), substr( basename( $img_scale ), 2), $img_scale );

			$img['b']['file'] = $img_scale;
			$img['b']['url'] = $url_base . basename( $img_scale );
			list( $img['b']['w'],$img['b']['h'] ) = getimagesize( $img_scale );
		}

		// finally, delete the original
		@unlink( $image_ori );

		// the the image to the post_meta
		add_post_meta( $post_id, 'bsuite_post_icon', $img, TRUE ) or update_post_meta( $post_id, 'bsuite_post_icon', $img);

		return $img;
	}

	function icon_editor_iframe( ){
		global $post_ID;

		echo '<a href="#bsuite_post_icon" id="bsuite_post_icon_clickme"></a><noscript>This feature requires JavaScript.</noscript>';
	}

	function icon_get_default( $post_id, $size = 's' ){
		if( is_array( $this->icon_sizes[ $size ] ))
			return( $this->icon_sizes[ $size ] );
		else
			return( $this->icon_sizes['s'] );
	}

	function icon_get_tofit( $img, $size = 's' ){
		if( is_array( $this->icon_sizes[ $size ] )){
			if( is_array( $img )){

				// what area are we looking for?
				$area_expected = $this->icon_sizes[ $size ]['w'] * $this->icon_sizes[ $size ]['h'];

				// what areas do we have?
				$larger = $smaller = array();
				foreach( $img as $key => $val ){
					$area = $val['w'] * $val['h'];
					if( $area >= ( $area_expected * .8))
						$larger[ $key ] = $area; 
					if( $area <= ( $area_expected * 1.2))
						$smaller[ $key ] = $area; 
				}

				// which of those is closest? (biased toward larger size)
				if( count( $larger ) ){
					asort( $larger, SORT_NUMERIC );
					$nearest = key( $larger );
				}else if( count( $smaller ) ){
					asort( $smaller, SORT_NUMERIC );
					$nearest = key( array_slice( $smaller, -1, 1, TRUE ));
				}

				// okay, we got one, let's use it
				$return = $img[ $nearest ];
				$return['w'] = $this->icon_sizes[ $size ]['w'];
				$return['h'] = $this->icon_sizes[ $size ]['h'];

				return( $return );
			}
		}else{
			// we don't know what size you're looking for
			// we'll try the 's' size instead
			return( $this->icon_get_tofit( $img, 's' )); 
		}
	}

	function icon_get_a( $post_id, $size = 's' ){
		$img = apply_filters( 'bsuite_post_icon', get_post_meta( $post_id, 'bsuite_post_icon', TRUE ), $post_id );

		if( is_array( $img ))
			if( is_array( $img[ $size ] ))
				return( $img[ $size ] );
			else
				return( $this->icon_get_tofit( $img, $size ));
		else
			return( $this->icon_get_default( $post_id, $size ));
	}

	function icon_get_h( $post_id, $size = 's', $nostyle = FALSE, $ow = 0, $oh = 0 ){
		if( $img = $this->icon_get_a( $post_id, $size )){
			if( $nostyle || strpos( current_filter(), 'rss' ))
				return( '<img src="'. $img['url'] .'" class="bsuite_post_icon bsuite_post_icon_'. $post_id .'" width="'. ( $ow ? $ow : $img['w'] ) .'" height="'. ( $oh ? $oh : $img['h'] ) .'" alt="'. attribute_escape( get_the_title( $post_id )) .'" />' );
			return( '<img src="'. $this->path_web .'/img/spacer.gif" class="bsuite_post_icon bsuite_post_icon_'. $post_id .'" width="'. ( $ow ? $ow : $img['w'] ) .'" height="'. ( $oh ? $oh : $img['h'] ) .'" style="background: #bbbbbb url( '. $img['url'] .' ) no-repeat scroll center;" alt="'. attribute_escape( get_the_title( $post_id )) .'" />' );
		}
		return( FALSE );
	}


	// end post icon related functions







	function bsuite_mycss_printstyles(){
		@header('Content-Type: text/css; charset=' . get_option('blog_charset'));

		echo get_option( 'bsuite_mycss' );
		die();
	}

	function bsuite_mycss_hidesstylesheet( $input ){
		return( $this->path_web . '/css/empty.css' );
	}

	function mycss_sanitize( $input ){
		$input = wp_filter_nohtml_kses( $input );
		$input = preg_replace('/\/\*.*?\*\//sm', '', $input); // strip comments

		$safecss = '';
		foreach( explode( "\n", $input ) as $line )
			$safecss .= $this->mycss_cleanline( $line );

		return( $safecss );
	}

	function mycss_cleanline( $input ){
		$evil = 0;

		$filtered = wp_kses_decode_entities( $input );
		$filtered = preg_replace('/expression[^\(]?\(.*?\)/i', '', $filtered, -1, $flag ); // strip expressions
		if( $flag ) $evil++;

		$filtered = preg_replace('/@import/i', '', $filtered, -1, $flag ); // strip @import
		if( $flag ) $evil++;

		$filtered = preg_replace('/about:/i', '', $filtered, -1, $flag ); // strip about: uris
		if( $flag ) $evil++;

		$filtered = preg_replace_callback('/([\w]*?):\/\//si', array( $this, 'mycss_cleanuri' ), $filtered, -1, $flag ); // strip non http uris
		if( $flag ) $evil++;

		return( $evil ? $filtered : $input );
	}

	function mycss_cleanuri( $input ){
		if( !preg_match( '/^http:\/\//', $input[0] ))
			return '';

		return( $input[0] );
	}

















	//
	// sharelinks
	//
	function sharelinks(){
		global $wp_query;

		// exit if 404
		if($wp_query->is_404)
			return(FALSE);

		// identify the based post ID, if any, and establish some basics
		$post_id = FALSE;
		if(!empty($wp_query->is_singular) && !empty($wp_query->query_vars['p']))
			$post_id = $wp_query->query_vars['p'];
		else if(!empty($wp_query->is_singular) && !empty($wp_query->queried_object_id))
			$post_id = $wp_query->queried_object_id;
		else if( !empty( $this->bsuggestive_to ))
			$post_id = $this->bsuggestive_to;

		if($post_id){
			$the_permalink = urlencode(get_permalink($post_id));
			$the_title = urlencode(get_the_title($post_id));
			$the_excerpt = apply_filters('the_excerpt', get_the_excerpt());
		}else{
			$the_permalink = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://' . $_SERVER['HTTP_HOST'] . add_query_arg('bsuite_share');

			unset($wp_query->query['bsuite_share']);
			unset($wp_query->query['attachment']);
			if(count($wp_query->query))
				$the_title = get_bloginfo('name') .' ('. wp_specialchars( implode(array_unique(explode('|', strtolower(implode(array_values($wp_query->query), '|')))), ', ')) .')';
			else
				$the_title = get_bloginfo('name');

			$the_excerpt = '';
		}
		$content = '<ul class="bsuite_sharelinks">';

		// the embed links 
		if( $post_id && ( $embed = $this->link2me( $post_id ))){
			$content .= '<li id="bsuite_share_embed" class="bsuite_share_embed"><h3>Link or embed this</h3>' . $embed .'</li>';
		}

		// the bookmark links 
		$content .= '<li id="bsuite_share_bookmark" class="bsuite_share_bookmark"><h3>Bookmark this at</h3><ul>';
		global $services_bookmark;
		foreach ($services_bookmark as $key => $data) {
			$content .= '<li><img src="' . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . '/img/'. $key .'.gif" width="16" height="16" alt="'. attribute_escape($data['name']) .' sharing icon">&nbsp;<a href="'. str_replace(array('{title}', '{url}'), array($the_title, $the_permalink), $data['url']) .'">'. $data['name'] .'</a></li>';
		}
		$content .= '</ul></li>';

		// the email links
		$content .= '<li id="bsuite_share_email" class="bsuite_share_email"><h3>Email this page</h3><ul><li><a href="mailto:?MIME-Version=1.0&Content-Type=text/html;&subject='. attribute_escape(urldecode($the_title)) .'&body=%0D%0AI found this at '.  attribute_escape(get_bloginfo('name')) .'%0D%0A'. attribute_escape(urldecode($the_permalink)) .'%0D%0A">Send this page using your computer&#039;s emailer</a></li></ul></li>';

		// the feed links
		$content .= '<li id="bsuite_share_feed" class="bsuite_share_feed"><h3>Stay up to date</h3><ul>';
		$feeds = array();
		if($wp_query->is_singular)
			$feeds[] = array('title' => 'Comments on this post', 'url' => get_post_comments_feed_link($post_id));
		if($wp_query->is_search)
			$feeds[] = array('title' => 'This Search', 'url' => $this->feedlink());
		$feeds[] = array('title' => 'All Posts', 'url' => get_bloginfo('atom_url'));
		$feeds[] = array('title' => 'All Comments', 'url' => get_bloginfo('comments_atom_url'));

		global $services_feed;
		foreach ($feeds as $feed) {
			$subscribe_links = array();
			foreach ($services_feed as $key => $data) {
				$subscribe_links[] = '<a href="'. str_replace(array('{url}', '{url_raw}'), array(urlencode($feed['url']), $feed['url']), $data['url']) .'">'. $data['name'] .'</a>';
			}

			$content .= '<li><img src="' . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . '/img/icon-feed-16x16.png" width="16" height="16" alt="'. attribute_escape($feed['title']) .' feed icon">&nbsp;<a href="'. $feed['url'] .'">'. $feed['title'] .'</a>. Subscribe via '.  implode($subscribe_links, ', ') .'.</li>';
		}
		$content .= '</ul></li>';

		// the translation links
		$content .= '<li id="bsuite_share_translate" class="bsuite_share_translate"><h3>Automatically translate this to</h3><ul>';
		global $services_translate;
		foreach ($services_translate as $key => $data) {
			$content .= '<li><a href="'. str_replace('{url}', $the_permalink, $data['url']) .'">'. $data['name'] .'</a></li>';
		}
		$content .= '</ul></li>';

		$content .= '</ul>';

		// powered by
		$content .= '<p class="bsuite_share_bsuitetag">Powered by <a href="http://maisonbisson.com/blog/bsuite">bSuite</a>.</p>';

		return( $content );
		//return(array('the_id' => $post_id, 'the_title' => urldecode($the_title), 'the_permalink' => urldecode($the_permalink), 'the_content' => $content, ));
	}

	function sharelinks_the_content( $content ) {
		if( is_single() && $sharelinks = $this->sharelinks() )
			return( $content . $sharelinks);
		return( $content );
	}
	// end sharelinks related functions


	//
	// link to me
	//
	function link2me_links( $post_id ){
		if( !$post_id )
			return( FALSE );

		//'<a href="'. get_permalink($post_id) .'" title="'. attribute_escape( strip_tags( get_the_title( $post_id ))) .'">'. strip_tags( get_the_title( $post_id )) .'</a>'; //not using this now

//echo '<h2>Hi!</h2>';
		return( apply_filters('bsuite_link2me', array( array('code' => get_permalink($post_id), 'name' => __( 'Permalink', 'bsuite' ))), $post_id));
	}

	function link2me( $post_id ){
		if( !$post_id ){
			global $id;
			$post_id = $id;
		}

		if( !$links = $this->link2me_links( $post_id ))
			return( FALSE );

		if( count( $links ) ){
	        $return = '<ul class="linktome">';
	        foreach( $links as $link ){
				$return .= '<li><h4>'. $link['name'] .'</h4><input class="linktome_input" type="text" value="'. htmlentities( $link['code'] ) .'" readonly="true" /></li>';
	        }
	        $return .= '</ul>';
			return( $return );
		}
		return( FALSE );
	}




	//
	// Stats Related
	//
	function bstat_js() {
		if( !$this->didstats ){
?>
<script type="text/javascript">
bsuite.api_location='<?php echo $this->path_web . '/worker.php' ?>';
bsuite.log();
</script>
<noscript><img src="<?php echo $this->path_web . '/worker.php' ?>" width="1" height="1" alt="stat counter" /></noscript>
<?php
		}
	}

	function bstat_get_term( $id ) {
		global $wpdb;

		if ( !$name = wp_cache_get( $id, 'bstat_terms' )) {
			$name = $wpdb->get_var("SELECT name FROM $this->hits_terms WHERE ". $wpdb->prepare( "term_id = %s", (int) $id ));
			wp_cache_add( $id, $name, 'bstat_terms', 0 );
		}
		return( $name );
	}

	function bstat_is_term( $term ) {
		global $wpdb;

		$cache_key = md5( substr( $term, 0, 255 ) );
		if ( !$term_id = wp_cache_get( $cache_key, 'bstat_termids' )) {
			$term_id = (int) $wpdb->get_var("SELECT term_id FROM $this->hits_terms WHERE ". $wpdb->prepare( "name = %s", substr( $term, 0, 255 )));
			wp_cache_add( $cache_key, $term_id, 'bstat_termids', 0 );
		}
		return( $term_id );
	}

	function bstat_insert_term( $term ) {
		global $wpdb;

		if ( !$term_id = $this->bstat_is_term( $term )) {
			if ( false === $wpdb->insert( $this->hits_terms, array( 'name' => $term ))){
				new WP_Error('db_insert_error', __('Could not insert term into the database'), $wpdb->last_error);
				return( 1 );
			}
			$term_id = (int) $wpdb->insert_id;
		}
		return( $term_id );
	}

	function bstat_is_session( $session_cookie ) {
		global $wpdb;

		if ( !$sess_id = wp_cache_get( $session_cookie, 'bstat_sessioncookies' )) {
			$sess_id = (int) $wpdb->get_var("SELECT sess_id FROM $this->hits_sessions WHERE ". $wpdb->prepare( "sess_cookie = %s", $session_cookie ));
			wp_cache_add( $session_cookie, $sess_id, 'bstat_sessioncookies', 10800 );
		}
		return($sess_id);
	}

	function bstat_insert_session( $session ) {
		global $wpdb;

		$s = array();
		if ( !$session_id = $this->bstat_is_session( $session->in_session )) {
			$this->session_new = TRUE;

			$s['sess_cookie'] = $session->in_session;
			$s['sess_date'] = $session->in_time;

			$se = unserialize( $session->in_extra );
			$s['sess_ip'] = $se['ip'];
			$s['sess_br'] = $se['br'];
			$s['sess_bb'] = $se['bb'];
			$s['sess_bl'] = $se['bl'];
			$s['sess_ba'] = urldecode( $se['ba'] );
// could use INET_ATON and INET_NTOA to reduce storage requirements for the IP address,
// but it's not human readable when browsing the table

			if ( false === $wpdb->insert( $this->hits_sessions, $s )){
				new WP_Error('db_insert_error', __('Could not insert session into the database'), $wpdb->last_error);
				return( FALSE );
			}
			$session_id = (int) $wpdb->insert_id;

			wp_cache_add($session->in_session, $session_id, 'bstat_sessioncookies', 10800 );
		}
		return( $session_id );
	}

	function bstat_migrator(){
		global $wpdb;

		if( !$this->get_lock( 'migrator' ))
			return( TRUE );

		// also use the options table
		if ( get_option( 'bsuite_doing_migration') > time() )
			return( TRUE );

		update_option( 'bsuite_doing_migration', time() + 250 );
		$status = get_option ( 'bsuite_doing_migration_status' );

		$getcount = get_option( 'bsuite_migration_count' );
		$since = date('Y-m-d H:i:s', strtotime('-1 minutes'));

		$res = $targets = $searchwords = $shistory = array();
		$res = $wpdb->get_results( "SELECT * 
			FROM $this->hits_incoming
			WHERE in_time < '$since'
			ORDER BY in_time ASC
			LIMIT $getcount" );

		$status['count_incoming'] = count( $res );
		update_option( 'bsuite_doing_migration_status', $status );

		foreach( $res as $hit ){
			$object_id = $object_type = $session_id = 0;

			if( !strlen( $hit->in_to ))
				$hit->in_to = get_option( 'siteurl' ) .'/';

			if( $hit->in_session )
				$session_id = $this->bstat_insert_session( $hit );

			$object_id = url_to_postid( $hit->in_to );

			// determine the target
			if( ( 1 > $object_id ) || (('posts' <> get_option( 'show_on_front' )) && $object_id == get_option( 'page_on_front' )) ){
				$object_id = $this->bstat_insert_term( $hit->in_to );
				$object_type = 1;
			}
			$targets[] = "($object_id, $object_type, 1, '$hit->in_time')";

			// look for search words
			if( ( $referers = implode( $this->get_search_terms( $hit->in_from ), ' ') ) && ( 0 < strlen( $referers ))) {
				$term_id = $this->bstat_insert_term( $referers );
				$searchwords[] = "($object_id, $object_type, $term_id, 1)";
			}

			if( $session_id ){
				if( $referers )
					$shistory[] = "($session_id, $term_id, 2)";

				if( $this->session_new ){
					$in_from = $this->bstat_insert_term( $hit->in_from );
					if( $referers )
						$shistory[] = "($session_id, $in_from, 3)";
				}

				$shistory[] = "($session_id, $object_id, $object_type)";
			}
		}

		$status['count_targets'] = count( $targets );
		$status['count_searchwords'] = count( $searchwords );
		$status['count_shistory'] = count( $shistory );
		update_option( 'bsuite_doing_migration_status', $status );

		if( count( $targets ) && !$status['did_targets'] ){
			if ( false === $wpdb->query( "INSERT INTO $this->hits_targets (object_id, object_type, hit_count, hit_date) VALUES ". implode( $targets, ',' ) ." ON DUPLICATE KEY UPDATE hit_count = hit_count + 1;" ))
				return new WP_Error('db_insert_error', __('Could not insert bsuite_hits_target into the database'), $wpdb->last_error);

			$status['did_targets'] = 1 ;
			update_option( 'bsuite_doing_migration_status', $status );
		}

		if( count( $searchwords ) && !$status['did_searchwords'] ){
			if ( false === $wpdb->query( "INSERT INTO $this->hits_searchphrases (object_id, object_type, term_id, hit_count) VALUES ". implode( $searchwords, ',' ) ." ON DUPLICATE KEY UPDATE hit_count = hit_count + 1;" ))
				return new WP_Error('db_insert_error', __('Could not insert bsuite_hits_searchword into the database'), $wpdb->last_error);

			$status['did_searchwords'] = 1;
			update_option( 'bsuite_doing_migration_status', $status );
		}

		if( count( $shistory ) && !$status['did_shistory'] ){
			if ( false === $wpdb->query( "INSERT INTO $this->hits_shistory (sess_id, object_id, object_type) VALUES ". implode( $shistory, ',' ) .';' ))
				return new WP_Error('db_insert_error', __('Could not insert bsuite_hits_session_history into the database'), $wpdb->last_error);

			$status['did_shistory'] = count( $shistory );
			update_option( 'bsuite_doing_migration_status', $status );
		}

		if( count( $res )){
			if ( false === $wpdb->query( "DELETE FROM $this->hits_incoming WHERE in_time < '$since' ORDER BY in_time ASC LIMIT ". count( $res ) .';'))
				return new WP_Error('db_insert_error', __('Could not clean up the incoming stats table'), $wpdb->last_error);
			if( $getcount > count( $res ))
				$wpdb->query( "OPTIMIZE TABLE $this->hits_incoming;");
		}

		if ( get_option( 'bsuite_doing_migration_popr') < time() && $this->get_lock( 'popr' )){
			if ( get_option( 'bsuite_doing_migration_popd') < time() && $this->get_lock( 'popd' ) ){
				$wpdb->query( "TRUNCATE $this->hits_pop" );
				$wpdb->query( "INSERT INTO $this->hits_pop (post_id, date_start, hits_total)
					SELECT object_id AS post_id, MIN(hit_date) AS date_start, SUM(hit_count) AS hits_total
					FROM $this->hits_targets
					WHERE object_type = 0
					AND hit_date >= DATE_SUB( NOW(), INTERVAL 45 DAY )
					GROUP BY object_id" );
				update_option( 'bsuite_doing_migration_popd', time() + 64800 );
			}
			$wpdb->query( "UPDATE $this->hits_pop p
				LEFT JOIN (
					SELECT object_id, COUNT(*) AS hit_count
					FROM (
						SELECT sess_id, sess_date
						FROM (
							SELECT sess_id, sess_date
							FROM $this->hits_sessions
							ORDER BY sess_id DESC
							LIMIT 12500
						) a
						WHERE sess_date >= DATE_SUB( NOW(), INTERVAL 1 DAY )
					) s
					LEFT JOIN $this->hits_shistory h ON h.sess_id = s.sess_id
					WHERE h.object_type = 0
					GROUP BY object_id
				) h ON h.object_id = p.post_id
				SET hits_recent = h.hit_count" );
			update_option( 'bsuite_doing_migration_popr', time() + 1500 );
		}

/*
		$posts = $wpdb->get_results("SELECT object_id, AVG(hit_count) AS hit_avg
				FROM $this->hits_targets
				WHERE hit_date >= DATE_SUB(CURDATE(),INTERVAL 30 DAY)
				AND object_type = 0
				GROUP BY object_id
				ORDER BY object_id ASC", ARRAY_A);
		$avg = array();
		foreach($posts as $post)
			$avg[$post['object_id']] = $post['hit_avg'];

		$posts = $wpdb->get_results("SELECT object_id, hit_count * (86400/TIME_TO_SEC(TIME(NOW()))) AS hit_now
				FROM $this->hits_targets
				WHERE hit_date = CURDATE()
				AND object_type = 0
				ORDER BY object_id ASC", ARRAY_A);
		$now = array();
		foreach($posts as $post)
			$now[$post['object_id']] = $post['hit_now'];

		$diff = array();
		foreach($posts as $post)
			$diff[$post['object_id']] = intval(($now[$post['object_id']] - $avg[$post['object_id']]) * 1000 );

		$win = count(array_filter($diff, create_function('$a', 'if($a > 0) return(TRUE);')));
		$lose = count($diff) - $win;

		$sort = array_flip($diff);
		ksort($sort);

		if(!empty($sort)){
			foreach(array_slice(array_reverse($sort), 0, $detail_lines) as $object_id){
				echo '<li><a href="'. get_permalink($object_id) .'">'. get_the_title($object_id) .'</a><br><small>Up: '. number_format($diff[$object_id] / 1000, 0) .' Avg: '. number_format($avg[$object_id], 0) .' Today: '. number_format($now[$object_id], 0) ."</small></li>\n";
			}
		}
*/

//print_r($wpdb->queries);

		update_option( 'bsuite_doing_migration', 0 );
		update_option( 'bsuite_doing_migration_status', array() );
		return(TRUE);
	}

	function get_search_engine( $ref ) {
		// a lot of inspiration and code for this function was taken from
		// Search Hilite by Ryan Boren and Matt Mullenweg
		global $wp_query;
		if( empty( $ref ))
			return false;

		$referer = urldecode( $ref );
		if (preg_match('|^http://(www)?\.?google.*|i', $referer))
			return('google');

		if (preg_match('|^http://search\.yahoo.*|i', $referer))
			return('yahoo');

		if (preg_match('|^http://search\.live.*|i', $referer))
			return('windowslive');

		if (preg_match('|^http://search\.msn.*|i', $referer))
			return('msn');

		if (preg_match('|^http://search\.lycos.*|i', $referer))
			return('lycos');

		$home = parse_url( get_settings( 'siteurl' ));
		$ref = parse_url( $referer );
		if ( strpos( ' '. $ref['host'] , $home['host'] ))
			return('internal');

		return(FALSE);
	}

	function get_search_terms( $ref ) {
		// a lot of inspiration and code for this function was taken from
		// Search Hilite by Ryan Boren and Matt Mullenweg
//		if( !$engine = $this->get_search_engine( $ref ))
//			return(FALSE);

$engine = $this->get_search_engine( $ref );

		$referer = parse_url( $ref );
		parse_str( $referer['query'], $query_vars );

		$query_array = array();
		switch ($engine) {
		case 'google':
			if( $query_vars['q'] )
				$query_array = explode(' ', urldecode( $query_vars['q'] ));
			break;

		case 'yahoo':
			if( $query_vars['p'] )
				$query_array = explode(' ', urldecode( $query_vars['p'] ));
			break;

		case 'windowslive':
			if( $query_vars['q'] )
				$query_array = explode(' ', urldecode( $query_vars['q'] ));
			break;

		case 'msn':
			if( $query_vars['q'] )
				$query_array = explode(' ', urldecode( $query_vars['q'] ));
			break;

		case 'lycos':
			if( $query_vars['query'] )
				$query_array = explode(' ', urldecode( $query_vars['query'] ));
			break;

		case 'internal':
			if( $query_vars['s'] )
				$query_array = explode(' ', urldecode( $query_vars['s'] ));

			// also need to handle the case where a search matches the /search/ pattern
			break;
		}

		$query_array = array_filter( array_map( array(&$this, 'trimquotes') , $query_array ));

		return $query_array;
	}

	function post_hits( $args = '' ) {
		global $wpdb;

		$defaults = array(
			'return' => 'formatted',
			'days' => 0,
			'template' => '<li><a href="%%link%%">%%title%%</a>&nbsp;(%%hits%%)</li>'
		);
		$args = wp_parse_args( $args, $defaults );

		$post_id = (int) $args['post_id'] > 1 ? 'AND object_id = '. (int) $args['post_id'] : '';

		$date = '';
		if($args['days'] > 1)
			$date  = "AND hit_date > '". date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - $args['days'], date("Y"))) ."'";

		// here's the query, but let's try to get the data from cache first
		$request = "SELECT
			FORMAT(SUM(hit_count), 0) AS hits, 
			FORMAT(AVG(hit_count), 0) AS average
			FROM $this->hits_targets
			WHERE 1=1
			$post_id
			AND object_type = 0
			$date
			";

		if ( !$result = wp_cache_get( (int) $args['post_id'] .'_'. (int) $args['days'], 'bstat_post_hits' ) ) {
			$result = $wpdb->get_results($request, ARRAY_A);
			wp_cache_add( (int) $args['post_id'] .'_'. (int) $args['days'], $result, 'bstat_post_hits', 1800 );
		}

		if(empty($result))
			return(NULL);

		if($args['return'] == 'array')
			return($result);

		if($args['return'] == 'formatted'){
			$list = str_replace(array('%%avg%%','%%hits%%'), array($result[0]['average'], $result[0]['hits']), $args['template']);
			return($list);
		}
	}

	function pop_posts( $args = '' ) {
		global $wpdb, $bsuite;

		if( !$this->get_lock( 'pop_posts' ))
			return( FALSE );

		$args = wp_parse_args( $args, array(
			'count' => 15,
			'return' => 'formatted',
			'show_icon' => 0,
			'show_title' => 1,
			'show_counts' => 1,
			'icon_size' => 's',
		));

		$date = 'AND hit_date = DATE(NOW())';
		if($args['days'] > 1)
			$date  = "AND hit_date > '". date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - $args['days'], date("Y"))) ."'";

		$limit = 'LIMIT '. ( absint( $args['count'] ) * 2 );

		$request = "SELECT object_id, SUM(hit_count) AS hit_count
			FROM $this->hits_targets
			WHERE 1=1
			AND object_type = 0
			$date
			GROUP BY object_id
			ORDER BY hit_count DESC
			$limit";
		$result = $wpdb->get_results($request, ARRAY_A);

		if(empty($result))
			return(NULL);

		if($args['return'] == 'array')
			return($result);

		if($args['return'] == 'formatted'){
			$list = '';
			foreach($result as $post){
				$list .='<li>'. ( $args['show_icon'] ? '<a href="'. get_permalink( $post['object_id'] ) .'" class="bsuite_post_icon_link" title="'. attribute_escape( get_the_title( $post['object_id'] )).'">'. $this->icon_get_h( $post['object_id'], $args['icon_size'] ) .'</a>' : '' ) . ( $args['show_title'] ? '<a href="'. get_permalink( $post['object_id'] ) .'" title="'. attribute_escape( get_the_title( $post['object_id'] )).'">'. get_the_title( $post['object_id'] ) . '</a>' : '' ) . ( $args['show_counts'] ? '&nbsp;('. $post['hit_count'] .')' : '' ) .'</li>';
			}
			return($list);
		}
	}

	function pop_refs( $args = '' ) {
		global $wpdb, $bsuite;

		if( !$this->get_lock( 'pop_refs' ))
			return( FALSE );

		$defaults = array(
			'count' => 15,
			'return' => 'formatted',
			'template' => '<li>%%title%%&nbsp;(%%hits%%)</li>'
		);
		$args = wp_parse_args( $args, $defaults );

		$limit = 'LIMIT '. (int) $args['count'];

		$request = "SELECT COUNT(*) AS hit_count, name
			FROM (
				SELECT object_id
				FROM $this->hits_shistory
				WHERE object_type = 2
				ORDER BY sess_id DESC
				LIMIT 1000
			) a
			LEFT JOIN $this->hits_terms t ON a.object_id = t.term_id
			GROUP BY object_id
			ORDER BY hit_count DESC
			$limit";

		$result = $wpdb->get_results($request, ARRAY_A);

		if(empty($result))
			return(NULL);

		if($args['return'] == 'array')
			return($result);

		if($args['return'] == 'formatted'){
			$list = '';
			foreach($result as $row){
				$list .= str_replace(array('%%title%%','%%hits%%'), array($row['name'], $row['hit_count']), $args['template']);
			}
			return($list);
		}
	}
	// end stats functions



	//
	// Searchsmart
	//
	function searchsmart_posts_request( $query ){
		global $wp_query, $wpdb;

		if($wp_query->is_admin)
			return($query);

		if (!empty($wp_query->query_vars['s'])) {
			$limit = explode('LIMIT', $query);
			if(!$limit[1]){
				// $paged, $posts_per_page, and $limit are here for cases
				// where the query doesn't have an explicit LIMIT declaration
				$paged = $wp_query->query_vars['paged'];
				if(!$paged)
					$paged = 1;

				$posts_per_page = $wp_query->query_vars['posts_per_page'];
				if(!$posts_per_page)
					$posts_per_page = get_settings('posts_per_page');

				$limit = explode('LIMIT', $query);
				if(!$limit[1])
					$limit[1] = ($paged - 1) * $posts_per_page .', '. $posts_per_page;
			}

//print_r($wp_query);
//echo '<h2>'. $this->searchsmart_query( $wp_query->query_vars['s'], 'LIMIT '. $limit[1] ) .'</h2>';
			return( $this->searchsmart_query( $wp_query->query_vars['s'], 'LIMIT '. $limit[1] ));
		}
		return( $query );
	}

	function searchsmart_query( $searchphrase, $limit = 'LIMIT 0,5' ){
		global $wpdb;

		if( 3 < strlen( trim( $searchphrase ))){
			return("SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.* 
				FROM (
					SELECT post_id, MATCH (content, title) AGAINST (". $wpdb->prepare( '%s', $searchphrase ) .") AS score 
					FROM $this->search_table
					WHERE MATCH (content, title) AGAINST (". $wpdb->prepare( '%s', $searchphrase ) .")
					ORDER BY score DESC
					LIMIT 1000
				) s
				LEFT JOIN $wpdb->posts ON ( s.post_id = $wpdb->posts.ID ) 
				WHERE 1=1 
				AND post_status IN ('publish', 'private')
				ORDER BY score DESC 
				$limit");
		}else{
			return("SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.*
				FROM $wpdb->posts 
				WHERE 1=1 
				AND post_content LIKE ". $wpdb->prepare( '%s', '%'. $searchphrase .'%' ) ."
				AND post_status IN ('publish', 'private')
				ORDER BY post_date_gmt DESC $limit");
		}
	}

	function searchsmart_direct(){
		global $wp_query, $wp_rewrite;

		// redirect when there's a redirection order for the post
		if( $wp_query->is_singular && get_post_meta( $wp_query->post->ID, 'redirect', TRUE ))
			wp_redirect( get_post_meta( $wp_query->post->ID, 'redirect', TRUE ), '301');

		// redirects ?s={search_term} to /search/{search_term} if permalinks are working
		if( isset( $_GET['s'] ) && !empty( $wp_rewrite->permalink_structure ) )
			wp_redirect(get_option('siteurl') .'/'. $wp_rewrite->search_base .'/'. urlencode( $_GET['s'] ), '301');

		// serves the quickview links
		if( $wp_query->is_singular && isset( $_GET['quickview'] ) )
			$this->quickview();

		// redirects the search to the single page if the search returns only one item
		if( !$wp_query->is_singular && 1 === $wp_query->post_count && !$wp_query->is_feed )
			wp_redirect( get_permalink( $wp_query->post->ID ) , '302');

		return(TRUE);
	}

	function searchsmart_post_link_direct( $permalink, $post ){
		if( $redirect = get_post_meta( $post->ID, 'redirect', TRUE ))
			return( $redirect );
		return( $permalink );
	}

	function searchsmart_edit( $content ){
		// called when posts are edited or saved
		if( (int) $_POST['post_ID'] )
			$this->searchsmart_delpost( (int) $_POST['post_ID'] );
		return($content);
	}

	function searchsmart_delpost( $post_id ){
		global $wpdb;
		$wpdb->get_results( "DELETE FROM $this->search_table WHERE post_id = $post_id" );
	}

	function searchsmart_content( $content ){

		// remove bsuite tokens and html formatting
		$content = preg_replace(
			'/\[\[([^\]])*\]\]/',
			'',
			strip_tags(
				str_ireplace(array('<br />', '<br/>', '<br>', '</p>', '</li>', '</h1>', '</h2>', '</h3>', '</h4>'), "\n", 
					stripslashes(
						html_entity_decode( $content )
					)
				)
			)
		);

		// shortcodes
		$content = preg_replace( '/\[(.*?)\]/', '', $content );

		// find words with accented characters, create transliterated versions of them
		$unaccented = array_diff( str_word_count( $content, 1 ), str_word_count( remove_accents( $content ), 1 ));

//		// remove punctuation
//		$content = trim(preg_replace(
//			'/([[:punct:]])*/',
//			'',
//			$content));

		// apply filters
		return( apply_filters('bsuite_searchsmart_content', $content .' '. implode( ' ', $unaccented )));

	}

	function searchsmart_upindex(){
		// put content in the keyword search index
		global $wpdb;

		update_option('bsuite_doing_ftindex', time() + 300 );

		$posts = $wpdb->get_results("SELECT a.ID, a.post_content, a.post_title
			FROM $wpdb->posts a
			LEFT JOIN $this->search_table b ON a.ID = b.post_id
			WHERE a.post_status = 'publish'
			AND b.post_id IS NULL
			LIMIT 25
			");

		if( count( $posts )) {
			$insert = array();
			foreach( $posts as $post ) {
				$insert[] = '('. (int) $post->ID .', "'. $wpdb->escape( $this->searchsmart_content( $post->post_title ."\n\n". $post->post_content )) .'", "'. $wpdb->escape( $post->post_title ) .'")';
			}
		}else{
			return( FALSE );
		}

		if( count( $insert )) {
			$wpdb->get_results( 'REPLACE INTO '. $this->search_table .'
						(post_id, content, title) 
						VALUES '. implode( ',', $insert ));
		}

		// diabled so that the update runs less often.
		update_option('bsuite_doing_ftindex', 0 );

		return( count( $posts ));
	}

	function searchsmart_upindex_passive(){
		// finds unindexed posts and adds them to the fulltext index in groups of 10, runs via cron
		global $wpdb;

		if( !$this->get_lock( 'ftindexer' ))
			return( TRUE );

		// also use the options table
		if ( get_option('bsuite_doing_ftindex') > time() )
			return( TRUE );

		$this->searchsmart_upindex();

		return(TRUE);
	}
	// end Searchsmart



	function quickview(){
		global $wp_query;

		// make wp think this is a posts page, not a singular page
		$wp_query->is_single = FALSE;
		$wp_query->is_page = FALSE;
		$wp_query->is_singular = FALSE;
		$wp_query->is_posts_page = TRUE;
		$this->is_quickview = TRUE;

		//loop
		if (have_posts()){
			while (have_posts()){
				the_post();
				ob_start();
?>
<div id="post-<?php echo $id ?>" class="hentry quickview">
	<a href="<?php the_permalink() ?>" class="bsuite_post_icon_link" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_icon( 'm' ) ?></a>
	<h3 class="entry-title"><a href="<?php the_permalink() ?>" title="<?php printf(__('Permalink to %s', 'sandbox'), wp_specialchars(get_the_title(), 1)) ?>" rel="bookmark"><?php the_title() ?></a></h3>
	<div class="entry-excerpt">
		<?php the_excerpt(''.__( 'Read the rest of this entry' ).'') ?>
	</div>
	<div class="entry-meta">
		<span class="author vcard"><?php printf(__('Posted by %s'), '<a class="url fn n" href="'.get_author_link(false, $authordata->ID, $authordata->user_nicename).'" title="' . sprintf(__('View all posts by %s', 'sandbox'), $authordata->display_name) . '">'.get_the_author().'</a>') ?></span>
		<span class="meta-sep">&middot;</span>
		<span class="entry-date"><abbr class="published" title="<?php the_time('Y-m-d\TH:i:sO'); ?>"><?php unset($previousday); printf(__('%1$s &#8211; %2$s', 'sandbox'), the_date('', '', '', false), get_the_time()) ?></abbr></span>
		<span class="meta-sep">&middot;</span>

		<?php edit_post_link(__( 'Edit' ), '<span class="edit-link">', "</span> <span class=\"meta-sep\">&middot;</span>\n"); ?>
			<span class="comments-link"><?php comments_popup_link(__( 'Comments (0)' ), __( 'Comments (1)' ), __( 'Comments (%)' )) ?></span>
	</div>
</div>
<?php
			die( apply_filters( 'bsuite_quickview_excerpt', ob_get_clean() ));


			}
		}
die();
	}



	// bSuggestive related functions
	function bsuggestive_query( $id ) {
		global $wpdb;

		$id = (int) $id;

		if( $id ){
			$taxonomies = ( array_filter( apply_filters( 'bsuite_suggestive_taxonomies', array( 'post_tag', 'category' ))));

			$taxonomies = array_filter( array_map( array( &$wpdb, 'escape' ), $taxonomies ));

			$ignore_ids = ( array_filter( apply_filters( 'bsuite_suggestive_ignoreposts', array( $id ))));
			if( is_array( $ignore_ids ))
				$ignore_ids = implode( ',', array_filter( array_map( 'absint' , $ignore_ids )));
			else
				$ignore_ids = $id;

			if( count( $taxonomies ))
				return( apply_filters('bsuite_suggestive_query',
					"SELECT t_r.object_id AS post_id, COUNT(t_r.object_id) AS hits
					FROM ( SELECT t_ra.term_taxonomy_id
						FROM $wpdb->term_relationships t_ra
						LEFT JOIN $wpdb->term_taxonomy t_ta ON t_ta.term_taxonomy_id = t_ra.term_taxonomy_id
						WHERE t_ra.object_id  = $id
						AND t_ta.taxonomy IN ('". implode( $taxonomies, "','") ."')
					) ttid
					LEFT JOIN $wpdb->term_relationships t_r ON t_r.term_taxonomy_id = ttid.term_taxonomy_id
					LEFT JOIN $wpdb->posts p ON t_r.object_id  = p.ID
					WHERE p.ID NOT IN( $ignore_ids )
					AND p.post_status = 'publish'
					GROUP BY p.ID
					ORDER BY hits DESC, p.post_date_gmt DESC
					LIMIT 150", $id)
				);
		}
		return FALSE;
	}

	function bsuggestive_getposts( $id ) {
		global $wpdb;

		if ( !$related_posts = wp_cache_get( $id, 'bsuite_related_posts' ) ) {
			if( $the_query = $this->bsuggestive_query( $id ) ){
				$related_posts = $wpdb->get_col($the_query);
				wp_cache_set( $id, $related_posts, 'bsuite_related_posts', time() + 900000 ); // cache for 25 days
				return($related_posts); // if we have to go to the DB to get the posts, then this will get returned
			}
			return( FALSE ); // if there's nothing in the cache and we've got no query
		}
		return($related_posts); // if the cache is still warm, then we return this
	}

	function bsuggestive_delete_cache( $id ) {
		$id = (int) $id;
		if ( !$id )
			return FALSE;

		wp_cache_delete( $id, 'bsuite_related_posts' );
	}

	function bsuggestive_the_related($before = '<li>', $after = '</li>') {
		global $post;
		$report = FALSE;

		$id = (int) $post->ID;
		if ( !$id )
			return( FALSE ); // no ID, no service

		if( ( $posts = $this->bsuggestive_getposts( $id )) && is_array( $posts )){
			$posts = array_slice( $posts, 0, 5 );
			$report = '';
			foreach($posts as $post_id){
//				$post = &get_post( $post_id );
				$url = get_permalink($post_id);
				$linktext = get_the_title($post_id);
				$report .= $before . "<a href='$url'>$linktext</a>". $after;
			}
		}
		return($report);
	}

	function bsuggestive_the_content( $content ) {
		if( $related = $this->bsuggestive_the_related() )
			return( $content .'<h3 class="bsuite_related">Related items</h3><ul class="bsuite_related">'. $related .'</ul>' );
		return( $content );
	}

	function bsuggestive_bypageviews_getposts( $id ) {
		global $wpdb;

		$id = absint( $id );

		$ignore_ids = ( array_filter( apply_filters( 'bsuite_suggestive_ignoreposts', array( $id ))));
		if( is_array( $ignore_ids ))
			$ignore_ids = implode( ',', array_filter( array_map( 'absint' , $ignore_ids )));
		else
			$ignore_ids = $id;

		if ( !$related_posts = wp_cache_get( $id, 'bsuite_relatedbypageviews_posts' ) ) {
			if( $related_posts = $wpdb->get_col(
				"SELECT h.object_id, COUNT(h.object_id) AS hits
				FROM
				(
					SELECT sess_id
					FROM $this->hits_shistory
					WHERE object_id = $id
					AND object_type = 0

					GROUP BY sess_id DESC
					LIMIT 250

				) s
				LEFT JOIN $this->hits_shistory h ON h.sess_id = s.sess_id
				LEFT JOIN 
				(
					SELECT post_id
					FROM $this->hits_pop
					ORDER BY hits_recent DESC
					LIMIT 7
				) pop ON pop.post_id = h.object_id
				WHERE h.object_id NOT IN( $ignore_ids )
				AND h.object_type = 0
				AND pop.post_id IS NULL
				GROUP BY h.object_id
				ORDER BY hits DESC
				LIMIT 0,150"
			)){
				wp_cache_set( $id, $related_posts, 'bsuite_relatedbypageviews_posts', time() + 90000 ); // cache it for 25 hours
				return( $related_posts ); // if we have to go to the DB to get the posts, then this will get returned
			}
			return( FALSE ); // if there's nothing in the cache and we've got no query
		}
		return($related_posts); // if the cache is still warm, then we return this
	}

	function bsuggestive_bypageviews_the_related($before = '<li>', $after = '</li>') {
		global $post;
		$report = FALSE;

		$id = (int) $post->ID;
		if ( !$id )
			return( FALSE ); // no ID, no service

		if( ( $posts = $this->bsuggestive_bypageviews_getposts( $id )) && is_array( $posts )){
			$posts = array_slice( $posts, 0, 5 );
			$report = '';
			foreach($posts as $post_id){
//				$post = &get_post( $post_id );
				$url = get_permalink($post_id);
				$linktext = get_the_title($post_id);
				$report .= $before . "<a href='$url'>$linktext</a>". $after;
			}
		}
		return($report);
	}

	function bsuggestive_bypageviews_the_content( $content ) {
		if( $related = $this->bsuggestive_bypageviews_the_related() )
			return( $content . '<h3 class="bsuite_related_bypageviews">People who looked at this item also looked at...</h3><ul class="bsuite_related">'. $related .'</ul>' );
		return( $content );
	}
	// end bSuggestive


/*
	//
	// user-contributed comments
	//
	function uctags_preprocess_comment( $comment ) {
		$comment['bsuite_uctags'] = !empty( $_REQUEST['bsuite_uctags'] );
		if($comment['bsuite_uctags']){
print_r( array_map( 'trim',  explode( ',', wp_filter_nohtml_kses( $_REQUEST['bsuite_uctags'] ))));
//die;
		}

print_r( $_REQUEST );
die;
		return( $comment );
	}
	// end user-contributed comment functions
*/

	function pagetree(){
		// identify the family tree of a page, return an array
		global $wp_query;
		$tree = NULL;

		if ($wp_query->is_page){
			$object = $wp_query->get_queried_object();

			// cycle through the tree and build an array
			$parent_id = $object->post_parent;
			$tree[]  = $object->ID;
			while ($parent_id){
				$page = get_page($parent_id);
				$tree[]  = $page->ID;
				$parent_id  = $page->post_parent;
			}

			// the tree is in reverse order.
			$tree = array_reverse($tree);
		}
		return $tree;
	}



	//
	// cron utility functions
	//
	function cron_reccurences( $schedules ) {
		$schedules['bsuite_interval'] = array('interval' => get_option( 'bsuite_migration_interval' ), 'display' => __( 'bSuite interval. Set in bSuite options page.' ));
		return( $schedules );
	}

	function cron_register() {
		// take a look at Glenn Slaven's tutorial on WP's psudo-cron:
		// http://blog.slaven.net.au/archives/2007/02/01/timing-is-everything
		wp_clear_scheduled_hook('bsuite_interval');
		wp_schedule_event( time() + 120, 'bsuite_interval', 'bsuite_interval' );
	}
	// end cron functions



	function get_lock( $lock ){
		global $wpdb;

		if( !$lock = preg_replace( '/[^a-z|0-9|_]/i', '', str_replace( ' ', '_', $lock )))
			return( FALSE );

		// use a named mysql lock to prevent simultaneous execution
		// locks automatically drop when the connection is dropped
		// http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_get-lock
		if( 0 == $wpdb->get_var( 'SELECT GET_LOCK("'. $wpdb->prefix . 'bsuitelock_'. $lock .'", ".001")' ))
			return( FALSE );
		return( TRUE );
	}

	function release_lock( $lock ){
		global $wpdb;

		if( !$lock = preg_replace( '/[^a-z|0-9|_]/i', '', str_replace( ' ', '_', $lock )))
			return( FALSE );

		if( 0 == $wpdb->get_var( 'SELECT RELEASE_LOCK("'. $wpdb->prefix . 'bsuitelock_'. $lock .'", ".001")' ))
			return( FALSE );
		return( TRUE );
	}

	//
	// loadaverage related functions
	//
	function get_loadavg(){
		static $result = FALSE;
		if($result)
			return($result);

		if(function_exists('sys_getloadavg')){
			$load_avg = sys_getloadavg();
		}else{
			$load_avg = &$this->sys_getloadavg();
		}
		return( round( $load_avg[0], 2 ));
	}

	function sys_getloadavg(){
		// the following code taken from tom pittlik's comment at
		// http://php.net/manual/en/function.sys-getloadavg.php
		$str = substr( strrchr( shell_exec( 'uptime' ),':' ),1 );
		$avs = array_map( 'trim', explode( ',', $str ));
		return( $avs );
	}
	// end load average related functions


	// A short but powerfull recursive function
	// that works also if the dirs contain hidden files
	//
	// taken from http://us.php.net/manual/en/function.unlink.php
	//
	// contributions from:
	// ayor at ayor dot biz (20-Dec-2007 09:02)
	// ggarciaa at gmail dot com (04-July-2007 01:57)
	// stefano at takys dot it (28-Dec-2005 11:57)
	//
	// $dir = the target directory
	// $DeleteMe = if true delete also $dir, if false leave it alone
	function unlink_recursive($dir, $DeleteMe = FALSE) {
		if(!$dh = @opendir($dir)) return;
		while (false !== ($obj = readdir($dh))) {
			if($obj=='.' || $obj=='..') continue;
			if (!@unlink($dir.'/'.$obj)) $this->unlink_recursive($dir.'/'.$obj, true);
		}

		closedir($dh);
		if ($DeleteMe){
			@rmdir($dir);
		}
	}

	// timers
	function timer_start( $name = 1 ) {
		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$this->time_start[ $name ] = $mtime[1] + $mtime[0];
		return true;
	}

	function timer_stop( $name = 1 ) {
		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$time_end = $mtime[1] + $mtime[0];
		$time_total = $time_end - $this->time_start[ $name ];
		return $time_total;
	}
	// end timers


	function trimquotes( $in ) {
		return( trim( trim( $in ), "'\"" ));
	}

	function feedlink(){
		return(strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))) . '://' . $_SERVER['HTTP_HOST'] . add_query_arg('feed', 'rss', add_query_arg('bsuite_share')));
	}

	// set a cookie
	function cookie($name, $value = NULL) {
		if($value === NULL){
			if($_GET[$name]) return $_GET[$name];
			elseif($_POST[$name]) return $_POST[$name];
			elseif($_SESSION[$name]) return $_SESSION[$name];
			elseif($_COOKIE[$name]) return $_COOKIE[$name];
			else return false;
		}else{
			setcookie($name, $value, time()+60*60*24*30, '/', '.scriblio.net');
			return($value);
		}
	}
	// end 



	// get and display rss feeds
	function get_feed($feed, $count = 15, $template = '<li>%%title%%<br />%%content%%</li>', $return = FALSE){
		if(!function_exists('fetch_rss'))
			require_once (ABSPATH . WPINC . '/rss.php');
		$rss = fetch_rss($feed);
		//print_r($rss);

		$i = $list = NULL;
//print_r($rss);
		foreach($rss->items as $item){
			$i++;
			if($i > $count)
				break;
			$link = $item['link'];
			$title = $item['title'];
			if($item['content']['encoded']){
				$content = $item['content']['encoded'];
			}else{
				$content = $item['description'];
			}
			$list .= str_replace( array( '%%title%%','%%content%%','%%link%%' ), array( $title, $content, $link ), $template );
//echo $template;

		}

		if($return)
			return($list);
		echo $list;
	}
	// end function get_rss


	// machine tags
	function machtag_save_post($post_id, $post) {
		// Passed machine tags overwrite existing if not empty
		if ( isset( $_REQUEST['bsuite_machine_tags'] ))

			foreach( $this->machtag_parse_tags( $_REQUEST['bsuite_machine_tags'] ) as $taxonomy => $tags ){

				if( 'post_tag' == $taxonomy ){
					wp_set_post_tags($post_id, $tags, TRUE);
					continue;
				}

				if(!is_taxonomy( $taxonomy ))
					register_taxonomy($taxonomy, 'post');
				wp_set_object_terms($post_id, $tags, $taxonomy);
			}
	}

	function machtag_parse_tags( $tags_input ) {
		$tags = $tags_parsed = array();
		if( !is_array( $tags_input )){
			$tag_lines = explode( "\n", $tags_input );
			foreach($tag_lines as $tag_line)
				$tags_parsed[] = $this->machtag_parse_tag( $tag_line );
		}else{
			$tags_parsed = $tags_input;
		}

		foreach( $tags_parsed as $tag_parsed )
			if( !empty( $tag_parsed['taxonomy'] ) && !empty( $tag_parsed['term'] ))
				$tags[$tag_parsed['taxonomy']][] = $tag_parsed['term'];
		return( $tags );
	}

	function machtag_parse_tag( $tag ) {
		$namespace = $taxonomy = $term = FALSE;
		$taxonomy = 'post_tag';

		$temp_a = explode(':', $tag, 2);

		if($temp_a[1]){
			$temp_b = explode('=', $temp_a[1], 2);

			if($temp_b[1]){
				// has namespace, fieldname, & value
				$namespace = trim( $temp_a[0] );
				$taxonomy = trim( $temp_b[0] );
				$term = trim( $temp_b[1] );
			}else{
				// has just fieldname & value
				$taxonomy = trim( $temp_a[0] );
				$term = trim( $temp_b[0] );
			}
		}else{
			$temp_b = explode('=', $temp_a[0], 2);

			if($temp_b[1]){
				// has just fieldname & value
				$taxonomy = trim( $temp_b[0] );
				$term = trim( $temp_b[1] );
			}else{
				// has just value
				$term = trim( $temp_b[0] );
			}
		}

		return(array('taxonomy' => $taxonomy, 'term' => $term));
	}

	// add tools to edit screens
	function edit_current_user_can( $user_caps, $requested_caps, $cap_data ){
		// this bit of code taken from Blicki and used under the terms of the GPL
		// Blicki info: http://wordpress.org/extend/plugins/blicki/
		// http://www.blicki.com/

		global $post_ID, $page_ID;

		$requested_cap = $cap_data[0];
		$user_id = $cap_data[1];

		if ( isset($cap_data[2]) && ( 0 < (int) $cap_data[2] ))
			$post_id = $cap_data[2];
		else if ( isset($post_ID) && ( 0 < (int) $post_ID ))
			$post_id = $post_ID;
		else if ( isset($page_ID) && ( 0 < (int) $page_ID ))
			$post_id = $page_ID;
		//$current_user = new WP_User($user_id);

		switch( $requested_cap ){
			case 'publish_pages':
				foreach ($requested_caps as $req_cap)
					$req_caps[$req_cap] = true;

				$who_can_edit = get_post_meta($post_id, '_bsuite_who_can_edit', true);
				if ( empty($who_can_edit) )
					$who_can_edit = get_settings('bsuite_who_can_publish');

				switch( $who_can_edit ){
					case 'anyone':
						$user_caps = array_merge($user_caps, $req_caps);
						break;

					case 'registered_users':
						if ( is_user_logged_in() )
							$user_caps = array_merge($user_caps, $req_caps);
						break;

					case 'authors':
						if ( is_user_logged_in() && isset($user_caps['author']))
							$user_caps = array_merge($user_caps, $req_caps);
						break;

					default:
						$caps = map_meta_cap('edit_page', $user_id, $post_id);
						foreach ($caps as $cap) {
							if ( empty($user_caps[$cap]) || !$user_caps[$cap] )
								return $user_caps;
						}
						$user_caps = array_merge($user_caps, $req_caps);
				}
				break;

			case 'edit_page':
			case 'edit_others_pages':
			case 'edit_published_pages':
				foreach ($requested_caps as $req_cap)
					$req_caps[$req_cap] = true;
				$who_can_edit = get_post_meta($post_id, '_bsuite_who_can_edit', true);
				if ( empty($who_can_edit) )
					$who_can_edit = get_settings('bsuite_who_can_edit');
				if ( 'anyone' == $who_can_edit ) {
					$user_caps = array_merge($user_caps, $req_caps);
				} else if ('registered_users' == $who_can_edit ) {
					if ( is_user_logged_in() )
						$user_caps = array_merge($user_caps, $req_caps);
				} else if ('authors' == $who_can_edit ) {
					if ( is_user_logged_in() && isset($user_caps['author']))
						$user_caps = array_merge($user_caps, $req_caps);
				} else {
					$caps = map_meta_cap('edit_page', $user_id, $post_id);
					foreach ($caps as $cap) {
						if ( empty($user_caps[$cap]) || !$user_caps[$cap] )
							return $user_caps;
					}
					$user_caps = array_merge($user_caps, $req_caps);
				}
				break;

			case 'edit_pages':
			case 'read':
				foreach ($requested_caps as $req_cap)
					$req_caps[$req_cap] = true;
				$who_can_edit = get_option('bsuite_who_can_edit');
				if ( 'anyone' == $who_can_edit ) {
					$user_caps = array_merge($user_caps, $req_caps);
				} else if ('registered_users' == $who_can_edit ) {
					if ( is_user_logged_in() )
						$user_caps = array_merge($user_caps, $req_caps);
				}
				return $user_caps;
				break;

			case 'delete_page':
			case 'delete_pages':
				foreach ($requested_caps as $req_cap)
					$req_caps[$req_cap] = true;
				$who_can_delete = get_option('bsuite_who_can_delete');
				if ( 'anyone' == $who_can_delete ) {
					$user_caps = array_merge($user_caps, $req_caps);
				} else if ('registered_users' == $who_can_delete ) {
					if ( is_user_logged_in() )
						$user_caps = array_merge($user_caps, $req_caps);
				}
				return $user_caps;
				break;

			case 'bsuite_change_access':
				$caps = map_meta_cap('edit_page', $user_id, $post_id);
				foreach ($caps as $cap) {
					if ( empty($user_caps[$cap]) || !$user_caps[$cap] )
						return $user_caps;
				}
				$user_caps['bsuite_change_access'] = true;
				break;
		}

		return $user_caps;
	}

	function edit_publish_page( $post_ID ) {
		if ( !isset($_POST['bsuite_who_can_edit']) )
			return $post_ID;

		$who = $_POST['bsuite_who_can_edit'];
		if ( ! update_post_meta($post_ID, '_bsuite_who_can_edit',  $who))
			add_post_meta($post_ID, '_bsuite_who_can_edit',  $who, true);

		return $post_ID;
	}

	function edit_page_form() {
// must change hooks. example: http://planetozh.com/blog/2008/02/wordpress-snippet-add_meta_box/
		$this->edit_insert_perms_form();
		$this->edit_insert_tag_form();
		$this->edit_insert_category_form();
		$this->edit_insert_excerpt_form();
//		$this->edit_insert_tools();
		$this->edit_insert_machinetag_form();
	}

	function edit_post_form() {
//		$this->edit_insert_tools();
		$this->edit_insert_machinetag_form();
	}

	function edit_comment_form() {
		// there's no edit_comment_form hook!!!
		$this->edit_insert_tag_form();
		$this->edit_insert_tools();
		$this->edit_insert_machinetag_form();
	}

	function edit_insert_perms_form() {
		global $post_ID;
		if ( !current_user_can( 'bsuite_change_access' ) )
			return;

		$who = get_post_meta( $post_ID, '_bsuite_who_can_edit', TRUE );
		if ( empty($who) )
			$who = get_settings( 'bsuite_who_can_edit' );
?>
<div id="editpermsdiv" class="postbox if-js-closed">
<h3><?php _e('Who can edit this page?'); ?></h3>
<div class="inside">
<select name="bsuite_who_can_edit" id="bsuite_who_can_edit">
<option value="anyone" <?php selected('anyone', $who); ?>><?php _e('Anyone') ?></option>
<option value="registered_users" <?php selected('registered_users', $who); ?>><?php _e('Registered users') ?></option>
<option value="authors" <?php selected('authors', $who); ?>><?php _e('Authors and Editors') ?></option>
<option value="editors" <?php selected('editors', $who); ?>><?php _e('Just Editors') ?></option>
</select>
</div>
</div>
<?php
	}

	function edit_insert_tag_form() {
		global $post_ID;
		?>
<script type='text/javascript'>
/* <![CDATA[ */
	postL10n = {
		tagsUsed: "Tags used on this page:",
		add: "Add",
		addTag: "Add new tag",
		separate: "Separate tags with commas",
		cancel: "Cancel",
		edit: "Edit"
	}
/* ]]> */
</script>
<div id="tagsdiv" class="postbox <?php echo postbox_classes('tagsdiv', 'post'); ?>">
<h3><?php _e('Tags'); ?></h3>
<div class="inside">
<p id="jaxtag"><input type="text" name="tags_input" class="tags-input" id="tags-input" size="40" tabindex="3" value="<?php echo get_tags_to_edit( $post_ID ); ?>" /></p>
<div id="tagchecklist"></div>
</div>
</div>
		<?php
	}

	function edit_insert_tools() {
		global $post_ID;
		?>
		<fieldset id="bsuite_tools">
			<legend>bSuite Tools: <a id="bsuite_auto_tag_button">Auto Tag</a> <a id="bsuite_auto_excerpt_button">Auto Excerpt</a> (<a href="http://maisonbisson.com/blog/bsuite/auto-tag-excerpt">about these tools</a>) (NOT WORKING, for now)</legend>
		</fieldset>
		<?php
	}

	function edit_insert_machinetag_form() {
		global $post_ID;

		$tags = wp_get_object_terms($post_ID, get_object_taxonomies('post'));

		$tags_to_edit = array();
		foreach($tags as $key => $tag){
			if($tag->taxonomy == 'post_tag' || $tag->taxonomy == 'category')
				continue;
			$tags_to_edit[] = '<li><input class="taxonomy" type="text" id="bsuite_machine_tags_'. $key .'_taxonomy" name="bsuite_machine_tags['. $key .'][taxonomy]" value="'. $tag->taxonomy .'" /> : <input class="term" type="text" id="bsuite_machine_tags_'. $key .'_term" name="bsuite_machine_tags['. $key .'][term]" value="'. $tag->name .'" /></li>';
		}
//		natcasesort($tags_to_edit);

		$key++;
		$tags_to_edit[] = '<li><input class="taxonomy" type="text" id="bsuite_machine_tags_'. $key .'_taxonomy" name="bsuite_machine_tags['. $key .'][taxonomy]" value="" /> : <input class="term" type="text" id="bsuite_machine_tags_'. $key .'_term" name="bsuite_machine_tags['. $key .'][term]" value="" /></li>';

		?>
<div id="bsuite_machinetags" class="postbox <?php echo postbox_classes('postexcerpt', 'post'); ?>">
<h3><?php _e('bSuite Machine Tags') ?></h3>
<div class="inside">
<ul id="bsuite_machine_tags">
<?php echo implode($tags_to_edit, "\n"); ?>
</ul>

<a href="http://maisonbisson.com/blog/bsuite/machine-tags" title="Machine Tag Documentation">About machine tags</a>
</div>
</div>
		<?php
	}

	function edit_insert_excerpt_form() {
		global $post_ID, $post;
		?>
<div id="postexcerpt" class="postbox <?php echo postbox_classes('postexcerpt', 'post'); ?>">
<h3><?php _e('Excerpt') ?></h3>
<div class="inside"><textarea rows="1" cols="40" name="excerpt" tabindex="6" id="excerpt"><?php echo $post->post_excerpt ?></textarea>
<p><?php _e('Excerpts are optional hand-crafted summaries of your content. You can <a href="http://codex.wordpress.org/Template_Tags/the_excerpt" target="_blank">use them in your template</a>'); ?></p>
</div>
</div>
		<?php
	}

	function edit_insert_category_form() {
		global $post_ID, $post;
		?>
<div id="categorydiv" class="postbox <?php echo postbox_classes('categorydiv', 'post'); ?>">
<h3><?php _e('Categories') ?></h3>
<div class="inside">

<div id="category-adder" class="wp-hidden-children">
	<h4><a id="category-add-toggle" href="#category-add" class="hide-if-no-js" tabindex="3"><?php _e( '+ Add New Category' ); ?></a></h4>
	<p id="category-add" class="wp-hidden-child">
		<input type="text" name="newcat" id="newcat" class="form-required form-input-tip" value="<?php _e( 'New category name' ); ?>" tabindex="3" />
		<?php wp_dropdown_categories( array( 'hide_empty' => 0, 'name' => 'newcat_parent', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => __('Parent category'), 'tab_index' => 3 ) ); ?>
		<input type="button" id="category-add-sumbit" class="add:categorychecklist:category-add button" value="<?php _e( 'Add' ); ?>" tabindex="3" />
		<?php wp_nonce_field( 'add-category', '_ajax_nonce', false ); ?>
		<span id="category-ajax-response"></span>
	</p>
</div>

<ul id="category-tabs">
	<li class="ui-tabs-selected"><a href="#categories-all" tabindex="3"><?php _e( 'All Categories' ); ?></a></li>
	<li class="wp-no-js-hidden"><a href="#categories-pop" tabindex="3"><?php _e( 'Most Used' ); ?></a></li>
</ul>

<div id="categories-pop" class="ui-tabs-panel" style="display: none;">
	<ul id="categorychecklist-pop" class="categorychecklist form-no-clear" >
		<?php $popular_ids = wp_popular_terms_checklist('category'); ?>
	</ul>
</div>

<div id="categories-all" class="ui-tabs-panel">
	<ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
		<?php wp_category_checklist($post_ID) ?>
	</ul>
</div>

</div>
</div>
		<?php
	}
	// end adding tools to edit screens



	function autoksum_doapi( $text ){
		// api: http://api.scriblio.net/docs/summarize

		// The POST URL and parameters
		$request = 'http://api.scriblio.net/v01b/summarize/';
		$postargs = array( 
			'text' => strip_tags( 
				str_replace( array( '<','>' ), array( "\n\n<",">\n\n" ), 
					strip_tags( 
						preg_replace( '/\[(.*?)\]/', '', 
							preg_replace( '!(<(?:h[1-6])[^>]*>[^<]*<(?:\/h[1-6])[^>]*>)!', '', 
								$text )), 
						'<p><ul><ol><li><tr><td><table>' 
					)
				)
			), 
			'wordcount' => 35 , 
			'output' => 'php' 
		);

		// Get the curl session object
		$session = curl_init($request);

		// Set the POST options.
		curl_setopt ($session, CURLOPT_POST, TRUE);
		curl_setopt ($session, CURLOPT_POSTFIELDS, $postargs);
		curl_setopt($session, CURLOPT_HEADER, FALSE);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, TRUE);

		// Do the POST and then close the session
		$response = curl_exec($session);
		curl_close($session);

		// return
		if( $response = unserialize( substr( $response, strpos( $response, 'a:' ))))
			return( $response );
		else
			return( FALSE );
	}

	function autoksum_get_text( $text ){
		$text = $this->autoksum_doapi( $text );
		return( $text['summary'] );
	}

//	function autoksum_excerpt_image(){
//		return( apply_filters( 'bsuite_excerpt', $api_result['summary'] ));
//	}

	function autoksum_backfill(){
		global $wpdb;

		$posts = $wpdb->get_results( 'SELECT ID, post_content
			FROM '. $wpdb->posts .'
			WHERE post_status = "publish"
			AND post_excerpt = ""
			LIMIT 5' );

		if( count( $posts )) {
			$insert = array();
			foreach( $posts as $post ) {
				$api_result = $this->autoksum_doapi( $post->post_content );
				if( $api_result['summary'] ){
					$insert[] = '('. (int) $post->ID .', "'. $wpdb->escape( $api_result['summary'] ) .'")';
					$post_tags[ $post->ID ] = array_merge( $api_result['caps'], $api_result['keywords'] );
				}else{
					$insert[] = '('. (int) $post->ID .', "'. $wpdb->escape( wp_trim_excerpt( get_post_field( 'post_content', $post->ID ))) .'")';
				}
			}
		}else{
			return( FALSE );
		}

		if( count( $insert )) {
			$wpdb->get_results( 'INSERT INTO '. $wpdb->posts .'
				(ID, post_excerpt) 
				VALUES '. implode( ',', $insert ) .'
				ON DUPLICATE KEY UPDATE post_excerpt = VALUES( post_excerpt )');

			foreach( $post_tags as $post_id => $tags )
				if( !get_the_terms( $post_id , 'post_tag' ))
					wp_set_post_tags( $post_id, $tags , FALSE);
		}

// TODO: delete any affected post caches here

		return( count( $posts ));
	}



	// widgets
	function widget_related_posts($args) {
		global $post, $wpdb;

		if(!is_singular()) // can only run on single pages/posts
			return(NULL);

		$id = (int) $post->ID; // needs an ID of that page/post
		if(!$id)
			return(NULL);

		extract($args, EXTR_SKIP);
		$options = get_option('bsuite_related_posts');
		$title = empty($options['title']) ? __('Related Posts', 'bsuite') : $options['title'];
		if ( !$number = (int) $options['number'] )
			$number = 5;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

		if ( $related_posts = array_slice( $this->bsuggestive_getposts( $id ), 0, $number )) {
?>

			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul id="relatedposts"><?php
				if ( $related_posts ) : foreach ($related_posts as $post_id) :
				echo  '<li class="relatedposts"><a href="'. get_permalink($post_id) . '">' . get_the_title($post_id) . '</a></li>';
				endforeach; endif;?></ul>
			<?php echo $after_widget; ?>
<?php
		}
	}

	function widget_related_posts_control() {
		$options = $newoptions = get_option('bsuite_related_posts');
		if ( $_POST['bsuite-related-posts-submit'] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['bsuite-related-posts-title']));
			$newoptions['number'] = (int) $_POST['bsuite-related-posts-number'];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('bsuite_related_posts', $options);
		}
		$title = attribute_escape($options['title']);
		if ( !$number = (int) $options['number'] )
			$number = 5;
	?>
				<p><label for="bsuite-related-posts-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="bsuite-related-posts-title" name="bsuite-related-posts-title" type="text" value="<?php echo $title; ?>" /></label></p>
				<p><label for="bsuite-related-posts-number"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="bsuite-related-posts-number" name="bsuite-related-posts-number" type="text" value="<?php echo $number; ?>" /></label> <?php _e('(at most 15)'); ?></p>
				<input type="hidden" id="bsuite-related-posts-submit" name="bsuite-related-posts-submit" value="1" />
	<?php
	}

	function widget_sharelinks($args) {
		global $post, $wpdb;

		if( is_404() ) // no reason to run if it's a 404
			return(FALSE);

		extract($args, EXTR_SKIP);
		$options = get_option('bsuite_sharelinks');
		$title = empty($options['title']) ? __('Bookmark &amp; Feeds', 'bsuite') : $options['title'];

		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul id="sharelinks">';
		echo '<li><img src="'. $this->path_web .'/img/icon-share-16x16.gif" width="16" height="16" alt="bookmark and share icon" />&nbsp;<a href="#bsuite_share_bookmark" title="bookmark and share links">Bookmark and Share</a></li>';
		echo '<li><img src="'. $this->path_web .'/img/icon-feed-16x16.png" width="16" height="16" alt="RSS and feeds icon" />&nbsp;<a href="#bsuite_share_feed" title="RSS and feed links">RSS Feeds</a></li>';
		echo '<li><img src="'. $this->path_web .'/img/icon-translate-16x16.png" width="16" height="16" alt="RSS and feeds icon" />&nbsp;<a href="#bsuite_share_translate" title="RSS and feed links">Translate</a></li>';
		echo '</ul>';
		echo $after_widget;
	}

	function widget_recently_commented_posts( $args, $widget_args = 1) {
		// this code pretty much directly rips off WordPress' native recent comments widget,
		// the difference here is that I'm displaying recently commented posts, not recent comments.
		global $wpdb, $commented_posts;

		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );

		extract($args, EXTR_SKIP);
		$options = get_option('bsuite_recently_commented_posts');
		$title = empty($options[ $number ]['title']) ? __('Recently Commented Posts', 'bsuite') : $options[ $number ]['title'];
		if ( !$posts = (int) $options[ $number ]['number'] )
			$posts = 5;
		else if ( $posts < 1 )
			$posts = 1;
		else if ( $posts > 15 )
			$posts = 15;

		$options[ $number ]['icon_size'] = 's';
		if( !$options[ $number ]['show_icon'] && !$options[ $number ]['show_title'] )
			$options[ $number ]['show_title'] = $options[ $number ]['show_counts'] = 1;

		if ( !$commented_posts = wp_cache_get( 'recently_commented_posts-'. $number, 'widget' ) ) {
			$commented_posts = $wpdb->get_results("SELECT comment_ID, comment_post_ID, COUNT(comment_post_ID) as comment_count, MAX(comment_date_gmt) AS sort_order FROM $wpdb->comments WHERE comment_approved = '1' GROUP BY comment_post_ID ORDER BY sort_order DESC LIMIT $posts");
			wp_cache_set( 'recently_commented_posts-'. $number, $commented_posts, 'widget' );
		}
	?>

			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul id="recentcomments"><?php
				if ( $commented_posts ) : foreach ( $commented_posts as $comment ) :
				echo  '<li class="recentcomments">'. ( $options[ $number ]['show_icon'] ? '<a href="'. get_permalink( $comment->comment_post_ID ) . '#comment-' . $comment->comment_ID . '" class="bsuite_post_icon_link" title="'. attribute_escape( get_the_title( $comment->comment_post_ID )).'">'. $this->icon_get_h( $comment->comment_post_ID, $options[ $number ]['icon_size'] ) .'</a>' : '' ) . ( $options[ $number ]['show_title'] ? '<a href="'. get_permalink( $comment->comment_post_ID ) . '#comment-' . $comment->comment_ID . '" title="'. attribute_escape( get_the_title( $comment->comment_post_ID )).'">'. get_the_title( $comment->comment_post_ID ) . '</a>' : '' ) . ( $options[ $number ]['show_counts'] ? '&nbsp;('. $comment->comment_count .')' : '' ) .'</li>';
				endforeach; endif;?></ul>
			<?php echo $after_widget; ?>
	<?php
	}

	function widget_recently_commented_posts_delete_cache() {
		if ( !$options = get_option('bsuite_recently_commented_posts') )
			$options = array();
		foreach ( array_keys($options) as $o )
			wp_cache_delete( 'recently_commented_posts-'. $o, 'widget' );
	}

	function widget_recently_commented_posts_control( $widget_args ) {
		global $wp_registered_widgets;
		static $updated = false;

		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );

		$options = get_option('bsuite_recently_commented_posts');
		if ( !is_array($options) )
			$options = array();

		if ( !$updated && !empty($_POST['sidebar']) ) {
			$sidebar = (string) $_POST['sidebar'];

			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar =& $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();

			foreach ( $this_sidebar as $_widget_id ) {
				if ( array( &$this, 'widget_recently_commented_posts' ) == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if ( !in_array( "bsuite-commented-posts-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed.
						unset($options[$widget_number]);
				}
			}

			foreach ( (array) $_POST['bsuite-commented-posts'] as $widget_number => $widget_var ) {
				if ( !isset($widget_var['number']) && isset($options[$widget_number]) ) // user clicked cancel
					continue;

				$options[$widget_number]['title'] = strip_tags(stripslashes($widget_var['title']));
				$options[$widget_number]['number'] = (int) $widget_var['number'];
				$options[$widget_number]['show_title'] = (int) $widget_var['show_title'];
				$options[$widget_number]['show_counts'] = (int) $widget_var['show_counts'];
				$options[$widget_number]['show_icon'] = (int) $widget_var['show_icon'];
				$options[$widget_number]['icon_size'] = $widget_var['icon_size'] ? 's' : 0;
			}

			update_option('bsuite_recently_commented_posts', $options);
			$this->widget_recently_commented_posts_delete_cache();
			$updated = true;
		}

		if ( -1 == $number ) {
			$title = __( 'Recently Commented', 'bsuite' );
			$posts = 5;
			$days = 7;
			$show_icon = '';
			$show_title = 'checked="checked"';
			$show_counts = 'checked="checked"';

			// we reset the widget number via JS
			$number = '%i%';
		} else {
			$title = attribute_escape( $options[$number]['title'] );
			if ( !$posts = (int) $options[$number]['number'] )
				$posts = 5;
			$show_icon = $options[$number]['show_icon'] ? 'checked="checked"' : '';
			$show_title = $options[$number]['show_title'] ? 'checked="checked"' : '';
			$show_counts = $options[$number]['show_counts'] ? 'checked="checked"' : '';
		}
?>
		<p><label for="bsuite-commented-posts-title-<?php echo $number; ?>"><?php _e('Title:'); ?> <input style="width: 250px;" id="bsuite-commented-posts-title-<?php echo $number; ?>" name="bsuite-commented-posts[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" /></label></p>

		<p><label for="bsuite-commented-posts-number-<?php echo $number; ?>"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="bsuite-commented-posts-number-<?php echo $number; ?>" name="bsuite-commented-posts[<?php echo $number; ?>][number]" type="text" value="<?php echo $posts; ?>" /></label> <?php _e('(at most 15)'); ?></p>

		<p><?php _e('Show:'); ?>
			<label for="bsuite-commented-posts-show_icon-<?php echo $number; ?>"><?php _e('icon:'); ?> <input class="checkbox" type="checkbox" value="1" <?php echo $show_icon; ?> id="bsuite-commented-posts-show_icon-<?php echo $number; ?>" name="bsuite-commented-posts[<?php echo $number; ?>][show_icon]" /></label> 
			<label for="bsuite-commented-posts-show_title-<?php echo $number; ?>"><?php _e('title:'); ?> <input class="checkbox" type="checkbox" value="1" <?php echo $show_title; ?> id="bsuite-commented-posts-show_title-<?php echo $number; ?>" name="bsuite-commented-posts[<?php echo $number; ?>][show_title]" /></label> 
			<label for="bsuite-commented-posts-show_counts-<?php echo $number; ?>"><?php _e('counts:'); ?> <input class="checkbox" type="checkbox" value="1" <?php echo $show_counts; ?> id="bsuite-commented-posts-show_counts-<?php echo $number; ?>" name="bsuite-commented-posts[<?php echo $number; ?>][show_counts]" /></label>
		</p>

		<input type="hidden" id="bsuite-commented-posts-submit" name="bsuite-commented-posts[<?php echo $number; ?>][submit]" value="1" />
	<?php
	}

	function widget_recently_commented_posts_register() {
		if ( !$options = get_option('bsuite_recently_commented_posts') )
			$options = array();
		$widget_ops = array('classname' => 'bsuite-recently-commented-posts', 'description' => __('A list of posts and pages with recent comments', 'bsuite'));
		$control_ops = array('width' => 320, 'height' => 90, 'id_base' => 'bsuite-commented-posts');
		$name = 'bSuite<br /> '. __( 'Recently Commented Posts', 'bsuite' );

		$id = false;
		foreach ( array_keys($options) as $o ) {
			// Old widgets can have null values for some reason
			if ( !isset($options[$o]['number'] ))
				continue;

			$id = "bsuite-commented-posts-$o"; // Never never never translate an id
			wp_register_sidebar_widget($id, $name, array(&$this, 'widget_recently_commented_posts'), $widget_ops, array( 'number' => $o ));
			wp_register_widget_control($id, $name, array(&$this, 'widget_recently_commented_posts_control'), $control_ops, array( 'number' => $o ));
		}

		// If there are none, we register the widget's existance with a generic template
		if ( !$id ) {
			wp_register_sidebar_widget( 'bsuite-commented-posts-1', $name, array(&$this, 'widget_recently_commented_posts'), $widget_ops, array( 'number' => -1 ) );
			wp_register_widget_control( 'bsuite-commented-posts-1', $name, array(&$this, 'widget_recently_commented_posts_control'), $control_ops, array( 'number' => -1 ) );
		}else{
//			add_action( 'wp_head', 'wp_widget_recent_comments_style' );
			add_action( 'comment_post', array(&$this, 'widget_recently_commented_posts_delete_cache' ));
			add_action( 'wp_set_comment_status', array(&$this, 'widget_recently_commented_posts_delete_cache' ));
		}
	}

	function widget_popular_posts( $args, $widget_args = 1 ) {
		global $post, $wpdb;

		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );

		extract($args, EXTR_SKIP);
		$options = get_option('bstat_pop_posts');

		$opt = array( 
			'show_icon' => $options[ $number ]['show_icon'],
			'show_title' => $options[ $number ]['show_title'],
			'show_counts' => $options[ $number ]['show_counts'],
		);
		$title = empty($options[ $number ]['title']) ? __('Popular Posts', 'bsuite') : $options[ $number ]['title'];
		if ( !$opt['count'] = (int) $options[ $number ]['number'] )
			$opt['count'] = 5;
		else if ( $opt['count'] < 1 )
			$opt['count'] = 1;
		else if ( $opt['count'] > 15 )
			$opt['count'] = 15;

		if ( !$opt['days'] = (int) $options[ $number ]['days'] )
			$opt['days'] = 7;
		else if ( $opt['days'] < 1 )
			$opt['days'] = 1;
		else if ( $opt['days'] > 30 )
			$opt['days'] = 30;

		$opt['icon_size'] = 's';
		if( !$opt['show_icon'] && !$opt['show_title'] )
			$opt['show_title'] = $opt['show_counts'] = 1;

		if ( !$pop_posts = wp_cache_get( 'bstat-pop-posts-'. $number , 'widget' ) ) {
			$pop_posts = $this->pop_posts( $opt );
			wp_cache_set( 'bstat-pop-posts-'. $number , $pop_posts, 'widget', 3600 );
		}

		if ( !empty($pop_posts) ) {
?>
			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul><?php
				echo $pop_posts;
				?></ul>
			<?php echo $after_widget; ?>
<?php
		}
	}

	function widget_popular_posts_delete_cache() {
		if ( !$options = get_option('bstat_pop_posts') )
			$options = array();
		foreach ( array_keys($options) as $o )
			wp_cache_delete( 'bstat-pop-posts-'. $o, 'widget' );
	}


	function widget_popular_posts_control($widget_args) {
		global $wp_registered_widgets;
		static $updated = false;

		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );

		$options = get_option('bstat_pop_posts');
		if ( !is_array($options) )
			$options = array();

		if ( !$updated && !empty($_POST['sidebar']) ) {
			$sidebar = (string) $_POST['sidebar'];

			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar =& $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();

			foreach ( $this_sidebar as $_widget_id ) {
				if ( array(&$this, 'widget_popular_posts') == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if ( !in_array( "bstat-pop-posts-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed.
						unset($options[$widget_number]);
				}
			}

			foreach ( (array) $_POST['bstat-pop-posts'] as $widget_number => $widget_var ) {
				if ( !isset($widget_var['number']) && isset($options[$widget_number]) ) // user clicked cancel
					continue;

				$options[$widget_number]['title'] = strip_tags(stripslashes($widget_var['title']));
				$options[$widget_number]['number'] = (int) $widget_var['number'];
				$options[$widget_number]['days'] = (int) $widget_var['days'];
				$options[$widget_number]['show_title'] = (int) $widget_var['show_title'];
				$options[$widget_number]['show_counts'] = (int) $widget_var['show_counts'];
				$options[$widget_number]['show_icon'] = (int) $widget_var['show_icon'];
				$options[$widget_number]['icon_size'] = $widget_var['icon_size'] ? 's' : 0;
			}

			update_option('bstat_pop_posts', $options);
			$this->widget_popular_posts_delete_cache();
			$updated = true;
		}

		if ( -1 == $number ) {
			$title = __( 'Popular Posts', 'bsuite' );
			$posts = 5;
			$days = 7;
			$show_icon = '';
			$show_title = 'checked="checked"';
			$show_counts = 'checked="checked"';

			// we reset the widget number via JS
			$number = '%i%';
		} else {
			$title = attribute_escape( $options[$number]['title'] );
			if ( !$posts = (int) $options[$number]['number'] )
				$posts = 5;
			if ( !$days = (int) $options[$number]['days'] )
				$days = 7;
			$show_icon = $options[$number]['show_icon'] ? 'checked="checked"' : '';
			$show_title = $options[$number]['show_title'] ? 'checked="checked"' : '';
			$show_counts = $options[$number]['show_counts'] ? 'checked="checked"' : '';
		}

?>
		<p><label for="bstat-pop-posts-title-<?php echo $number; ?>"><?php _e('Title:'); ?> <input style="width: 250px;" id="bstat-pop-posts-title-<?php echo $number; ?>" name="bstat-pop-posts[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" /></label></p>

		<p><label for="bstat-pop-posts-number-<?php echo $number; ?>"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="bstat-pop-posts-number-<?php echo $number; ?>" name="bstat-pop-posts[<?php echo $number; ?>][number]" type="text" value="<?php echo $posts; ?>" /></label> <?php _e('(at most 15)'); ?></p>

		<p><label for="bstat-pop-posts-days-<?php echo $number; ?>"><?php _e('In past x days (1 = today only):'); ?> <input style="width: 25px; text-align: center;" id="bstat-pop-posts-days-<?php echo $number; ?>" name="bstat-pop-posts[<?php echo $number; ?>][days]" type="text" value="<?php echo $days; ?>" /></label> <?php _e('(at most 30)'); ?></p>

		<p><?php _e('Show:'); ?>
			<label for="bstat-pop-posts-show_icon-<?php echo $number; ?>"><?php _e('icon:'); ?> <input class="checkbox" type="checkbox" value="1" <?php echo $show_icon; ?> id="bstat-pop-posts-show_icon-<?php echo $number; ?>" name="bstat-pop-posts[<?php echo $number; ?>][show_icon]" /></label> 
			<label for="bstat-pop-posts-show_title-<?php echo $number; ?>"><?php _e('title:'); ?> <input class="checkbox" type="checkbox" value="1" <?php echo $show_title; ?> id="bstat-pop-posts-show_title-<?php echo $number; ?>" name="bstat-pop-posts[<?php echo $number; ?>][show_title]" /></label> 
			<label for="bstat-pop-posts-show_counts-<?php echo $number; ?>"><?php _e('counts:'); ?> <input class="checkbox" type="checkbox" value="1" <?php echo $show_counts; ?> id="bstat-pop-posts-show_counts-<?php echo $number; ?>" name="bstat-pop-posts[<?php echo $number; ?>][show_counts]" /></label>
		</p>

		<input type="hidden" id="bstat-pop-posts-submit" name="bstat-pop-posts[<?php echo $number; ?>][submit]" value="1" />
<?php
	}

	function widget_popular_posts_register() {
		if ( !$options = get_option('bstat_pop_posts') )
			$options = array();
		$widget_ops = array('classname' => 'bstat-pop-posts', 'description' => __('Your site&#8217;s most popular posts and pages', 'bsuite'));
		$control_ops = array('width' => 320, 'height' => 90, 'id_base' => 'bstat-pop-posts');
		$name = 'bSuite<br /> '. __( 'Popular Posts', 'bsuite' );

		$id = false;
		foreach ( array_keys($options) as $o ) {
			// Old widgets can have null values for some reason
			if ( !isset($options[$o]['title']))
				continue;
			$id = "bstat-pop-posts-$o"; // Never never never translate an id
			wp_register_sidebar_widget($id, $name, array(&$this, 'widget_popular_posts'), $widget_ops, array( 'number' => $o ));
			wp_register_widget_control($id, $name, array(&$this, 'widget_popular_posts_control'), $control_ops, array( 'number' => $o ));
		}

		// If there are none, we register the widget's existance with a generic template
		if ( !$id ) {
			wp_register_sidebar_widget( 'bstat-pop-posts-1', $name, array(&$this, 'widget_popular_posts'), $widget_ops, array( 'number' => -1 ) );
			wp_register_widget_control( 'bstat-pop-posts-1', $name, array(&$this, 'widget_popular_posts_control'), $control_ops, array( 'number' => -1 ) );
		}
	}

	function widget_popular_refs($args) {
		global $post, $wpdb;

		extract($args, EXTR_SKIP);
		$options = get_option('bstat_pop_refs');
		$title = empty($options['title']) ? __('Recent Search Terms') : $options['title'];
		if ( !$number = (int) $options['number'] )
			$number = 5;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

		if ( !$days = (int) $options['days'] )
			$days = 7;
		else if ( $days < 1 )
			$days = 1;
		else if ( $days > 30 )
			$days = 30;

		if ( !$pop_refs = wp_cache_get( 'bstat_pop_refs', 'widget' ) ) {
			$pop_refs = $this->pop_refs("count=$number&days=$days");
			wp_cache_add( 'bstat_pop_refs', $pop_refs, 'widget', 3600 );
		}

		if ( !empty($pop_refs) ) {
?>
			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul id="bstat-pop-refs"><?php
				echo $pop_refs;
				?></ul>
			<?php echo $after_widget; ?>
<?php
		}
	}

	function widget_popular_refs_delete_cache() {
		wp_cache_delete( 'bstat_pop_refs', 'widget' );
	}

	function widget_popular_refs_control() {
		$options = $newoptions = get_option('bstat_pop_refs');
		if ( $_POST['bstat-pop-refs-submit'] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['bstat-pop-refs-title']));
			$newoptions['number'] = (int) $_POST['bstat-pop-refs-number'];
			$newoptions['days'] = (int) $_POST['bstat-pop-refs-days'];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('bstat_pop_refs', $options);
			$this->widget_popular_refs_delete_cache();
		}
		$title = attribute_escape($options['title']);
		if ( !$number = (int) $options['number'] )
			$number = 5;
		if ( !$days = (int) $options['days'] )
			$days = 7;
	?>
				<p><label for="bstat-pop-refs-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="bstat-pop-refs-title" name="bstat-pop-refs-title" type="text" value="<?php echo $title; ?>" /></label></p>
				<p><label for="bstat-pop-refs-number"><?php _e('Number of refs to show:'); ?> <input style="width: 25px; text-align: center;" id="bstat-pop-refs-number" name="bstat-pop-refs-number" type="text" value="<?php echo $number; ?>" /></label> <?php _e('(at most 15)'); ?></p>
				<input type="hidden" id="bstat-pop-refs-submit" name="bstat-pop-refs-submit" value="1" />
	<?php
	}

	function widgets_register(){
		$this->widget_recently_commented_posts_register();
		$this->widget_popular_posts_register();

		wp_register_sidebar_widget('bsuite-related-posts', __('bSuite Related Posts', 'bsuite'), array(&$this, 'widget_related_posts'), 'bsuite_related_posts');
		wp_register_widget_control('bsuite-related-posts', __('bSuite Related Posts', 'bsuite'), array(&$this, 'widget_related_posts_control'), 'width=320&height=90');

		wp_register_sidebar_widget('bsuite-sharelinks', __('bSuite Share Links', 'bsuite'), array(&$this, 'widget_sharelinks'), 'bsuite_sharelinks');

		wp_register_sidebar_widget('bstat-pop-refs', __('bStat Refs'), array(&$this, 'widget_popular_refs'), 'bstat-pop-refs');
		wp_register_widget_control('bstat-pop-refs', __('bStat Refs'), array(&$this, 'widget_popular_refs_control'), 'width=320&height=90');
	}
	// end widgets



	// administrivia
	function activate() {

		update_option('bsuite_doing_migration', time() + 7200 );

		$this->createtables();
		$this->cron_register();

		// set some defaults for the plugin
		if(!get_option('bsuite_insert_related'))
			update_option('bsuite_insert_related', TRUE);

		if(!get_option('bsuite_insert_sharelinks'))
			update_option('bsuite_insert_sharelinks', FALSE);

		if(!get_option('bsuite_searchsmart'))
			update_option('bsuite_searchsmart', TRUE);

		if(!get_option('bsuite_swhl'))
			update_option('bsuite_swhl', TRUE);

		if(!get_option('bsuite_insert_css'))
			update_option('bsuite_insert_css', TRUE);

		if(!get_option('bsuite_migration_interval'))
			update_option('bsuite_migration_interval', 90);

		if(!get_option('bsuite_migration_count'))
			update_option('bsuite_migration_count', 100);

		if(!get_option('bsuite_load_max'))
			update_option('bsuite_load_max', 4);


		// allow authors to edit their own pages by default
		if(!get_option('bsuite_who_can_edit'))
			update_option('bsuite_who_can_edit', 'authors');

		if(!get_option('bsuite_managefocus_month'))
			update_option('bsuite_managefocus_month', FALSE);
		if(!get_option('bsuite_managefocus_author'))
			update_option('bsuite_managefocus_author', FALSE);


		// set some defaults for the widgets
		if(!get_option('bsuite_related_posts'))
			update_option('bsuite_related_posts', array('title' => 'Related Posts', 'number' => 7));

		if(!get_option('bstat_pop_refs'))
			update_option('bstat_pop_refs', array('title' => 'Popular Searches', 'number' => 5));
	}

	function createtables() {
		global $wpdb;

		$charset_collate = '';
		if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}

		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

		dbDelta("
			CREATE TABLE $this->search_table (
				post_id bigint(20) NOT NULL,
				content text,
				title text,
				PRIMARY KEY  (post_id),
				FULLTEXT KEY search (content, title)
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_incoming (
				in_time timestamp NOT NULL default CURRENT_TIMESTAMP,
				in_type tinyint(4) NOT NULL default '0',
				in_session varchar(32) default '',
				in_to text NOT NULL,
				in_from text,
				in_extra text
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_terms (
				term_id bigint(20) NOT NULL auto_increment,
				name varchar(255) NOT NULL default '',
				PRIMARY KEY  (term_id),
				UNIQUE KEY name_uniq (name),
				KEY name (name(8))
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_targets (
				object_id bigint(20) unsigned NOT NULL default '0',
				object_type smallint(6) NOT NULL,
				hit_count smallint(6) unsigned NOT NULL default '0',
				hit_date date NOT NULL default '0000-00-00',
				PRIMARY KEY  (object_id,object_type,hit_date)
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_searchphrases (
				object_id bigint(20) unsigned NOT NULL default '0',
				object_type smallint(6) NOT NULL,
				term_id bigint(20) unsigned NOT NULL default '0',
				hit_count smallint(6) unsigned NOT NULL default '0',
				PRIMARY KEY  (object_id,object_type,term_id),
				KEY term_id (term_id)
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_sessions (
				sess_id bigint(20) NOT NULL auto_increment,
				sess_cookie varchar(32) NOT NULL default '',
				sess_date datetime default NULL,
				sess_ip varchar(16) NOT NULL default '',
				sess_bl varchar(8) default '',
				sess_bb varchar(24) default '',
				sess_br varchar(24) default '',
				sess_ba varchar(200) default '',
				PRIMARY KEY  (sess_id),
				UNIQUE KEY sess_cookie_uniq (sess_cookie),
				KEY sess_cookie (sess_cookie(2))
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_shistory (
				sess_id bigint(20) NOT NULL auto_increment,
				object_id bigint(20) NOT NULL,
				object_type smallint(6) NOT NULL,
				KEY sess_id (sess_id),
				KEY object_id (object_id,object_type)
			) ENGINE=MyISAM $charset_collate
			");

		dbDelta("
			CREATE TABLE $this->hits_pop (
				post_id bigint(20) NOT NULL,
				date_start date NOT NULL,
				hits_total bigint(20) NOT NULL,
				hits_recent int(10) NOT NULL
			) ENGINE=MyISAM $charset_collate
			");
	}

	function mu_options( $options ) {
		$added = array( 'bsuite' => array( 'bsuite_insert_related', 'bsuite_insert_sharelinks', 'bsuite_searchsmart', 'bsuite_swhl', 'bsuite_who_can_edit' ));

		$options = add_option_whitelist( $added, $options );

		return( $options );
	}

	function kses_allowedposttags() {
		global $allowedposttags;
		$allowedposttags['h1']['id'] = array();
		$allowedposttags['h1']['class'] = array();
		$allowedposttags['h2']['id'] = array();
		$allowedposttags['h2']['class'] = array();
		$allowedposttags['h3']['id'] = array();
		$allowedposttags['h3']['class'] = array();
		$allowedposttags['h4']['id'] = array();
		$allowedposttags['h4']['class'] = array();
		$allowedposttags['h5']['id'] = array();
		$allowedposttags['h5']['class'] = array();
		$allowedposttags['h6']['id'] = array();
		$allowedposttags['h6']['class'] = array();
		return(TRUE);
	}

	function makeXMLTree($data){
		// create parser
		$parser = xml_parser_create();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
		xml_parse_into_struct($parser,$data,$values,$tags);
		xml_parser_free($parser);

		// we store our path here
		$hash_stack = array();

		// this is our target
		$ret = array();
		foreach ($values as $key => $val) {

			switch ($val['type']) {
				case 'open':
					array_push($hash_stack, $val['tag']);
					if (isset($val['attributes']))
						$ret = $this->composeArray($ret, $hash_stack, $val['attributes']);
					else
						$ret = $this->composeArray($ret, $hash_stack);
				break;

				case 'close':
					array_pop($hash_stack);
				break;

				case 'cdata':
					array_push($hash_stack, 'cdata');
					$ret = $this->composeArray($ret, $hash_stack, $val['value']);
					array_pop($hash_stack);
				break;

				case 'complete':
					array_push($hash_stack, $val['tag']);
					$ret = $this->composeArray($ret, $hash_stack, $val['value']);
					array_pop($hash_stack);

					// handle attributes
					if (isset($val['attributes'])){
						foreach($val['attributes'] as $a_k=>$a_v){
							$hash_stack[] = $val['tag'].'_attribute_'.$a_k;
							$ret = $this->composeArray($ret, $hash_stack, $a_v);
							array_pop($hash_stack);
						}
					}
				break;
			}
		}

		return($ret);
	} // end makeXMLTree

	function &composeArray($array, $elements, $value=array()){
		// function used exclusively by makeXMLTree to help turn XML into an array

		// get current element
		$element = array_shift($elements);

		// does the current element refer to a list
		if(sizeof($elements) > 0){
			$array[$element][sizeof($array[$element])-1] = &$this->composeArray($array[$element][sizeof($array[$element])-1], $elements, $value);
		}else{ // if (is_array($value))
			$array[$element][sizeof($array[$element])] = $value;
		}

		return($array);
	} // end composeArray



	function command_rebuild_searchsmart() {
		// update search table with content from all posts
		global $wpdb; 

		set_time_limit(0);
		ignore_user_abort(TRUE);
		$interval = 25;


		if( !isset( $_REQUEST[ 'n' ] ) ) {
			$n = 0;
			$this->createtables();
			$wpdb->get_results( 'TRUNCATE TABLE '. $this->search_table );
		} else {
			$n = (int) $_REQUEST[ 'n' ] ;
		}
		if( $count = $this->searchsmart_upindex() ) {
			echo '<div class="updated"><p><strong>' . __('Rebuilding bSuite search index.', 'bsuite') . '</strong> Already did '. ( $n + $count ) .', be patient already!</p></div><div class="narrow">';

			?>
			<p><?php _e("If your browser doesn't start loading the next page automatically click this link:"); ?> <a href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php&Options=<?php echo urlencode( __( 'Rebuild bSuite search index', 'bsuite' )) ?>&n=<?php echo ($n + $interval) ?>"><?php _e("Next Posts"); ?></a> </p></div>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php&Options=<?php echo urlencode( __( 'Rebuild bSuite search index', 'bsuite' )) ?>&n=<?php echo ($n + $interval) ?>";
			}
			setTimeout( "nextpage()", 250 );

			//-->
			</script>
			<?php
		} else {
			echo '<div class="updated"><p><strong>'. __('bSuite metdata index rebuilt.', 'bsuite') .'</strong></p></div>';
			?>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php";
			}
			setTimeout( "nextpage()", 3000 );

			//-->
			</script>
			<?php
		}
	}

	function command_rebuild_autoksum() {
		// update search table with content from all posts
		global $wpdb; 

		set_time_limit(0);
		ignore_user_abort(TRUE);
		$interval = 5;


		if( !isset( $_REQUEST[ 'n' ] ) ) {
			$n = 0;
		} else {
			$n = (int) $_REQUEST[ 'n' ] ;
		}
		if( $count = $this->autoksum_backfill() ) {
			echo '<div class="updated"><p><strong>' . __('Generating excerpts.', 'bsuite') . '</strong> Already did '. ( $n + $count ) .', be patient already!</p></div><div class="narrow">';

			?>
			<p><?php _e("If your browser doesn't start loading the next page automatically click this link:"); ?> <a href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php&Options=<?php echo urlencode( __( 'Add post_excerpt to all posts', 'bsuite' )) ?>&n=<?php echo ($n + $interval) ?>"><?php _e("Next Posts"); ?></a> </p></div>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php&Options=<?php echo urlencode( __( 'Add post_excerpt to all posts', 'bsuite' )) ?>&n=<?php echo ($n + $interval) ?>";
			}
			setTimeout( "nextpage()", 250 );

			//-->
			</script>
			<?php
		} else {
			echo '<div class="updated"><p><strong>'. __('All posts now have excerpts! All done.', 'bsuite') .'</strong></p></div>';
			?>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="?page=<?php echo plugin_basename(dirname(__FILE__)); ?>/ui_options.php";
			}
			setTimeout( "nextpage()", 3000 );

			//-->
			</script>
			<?php
		}
	}
}

// now instantiate this object
$bsuite = & new bSuite;

// get the social bookmarking and sharing stuff
require_once( dirname( __FILE__) .'/inc_social.php' );

// get the externally defined widgets
require_once( dirname( __FILE__) .'/widgets/cms.php' );



function is_quickview(){
	global $bsuite;
	return( $bsuite->is_quickview );
}

function the_icon( $size = 's', $ow = 0, $oh = 0 ){
	global $bsuite, $id;

	$size = preg_replace( '/[^a-z|0-9|_]/i', '', $size );
	$ow = absint( $ow );
	$oh = absint( $oh );

	if( $id )
		echo $bsuite->icon_get_h( $id, $size, $ow, $oh );
}

function the_related(){
	global $bsuite;
	echo $bsuite->bsuggestive_the_related();
}

function paginated_links(){
	GLOBAL $wp_query;


	$page = 1;
	if( (int) $wp_query->query_vars['paged'] )
		$page = (int) $wp_query->query_vars['paged'];
	$total = (int) $wp_query->max_num_pages;

	$page_links = paginate_links( array(
		'base' => add_query_arg( 'paged', '%#%' ),
		'format' => '',
		'total' => $total,
		'current' => $page
	));

	if ( $page_links )
		echo "<p class='pagenav'>$page_links</p>";
}

function bsuite_feedlink() {
	global $bsuite;
	return( $bsuite->feedlink() );
}

function bsuite_link2me() {
	global $bsuite;
	echo $bsuite->link2me();
}

function bstat_hits($template = '%%hits%% hits, about %%avg%% daily', $post_id = NULL, $todayonly = 0, $return = NULL) {
	global $bsuite;
	if(!empty($return))
		return($bsuite->post_hits(array('post_id' => $post_id,'days' => $todayonly, 'template' => $template )));
	echo $bsuite->post_hits(array('post_id' => $post_id,'days' => $todayonly, 'template' => $template ));
}


// deprecated functions
function bstat_pulse($post_id = 0, $maxwidth = 400, $disptext = 1, $dispcredit = 1, $accurate = 4) {
	// this one isn't so much deprecated as, well, 
	// the code sucks and I haven't re-written it yet

	global $wpdb, $bstat;

	$post_id = (int) $post_id;

	$for_post_id = $post_id > 1 ? 'AND post_id = '. $post_id : '';

	// here's the query, but let's try to get the data from cache first
	$request = "SELECT
		SUM(hit_count) AS hits, 
		hit_date
		FROM $bstat->hits_table
		WHERE 1=1
		$for_post_id
		GROUP BY hit_date
		";

	if ( !$result = wp_cache_get( $post_id, 'bstat_post_pulse' ) ) {
		$result = $wpdb->get_results($request, ARRAY_A);
		wp_cache_add( $post_id, $result, 'bstat_post_pulse', 3600 );
	}

	if(empty($result))
		return(NULL);

	$tot = count($result);

	if(count($result)>0){
		$point = null;
		$point[] = 0;
		foreach($result as $row){
			$point[] = $row['hits'];
		}
		$sum = array_sum($point);
		$max = max($point);
		$avg = round($sum / $tot);

		if($accurate == 4){
			$graphaccurate = get_option('bstat_graphaccurate');
		}else{
			$graphaccurate = $accurate;
		}

		$minwidth = ($maxwidth / 8.1);
		if($graphaccurate) $minwidth = ($maxwidth / 4.1);

		while(count($point) <= $minwidth){
			$newpoint = null;
			for ($i = 0; $i < count($point); $i++) {
				if($i > 0){
					if(!$graphaccurate) $newpoint[] = ((($point[$i-1] * 2) + $point[$i]) / 3);
					$newpoint[] = (($point[$i-1] + $point[$i]) / 2);
					if(!$graphaccurate) $newpoint[] = (($point[$i-1] + ($point[$i-1] * 2)) / 3);
				}
				$newpoint[] = $point[$i];
			}
			$point = $newpoint;
		}


		$tot = count($point);
		$width = round($maxwidth / $tot);
		if($width > 3)
			$width = 4;

		if($width < 1)
			$width = 1;

		if(($width  * $tot) > $maxwidth)
			$skipstart = (($width  * $tot) - $maxwidth) / $width;

		$i = 1;
		$hit_chart = "";
		foreach($point as $row){
			if((!$skipstart) || ($i > $skipstart)){
				$hit_chart .= "<img src='" . get_settings('siteurl') .'/'. PLUGINDIR .'/'. plugin_basename(dirname(__FILE__))  . "/img/spacer.gif' width='$width' height='" . round((($row) / $max) * 100) . "' alt='graph element.' />";
				}
			$i++;
		}

		$pre = "<div id=\"bstat_pulse\">";
		$post = "</div>";
		$disptext = ($disptext == 1) ? (number_format($sum) .' total reads, averaging '. number_format($avg) .' daily') : ("");
		$dispcredit = ($dispcredit == 1) ? ("<small><a href='http://maisonbisson.com/blog/search/bsuite' title='a pretty good WordPress plugin'>stats powered by bSuite bStat</a></small>") : ("");
		$disptext = (($disptext) || ($dispcredit)) ? ("\n<p>$disptext\n<br />$dispcredit</p>") : ("");

		echo($pre . $hit_chart . "\n" . $disptext . $post);
	}
}



// php4 compatibility, argh
if(!function_exists('str_ireplace')){
	function str_ireplace($a, $b, $c){
		return str_replace($a, $b, $c);
	}
}