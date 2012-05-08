<?php global $un_model ?>
<?php global $unpro_model ?>
<li class="feedback" id="feedback-<?php the_ID() ?>">
	<a class="feedback-title" href="#feedback-<?php the_ID() ?>"><?php the_title() ?></a>
	<div class="feedback-meta">
		<a class="likes<?php echo (in_array(get_the_ID(), preg_split('/,/', isset($_COOKIE['likes']) ? $_COOKIE['likes'] : ''))) ? ' un-liked' : '' ?>" href="#like-<?php the_ID() ?>">
			<?php if (un_get_the_feedback_likes() ): ?>
				<?php un_the_feedback_likes()?>
			<?php else: ?>
				<?php _e('I like it!', 'usernoise-pro')?>
			<?php endif ?>
		</a>
		<?php if (un_get_the_feedback_status()): ?>
			<span class="status <?php un_the_feedback_status_slug() ?>"><?php un_the_feedback_status() ?></span>
			&middot;
		<?php endif ?>
		<a href="#feedback-<?php the_ID() ?>" class="comments">
		<?php if ($post->comment_count): ?>
			<?php echo _n('Comment', 'Comments', $post->comment_count)?>
		<?php else: ?>
			<?php _e('Leave a comment') ?>
		<?php endif ?>
		</a>
	</div>
</li>