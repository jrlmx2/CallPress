<?php
/**
 * The loop that displays tickets.
 *
 * This is the loop that grabs all tickets and displays them accordingly. In this loop filters and ticket bubbling are executed if enabled.
 * Toggleable options are located in the admin menu for administrators of the system.
 *
 * @package WordPress
 * @subpackage CallPress
 * @since CallPress 0.5
 */
?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if ( $wp_query->max_num_pages > 1 ) : ?>
	<div id="nav-above" class="navigation">
		<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older tickets', 'callpress' ) ); ?></div>
		<div class="nav-next"><?php previous_posts_link( __( 'Newer tickets <span class="meta-nav">&rarr;</span>', 'callpress' ) ); ?></div>
	</div><!-- #nav-above -->
<?php endif; ?>

<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( ! have_posts() ) : ?>
	<div id="post-0" class="post error404 not-found">
		<h1 class="entry-title"><?php _e( "Ticket Missing.", 'twentyten' ); ?></h1>
		<div class="entry-content">
			<p><?php _e( 'The ticket does not exist. If you feel this is in error please report this problem to an administrator.', 'twentyten' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
	</div><!-- #post-0 -->
<?php endif; ?>

<?php
	/* Start the Loop.
	 *
	 * This particular loop is where tickets and all views associated with tickets get processed. There are three main views that are processed through this loop.
	 * The ticket list, the ticket view, the ticket add/edit process. 
	 *
	 */ ?>
<?php while ( have_posts() ) : the_post(); ?>
	<?php global $post; ?>
	<?php die($post); ?>

<?php endwhile; // End the loop. Whew. ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if (  $wp_query->max_num_pages > 1 ) : ?>
				<div id="nav-below" class="navigation">
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
				</div><!-- #nav-below -->
<?php endif; ?>
