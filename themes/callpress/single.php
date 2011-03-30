<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

<?php if( $post->thecontent == "" && $post->post_title == "zxcv" ) : ?>
<!-- add form -->

<h1 id="ticket_title" class="grid_6 alpha"> Add Ticket Form </h1>
<form id="add_ticket" class="grid_16">
	<ul class="grid_10 alpha">
		<li class="grid_8 alpha">
			<label class="grid_2 alpha">title:</label>
			<input class="grid_4" name="title" />
		</li>
		<li class="grid_8 alpha">
			<label class="grid_2 alpha">content:</label>
			<textarea class="grid_5 omega" col=4 name="content" ></textarea>
		</li>
		<li class="grid_8 alpha">
			<label class="grid_2 alpha">tags:</label>
			<input class="grid_4" name="tags" />
		</li>
		<li class="grid_8 alpha">
			<input class="grid_2" type="submit" value="Add Ticket" />
		</li>
	</ul>
</form>
	
<?php else : ?>
<!-- view single ticket -->
	<h2><?php echo $post->post_title; ?></h2>
	<p><?php echo $post->post_content; ?></p>
	<?php comments_template(); ?>

<?php endif; ?>
			

<?php endwhile; ?>
<?php get_footer(); ?>
