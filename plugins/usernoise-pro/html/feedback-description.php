<li class="clearfix">
	<div class="avatar-wrapper">
		<?php echo get_avatar($id_or_email,'48', usernoisepro_url("/images/default-avatar.gif") ) ?>
	</div>
	<div class="single-comment-content">
		<h4><?php echo un_feedback_author_name(get_the_ID()) ?>, <span class="date"><?php the_date()?></span> <?php if ($type && $type != 'praise'): ?>
			<span class="feedback-status"><?php echo $unpro_model->get_feedback_status_name(get_the_ID()) ?></span>
		<?php endif ?></h4>
		<?php the_content() ?>
	</div>
</li>