<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php echo $ninespot->timer( 'init' , 'page' ) ."\n"; ?>
<meta http-equiv="content-type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php wp_title(' | ', true, 'right'); ?><?php bloginfo('name'); ?><?php if( $nines_title ) { echo ' '.$nines_title; }?></title>
<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats -->
<meta name="viewport" content="width=980">
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('template_directory'); ?>/includes/struct960.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); ?>" />
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); do_action('get_header'); // for plugin compatibility ?>
<?php echo "\n". $ninespot->timer( 'html head' , 'widget' ) . $ninespot->timer( 'html head' , 'area' ) ."\n"; // twice so that we can reset both the widget and area timers ?>
</head>
<body <?php $ninespot->body_class(); ?>>
<?php 
	if ( !function_exists('dynamic_sidebar') )
	{
		echo 'This theme requires widget capabilities.';
	}//end if
	else
	{
		$ninespot->renderSpot('head');
		
		$ninespot->renderSpotIfEnabled('nav');

		echo "\n".'<div id="nav-sep" class="container_16"><div class="inner grid_16"><div class="extra"></div></div></div>'."\n";

		$ninespot->renderSpotIfEnabled('avant-body');

		$ninespot->renderSpot('body');

		echo "\n".'<div id="body-apres-body-sep" class="container_16"><div class="inner grid_16">&nbsp;</div></div>'."\n";

		$ninespot->renderSpotIfEnabled('apres-body');
		$ninespot->renderSpotIfEnabled('foot');

		$ninespot->renderSpotIfEnabled('apres-foot');

		echo "\n".'<div id="copy" class="container_16"><div class="inner grid_16"></div></div>'."\n";
		echo '<div class="clear">&nbsp;</div>'."\n";
	}//end else
	do_action('get_footer'); wp_footer(); // for plugin compatibility

	$ninespot->credit();
	echo $ninespot->timer( 'page rendering' , 'page' ) ."\n";
?>
</body>
</html>
