<table>
	<tr>
		<th><?php _e('Name') ?></th>
		<th><?php _e('Value') ?></th>
	</tr>
	<?php foreach($query_vars as $name => $value): ?>
		<tr class="<?php $h->cycle(array('odd', 'even'))?>">
			<td><?php echo esc_html($name)?></td>
			<td>
				<?php ob_start() ?>
				<?php var_dump($value)?>
				<?php echo esc_html(ob_get_clean())?>
			</td>
		</tr>
	<?php endforeach ?>
</table>
<br>
<label><?php _e('Empty query vars')?>:</label>
<?php echo esc_html(join(', ', $empty)) ?>