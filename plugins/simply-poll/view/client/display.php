<div class="sp-poll" id="poll-<?php echo $pollid; ?>">
	<p class="sp-question"><?php echo $question; ?></p>
	
	<form method="post" action="<?php echo $postFile; ?>">
	
		<input type="hidden" name="poll" value="<?php echo $pollid; ?>" />
		<input type="hidden" name="backurl" value="<?php echo $thisPage; ?>" />
		
		<?php 
			if ($userCannotTakePoll) :
				
				require(SP_DIR.SP_RESULTS);
				
			else : 
		?>
			
			<fieldset>
				<ul class="sp-list">
				<?php foreach($answers as $key => $answers) : ?>

					<li class="sp-item">
						<input type="radio" name="answer" value="<?php echo $key; ?>" id="poll-<?php echo $pollid; ?>-<?php echo $key; ?>" class="sp-input-radio" />
						<label for="poll-<?php echo $pollid; ?>-<?php echo $key; ?>" class="sp-label">
							<?php echo $answers['answer']; ?>
						</label>
					</li>
					
				<?php endforeach; ?>
				</ul>
			</fieldset>
		
			<p><input type="submit" class="sp-btn" value="<?php _e('Vote'); ?>" /></p>
			
		<?php endif; ?>
		
	</form>
</div>