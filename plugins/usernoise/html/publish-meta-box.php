<div class="submitbox" id="submitpost">
	<div id="minor-publishing">
	<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
	
	<div id="misc-publishing-actions">

	<div class="misc-pub-section<?php if ( !$can_publish ) { echo ' misc-pub-section-last'; } ?>"><label for="post_status"><?php _e('Status:') ?></label>
	<span id="post-status-display">
	<?php
	switch ( $post->post_status ) {
		case 'private':
			_e('Privately Published');
			break;
		case 'publish':
			_e('Reviewed', 'usernoise');
			break;
		case 'future':
			_e('Scheduled');
			break;
		case 'pending':
			_e('Pending Review', 'usernoise');
			break;
		case 'draft':
		case 'auto-draft':
			_e('Draft');
			break;
	}
	?>
	</span>

	</div><?php // /misc-pub-section ?>


	<?php
	// translators: Publish box date formt, see http://php.net/date
	$datef = __( 'M j, Y @ G:i' );
	$stamp = __('Sent on: <b>%1$s</b>');
	$date = date_i18n( $datef, strtotime( $post->post_date ) );
	if ( $can_publish ) : // Contributors don't get to choose the date of publish ?>
		<div class="misc-pub-section curtime misc-pub-section-last">
			<span id="timestamp">
			<?php printf($stamp, $date); ?></span>
		</div><?php // /misc-pub-section ?>
	<?php endif; ?>

	<?php do_action('post_submitbox_misc_actions'); ?>
	</div>
	<div class="clear"></div>
	</div>

	<div id="major-publishing-actions">
		<?php do_action('post_submitbox_start'); ?>
		<div id="delete-action">
		<?php
		if ( current_user_can( "delete_post", $post->ID ) ) {
			if ( !EMPTY_TRASH_DAYS )
				$delete_text = __('Delete Permanently');
			else
				$delete_text = __('Move to Trash');
			?>
		<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
		} ?>
		</div>

		<div id="publishing-action">
			<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading" alt="" />
			<?php if ($post->post_status == 'pending'): ?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
				<?php submit_button( __( 'Reviewed', 'usernoise' ), 'primary', 'publish', false, array( 'tabindex' => '5', 'accesskey' => 'p' ) ); ?>
			<?php else: ?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
				<input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="<?php esc_attr_e('OK') ?>" />
			<?php endif ?>
		</div>
		<input name="un_redirect_back" value="<?php echo esc_attr(isset($_REQUEST['un_redirect_back']) ? $_REQUEST['un_redirect_back'] : $_SERVER['HTTP_REFERER']) ?>" type="hidden">
		<div class="clear"></div>
	</div>
</div>
