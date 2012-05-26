<?php /* Template Name: home
*/ ?>
<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header(); ?>
<script type="text/javascript" src="http://opencounter.org/wp-content/themes/opencounter/scripts/thinbox2.js"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
    if(Cookies.get("betachip") != "eaten"){
	ThinBox.open("betawindow.html",{'width':'500px','height':'350px'});
        Cookies.set("betachip", "eaten", { expires: 21 * 24 * 60 * 60 });
    }
});
</script>

	<div id="content" class="narrowcolumn" role="main">
            <div id="header4">
</div>

	<?php if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>

			<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
				
				

				<div class="entry">
					<?php the_content(__('Read the rest of this entry &raquo;', 'kubrick')); ?>
				</div>

				
			</div>

		<?php endwhile; ?>
<div class="navigation">


<div class="alignright">
<<<<<<< HEAD
        <?php next_link(); ?>
=======
         <?php next_link(); ?>
>>>>>>> fixed link
    </div>



</div>

	<?php else : ?>

		<h2 class="center"><?php _e('Not Found', 'kubrick'); ?></h2>
		<p class="center"><?php _e('Sorry, but you are looking for something that isn&#8217;t here.', 'kubrick'); ?></p>
		<?php get_search_form(); ?>

	<?php endif; ?>

	</div>


<?php get_footer(); ?>
