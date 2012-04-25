<?php
	global $spAdmin;
	$response	= null;
	$formData	= array();
	
	if( $_GET['page'] == 'sp-add' ) {
		
		$poll					= $_POST;
		$formData['display']	= 'Add Poll';
		
	} elseif( $_GET['page'] == 'sp-update' ) {
		
		$id						= (int)$_GET['id'];
		$poll					= $spAdmin->grabPoll($id);
		$formData['display']	= __('Update Poll');
		
	}
	
	if( isset($_POST['polledit']) ) {
		$poll = $spAdmin->setEdit($_POST);
	}
	
?><div class="wrap">
	
	<div id="icon-edit-comments" class="icon32"><br /></div> 
	<h2><?php echo $formData['display']; ?></h2>
	
	<?php 
//		Messages that are returned from the add/update script 
	
		if( isset($poll['error']) ) {
			foreach( $poll['error'] as $error ) {
				echo '<p class="error">'.$error.'</p>';
			}
			
		} elseif( isset($poll['response']) ) {
			foreach( $poll['response'] as $response ) {
				echo '<p class="response">'.$response.'</p>';
			}
			
		}
	?>
	

	<?php if( isset($poll['return']['success']) ) : ?>
		<h3><?php echo $poll['return']['success']; ?></h3>
		<p>
			<a href="admin.php?page=sp-poll"><?php _e('Back'); ?></a> or 
			<a href="admin.php?page=sp-update&id=<?php echo $poll['return']['pollid']; ?>"><?php _e('update'); ?> "<?php echo $poll['question']; ?>"</a>
		</p>
	<?php else : ?>
	
		<p><?php echo $response; ?></p>
	
		<?php if( isset($poll['updated']) ) : ?>
			<p>
				<?php _e('Added'); ?>: <?php echo date('F j, Y, g:i a', $poll['added']); ?><br />
				<?php _e('Updated'); ?>: <?php echo date('F j, Y, g:i a', $poll['updated']); ?>
			</p>
		<?php endif; ?>

		
		<form method="post" id="polledit">
			
			<p>
				<h2><label for="question"><?php _e('Question'); ?></label></h2>
				<input type="text" name="question" size="50" class="required" id="question" value="<?php
					if( isset($poll['question']) )
						echo stripcslashes($poll['question']);
				?>"/>
			</p>
			
			<fieldset id="answers">
				
				<legend><h2><?php _e('Answers'); ?></h2></legend>
				<ul>
					<?php
						if( isset($poll['answers']) ) {
							$limit = count($poll['answers']);
						} else {
							$limit = 10;
						}
					
						for( $i=1; $i<=$limit; ++$i ) {
							$answer	= null;
							$class	= null;
							$votes	= null;
							
							if( isset($poll['answers'][$i]['answer']) )
								$answer	= $poll['answers'][$i]['answer'];
							
							if( $i<=2 )
								$class	= 'required';
							
							if( isset($poll['answers'][$i]['vote']) )
								$votes	= 'votes: <strong>'.$poll['answers'][$i]['vote'].'</strong>';
							
							echo '<li><input type="text" name="answers['.$i.'][answer]" size="50" value="'.$answer.'" class="'.$class.'" /> '.$votes.'</li>';
						}
					?>
				</ul>
				
			</fieldset>

			<?php
				if( isset($id) ){
					$buttonValue = $id;
				} else {
					$buttonValue = __('new');
				}
			?>
			
			<p><button type="submit" name="polledit" value="<?php echo $buttonValue; ?>" class="button-primary"><?php echo $formData['display']; ?></p>
			
		</form>
	<?php endif; ?>
	
</div>