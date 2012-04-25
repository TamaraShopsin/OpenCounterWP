<?php
	global $spAdmin;
	
	$pollDB = new SimplyPollDB();
	
	$id = (int)$_GET['id'];
	$poll = $spAdmin->grabPoll($id);
		
	if(isset($_POST['reset']) && $_POST['reset'] == 'yes') {
		$pollDB->resetPoll($poll);
		$message = 'Poll reset';
		
	} elseif(isset($_POST['reset']) && $_POST['reset'] == 'no') {
		$message = 'All poll votes are still intact';
	}
	
?><div class="wrap">
	<div id="icon-edit-comments" class="icon32"><br /></div> 
	<h2>
		<?php _e('Reset Poll'); ?>
	</h2>
	
	<?php if(isset($message)) : ?>
		
		<script>
			setTimeout( "pageRedirect()", 3000 );
			
			function pageRedirect() {
				window.location.replace('<?php admin_url(); ?>admin.php?page=sp-view&id=<?php echo $id; ?>');
			}
		</script>
		
		<p><?php echo $message; ?></p>
		
		<p><?php _e('Page will refresh in a moment...'); ?></p>
		
		<p><a href="<?php admin_url(); ?>admin.php?page=sp-veiw&id=<?php echo $id; ?>" class="button"><?php _e('Back'); ?></a></p>
		
	<?php else : ?>
	
		<?php if(!$poll) : ?>
			<p><?php _e('There is no poll with the ID'); ?> <strong><?php echo $id; ?></strong></p>
			
			<p><a href="<?php admin_url(); ?>admin.php?page=sp-poll"><?php _e('Back'); ?></a></p>
		<?php else : ?>
			
			<p><?php _e('Are you sure you want to reset poll'); ?> "<strong><?php echo $poll['question']; ?></strong>"?</p>
			
			<form method="post">
				
				<input type="hidden" name="id" value="<?php echo $id; ?>" />
				<p>
					<select name="reset">
						<option value="no"><?php _e('No'); ?></option>
						<option value="yes"><?php _e('Yes'); ?></option>
					</select>
					<input type="submit" class="button" value="<?php _e('Submit'); ?>" />
				</p>
			</form>
		
		<?php endif; ?>
		
	<?php endif; ?>

</div>