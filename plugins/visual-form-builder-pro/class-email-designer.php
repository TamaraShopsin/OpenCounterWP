<?php
/**
 * Class that builds our Entries table
 * 
 * @since 1.2
 */
class VisualFormBuilder_Pro_Designer {

	public function __construct(){
		global $wpdb;
		
		/* Setup global database table names */
		$this->field_table_name = $wpdb->prefix . 'vfb_pro_fields';
		$this->form_table_name = $wpdb->prefix . 'vfb_pro_forms';
		$this->entries_table_name = $wpdb->prefix . 'vfb_pro_entries';
		
		add_action( 'admin_init', array( &$this, 'design_options' ) );
	}
	
	public function design_options(){
		global $wpdb;
		
		$form_nav_selected_id = ( isset( $_REQUEST['form_id'] ) ) ? $_REQUEST['form_id'] : '1';
		
		/* Query to get all forms */
		$order = sanitize_sql_orderby( 'form_id ASC' );
		$query = "SELECT * FROM $this->form_table_name ORDER BY $order";
		
		/* Build our forms as an object */
		$forms = $wpdb->get_results( $query );

		/* Loop through each form and assign a form id, if any */
		?>
        
        <form method="post" id="design-switcher">
            <label for="form_id"><strong>Select email to design:</strong></label> 
            <select name="form_id">
		<?php
		foreach ( $forms as $form ) {
			if ( $form_nav_selected_id == $form->form_id ) {
				
				$email_design = unserialize( $form->form_email_design );

				$color_scheme = stripslashes( $email_design['color_scheme'] );
				$format = stripslashes( $email_design['format'] );
				$link_love = stripslashes( $email_design['link_love'] );
				$footer_text = stripslashes( $email_design['footer_text'] );
				$background_color = stripslashes( $email_design['background_color'] );
				$header_color = stripslashes( $email_design['header_color'] );
				$fieldset_color = stripslashes( $email_design['fieldset_color'] );
				$section_color = stripslashes( $email_design['section_color'] );
				$section_text_color = stripslashes( $email_design['section_text_color'] );
				$text_color = stripslashes( $email_design['text_color'] );
				$link_color = stripslashes( $email_design['link_color'] );
				$row_color = stripslashes( $email_design['row_color'] );
				$row_alt_color = stripslashes( $email_design['row_alt_color'] );
				$border_color = stripslashes( $email_design['border_color'] );
				$footer_color = stripslashes( $email_design['footer_color'] );
				$footer_text_color = stripslashes( $email_design['footer_text_color'] );
				$font_family = stripslashes( $email_design['font_family'] );
				$header_font_size = stripslashes( $email_design['header_font_size'] );
				$fieldset_font_size = stripslashes( $email_design['fieldset_font_size'] );
				$section_font_size = stripslashes( $email_design['section_font_size'] );
				$text_font_size = stripslashes( $email_design['text_font_size'] );
				$footer_font_size = stripslashes( $email_design['footer_font_size'] );
				
			}
			
			echo '<option value="' . $form->form_id . '"' . selected( $form->form_id, $form_nav_selected_id, 0 ) . ' id="' . $form->form_key . '">' . stripslashes( $form->form_title ) . '</option>';
		}
?>
		</select>
        <input type="submit" value="Select" class="button" id="Submit" name="Submit">
        </form>
        
        
		<form id="email-design" method="post" enctype="multipart/form-data">
        	<input name="action" type="hidden" value="email_design" />
			<input name="form_id" type="hidden" value="<?php echo $form_nav_selected_id; ?>" />
            <?php 
				/* Security nonce */
				wp_nonce_field( 'update-design-' . $form_nav_selected_id );
			?>
				<h3>Colors</h3>
				<table class="form-table">
                      <tr valign="top">
                        <th scope="row">Body Background Color</th>
                        <td><input type="text" name="background_color" id="vfb-background-color" class="color" value="<?php echo $background_color; ?>" />
                          <div id="picker-background-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-background-color" class="email-design-default text-black" >#eeeeee</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Header Background Color</th>
                        <td><input type="text" name="header_color" id="vfb-header-color" class="color" value="<?php echo $header_color; ?>" />
                          <div id="picker-header-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-header-color" class="email-design-default text-white">#810202</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Fieldset Text Color</th>
                        <td><input type="text" name="fieldset_color" id="vfb-fieldset-color" class="color" value="<?php echo $fieldset_color; ?>" />
                          <div id="picker-fieldset-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-fieldset-color" class="email-design-default text-white">#680606</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Section Background Color</th>
                        <td><input type="text" name="section_color" id="vfb-section-color" class="color" value="<?php echo $section_color; ?>" />
                          <div id="picker-section-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span ><a href="#" id="default-section-color" class="email-design-default text-white">#5C6266</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Section Text Color</th>
                        <td><input type="text" name="section_text_color" id="vfb-section-text-color" class="color" value="<?php echo $section_text_color; ?>" />
                          <div id="picker-section-text-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-section-text-color" class="email-design-default text-black">#ffffff</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Text Color</th>
                        <td><input type="text" name="text_color" id="vfb-text-color" class="color" value="<?php echo $text_color; ?>" />
                          <div id="picker-text-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-text-color" class="email-design-default text-white">#333333</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Link Color</th>
                        <td><input type="text" name="link_color" id="vfb-link-color" class="color" value="<?php echo $link_color; ?>" />
                          <div id="picker-link-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-link-color" class="email-design-default text-white">#1b8be0</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Row Background Color</th>
                        <td><input type="text" name="row_color" id="vfb-row-color" class="color" value="<?php echo $row_color; ?>" />
                          <div id="picker-row-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-row-color" class="email-design-default text-black">#ffffff</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Alternate Row Background Color</th>
                        <td><input type="text" name="row_alt_color" id="vfb-row-alt-color" class="color" value="<?php echo $row_alt_color; ?>" />
                          <div id="picker-row-alt-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-row-alt-color" class="email-design-default text-black">#eeeeee</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Row Border Color</th>
                        <td><input type="text" name="border_color" id="vfb-border-color" class="color" value="<?php echo $border_color; ?>" />
                          <div id="picker-border-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-border-color" class="email-design-default text-black">#cccccc</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Footer Color</th>
                        <td><input type="text" name="footer_color" id="vfb-footer-color" class="color" value="<?php echo $footer_color; ?>" />
                          <div id="picker-footer-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-footer-color" class="email-design-default text-white">#333333</a></span></span></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Footer Text Color</th>
                        <td><input type="text" name="footer_text_color" id="vfb-footer-text-color" class="color" value="<?php echo $footer_text_color; ?>" />
                          <div id="picker-footer-text-color" class="colorPicker"></div>
                          <br />
                          <span>Default color: <span><a href="#" id="default-footer-text-color" class="email-design-default text-black">#ffffff</a></span></span></td>
                      </tr>
				 </table>
				 <h3>Fonts</h3>
				 <table class="form-table">
				  <tr valign="top">
				  	<th scope="row">Font Family</th>
					<td>
						<select name="font_family">
							<option value="Arial"<?php selected( $font_family, 'Arial' ); ?>>Arial</option>
							<option value="Georgia"<?php selected( $font_family, 'Georgia' ); ?>>Georgia</option>
							<option value="Helvetica"<?php selected( $font_family, 'Helvetica' ); ?>>Helvetica</option>
							<option value="Tahoma"<?php selected( $font_family, 'Tahoma' ); ?>>Tahoma</option>
							<option value="Verdana"<?php selected( $font_family, 'Verdana' ); ?>>Verdana</option>
						</select>
					</td>
				  </tr>
				  <tr valign="top">
				  	<th scope="row">Header Font Size</th>
					<td>
						<select name="header_font_size">
                            <?php $this->font_size_helper( $header_font_size ); ?>
						</select> px
					</td>
				  </tr>
				  <tr valign="top">
				  	<th scope="row">Fieldset Font Size</th>
					<td>
						<select name="fieldset_font_size">
                            <?php $this->font_size_helper( $fieldset_font_size ); ?>
						</select> px
					</td>
				  </tr>
				  <tr valign="top">
				  	<th scope="row">Section Font Size</th>
					<td>
						<select name="section_font_size">
						  <?php $this->font_size_helper( $section_font_size ); ?>
						</select> px
					</td>
				  </tr>
				  <tr valign="top">
				  	<th scope="row">Text Font Size</th>
					<td>
						<select name="text_font_size">
                            <?php $this->font_size_helper( $text_font_size ); ?>
						</select> px
					</td>
				  </tr>
                  <tr valign="top">
				  	<th scope="row">Footer Font Size</th>
					<td>
						<select name="footer_font_size">
                            <?php $this->font_size_helper( $footer_font_size ); ?>
						</select> px
					</td>
				  </tr>
				</table>
                <h3>Settings</h3>
				 <table class="form-table">
				  <tr valign="top">
				  	<th scope="row">Email Format</th>
					<td>
                    	<?php $format = ( $format == '' ) ? 'html' : $format; ?>
                    	<input type="radio" name="format" value="html" <?php checked( $format, 'html' ); ?>  /> HTML
                        <input type="radio" name="format" value="text" <?php checked( $format, 'text' ); ?> /> Plain Text
                    </td>
                  </tr>
                  <tr valign="top">
				  	<th scope="row">Link back to Visual Form Builder?</th>
					<td>
                    	<?php $link_love = ( $link_love == '' ) ? 'yes' : $link_love; ?>
                    	<input type="radio" name="link_love" value="yes" <?php checked( $link_love, 'yes' ); ?>  /> Yes
                        <input type="radio" name="link_love" value="no" <?php checked( $link_love, 'no' ); ?> /> No
                    </td>
                  </tr>
                  <tr valign="top">
                        <th scope="row">Additional Footer Text</th>
                        <td><input type="text" name="footer_text" id="vfb-footer-text" class="regular-text" value="<?php echo $footer_text; ?>" />
                        </td>
                      </tr>
                 </table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"  /></p>
		</form>
        
        <h2>Preview</h2>
        <p>Save options to view your recent changes.</p>
        <iframe src="<?php echo plugins_url( 'visual-form-builder-pro' ); ?>/email-preview.php?form=<?php echo $form_nav_selected_id; ?>" width="100%" height="600" ></iframe>
<?php
	}

	public function font_size_helper( $field_name ){
?>
        <option value="8"<?php selected( $field_name, 8 ); ?>>8</option>
        <option value="9"<?php selected( $field_name, 9 ); ?>>9</option>
        <option value="10"<?php selected( $field_name, 10 ); ?>>10</option>
        <option value="11"<?php selected( $field_name, 11 ); ?>>11</option>
        <option value="12"<?php selected( $field_name, 12 ); ?>>12</option>
        <option value="13"<?php selected( $field_name, 13 ); ?>>13</option>
        <option value="14"<?php selected( $field_name, 14 ); ?>>14</option>
        <option value="15"<?php selected( $field_name, 15 ); ?>>15</option>
        <option value="16"<?php selected( $field_name, 16 ); ?>>16</option>
        <option value="18"<?php selected( $field_name, 18 ); ?>>18</option>
        <option value="20"<?php selected( $field_name, 20 ); ?>>20</option>
        <option value="24"<?php selected( $field_name, 24 ); ?>>24</option>
        <option value="28"<?php selected( $field_name, 28 ); ?>>28</option>
        <option value="32"<?php selected( $field_name, 32 ); ?>>32</option>
        <option value="36"<?php selected( $field_name, 36 ); ?>>36</option>
<?php
	}
}
?>