<table width="100%">
	<tr>
		<th><?php _e('Name') ?></th>
		<th><?php _e('Hooks') ?></th>
	</tr>
	<?php foreach($filters as $name => $filter): ?>
		<tr class="<?php $h->cycle(array('odd', 'even'))?>">
			<td><?php echo esc_html($name)?></td>
			<td style="padding: 5px 0">
				<table class="internal" width="100%" cellpadding="0">
					<?php foreach ($filter as $priority => $functions): ?>
						<tr>
							<td width="50" valign="top" align="right" nowrap><?php echo esc_html($priority)?> &rarr;</td>
							<td><?php echo esc_html(join(', ', $functions)) ?></td>
						</tr>
					<?php endforeach ?>
				</table>
			</td>
		</tr>
	<?php endforeach ?>
</table>
