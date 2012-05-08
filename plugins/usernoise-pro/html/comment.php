<li class="clearfix"><div class="avatar-wrapper">
		<?php echo get_avatar(get_comment_author_email(get_comment_id()),'48', usernoisepro_url("/images/default-avatar.gif") ) ?>
	</div><div class="single-comment-content">
		<h4><?php comment_author() ?>, <span class="date"><?php comment_date()?></span></h4>
		<?php comment_text() ?>
	</div>
</li>