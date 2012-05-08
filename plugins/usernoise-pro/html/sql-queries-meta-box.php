<table>
	<tr>
		<th><?php _e('SQL') ?></th>
		<th><?php _e('Time') ?></th>
		<th><?php _e('Stacktrace')?> </th>
	</tr>
	<?php $total_time = 0 ?>
	<?php foreach($sql as $row): ?>
		<tr class="<?php $h->cycle(array('odd', 'even'))?>">
			<td><?php echo esc_html($row[0])?></td>
			<?php $time = (float)$row[1] * 1000 ?>
			<?php $total_time += $time ?>
			<td><?php echo esc_html(number_format($time, 2))?><?php _e('ms')?></td>
			<td><?php echo str_replace(',', ' &rarr; ', esc_html($row[2]))?></td>
		</tr>
	<?php endforeach ?>
	<tr class="totals">
		<td><?php echo count($sql)?> <?php _e('queries')?></td>
		<td><?php echo number_format($total_time, 2)?><?php _e('ms')?></td>
		<td></td>
	</tr>
</table>