<div id="replywrapper">
	<?php $un_h->textarea('message', '', array('rows' => 7, 'id' => 'replybody'))?>
</div>
<label><?php _e('Subject', 'usernoise')?></label>
<?php $un_h->text_field('subject', __('Feedback', 'usernoise', 'admin') . ': ' . $post->post_title)?>

<div class="hide-if-no-js">
	<input type="button" class="button-primary" id="un-reply-submit" value="<?php _e('Send', 'usernoise')?>">
	&nbsp;<img src="<?php echo usernoise_url('/images/loader.gif') ?>" id="un-reply-loader" style="display: none;">
</div>
