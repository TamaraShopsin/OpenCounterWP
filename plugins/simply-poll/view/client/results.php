<dl class="sp-results">
	<?php foreach($answers as $key => $answer) : ?>
		
		<?php $percentage = round((int)$answer['vote'] / (int)$totalvotes * 100); ?>
		
		<dt class="sp-answer"><?php echo $answer['answer']; ?></dt>
		<dd class="sp-answer-response" style="width:<?php echo $percentage; ?>%"><?php echo $percentage; ?>%</dd>
		
	<?php endforeach; ?>
</dl>

<p class="sp-total"><?php _e('Total votes'); ?>: <?php echo $totalvotes; ?></p>