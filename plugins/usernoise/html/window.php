<!DOCTYPE html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=<?php bloginfo('charset') ?>">
	<link rel="stylesheet" href="<?php echo esc_attr(usernoise_url('/css/window.css')) ?>" type="text/css">
	<link rel="stylesheet" href="<?php echo esc_attr(usernoise_url('/css/fixes.css')) ?>" type="text/css">
	<script src="<?php bloginfo('wpurl') ?>/wp-includes/js/jquery/jquery.js"></script>
	<script src="<?php echo esc_attr(usernoise_url('/vendor/jquery.resize.js')) ?>"></script>
	<script>var usernoise = {};</script>
	<script src="<?php echo esc_attr(usernoise_url('/js/window.js')) ?>"></script>
	<?php do_action('un_head') ?>
</head>
<body>
	<div id="window" <?php un_window_class() ?>>
		<a id="window-close" href="#"><?php _e('Close', 'usernoise') ?></a>
		<div id="viewport" class="clearfix">
			<?php do_action('before_feedback_wrapper')?>
			<div id="feedback-wrapper">
				<div id="feedback-form-wrapper">
					<h2>
					<?php echo un_get_option(UN_FEEDBACK_FORM_TITLE, __('Feedback', 'usernoise')) ?>
					<?php if (current_user_can('edit_others_posts')): ?>
						<a class="button-settings" id="button-settings" href="<?php echo admin_url('options-general.php?page=usernoise')?>">
							<?php echo strtolower(__('Settings', 'usernoise')) ?></a>
						<?php endif ?>
					</h2>
					<p><?php echo un_feedback_form_text() ?></p>
					<?php do_action('un_fedback_form_before')?>
					<form action="<?php echo admin_url('admin-ajax.php') ?>?action=un_feedback_form_submit" method="post" id="feedback-form">
						<?php if (un_get_option(UN_FEEDBACK_FORM_SHOW_TYPE)): ?>
							<div id="types-wrapper">
								<?php $un_h->link_to(__('Idea', 'usernoise') . '<span class="selection"></span>', '#', array('id' => 'un-type-idea', 'class' => 'selected'))?>
								<?php $un_h->link_to(__('Problem', 'usernoise') . '<span class="selection"></span>', '#', array('id' => 'un-type-problem'))?>
								<?php $un_h->link_to(__('Question', 'usernoise') . '<span class="selection"></span>', '#', array('id' => 'un-type-question'))?>
								<?php $un_h->link_to(__('Praise', 'usernoise') . '<span class="selection"></span>', '#', array('id' => 'un-type-praise'))?>
								<?php $un_h->hidden_field('type', 'idea')?>
							</div>
						<?php endif ?>
						<?php $un_h->textarea('description', __('Your feedback', 'usernoise'), array('id' => 'un-description', 'class' => 'text text-empty'))?>
						<?php if (un_get_option(UN_FEEDBACK_FORM_SHOW_SUMMARY)): ?>
							<?php $un_h->text_field('title', __('Short summary', 'usernoise'), array('id' => 'un-title', 'class' => 'text text-empty'))?>
						<?php endif ?>
						<?php if (un_get_option(UN_FEEDBACK_FORM_SHOW_EMAIL)): ?>
							<?php $un_h->text_field('email', __('Your email (will not be published)', 'usernoise'), array('id' => 'un-email', 'class' => 'text text-empty'))?>
						<?php endif ?>
						<?php do_action('un_feedback_form_body') ?>
						<input type="submit" class="un-submit" value="<?php echo esc_attr(un_submit_feedback_button_text()) ?>" id="un-feedback-submit">
						&nbsp;<img src="<?php echo usernoise_url('/images/loader.gif') ?>" id="un-feedback-loader" class="loader" style="display: none;">
						<div id="feedback-errors-wrapper" style="display: none;">
							<div class="errors"></div>
						</div>
					</form>
					<?php do_action('un_feedback_form_after')?>
				</div>
				<div id="thankyou" style="display: none;">
					<h2><?php echo un_get_option(UN_THANKYOU_TITLE, __('Thank you', 'usernoise')) ?></h2>
					<p>
						<?php echo un_get_option(UN_THANKYOU_TEXT, __('Your feedback has been received.', 'usernoise')) ?>
					</p>
					<a href="#" id="feedback-close"><img src="<?php echo usernoise_url('/images/ok.png')?>" id="thankyou-image"></a>
				</div>
				</div>
			<?php do_action('un_after_feedback_wrapper')?>
		</div>
	</div>
</body>