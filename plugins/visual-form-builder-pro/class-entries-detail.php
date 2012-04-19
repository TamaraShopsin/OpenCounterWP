<?php
/**
 * Class that builds our Entries detail page
 * 
 * @since 1.4
 */
class VisualFormBuilder_Pro_Entries_Detail{
	public function __construct(){
		global $wpdb;
		
		/* Setup global database table names */
		$this->field_table_name = $wpdb->prefix . 'vfb_pro_fields';
		$this->form_table_name = $wpdb->prefix . 'vfb_pro_forms';
		$this->entries_table_name = $wpdb->prefix . 'vfb_pro_entries';
		
		add_action( 'admin_init', array( &$this, 'entries_detail' ) );
	}
	
	public function entries_detail(){
		global $wpdb;
		
		$entry_id = absint( $_REQUEST['entry'] );
		
		$query = "SELECT forms.form_title, entries.* FROM $this->form_table_name AS forms INNER JOIN $this->entries_table_name AS entries ON entries.form_id = forms.form_id WHERE entries.entries_id  = $entry_id;";
		
		$entries = $wpdb->get_results( $query );
		
		echo '<p>' . sprintf( '<a href="?page=%s&view=%s" class="view-entry">&laquo; Back to Entries</a>', $_REQUEST['page'], $_REQUEST['view'] ) . '</p>';
		
		/* Get the date/time format that is saved in the options table */
		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		
		/* Loop trough the entries and setup the data to be displayed for each row */
		foreach ( $entries as $entry ) {
			$data = unserialize( $entry->data );
?>
			<form id="entry-edit" method="post" action="">
				<input name="action" type="hidden" value="update_entry" />
				<input name="entry_id" type="hidden" value="<?php echo $entry_id; ?>" />
				
				<?php wp_nonce_field( 'update-entry-' . $entry_id ); ?>
			<h3><span><?php echo stripslashes( $entry->form_title ); ?> : <?php echo __( 'Entry' , 'visual-form-builder'); ?> # <?php echo $entry->entries_id; ?></span></h3>
            <div id="poststuff" class="metabox-holder has-right-sidebar">
				<div id="side-info-column" class="inner-sidebar">
					<div id="side-sortables">
						<div id="submitdiv" class="postbox">
							<h3><span><?php echo __( 'Details' , 'visual-form-builder'); ?></span></h3>
							<div class="inside">
							<div id="submitbox" class="submitbox">
								<div id="minor-publishing">
									<div id="misc-publishing-actions">
										<div class="misc-pub-section">
											<span><strong><?php echo  __( 'Form Title' , 'visual-form-builder'); ?>: </strong><?php echo stripslashes( $entry->form_title ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo  __( 'Date Submitted' , 'visual-form-builder'); ?>: </strong><?php echo date( "$date_format $time_format", strtotime( $entry->date_submitted ) ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'IP Address' , 'visual-form-builder'); ?>: </strong><?php echo $entry->ip_address; ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Email Subject' , 'visual-form-builder'); ?>: </strong><?php echo stripslashes( $entry->subject ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Sender Name' , 'visual-form-builder'); ?>: </strong><?php echo stripslashes( $entry->sender_name ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Sender Email' , 'visual-form-builder'); ?>: </strong><a href="mailto:<?php echo stripslashes( $entry->sender_email ); ?>"><?php echo stripslashes( $entry->sender_email ); ?></a></span>
										</div>
										<div class="misc-pub-section misc-pub-section-last">
											<span><strong><?php echo __( 'Emailed To' , 'visual-form-builder'); ?>: </strong><?php echo preg_replace('/\b([A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4})\b/i', '<a href="mailto:$1">$1</a>', implode( ',', unserialize( stripslashes( $entry->emails_to ) ) ) ); ?></span>
										</div>
										<div class="clear"></div>
									</div>
								</div>
								
								<div id="major-publishing-actions">
									<div id="delete-action"><?php echo sprintf( '<a class="submitdelete deletion entry-delete" href="?page=%s&view=%s&action=%s&entry=%s">Delete</a>', $_REQUEST['page'], $_REQUEST['view'], 'delete', $entry_id ); ?></div>
                                    
                                    <?php if ( is_array( $data[0] ) ) : ?>
										<div id="publishing-action"><input class="button-primary" type="submit" value="Update Entry" /></div>
                                    <?php endif; ?>

									<div class="clear"></div>
								</div>
							</div>
							</div>
						</div>
					</div>
				</div>
			<div>
				<div id="post-body-content">
        <?php
			$count = 0;
			$open_fieldset = $open_section = false;
			
			foreach ( $data as $k => $v ) {
				if ( !is_array( $v ) ) {
					if ( $count == 0 ) {
						echo '<div class="postbox">
							<h3><span>' . $entry->form_title . ' : ' . __( 'Entry' , 'visual-form-builder') .' #' . $entry->entries_id . '</span></h3>
							<div class="inside">';
					}
					
					echo '<h4>' . ucwords( $k ) . '</h4>';
					echo $v;
					$count++;
				}
				else {
					/* Cast each array as an object */
					$obj = (object) $v;

					/* Close each section */
					if ( $open_section == true ) {
						/* If this field's parent does NOT equal our section ID */
						if ( $sec_id && $sec_id !== $obj->parent_id ) {
							echo '</div>';
							$open_section = false;
						}
					}
					
					if ( $obj->type == 'fieldset' ) {
						/* Close each fieldset */
						if ( $open_fieldset == true )
							echo '</div>';
						
						echo '<div class="vfb-details"><h2>' . stripslashes( $obj->name ) . '</h2>';
					
						$open_fieldset = true;
					}
					elseif ( $obj->type == 'section' ) {
						/* Close each fieldset */
						if ( $open_section == true )
							echo '</div>';
						
						echo '<div class="vfb-details section"><h3 class="section-heading">' . stripslashes( $obj->name ) . '</h3>';
						
						/* Save section ID for future comparison */
						$sec_id = $obj->id;
						$open_section = true;
					}
					
					switch ( $obj->type ) {
						case 'fieldset' :
						case 'section' :
						case 'submit' :
						case 'page-break' :
						case 'verification' :
						case 'secret' :
							?>
                            	<input name="field[<?php echo $obj->id; ?>]" type="hidden" value="<?php echo stripslashes( $obj->value ); ?>" />
                            <?php
						break;
						
						case 'text' :
						case 'email' :
						case 'url' :
						case 'currency' :
						case 'number' :
						case 'phone' :
						case 'ip-address' :
						case 'credit-card' :
							?>
							<div class="postbox">
                            <h3><span><?php echo stripslashes( $obj->name ); ?></span></h3>
                                <div class="inside"><input name="field[<?php echo $obj->id; ?>]" type="text" value="<?php echo stripslashes( $obj->value ); ?>" style="width:98%;" /></div>
                            </div>
                        	<?php
						break;
						
						case 'textarea' :
						case 'address' :
						case 'html' :
							?>
							<div class="postbox">
                            <h3><span><?php echo stripslashes( $obj->name ); ?></span></h3>
                                <div class="inside"><textarea name="field[<?php echo $obj->id; ?>]" type="text" style="width:98%;"><?php echo htmlentities( stripslashes( $obj->value ) ); ?></textarea></div>
                            </div>
                        	<?php
						break;
						
						case 'select' :
							?>
							<div class="postbox">
                            <h3><span><?php echo stripslashes( $obj->name ); ?></span></h3>
                                <div class="inside">
                                    <select name="field[<?php echo $obj->id; ?>]" style="width:25%;">
                                    <?php
                                        $options = ( is_array( unserialize( $obj->options ) ) ) ? unserialize( $obj->options ) : explode( ',', unserialize( $obj->options ) );
                                        
                                        foreach( $options as $option => $value ) {
                                            echo '<option value="' . stripslashes( $value ) . '"' . selected( $obj->value, $value ) . '>' . stripslashes( $value ) . '</option>';
                                        }
                                    ?>
                                    </select>
                                </div>
                            </div>
                        	<?php
						break;
						
						case 'radio' :
							?>
							<div class="postbox">
                            <h3><span><?php echo stripslashes( $obj->name ); ?></span></h3>
                                <div class="inside">
                                    <?php
                                        $options = ( is_array( unserialize( $obj->options ) ) ) ? unserialize( $obj->options ) : explode( ',', unserialize( $obj->options ) );

                                        foreach( $options as $option => $value ) {
                                            echo '<input type="radio" name="field[' . $obj->id . ']" value="' . stripslashes( $value ) . '" ' . checked( $obj->value, $value, 0) . '> ' . stripslashes( $value ) . '<br />';
                                        }
                                    ?>
                                </div>
                            </div>
                        	<?php
						break;
						
						case 'checkbox' :
							?>
							<div class="postbox">
                            <h3><span><?php echo stripslashes( $obj->name ); ?></span></h3>
                                <div class="inside">
                                    <?php
                                        $options = ( is_array( unserialize( $obj->options ) ) ) ? unserialize( $obj->options ) : explode( ',', unserialize( $obj->options ) );
										
										$vals = explode( ', ', $obj->value ); 
										
                                        foreach( $options as $option => $value ) {
											$checked = ( in_array( $value, $vals ) ) ? 'checked="checked" ' : '';
												
                                           	echo '<input type="checkbox" name="field[' . $obj->id . '][]" value="' . stripslashes( $value ) . '" ' . $checked . '> ' . stripslashes( $value ) . '<br />';
                                        }
                                    ?>
                                </div>
                            </div>
                        	<?php
						break;
						
						default :
							?>
							<div class="postbox">
                            <h3><span><?php echo stripslashes( $obj->name ); ?></span></h3>
                                <div class="inside"><input name="field[<?php echo $obj->id; ?>]" type="text" value="<?php echo stripslashes( $obj->value ); ?>" style="width:98%;" /></div>
                            </div>
                        	<?php
						break;
					}
				}
			}
			
			if ( $count > 0 )
				echo '</div></div>';
		
			echo '</div></div></div></div>';
		}
		
		echo '<br class="clear"></div>';
		
		
		echo '</form>';
	}
}
?>