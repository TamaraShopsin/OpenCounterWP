<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header(); ?> <div id="header3">
    <a href="http://opencounter.org">
    <canvas id="clockcanvas" width="50" height="50"></canvas>
</a>
    
        
    
</div>

	<div id="content" class="narrowcolumn" role="main">

	<?php if (have_posts()) : ?>

		<h2 class="pagetitle"><?php _e('Search Results', 'kubrick'); ?></h2>

		


		<?php while (have_posts()) : the_post(); ?>

			<div <?php post_class(); ?>>
				<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__('Permanent Link to %s', 'kubrick'), the_title_attribute('echo=0')); ?>"><?php the_title(); ?></a></h3>
				 </div>

		<?php endwhile; ?>

		

	<?php else : ?>

		<h2 class="center"><?php _e('No posts found. Try a different search?', 'kubrick'); ?></h2>
		<?php get_search_form(); ?>

	<?php endif; ?>

	</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
