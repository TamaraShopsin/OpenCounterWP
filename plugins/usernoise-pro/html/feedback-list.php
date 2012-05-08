<div id="feedback-list-block">
	<a href="#" id="button-back" style="display: none"><span><?php _e('Back', 'usernoise-pro')?></span></a>
	<h3><?php echo un_get_option(UN_FEEDBACK_FORM_SHOW_TYPE) ? __('Popular Ideas', 'usernoise-pro') : __('Popular Feedback') ?></h3>
	<?php global $post; ?>
	<?php $query = array('post_type' => FEEDBACK, 'posts_per_page' => 5, 
		'post_status' => 'publish', 
		'feedback_type_slug' => un_get_option(UN_FEEDBACK_FORM_SHOW_TYPE) ? 'idea' : null,
		'orderby' => 'likes','order' => 'DESC') ?>
	<?php $feedback = new WP_Query($query) ?>
		<div id="feedback-list" class="scrollable" data-pages="<?php echo $feedback->max_num_pages ?>">
			<div class="viewport">
				<div class="overview">
					<ul>
						<li id="load-more-feedback" style="display: none;">
							<img id="feedback-list-loader" src="<?php echo usernoisepro_url('/images/ajax-loader-on-gray.gif')?>" style="display: none;">
							<a href="#" class="button-gray-on-gray"><span><?php _e('Load more', 'usernoise-pro')?></span></a>
						</li>
					</ul>
				</div>
			</div>
			<div class="scrollbar"><div class="track"><div class="thumb"><div class="top"></div><div class="end"></div></div></div></div>
		</div>
</div>
