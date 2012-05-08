<div class="un-admin-section un-admin-section-first">
	<strong><?php _e('URL:')?></strong>&nbsp;
	<?php $h->link_to(get_post_meta($post->ID, '_url', true), get_post_meta($post->ID, '_url', true))?>
</div>
<div class="un-admin-section">
	<strong><?php _e('Browser:')?></strong>&nbsp;
	<?php echo esc_html(get_post_meta($post->ID, '_user_agent', true)) ?>
</div>
<?php if ($ip = get_post_meta($post->ID, '_ip', true)): ?>
	<div class="un-admin-section">
		<strong><?php _e('Client IP:')?></strong>&nbsp;
		<?php echo esc_html($ip) ?>
	</div>
<?php endif ?>

<?php if ($ip = get_post_meta($post->ID, '_x_forwarded_for', true)): ?>
	<div class="un-admin-section un-admin-section-last">
		<strong><?php _e('X-Forwarded-For:')?></strong>&nbsp;
		<?php echo esc_html($ip) ?>
	</div>
<?php endif ?>

<?php if ($html = get_post_meta($post->ID, '_document', true)): ?>
		<div class="un-admin-section un-admin-section-last">
			<?php $h->link_to(__('View page snapshot'), admin_url('admin-ajax.php?action=un_preview_html&id='. $post->ID),
				array('target' => '_new'));?>
				
		</div>
<?php endif ?>