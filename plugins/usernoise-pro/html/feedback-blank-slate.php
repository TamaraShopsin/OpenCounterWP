<?php global $un_model ?>
<li class="feedback blank-slate">
	<?php if ($type): ?>
		<?php echo sprintf(__('No %s yet', 'usernoise-pro'), $un_model->get_plural_feedback_type_label($type)) ?>
	<?php else: ?>
		<?php _e('No feedback yet', 'usernoise-pro') ?>
	<?php endif ?>
</li>