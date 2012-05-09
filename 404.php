<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header();
?><div id="header3">
    <a href="http://opencounter.org">
    <canvas id="clockcanvas" width="50" height="50"></canvas>
</a>
    
    <script type="text/javascript">
jQuery(document).ready(function(){
    jQuery(".wipeoutimage").mouseover( function(){
        jQuery(".wipeoutimage").animate({
            marginLeft: "3000px",
            marginTop: "150px"
        }, 2000, function() {  } );
    } );
} );
</script>
    
        
    
</div>

	<div id="content" class="narrowcolumn">
           
<div class="wipeoutimage"><img src="http://opencounter.org/wp-content/themes/opencounter/images/surfer404.png" alt="404"/></div>
		<p class="b"><?php _e('Wipe Out 404', 'kubrick'); ?></p>
<p class="s">Maybe try searching for it, dude.<?php get_search_form(); ?></p> 
	</div>


<?php get_sidebar(); ?>

<?php get_footer(); ?>
