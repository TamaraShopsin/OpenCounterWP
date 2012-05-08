<div id="feedback-discussion">
	<div id="comment-list" class="scrollable" style="display: none;">
		<div class="viewport">
			<div class="overview">
			<ul>
			</ul>
			</div>
		</div>
		<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
	</div>
	<img src="<?php echo usernoise_url('/images/loader.gif') ?>" id="comments-loader" class="loader">
	<div id="leave-a-comment-wrapper2">
		<div id="leave-a-comment-wrapper">
			<a href="#" id="leave-a-comment" class="button-gray-on-gray">
				<span><?php _e('Leave a comment') ?></span>
			</a>
			<span id="comment-count">1 comments</span>
		</div>
	</div>
	<div id="comment-form-wrapper">
		<form id="comment-form">
			<?php if (!is_user_logged_in()): ?>
				<?php $h->text_field('name', __('Name'), array('id' => 'un-comment-name', 'class' => 'text text-empty')) ?>
				<?php $h->text_field('email', __('Your email (will not be published)', 'usernoise-pro'), array('id' => 'un-comment-email', 'class' => 'text text-empty')) ?>
			<?php else: ?>
			<?php endif ?>
			<?php $textarea_rows = un_get_option(UN_FEEDBACK_FORM_SHOW_SUMMARY) || 
				un_get_option(UN_FEEDBACK_FORM_SHOW_TYPE) || un_get_option(UN_FEEDBACK_FORM_SHOW_EMAIL) ? '7' : '4' ?>
			<?php $h->textarea('comment', _x( 'Comment', 'noun'), array('id' => 'un-comment', 'rows' => $textarea_rows, 'class' => 'text'))?>
			<div id="submit-comment-wrapper">
				<a href="#" id="cancel-comment" class="button-gray-on-gray"><span><?php _e('Cancel')?></span></a>
				<a href="#" id="submit-comment" class="button-blue-on-gray"><span><?php _e('Submit')?></span></a>
				<img src="<?php echo usernoise_url('/images/loader.gif') ?>" id="comment-loader" class="loader" style="display: none;">
				<div id="comment-errors" class="errors" style="visibility: hidden;">Errors</div>
			</div>
		</form>
	</div>
</div>
<div style="clear: left"></div>
</div>