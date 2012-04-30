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
    
        
    
</div>

	<div id="content" class="narrowcolumn">

		<p class="b"><?php _e('Wipe Out 404', 'kubrick'); ?></p>
<p class="s">Maybe try searching for it, dude:<?php get_search_form(); ?></p> 
	</div>



<?php get_footer(); ?>
