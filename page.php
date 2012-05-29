<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header(); ?>
<div id="header3">
    <a href="<?php echo get_settings('home'); ?>">
    <canvas id="clockcanvas" width="50" height="50">
         <img src="http://opencounter.org/wp-content/uploads/2012/04/clockbig2.png"/>
    </canvas>
</a>
    
        
    
</div>
	<div id="content" class="narrowcolumn" role="main">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
		
			<div class="entry">
				<?php the_content('<p class="serif">' . __('Read the rest of this page &raquo;', 'kubrick') . '</p>'); ?>

				<?php wp_link_pages(array('before' => '<p><strong>' . __('Pages:', 'kubrick') . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
           	         
		         </div>
                 
		<?php endwhile; endif; ?>
	<?php edit_post_link(__('Edit this entry.', 'kubrick'), '<p>', '</p>'); ?>
	
	</div>

            <div class="navigation">

    <div class="alignleft">
        <?php previous_link(); ?>
    </div>
    <div class="alignright">
        <?php next_link(); ?>
    </div>

            </div>
                   	         
	
            </div>
            
            
</div>
            
            
<?php get_sidebar(); ?>
            
<?php get_footer(); ?>
