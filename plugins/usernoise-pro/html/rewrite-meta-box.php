<table>
<?php foreach($rewrite as $key => $value): ?>
	<tr class="<?php $h->cycle(array('odd', 'even'))?>">
		<td><?php echo esc_html($key) ?></td>
		<td><?php echo esc_html($value)?></td>
	</tr>
<?php endforeach ?>
</table>