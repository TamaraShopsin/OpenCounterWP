<?php

	global $spAdmin;
	
	$id			= (int)$_GET['id'];
	$poll		= $spAdmin->grabPoll($id);
	$question	= $poll['question'];
	$answers	= $poll['answers'];
	$totalvotes	= $poll['totalvotes'];

?>
<div class="wrap">
	<div id="icon-edit-comments" class="icon32"><br /></div> 
	<h2><?php echo $question; ?></h2>
	
	<p><?php _e('Total votes'); ?>: <?php echo $totalvotes; ?></p>
	
	<div id="poll-pie"></div>
	
	<script>
		jQuery(function(){
			var $ = jQuery;
			
			$(document).ready(function(){
				var data = [
					<?php foreach($answers as $key => $answer) : ?>
						['<strong><?php echo $answer['answer']; ?></strong> <?php _e('votes'); ?>: <?php echo $answer['vote']; ?>', <?php echo $answer['vote']; ?>],
					<?php endforeach; ?>
				];
				
				var plot1 = jQuery.jqplot ('poll-pie', [data], {
					seriesDefaults: {
						renderer: jQuery.jqplot.PieRenderer,
						rendererOptions: {
							showDataLabels: true,
							fill: false,
							sliceMargin: 4, 
						}
					}, 
					legend: { 
						show:		true, 
						location:	'nw'
					},
					grid: {
						background: 'transparent',
						borderWidth: 0,
						shadow: false
					}
				});
			});
		});
	</script>
	
	<p>
		<a href="<?php admin_url(); ?>admin.php?page=sp-update&amp;id=<?php echo $id; ?>" class="button"><?php _e('update'); ?></a>
		<a href="<?php admin_url(); ?>admin.php?page=sp-reset&amp;id=<?php echo $id; ?>" class="button"><?php _e('reset'); ?></a>
		<a href="<?php admin_url(); ?>admin.php?page=sp-delete&amp;id=<?php echo $id; ?>" class="button"><?php _e('delete'); ?></a>
	</p>
	
	<p>
		<a href="<?php admin_url(); ?>admin.php?page=sp-poll" class="button"><?php _e('Back'); ?></a>
	</p>

</div>