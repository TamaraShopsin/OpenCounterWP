<?php

	global $spAdmin;
	$poll = $spAdmin->grabPoll();

?>

<div class="wrap">
	<div id="icon-edit-comments" class="icon32"><br /></div> 
	<h2>
		Simply Poll
		<a href="admin.php?page=sp-add" class="add-new-h2"><?php _e('Add New Poll'); ?></a>
	</h2>
	
	<?php if($poll['polls']) : ?>
		<ul class="polls">
			<?php foreach($poll['polls'] as $key => $poll) : ?>
				<?php if($poll !== 'deleted') : ?>
					<?php $id = $poll['id']; ?>
					<li>
						<strong><?php echo $poll['question']; ?></strong>
						<p><?php _e('Shortcode'); ?>: <code>[poll id='<?php echo $id; ?>']</code></p>
						<p class="center">
							<a href="admin.php?page=sp-view&amp;id=<?php echo $id; ?>" class="button"><?php _e('view'); ?></a>
							<a href="admin.php?page=sp-update&amp;id=<?php echo $id; ?>" class="button"><?php _e('update'); ?></a>
							<a href="admin.php?page=sp-reset&amp;id=<?php echo $id; ?>" class="button"><?php _e('reset'); ?></a>
							<a href="admin.php?page=sp-delete&amp;id=<?php echo $id; ?>" class="button"><?php _e('delete'); ?></a>
						</p>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		
	<?php else : ?>
		
		<p><?php _e('No polls have been made yet'); ?>. <a href="admin.php?page=sp-add"><?php _e('Add New Poll'); ?></a>.</p>	
	
	<?php endif; ?>

</div>