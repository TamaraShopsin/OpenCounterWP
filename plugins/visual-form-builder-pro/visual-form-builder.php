<?php
/*
Plugin Name: Visual Form Builder Pro
Description: Dynamically build forms using a simple interface. Forms include jQuery validation, a basic logic-based verification system, and entry tracking.
Author: Matthew Muro
Version: 1.3.1
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/

/* Instantiate new class */
$visual_form_builder_pro = new Visual_Form_Builder_Pro();

/* Visual Form Builder class */
class Visual_Form_Builder_Pro{
	
	protected $vfb_db_version = '1.3.1',
			  $api_url = 'http://matthewmuro.com/plugin-api/';
	
	public function __construct(){
		global $wpdb;

		/* Setup global database table names */
		$this->field_table_name = $wpdb->prefix . 'vfb_pro_fields';
		$this->form_table_name = $wpdb->prefix . 'vfb_pro_forms';
		$this->entries_table_name = $wpdb->prefix . 'vfb_pro_entries';
		
		/* Make sure we are in the admin before proceeding. */
		if ( is_admin() ) {
			/* Build options and settings pages. */
			add_action( 'admin_menu', array( &$this, 'add_admin' ) );
			add_action( 'admin_menu', array( &$this, 'save' ) );
			
			add_action( 'wp_ajax_visual_form_builder_process_sort', array( &$this, 'process_sort_callback' ) );
			add_action( 'wp_ajax_visual_form_builder_create_field', array( &$this, 'create_field_callback' ) );
			add_action( 'wp_ajax_visual_form_builder_delete_field', array( &$this, 'delete_field_callback' ) );
			add_action( 'wp_ajax_visual_form_builder_build_chart', array( &$this, 'build_chart_callback' ) );
			add_action( 'wp_ajax_visual_form_builder_build_table', array( &$this, 'build_table_callback' ) );
			add_action( 'wp_ajax_visual_form_builder_build_data_table', array( &$this, 'build_data_table_callback' ) );
			add_action( 'wp_ajax_visual_form_builder_paypal_price', array( &$this, 'paypal_price_callback' ) );
			add_action( 'wp_ajax_visual_form_builder_form_settings', array( &$this, 'form_settings_callback' ) );
			
			add_action( 'load-toplevel_page_visual-form-builder-pro', array( &$this, 'add_contextual_help' ) );
			add_action( 'admin_init', array( &$this, 'export_entries' ) );
			
			/* Load the includes files */
			add_action( 'plugins_loaded', array( &$this, 'includes' ) );
			
			/* Adds a Screen Options tab to the Entries screen */
			add_action( 'admin_init', array( &$this, 'save_screen_options' ) );
			add_filter( 'screen_settings', array( &$this, 'add_visual_form_builder_screen_options' ) );
			
			
			/* Adds a Settings link to the Plugins page */
			add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );
			
			/* Add a database version to help with upgrades and run SQL install */
			if ( !get_option( 'vfb_pro_db_version' ) ) {
				update_option( 'vfb_pro_db_version', $this->vfb_db_version );
				$this->install_db();
			}
			
			/* If database version doesn't match, update and run SQL install */
			if ( get_option( 'vfb_pro_db_version' ) != $this->vfb_db_version ) {
				update_option( 'vfb_pro_db_version', $this->vfb_db_version );
				$this->install_db();
			}
			
			/* Load the jQuery and CSS we need if we're on our plugin page */
			add_action( 'load-toplevel_page_visual-form-builder-pro', array( &$this, 'form_admin_scripts' ) );
			add_action( 'load-toplevel_page_visual-form-builder-pro', array( &$this, 'form_admin_css' ) );
			
			/* Display plugin details screen for updating */
			add_filter( 'plugins_api', array( &$this, 'api_information' ), 10, 3 );

			/* Hook into the plugin update check */
			add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'api_check' ) );
			
			/* For testing only */
			//add_action( 'init', array( &$this, 'delete_transient' ) );
			
			add_action( 'admin_notices', array( &$this, 'admin_notices' ) );

		}
		
		add_shortcode( 'vfb', array( &$this, 'form_code' ) );
		add_action( 'init', array( &$this, 'email' ), 10 );
		add_action( 'init', array( &$this, 'confirmation' ), 12 );
		
		/* Add jQuery and CSS to the front-end */
		add_action( 'wp_head', array( &$this, 'form_css' ) );
		add_action( 'template_redirect', array( &$this, 'form_validation' ) );
		
		/* Load i18n */
		load_plugin_textdomain( 'visual-form-builder-pro', false , basename( dirname( __FILE__ ) ) . '/languages' );
		
		add_action( 'wp_ajax_visual_form_builder_autocomplete', array( &$this, 'autocomplete_callback' ) );
		add_action( 'wp_ajax_nopriv_visual_form_builder_autocomplete', array( &$this, 'autocomplete_callback' ) );
		add_action( 'wp_ajax_visual_form_builder_check_username', array( &$this, 'check_username_callback' ) );
		add_action( 'wp_ajax_nopriv_visual_form_builder_check_username', array( &$this, 'check_username_callback' ) );
	}
	
	/**
	 * Display admin notices
	 * 
	 * @since 1.0
	 */
	public function admin_notices(){
		if ( isset( $_REQUEST['action'] ) ) {
			switch( $_REQUEST['action'] ) {
				case 'create_form' :
					echo '<div id="message" class="updated"><p>' . __( 'The form has been successfully created.' , 'visual-form-builder-pro') . '</p></div>';
				break;
				case 'update_form' :
					echo '<div id="message" class="updated"><p>' . sprintf( __( 'The %s form has been updated.' , 'visual-form-builder-pro'), '<strong>' . $_REQUEST['form_title'] . '</strong>' ) . '</p></div>';
				break;
				case 'deleted' :
					echo '<div id="message" class="updated"><p>' . __( 'The form has been successfully deleted.' , 'visual-form-builder-pro') . '</p></div>';
				break;
				case 'copy_form' :
					echo '<div id="message" class="updated"><p>' . __( 'The form has been successfully duplicated.' , 'visual-form-builder-pro') . '</p></div>';
				break;
				case 'ignore_notice' :
					update_option( 'vfb_ignore_notice', 1 );
				break;
				case 'upgrade' :
					echo '<div id="message" class="updated"><p>' . __( 'You have successfully migrated to Visual Form Builder Pro!' , 'visual-form-builder-pro') . '</p></div>';
				break;
				case 'update_entry' :
					echo '<div id="message" class="updated"><p>' . __( 'Entry has been successfully updated.' , 'visual-form-builder-pro') . '</p></div>';
				break;
			}
			
		}
		
		/* If the free version of VFB is detected and the user is an admin, display the notice */
		if ( get_option( 'vfb_db_version' ) && current_user_can( 'install_plugins' ) ) {
			/* If they have upgraded, don't display */
			if ( ! get_option( 'vfb_db_upgrade' ) ) {
				/* If they've dismissed the notice, don't display */
				if ( ! get_option( 'vfb_ignore_notice' ) ) {
					echo '<div class="updated"><p>';
					echo sprintf( __( 'A version of Visual Form Builder has been detected. To copy your forms and data to Visual Form Builder Pro, <a href="%1$s">click here</a>.<br><br><strong>Note</strong>: It is recommended that you perform this action <em>before</em> you begin adding forms. Migrating <em>after</em> you have added forms to Visual Form Builder Pro will delete those forms.', 'visual-form-builder-pro' ), 'admin.php?page=visual-form-builder-pro&action=upgrade' );
					echo sprintf( __( '<a style="float:right;" href="%1$s">Dismiss</a>' , 'visual-form-builder-pro' ), '?action=ignore_notice' );
					echo '</p></div>';
				}
			}
		}
		
	}
	
	/**
	 * Delete transients on page load
	 * 
	 * FOR TESTING PURPOSES ONLY
	 *
	 * @since 1.0
	 */
	public function delete_transient() {
		delete_site_transient( 'update_plugins' );
	}

	/**
	 * Check the plugin versions to see if there's a new one
	 * 
	 * @since 1.0
	 */
	public function api_check( $transient ) {

		/* If no checked transiest, just return its value without hacking it */
		if ( empty( $transient->checked ) )
			return $transient;

		/* Append checked transient information */
		$plugin_slug = plugin_basename( __FILE__ );
		
		/* POST data to send to your API */
		$args = array(
			'action' => 'update-check',
			'plugin_name' => $plugin_slug,
			'version' => $transient->checked[ $plugin_slug ],
		);
		
		/* Send request checking for an update */
		$response = $this->api_request( $args );
		
		/* If response is false, don't alter the transient */
		if ( false !== $response )
			$transient->response[ $plugin_slug ] = $response;
		
		return $transient;
		
	}
	
	/**
	 * Send a request to the alternative API, return an object
	 * 
	 * @since 1.0
	 */
	public function api_request( $args ) {
	
		/* Send request */
		$request = wp_remote_post( $this->api_url, array( 'body' => $args ) );
		
		/* If request fails, stop */
		if ( is_wp_error( $request ) ||	wp_remote_retrieve_response_code( $request ) != 200	)
			return false;
		
		/* Retrieve and set response */
		$response = maybe_unserialize( wp_remote_retrieve_body( $request ) );
		
		/* Read server response, which should be an object */
		if ( is_object( $response ) )
			return $response;
		else
			return false;
		
		
	}
	
	/**
	 * Return the plugin details for the plugin update screen
	 * 
	 * @since 1.0
	 */
	public function api_information( $false, $action, $args ) {
	
		$plugin_slug = plugin_basename( __FILE__ );
	
		/* Check if this plugins API is about this plugin */
		if ( $args->slug != $plugin_slug )
			return false;

		/* POST data to send to your API */
		$args = array(
			'action' => 'plugin_information',
			'plugin_name' => $plugin_slug,
			'version' => $transient->checked[ $plugin_slug ],
		);

		/* Send request for detailed information */
		$response = $this->api_request( $args );
		
		/* Send request checking for information */
		$request = wp_remote_post( $this->api_url, array( 'body' => $args ) );
	
		return $response;
		
	}

	
	/**
	 * Adds extra include files
	 * 
	 * @since 1.0
	 */
	public function includes(){
		/* Load the Entries List class */
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'class-entries-list.php' );
		
		/* Load the Entries Details class */
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'class-entries-detail.php' );
		
		/* Load the Email Designer class */
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'class-email-designer.php' );
		
		/* Load the Analytics class */
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'class-analytics.php' );
	}
	
	/**
	 * Register contextual help. This is for the Help tab dropdown
	 * 
	 * @since 1.0
	 */
	public function add_contextual_help(){
		$screen = get_current_screen();

		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-getting-started',
			'title' => 'Getting Started',
			'content' => '<ul>
						<li>Click on the + tab, give your form a name and click Create Form.</li>
						<li>Select form fields from the box on the left and click a field to add it to your form.</li>
						<li>Edit the information for each form field by clicking on the down arrow.</li>
						<li>Drag and drop the elements to put them in order.</li>
						<li>Click Save Form to save your changes.</li>
					</ul>'
		) );

		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-item-config',
			'title' => 'Form Item Configuration',
			'content' => "<ul>
						<li><em>Name</em> will change the display name of your form input.</li>
						<li><em>Description</em> will be displayed below the associated input.</li>
						<li><em>Validation</em> allows you to select from several of jQuery's Form Validation methods for text inputs. For more about the types of validation, read the <em>Validation</em> section below.</li>
						<li><em>Required</em> is either Yes or No. Selecting 'Yes' will make the associated input a required field and the form will not submit until the user fills this field out correctly.</li>
						<li><em>Options</em> will only be active for Radio and Checkboxes.  This field contols how many options are available for the associated input.</li>
						<li><em>Size</em> controls the width of Text, Textarea, Select, and Date Picker input fields.  The default is set to Medium but if you need a longer text input, select Large.</li>
						<li><em>CSS Classes</em> allow you to add custom CSS to a field.  This option allows you to fine tune the look of the form.</li>
					</ul>"
		) );

		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-validation',
			'title' => 'Validation',
			'content' => "<p>Visual Form Builder uses the <a href='http://docs.jquery.com/Plugins/Validation/Validator'>jQuery Form Validation plugin</a> to perform clientside form validation.</p>
					<ul>
						
						<li><em>Email</em>: makes the element require a valid email.</li>
						<li><em>URL</em>: makes the element require a valid url.</li>
						<li><em>Date</em>: makes the element require a date. <a href='http://docs.jquery.com/Plugins/Validation/Methods/date'>Refer to documentation for various accepted formats</a>.
						<li><em>Number</em>: makes the element require a decimal number.</li>
						<li><em>Digits</em>: makes the element require digits only.</li>
						<li><em>Phone</em>: makes the element require a US or International phone number. Most formats are accepted.</li>
						<li><em>Time</em>: choose either 12- or 24-hour time format (NOTE: only available with the Time field).</li>
					</ul>"
		) );

		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-confirmation',
			'title' => 'Confirmation',
			'content' => "<p>Each form allows you to customize the confirmation by selecing either a Text Message, a WordPress Page, or to Redirect to a URL.</p>
					<ul>
						<li><em>Text</em> allows you to enter a custom formatted message that will be displayed on the page after your form is submitted. HTML is allowed here.</li>
						<li><em>Page</em> displays a dropdown of all WordPress Pages you have created. Select one to redirect the user to that page after your form is submitted.</li>
						<li><em>Redirect</em> will only accept URLs and can be used to send the user to a different site completely, if you choose.</li>
					</ul>"
		) );

		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-notification',
			'title' => 'Notification',
			'content' => "<p>Send a customized notification email to the user when the form has been successfully submitted.</p>
					<ul>
						<li><em>Sender Name</em>: the name that will be displayed on the email.</li>
						<li><em>Sender Email</em>: the email that will be used as the Reply To email.</li>
						<li><em>Send To</em>: the email where the notification will be sent. This must be a required text field with email validation.</li>
						<li><em>Subject</em>: the subject of the email.</li>
						<li><em>Message</em>: additional text that can be displayed in the body of the email. HTML tags are allowed.</li>
						<li><em>Include a Copy of the User's Entry</em>: appends a copy of the user's submitted entry to the notification email.</li>
					</ul>"
		) );
		
		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-paypal',
			'title' => 'PayPal',
			'content' => "<p>Forward successful form submissions to PayPal to collect simple payments (i.e. Registration Fees)</p>
					<ul>
						<li><em>Account Email</em>: the PayPal account to send payment to.</li>
						<li><em>Currency</em>: the currency type you will accept.</li>
						<li><em>Shipping</em>: the amount to charge for shipping, if shipping an item.</li>
						<li><em>Tax Rate</em>: the tax rate, if charging tax. This must be a percentage (i.e. 8.0).</li>
						<li><em>Item Name</em>: the item name that will be displayed on the PayPal checkout screen.</li>
						<li><em>Assign Prices</em>: choose a field from your completed form to assign prices.  Input fields will use the amount entered by the user.  Selects, Radios, and Checkboxes will allow you to assign values to the options for those fields.</li>					
					</ul>"
		) );

		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-tips',
			'title' => 'Tips',
			'content' => "<ul>
						<li>Fieldsets, a way to group form fields, are an essential piece of this plugin's HTML. As such, at least one fieldset is required and must be first in the order. Subsequent fieldsets may be placed wherever you would like to start your next grouping of fields.</li>
						<li>Security verification is automatically included on very form. It's a simple logic question and should keep out most, if not all, spam bots.</li>
						<li>There is a hidden spam field, known as a honey pot, that should also help deter potential abusers of your form.</li>
						<li>Nesting is allowed underneath fieldsets and sections.  Sections can be nested underneath fieldsets.  Nesting is not required, however, it does make reorganizing easier.</li>
						<li>Page Breaks must be placed last in a fieldset group. For example, if you have two Fieldsets and want a break, place the Page Break <em>immediately</em> before the second Fieldset starts.</li>
					</ul>"
		) ); 
	}
	
	/**
	 * Adds the Screen Options tab to the Entries screen
	 * 
	 * @since 1.0
	 */
	public function add_visual_form_builder_screen_options($current){
		global $current_screen;
		
		$options = get_option( 'visual-form-builder-screen-options' );

		if ( $current_screen->id == 'toplevel_page_visual-form-builder-pro' && isset( $_REQUEST['view'] ) && in_array( $_REQUEST['view'], array( 'entries' ) ) ){
			$current = '<h5>Show on screen</h5>
					<input type="text" value="' . $options['per_page'] . '" maxlength="3" id="visual-form-builder-per-page" name="visual-form-builder-screen-options[per_page]" class="screen-per-page"> <label for="visual-form-builder-per-page">Entries</label>
					<input type="submit" value="Apply" class="button" id="visual-form-builder-screen-options-apply" name="visual-form-builder-screen-options-apply">';
		}
		
		return $current;
	}
	
	/**
	 * Saves the Screen Options
	 * 
	 * @since 1.0
	 */
	public function save_screen_options(){
		$options = get_option( 'visual-form-builder-screen-options' );
		
		/* Default is 20 per page */
		$defaults = array(
			'per_page' => 20
		);
		
		/* If the option doesn't exist, add it with defaults */
		if ( !$options )
			update_option( 'visual-form-builder-screen-options', $defaults );
		
		/* If the user has saved the Screen Options, update */
		if ( isset( $_REQUEST['visual-form-builder-screen-options-apply'] ) && in_array( $_REQUEST['visual-form-builder-screen-options-apply'], array( 'Apply', 'apply' ) ) ) {
			$per_page = absint( $_REQUEST['visual-form-builder-screen-options']['per_page'] );
			$updated_options = array(
				'per_page' => $per_page
			);
			update_option( 'visual-form-builder-screen-options', $updated_options );
		}
	}
	
	/**
	 * Runs the export_entries function in the class-entries-list.php file
	 * 
	 * @since 1.0
	 */
	public function export_entries() {
		$entries = new VisualFormBuilder_Pro_Entries_List();
		
		/* If exporting all, don't pass the IDs */
		if ( 'export-all' === $entries->current_action() )
			$entries->export_entries();
		/* If exporting selected, pick up the ID array and pass them */
		elseif ( 'export-selected' === $entries->current_action() ) {
			$entry_id = ( is_array( $_REQUEST['entry'] ) ) ? $_REQUEST['entry'] : array( $_REQUEST['entry'] );
			$entries->export_entries( $entry_id );
		}
	}

	
	/**
	 * Install database tables
	 * 
	 * @since 1.0 
	 */
	static function install_db() {
		global $wpdb;
		
		$field_table_name = $wpdb->prefix . 'vfb_pro_fields';
		$form_table_name = $wpdb->prefix . 'vfb_pro_forms';
		$entries_table_name = $wpdb->prefix . 'vfb_pro_entries';
		
		/* Explicitly set the character set and collation when creating the tables */
		$charset = ( defined( 'DB_CHARSET' && '' !== DB_CHARSET ) ) ? DB_CHARSET : 'utf8';
		$collate = ( defined( 'DB_COLLATE' && '' !== DB_COLLATE ) ) ? DB_COLLATE : 'utf8_general_ci';
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); 
				
		$field_sql = "CREATE TABLE $field_table_name (
				field_id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_id BIGINT(20) NOT NULL,
				field_key VARCHAR(255) NOT NULL,
				field_type VARCHAR(25) NOT NULL,
				field_options TEXT,
				field_description TEXT,
				field_name TEXT NOT NULL,
				field_sequence BIGINT(20) DEFAULT '0',
				field_parent BIGINT(20) DEFAULT '0',
				field_validation VARCHAR(25),
				field_required VARCHAR(25),
				field_size VARCHAR(25) DEFAULT 'medium',
				field_css VARCHAR(255),
				field_layout VARCHAR(255),
				UNIQUE KEY  (field_id)
			) DEFAULT CHARACTER SET $charset COLLATE $collate;";

		$form_sql = "CREATE TABLE $form_table_name (
				form_id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_key TINYTEXT NOT NULL,
				form_title TEXT NOT NULL,
				form_email_subject TEXT,
				form_email_to TEXT,
				form_email_from VARCHAR(255),
				form_email_from_name VARCHAR(255),
				form_email_from_override VARCHAR(255),
				form_email_from_name_override VARCHAR(255),
				form_success_type VARCHAR(25) DEFAULT 'text',
				form_success_message TEXT,
				form_notification_setting VARCHAR(25),
				form_notification_email_name VARCHAR(255),
				form_notification_email_from VARCHAR(255),
				form_notification_email VARCHAR(25),
				form_notification_subject VARCHAR(255),
				form_notification_message TEXT,
				form_notification_entry VARCHAR(25),
				form_email_design TEXT,
				form_paypal_setting VARCHAR(25),
				form_paypal_email VARCHAR(255),
				form_paypal_currency VARCHAR(25) DEFAULT 'USD',
				form_paypal_shipping VARCHAR(255),
				form_paypal_tax VARCHAR(255),
				form_paypal_field_price TEXT,
				form_paypal_item_name VARCHAR(255),
				form_label_alignment VARCHAR(25),
				UNIQUE KEY  (form_id)
			) DEFAULT CHARACTER SET $charset COLLATE $collate;";
		
		$entries_sql = "CREATE TABLE $entries_table_name (
				entries_id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_id BIGINT(20) NOT NULL,
				data TEXT NOT NULL,
				subject TEXT,
				sender_name VARCHAR(255),
				sender_email VARCHAR(25),
				emails_to TEXT,
				date_submitted DATETIME,
				ip_address VARCHAR(25),
				UNIQUE KEY  (entries_id)
			) DEFAULT CHARACTER SET $charset COLLATE $collate;";
		
		/* Create or Update database tables */
		dbDelta( $field_sql );
		dbDelta( $form_sql );
		dbDelta( $entries_sql );
	}

	/**
	 * Queue plugin CSS for admin styles
	 * 
	 * @since 1.0
	 */
	public function form_admin_css() {
		wp_enqueue_style( 'visual-form-builder-style', plugins_url( 'visual-form-builder-pro' ) . '/css/visual-form-builder-admin.css' );
		wp_enqueue_style( 'visual-form-builder-main', plugins_url( 'visual-form-builder-pro' ) . '/css/nav-menu.css' );
		wp_enqueue_style( 'farbtastic' );
	}
	
	/**
	 * Queue plugin scripts for sorting form fields
	 * 
	 * @since 1.0 
	 */
	public function form_admin_scripts() {
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'farbtastic' );
		wp_enqueue_script( 'jquery-form-validation', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'form-elements-add', plugins_url( 'visual-form-builder-pro' ) . '/js/visual-form-builder.js' , array( 'jquery', 'jquery-form-validation' ), '', true );
		wp_enqueue_script( 'nested-sortable', plugins_url( 'visual-form-builder-pro' ) . '/js/jquery.ui.nestedSortable.js' , array( 'jquery', 'jquery-ui-sortable' ), '', true );
		
		/* Only load Google Charts if viewing Analytics to prevent errors */
		if ( 'visual-form-builder-pro' == $_REQUEST['page'] && isset( $_REQUEST['view'] ) && in_array( $_REQUEST['view'], array( 'reports' ) ) ) {
			wp_enqueue_script( 'google-ajax', 'https://www.google.com/jsapi', array( 'jquery' ), '', false );
			wp_enqueue_script( 'google-charts', plugins_url( 'visual-form-builder-pro' ) . '/js/charts.js', array( 'google-ajax' ), '', false );
		}
	}
	
	/**
	 * Queue form validation scripts
	 * 
	 * @since 1.0 
	 */
	public function form_validation() {
		wp_enqueue_script( 'jquery-form-validation', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'jquery-ui-core ', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'visual-form-builder-validation', plugins_url( 'visual-form-builder-pro' ) . '/js/visual-form-builder-validate.js' , array( 'jquery', 'jquery-form-validation' ), '', true );
		wp_enqueue_script( 'visual-form-builder-metadata', plugins_url( 'visual-form-builder-pro' ) . '/js/jquery.metadata.js' , array( 'jquery', 'jquery-form-validation' ), '', true );
		wp_enqueue_script( 'visual-form-builder-quicktags', plugins_url( 'visual-form-builder-pro' ) . '/js/js_quicktags.js' );
		wp_enqueue_script( 'farbtastic', admin_url( 'js/farbtastic.js' ) );

		wp_localize_script( 'visual-form-builder-validation', 'VfbAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
	
	/**
	 * Add form CSS to wp_head
	 * 
	 * @since 1.0 
	 */
	public function form_css() {
		echo apply_filters( 'visual-form-builder-css', '<link rel="stylesheet" href="' . plugins_url( 'css/visual-form-builder.css', __FILE__ ) . '" type="text/css" />' );
		echo apply_filters( 'vfb-date-picker-css', '<link media="all" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/themes/base/jquery-ui.css" rel="stylesheet" />' );
		wp_enqueue_style( 'farbtastic' );
	}
	
	/**
	 * Add Settings link to Plugins page
	 * 
	 * @since 1.8 
	 * @return $links array Links to add to plugin name
	 */
	public function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( __FILE__ ) )
			$links[] = '<a href="admin.php?page=visual-form-builder-pro">' . __( 'Settings' , 'visual-form-builder-pro') . '</a>';
	
		return $links;
	}
	
	/**
	 * Add options page to Settings menu
	 * 
	 * 
	 * @since 1.0
	 * @uses add_menu_page() Creates a menu item in the top level menu.
	 */
	public function add_admin() {
		add_menu_page( __( 'Visual Form Builder Pro', 'visual-form-builder-pro' ), __( 'Visual Form Builder Pro', 'visual-form-builder-pro' ), 'manage_categories', 'visual-form-builder-pro', array( &$this, 'admin' ) );
		
		add_submenu_page( 'visual-form-builder-pro', __( 'Add New', 'visual-form-builder-pro' ), __( 'Add New', 'visual-form-builder-pro' ), 'create_users', 'visual-form-builder-pro&amp;form=0', array( &$this, 'admin' ) );
		add_submenu_page( 'visual-form-builder-pro', __( 'Entries', 'visual-form-builder-pro' ), __( 'Entries', 'visual-form-builder-pro' ), 'manage_categories', 'visual-form-builder-pro&amp;view=entries', array( &$this, 'admin' ) );
		add_submenu_page( 'visual-form-builder-pro', __( 'Email Design', 'visual-form-builder-pro' ), __( 'Email Design', 'visual-form-builder-pro' ), 'create_users', 'visual-form-builder-pro&amp;view=design', array( &$this, 'admin' ) );
		add_submenu_page( 'visual-form-builder-pro', __( 'Analytics', 'visual-form-builder-pro' ), __( 'Analytics', 'visual-form-builder-pro' ), 'manage_categories', 'visual-form-builder-pro&amp;view=reports', array( &$this, 'admin' ) );
	
	}
	
	
	/**
	 * Actions to save, update, and delete forms/form fields
	 * 
	 * 
	 * @since 1.0
	 */
	public function save() {
		global $wpdb;
				
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'visual-form-builder-pro' && isset( $_REQUEST['action'] ) ) {
			
			switch ( $_REQUEST['action'] ) {
				case 'create_form' :
					
					$form_id = absint( $_REQUEST['form_id'] );
					$form_key = sanitize_title( $_REQUEST['form_title'] );
					$form_title = esc_html( $_REQUEST['form_title'] );
					
					check_admin_referer( 'create_form-' . $form_id );
					
					$email_design = array(
						'format' => 'html',
						'link_love' => 'yes',
						'footer_text' => '',
						'background_color' => '#eeeeee',
						'header_color' => '#810202',
						'fieldset_color' => '#680606',
						'section_color' => '#5C6266',
						'section_text_color' => '#ffffff',
						'text_color' => '#333333',
						'link_color' => '#1b8be0',
						'row_color' => '#ffffff',
						'row_alt_color' => '#eeeeee',
						'border_color' => '#cccccc',
						'footer_color' => '#333333',
						'footer_text_color' => '#ffffff',
						'font_family' => 'Arial',
						'header_font_size' => 32,
						'fieldset_font_size' => 20,
						'section_font_size' => 15,
						'text_font_size' => 13,
						'footer_font_size' => 11
					);
					
					$newdata = array(
						'form_key' => $form_key,
						'form_title' => $form_title,
						'form_email_design' => serialize( $email_design )
					);
					
					/* Set message to display */
					$this->message = '<div id="message" class="updated"><p>' . sprintf( __( 'The %s form has been created.' , 'visual-form-builder-pro'), "<strong>$form_title</strong>" ) . '</p></div>';
					
					/* Create the form */
					$wpdb->insert( $this->form_table_name, $newdata );
					
					/* Get form ID to add our first field */
					$new_form_selected = $wpdb->insert_id;
					
					/* Setup the initial fieldset */
					$initial_fieldset = array(
						'form_id' => $wpdb->insert_id,
						'field_key' => 'fieldset',
						'field_type' => 'fieldset',
						'field_name' => 'Fieldset',
						'field_sequence' => 0
					);
					
					/* Add the first fieldset to get things started */ 
					$wpdb->insert( $this->field_table_name, $initial_fieldset );
					
					$verification_fieldset = array(
						'form_id' => $new_form_selected,
						'field_key' => 'verification',
						'field_type' => 'verification',
						'field_name' => 'Verification',
						'field_description' => '(This is for preventing spam)',
						'field_sequence' => 1
					);
					
					/* Insert the submit field */ 
					$wpdb->insert( $this->field_table_name, $verification_fieldset );
					
					$verify_fieldset_parent_id = $wpdb->insert_id;
					
					$secret = array(
						'form_id' => $new_form_selected,
						'field_key' => 'secret',
						'field_type' => 'secret',
						'field_name' => 'Please enter any two digits with no spaces (Example: 12)',
						'field_size' => 'medium',
						'field_required' => 'yes',
						'field_parent' => $verify_fieldset_parent_id,
						'field_sequence' => 2
					);
					
					/* Insert the submit field */ 
					$wpdb->insert( $this->field_table_name, $secret );
					
					/* Make the submit last in the sequence */
					$submit = array(
						'form_id' => $new_form_selected,
						'field_key' => 'submit',
						'field_type' => 'submit',
						'field_name' => 'Submit',
						'field_parent' => $verify_fieldset_parent_id,
						'field_sequence' => 3
					);
					
					/* Insert the submit field */ 
					$wpdb->insert( $this->field_table_name, $submit );
					
					/* Redirect to keep the URL clean (use AJAX in the future?) */
					wp_redirect( 'admin.php?page=visual-form-builder-pro&form=' . $new_form_selected );
					exit();
					
				break;
				
				case 'update_form' :

					$form_id = absint( $_REQUEST['form_id'] );
					$form_key = sanitize_title( $_REQUEST['form_title'], $form_id );
					$form_title = esc_html( $_REQUEST['form_title'] );
					$form_subject = esc_html( $_REQUEST['form_email_subject'] );
					$form_to = serialize( array_map( 'esc_html', $_REQUEST['form_email_to'] ) );
					$form_from = esc_html( $_REQUEST['form_email_from'] );
					$form_from_name = esc_html( $_REQUEST['form_email_from_name'] );
					$form_from_override = esc_html( $_REQUEST['form_email_from_override'] );
					$form_from_name_override = esc_html( $_REQUEST['form_email_from_name_override'] );
					$form_success_type = esc_html( $_REQUEST['form_success_type'] );
					$form_notification_setting = esc_html( $_REQUEST['form_notification_setting'] );
					$form_notification_email_name = esc_html( $_REQUEST['form_notification_email_name'] );
					$form_notification_email_from = esc_html( $_REQUEST['form_notification_email_from'] );
					$form_notification_email = esc_html( $_REQUEST['form_notification_email'] );
					$form_notification_subject = esc_html( $_REQUEST['form_notification_subject'] );
					$form_notification_message = wp_richedit_pre( $_REQUEST['form_notification_message'] );
					$form_notification_entry = esc_html( $_REQUEST['form_notification_entry'] );
					$form_paypal_setting = esc_html( $_REQUEST['form_paypal_setting'] );
					$form_paypal_email = esc_html( $_REQUEST['form_paypal_email'] );
					$form_paypal_currency = esc_html( $_REQUEST['form_paypal_currency'] );
					$form_paypal_shipping = esc_html( $_REQUEST['form_paypal_shipping'] );
					$form_paypal_tax = esc_html( $_REQUEST['form_paypal_tax'] );
					$form_paypal_field_price = serialize( $_REQUEST['form_paypal_field_price'] );
					$form_paypal_item_name = esc_html( $_REQUEST['form_paypal_item_name'] );
					$form_label_alignment = esc_html( $_REQUEST['form_label_alignment'] );
					
					/* Add confirmation based on which type was selected */
					switch ( $form_success_type ) {
						case 'text' :
							$form_success_message = wp_richedit_pre( $_REQUEST['form_success_message_text'] );
						break;
						case 'page' :
							$form_success_message = esc_html( $_REQUEST['form_success_message_page'] );
						break;
						case 'redirect' :
							$form_success_message = esc_html( $_REQUEST['form_success_message_redirect'] );
						break;
					}
					
					check_admin_referer( 'update_form-' . $form_id );
					
					$newdata = array(
						'form_key' => $form_key,
						'form_title' => $form_title,
						'form_email_subject' => $form_subject,
						'form_email_to' => $form_to,
						'form_email_from' => $form_from,
						'form_email_from_name' => $form_from_name,
						'form_email_from_override' => $form_from_override,
						'form_email_from_name_override' => $form_from_name_override,
						'form_success_type' => $form_success_type,
						'form_success_message' => $form_success_message,
						'form_notification_setting' => $form_notification_setting,
						'form_notification_email_name' => $form_notification_email_name,
						'form_notification_email_from' => $form_notification_email_from,
						'form_notification_email' => $form_notification_email,
						'form_notification_subject' => $form_notification_subject,
						'form_notification_message' => $form_notification_message,
						'form_notification_entry' => $form_notification_entry,
						'form_paypal_setting' => $form_paypal_setting,
						'form_paypal_email' => $form_paypal_email,
						'form_paypal_currency' => $form_paypal_currency,
						'form_paypal_shipping' => $form_paypal_shipping,
						'form_paypal_tax' => $form_paypal_tax,
						'form_paypal_field_price' => $form_paypal_field_price,
						'form_paypal_item_name' => $form_paypal_item_name,
						'form_label_alignment' => $form_label_alignment
					);
					
					$where = array(
						'form_id' => $form_id
					);
					
					/* Update form details */
					$wpdb->update( $this->form_table_name, $newdata, $where );
					
					/* Initialize field sequence */
					$field_sequence = 0;
					
					/* Loop through each field and update all at once */
					if ( !empty( $_REQUEST['field_id'] ) ) {
						foreach ( $_REQUEST['field_id'] as $id ) {
							$field_name = ( isset( $_REQUEST['field_name-' . $id] ) ) ? esc_html( $_REQUEST['field_name-' . $id] ) : '';
							$field_key = sanitize_title( $field_name, $id );
							$field_desc = ( isset( $_REQUEST['field_description-' . $id] ) ) ? esc_html( $_REQUEST['field_description-' . $id] ) : '';
							$field_options = ( isset( $_REQUEST['field_options-' . $id] ) ) ? serialize( array_map( 'esc_html', $_REQUEST['field_options-' . $id] ) ) : '';
							$field_validation = ( isset( $_REQUEST['field_validation-' . $id] ) ) ? esc_html( $_REQUEST['field_validation-' . $id] ) : '';
							$field_required = ( isset( $_REQUEST['field_required-' . $id] ) ) ? esc_html( $_REQUEST['field_required-' . $id] ) : '';
							$field_size = ( isset( $_REQUEST['field_size-' . $id] ) ) ? esc_html( $_REQUEST['field_size-' . $id] ) : '';
							$field_css = ( isset( $_REQUEST['field_css-' . $id] ) ) ? esc_html( $_REQUEST['field_css-' . $id] ) : '';
							$field_layout = ( isset( $_REQUEST['field_layout-' . $id] ) ) ? esc_html( $_REQUEST['field_layout-' . $id] ) : '';
							
							$field_data = array(
								'field_key' => $field_key,
								'field_name' => $field_name,
								'field_description' => $field_desc,
								'field_options' => $field_options,
								'field_validation' => $field_validation,
								'field_required' => $field_required,
								'field_size' => $field_size,
								'field_css' => $field_css,
								'field_layout' => $field_layout,
								'field_sequence' => $field_sequence
							);
							
							$where = array(
								'form_id' => $_REQUEST['form_id'],
								'field_id' => $id
							);
							
							/* Update all fields */
							$wpdb->update( $this->field_table_name, $field_data, $where );
							
							$field_sequence++;
						}
						
						/* Check if a submit field type exists for backwards compatibility upgrades */
						$is_verification = $wpdb->get_var( "SELECT field_id FROM $this->field_table_name WHERE field_type = 'verification' AND form_id = $form_id" );
						$is_secret = $wpdb->get_var( "SELECT field_id FROM $this->field_table_name WHERE field_type = 'secret' AND form_id = $form_id" );
						$is_submit = $wpdb->get_var( "SELECT field_id FROM $this->field_table_name WHERE field_type = 'submit' AND form_id = $form_id" );
						
						/* Decrement sequence */
						$field_sequence--;
						
						/* If this form doesn't have a submit field, add one */
						if ( $is_verification == NULL ) {
							/* Adjust the sequence */
							$verification_fieldset = array(
								'form_id' => $form_id,
								'field_key' => 'verification',
								'field_type' => 'verification',
								'field_name' => 'Verification',
								'field_sequence' => $field_sequence
							);
							
							/* Insert the submit field */ 
							$wpdb->insert( $this->field_table_name, $verification_fieldset );
							
							$verification_id = $wpdb->insert_id;
						}
						
						/* If the verification field was inserted, use that ID as a parent otherwise set no parent */
						$verify_fieldset_parent_id = ( $verification_id !== false ) ? $verification_id : 0;
						
						/* If this form doesn't have a secret field, add one */
						if ( $is_secret == NULL ) {
							
							/* Adjust the sequence */
							$secret = array(
								'form_id' => $form_id,
								'field_key' => 'secret',
								'field_type' => 'secret',
								'field_name' => 'Please enter any two digits with no spaces (Example: 12)',
								'field_size' => 'medium',
								'field_required' => 'yes',
								'field_parent' => $verify_fieldset_parent_id,
								'field_sequence' => ++$field_sequence
							);
							
							/* Insert the submit field */ 
							$wpdb->insert( $this->field_table_name, $secret );
						}
						
						/* If this form doesn't have a submit field, add one */
						if ( $is_submit == NULL ) {
							
							/* Make the submit last in the sequence */
							$submit = array(
								'form_id' => $form_id,
								'field_key' => 'submit',
								'field_type' => 'submit',
								'field_name' => 'Submit',
								'field_parent' => $verify_fieldset_parent_id,
								'field_sequence' => ++$field_sequence
							);
							
							/* Insert the submit field */ 
							$wpdb->insert( $this->field_table_name, $submit );
						}
						else {
							/* Only update the Submit's parent ID if the Verification field is new */
							$data = ( $is_verification == NULL ) ? array( 'field_parent' => $verify_fieldset_parent_id, 'field_sequence' => ++$field_sequence ) : array( 'field_sequence' => $field_sequence	);
							
							$where = array(
								'form_id' => $form_id,
								'field_id' => $is_submit
							);
										
							/* Update the submit field */
							$wpdb->update( $this->field_table_name, $data, $where );
	
						}
					}
				
				break;
				
				case 'delete_form' :
					$id = absint( $_REQUEST['form'] );
					
					check_admin_referer( 'delete-form-' . $id );
					
					/* Delete form and all fields */
					$wpdb->query( $wpdb->prepare( "DELETE FROM $this->form_table_name WHERE form_id = %d", $id ) );
					$wpdb->query( $wpdb->prepare( "DELETE FROM $this->field_table_name WHERE form_id = %d", $id ) );
					
					/* Redirect to keep the URL clean (use AJAX in the future?) */
					wp_redirect( add_query_arg( 'action', 'deleted', 'admin.php?page=visual-form-builder-pro' ) );
					exit();
					
				break;
				
				case 'copy_form' :
					$id = absint( $_REQUEST['form'] );
					
					check_admin_referer( 'copy-form-' . $id );
					
					/* Get all fields and data for the request form */
					$fields_query = "SELECT * FROM $this->field_table_name WHERE form_id = $id";
					$forms_query = "SELECT * FROM $this->form_table_name WHERE form_id = $id";
					$emails = "SELECT form_email_from_override, form_notification_email FROM $this->form_table_name WHERE form_id = $id";
					
					$fields = $wpdb->get_results( $fields_query );
					$forms = $wpdb->get_results( $forms_query );
					$override = $wpdb->get_var( $emails );
					$notify = $wpdb->get_var( $emails, 1 );
					
					/* Copy this form and force the initial title to denote a copy */
					foreach ( $forms as $form ) {
						$data = array(
							'form_key' => sanitize_title( $form->form_key . ' copy' ),
							'form_title' => $form->form_title . ' Copy',
							'form_email_subject' => $form->form_email_subject,
							'form_email_to' => $form->form_email_to,
							'form_email_from' => $form->form_email_from,
							'form_email_from_name' => $form->form_email_from_name,
							'form_email_from_override' => $form->form_email_from_override,
							'form_email_from_name_override' => $form->form_email_from_name_override,
							'form_success_type' => $form->form_success_type,
							'form_success_message' => $form->form_success_message,
							'form_notification_setting' => $form->form_notification_setting,
							'form_notification_email_name' => $form->form_notification_email_name,
							'form_notification_email_from' => $form->form_notification_email_from,
							'form_notification_email' => $form->form_notification_email,
							'form_notification_subject' => $form->form_notification_subject,
							'form_notification_message' => $form->form_notification_message,
							'form_notification_entry' => $form->form_notification_entry,
							'form_email_design' => $form->form_email_design,
							'form_paypal_setting' => $form->form_paypal_setting,
							'form_paypal_email' => $form->form_paypal_email,
							'form_paypal_currency' => $form->form_paypal_currency,
							'form_paypal_shipping' => $form->form_paypal_shipping,
							'form_paypal_tax' => $form->form_paypal_tax,
							'form_paypal_field_price' => $form->form_paypal_field_price,
							'form_paypal_item_name' => $form->form_paypal_item_name,
							'form_label_alignment' => $form->form_label_alignment
						);
						
						$wpdb->insert( $this->form_table_name, $data );
					}
					
					/* Get form ID to add our first field */
					$new_form_selected = $wpdb->insert_id;
					
					/* Copy each field and data */
					foreach ( $fields as $field ) {
						
						$data = array(
							'form_id' => $new_form_selected,
							'field_key' => $field->field_key,
							'field_type' => $field->field_type,
							'field_name' => $field->field_name,
							'field_description' => $field->field_description,
							'field_options' => $field->field_options,
							'field_sequence' => $field->field_sequence,
							'field_validation' => $field->field_validation,
							'field_required' => $field->field_required,
							'field_size' => $field->field_size,
							'field_css' => $field->field_css,
							'field_layout' => $field->field_layout,
							'field_parent' => $field->field_parent
						);
						
						$wpdb->insert( $this->field_table_name, $data );
						
						/* If a parent field, save the old ID and the new ID to update new parent ID */
						if ( in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ) ) )
							$parents[ $field->field_id ] = $wpdb->insert_id;
							
						if ( $override == $field->field_id )
							$wpdb->update( $this->form_table_name, array( 'form_email_from_override' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );
						
						if ( $notify == $field->field_id )
							$wpdb->update( $this->form_table_name, array( 'form_notification_email' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );
					}
					
					/* Loop through our parents and update them to their new IDs */
					foreach ( $parents as $k => $v ) {
						$wpdb->update( $this->field_table_name, array( 'field_parent' => $v ), array( 'form_id' => $new_form_selected, 'field_parent' => $k ) );	
					}
					
				break;
				
				case 'email_design' :
					$form_id = absint( $_REQUEST['form_id'] );
					
					$color_scheme = esc_html( $_REQUEST['color_scheme'] );
					$format = esc_html( $_REQUEST['format'] );
					$link_love = esc_html( $_REQUEST['link_love'] );
					$footer_text = esc_html( $_REQUEST['footer_text'] );
					$background_color = esc_html( $_REQUEST['background_color'] );
					$header_color = esc_html( $_REQUEST['header_color'] );
					$fieldset_color = esc_html( $_REQUEST['fieldset_color'] );
					$section_color = esc_html( $_REQUEST['section_color'] );
					$section_text_color = esc_html( $_REQUEST['section_text_color'] );
					$text_color = esc_html( $_REQUEST['text_color'] );
					$link_color = esc_html( $_REQUEST['link_color'] );
					$row_color = esc_html( $_REQUEST['row_color'] );
					$row_alt_color = esc_html( $_REQUEST['row_alt_color'] );
					$border_color = esc_html( $_REQUEST['border_color'] );
					$footer_color = esc_html( $_REQUEST['footer_color'] );
					$footer_text_color = esc_html( $_REQUEST['footer_text_color'] );
					$font_family = esc_html( $_REQUEST['font_family'] );
					$header_font_size = esc_html( $_REQUEST['header_font_size'] );
					$fieldset_font_size = esc_html( $_REQUEST['fieldset_font_size'] );
					$section_font_size = esc_html( $_REQUEST['section_font_size'] );
					$text_font_size = esc_html( $_REQUEST['text_font_size'] );
					$footer_font_size = esc_html( $_REQUEST['footer_font_size'] );
					
					check_admin_referer( 'update-design-' . $form_id );
					
					$email_design = array(
						'color_scheme' => $color_scheme,
						'format' => $format,
						'link_love' => $link_love,
						'footer_text' => $footer_text,
						'background_color' => $background_color,
						'header_color' => $header_color,
						'fieldset_color' => $fieldset_color,
						'section_color' => $section_color,
						'section_text_color' => $section_text_color,
						'text_color' => $text_color,
						'link_color' => $link_color,
						'row_color' => $row_color,
						'row_alt_color' => $row_alt_color,
						'border_color' => $border_color,
						'footer_color' => $footer_color,
						'footer_text_color' => $footer_text_color,
						'font_family' => $font_family,
						'header_font_size' => $header_font_size,
						'fieldset_font_size' => $fieldset_font_size,
						'section_font_size' => $section_font_size,
						'text_font_size' => $text_font_size,
						'footer_font_size' => $footer_font_size
					);
					
					$newdata = array(
						'form_email_design' => serialize( $email_design )
					);
					
					$where = array(
						'form_id' => $form_id
					);
					
					/* Update form details */
					$wpdb->update( $this->form_table_name, $newdata, $where );
				break;
				
				case 'upgrade' :
					
					/* Set database names of free version */
					$vfb_fields = $wpdb->prefix . 'visual_form_builder_fields';
					$vfb_forms = $wpdb->prefix . 'visual_form_builder_forms';
					$vfb_entries = $wpdb->prefix . 'visual_form_builder_entries';
					
					/* Get all forms, fields, and entries */
					$forms = $wpdb->get_results( "SELECT * FROM $vfb_forms ORDER BY form_id" );
					
					/* Truncate the tables in case any forms or fields have been added */
					$wpdb->query( "TRUNCATE TABLE $this->form_table_name" );
					$wpdb->query( "TRUNCATE TABLE $this->field_table_name" );
					$wpdb->query( "TRUNCATE TABLE $this->entries_table_name" );
					
					/* Setup email design defaults */
					$email_design = array(
						'format' => 'html',
						'link_love' => 'yes',
						'footer_text' => '',
						'background_color' => '#eeeeee',
						'header_color' => '#810202',
						'fieldset_color' => '#680606',
						'section_color' => '#5C6266',
						'section_text_color' => '#ffffff',
						'text_color' => '#333333',
						'link_color' => '#1b8be0',
						'row_color' => '#ffffff',
						'row_alt_color' => '#eeeeee',
						'border_color' => '#cccccc',
						'footer_color' => '#333333',
						'footer_text_color' => '#ffffff',
						'font_family' => 'Arial',
						'header_font_size' => 32,
						'fieldset_font_size' => 20,
						'section_font_size' => 15,
						'text_font_size' => 13,
						'footer_font_size' => 11
					);
					
					/* Migrate all forms, fields, and entries */
					foreach ( $forms as $form ) :
						$data = array(
							'form_id' => $form->form_id,
							'form_key' => $form->form_key,
							'form_title' => $form->form_title,
							'form_email_subject' => $form->form_email_subject,
							'form_email_to' => $form->form_email_to,
							'form_email_from' => $form->form_email_from,
							'form_email_from_name' => $form->form_email_from_name,
							'form_email_from_override' => $form->form_email_from_override,
							'form_email_from_name_override' => $form->form_email_from_name_override,
							'form_success_type' => $form->form_success_type,
							'form_success_message' => $form->form_success_message,
							'form_notification_setting' => $form->form_notification_setting,
							'form_notification_email_name' => $form->form_notification_email_name,
							'form_notification_email_from' => $form->form_notification_email_from,
							'form_notification_email' => $form->form_notification_email,
							'form_notification_subject' => $form->form_notification_subject,
							'form_notification_message' => $form->form_notification_message,
							'form_notification_entry' => $form->form_notification_entry,
							'form_email_design' => serialize( $email_design )
						);
						
						$wpdb->insert( $this->form_table_name, $data );
						
						$fields = $wpdb->get_results( "SELECT * FROM $vfb_fields WHERE form_id = $form->form_id ORDER BY field_id" );
						/* Copy each field and data */
						foreach ( $fields as $field ) {

							$data = array(
								'field_id' => $field->field_id,
								'form_id' => $field->form_id,
								'field_key' => $field->field_key,
								'field_type' => $field->field_type,
								'field_name' => $field->field_name,
								'field_description' => $field->field_description,
								'field_options' => $field->field_options,
								'field_sequence' => $field->field_sequence,
								'field_validation' => $field->field_validation,
								'field_required' => $field->field_required,
								'field_size' => $field->field_size,
								'field_css' => $field->field_css,
								'field_layout' => $field->field_layout,
								'field_parent' => $field->field_parent
							);
							
							$wpdb->insert( $this->field_table_name, $data );						
						}
						
						$entries = $wpdb->get_results( "SELECT * FROM $vfb_entries WHERE form_id = $form->form_id ORDER BY entries_id" );
						
						/* Copy each entry */
						foreach ( $entries as $entry ) {

							$data = array(
								'form_id' => $entry->form_id,
								'data' => $entry->data,
								'subject' => $entry->subject,
								'sender_name' => $entry->sender_name,
								'sender_email' => $entry->sender_email,
								'emails_to' => $entry->emails_to,
								'date_submitted' => $entry->date_submitted,
								'ip_address' => $entry->ip_address
							);
							
							$wpdb->insert( $this->entries_table_name, $data );
						}

					endforeach;
					
					/* Automatically deactivate free version of Visual Form Builder, if active */
					if ( is_plugin_active( 'visual-form-builder/visual-form-builder.php' ) )
						deactivate_plugins( '/visual-form-builder/visual-form-builder.php' );
					
					/* Set upgrade as complete so admin notice closes */
					update_option( 'vfb_db_upgrade', 1 );
					
				break;
				
				case 'update_entry' :
					$entry_id = absint( $_REQUEST['entry_id'] );

					check_admin_referer( 'update-entry-' . $entry_id );
					
					/* Get this entry's data */
					$entry = $wpdb->get_var( $wpdb->prepare( "SELECT data FROM $this->entries_table_name WHERE entries_id = $entry_id" ) );
					
					$data = unserialize( $entry );
					
					/* Loop through each field in the update form and save in a way we can use */
					foreach ( $_REQUEST['field'] as $key => $value ) {
						$fields[] = array(
							'key' => $key,
							'value' => $value
						);
					}
					
					/* Loop through the entry data and replace the old values with the new */
					foreach ( $data as $key => $value ) {
						/* If it's an array, that's the only way we update */
						if ( is_array( $value ) ) {
							/* Cast each array as an object */
							$obj = (object) $value;
							
							/* Handle Checkboxes */
							if ( is_array( $fields[ $key ][ 'value' ] ) ){
								$fields[ $key ][ 'value' ] = implode( ', ', $fields[ $key ][ 'value' ] );
							echo $fields[ $key ][ 'value' ];
							}
							/* If the entry's field ID matches our $_REQUEST */
							if ( $obj->id == $fields[ $key ]['key'] ) {

								$newdata[] = array(
									'id' => $obj->id,
									'slug' => $obj->slug,
									'name' => $obj->name,
									'type' => $obj->type,
									'options' => $obj->options,
									'parent_id' => $obj->parent_id,
									'value' => $fields[ $key ]['value']
								);
							}
						}
					}
					
					$where = array(
						'entries_id' => $entry_id
					);
					
					/* Update entry data */
					$wpdb->update( $this->entries_table_name, array( 'data' => serialize( $newdata ) ), $where );
					
				break;
			}
		}
	}	
	
	/**
	 * The jQuery field sorting callback
	 * 
	 * @since 1.0
	 */
	public function process_sort_callback() {
		global $wpdb;
		
		$data = array();

		foreach ( $_REQUEST['order'] as $k ) {
			if ( 'root' !== $k['item_id'] ) {
				$data[] = array(
					'field_id' => $k['item_id'],
					'parent' => $k['parent_id']
					);
			}
		}

		foreach ( $data as $k => $v ) {
			/* Update each field with it's new sequence and parent ID */
			$wpdb->update( $this->field_table_name, array( 'field_sequence' => $k, 'field_parent' => $v['parent'] ), array( 'field_id' => $v['field_id'] ) );
		}

		die(1);
	}
	
	/**
	 * The jQuery field autocomplete callback
	 * 
	 * @since 1.0
	 */
	public function autocomplete_callback() {
		global $wpdb;
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_autocomplete' ) {
			$term = esc_html( $_REQUEST['term'] );
			$form_id = absint( $_REQUEST['form'] );
			$field_id = absint( $_REQUEST['field'] );
			
			$query_fields = "SELECT * FROM $this->field_table_name WHERE form_id = $form_id AND field_id = $field_id ORDER BY field_sequence ASC";
			$fields = $wpdb->get_results( $query_fields );
	
			$suggestions = array();
			
			foreach ( $fields as $field ) {
				$options = unserialize( $field->field_options );
				
				foreach ( $options as $opts ){
					/* Find a match in our list of options */
					$pos = stripos( $opts, $term );
					
					/* If a match was found, add it to the suggestions */
					if ( $pos !== false )
						$suggestions[] = array( 'value' => $opts );
				}
				
				/* Send a JSON-encoded array to our AJAX call */
				echo json_encode( $suggestions );
			}
		}
		
		die(1);
	}
	
	/**
	 * The jQuery unique username callback
	 * 
	 * @since 1.0
	 */
	public function check_username_callback() {
		global $wpdb;
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_check_username' ) {
			$username = esc_html( $_REQUEST['username'] );
			$users = get_users();
			$valid = 'true';
			
			/* Loop through each WP user */
			foreach( $users as $user ) {
				/* If the WP username matches what's entered on the form */
				if ( $user->user_login == $username )
					$valid = 'false';
			}
			
			echo $valid;
		}
		
		die(1);
	}
	
	/**
	 * The Google Chart bar chart callback
	 * 
	 * @since 1.0
	 */
	public function build_chart_callback() {
		global $wpdb;
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_build_chart' ) {
			$form_id = absint( $_REQUEST['form'] );
			$view = esc_html( $_REQUEST['view'] );
			
			/* Setup variables based on which time period we want to see */
			switch( $view ) {
				case 'months' :
					$where = 'Year, Month';
					$label = 'Month';
					$d = $tooltip = 'M Y';
				break;
				
				case 'weeks' :
					$where = 'Year, Week';
					$label = 'Week';
					$d = '\W\e\e\k W';
					$tooltip = '\W\e\e\k W \o\f Y';
				break;
				
				case 'days' :
					$where = 'Year, Month, Day';
					$label = 'Week';
					$d = 'M j';
					$tooltip = 'l, F d, Y';
				break;
			}
			
			/* Get counts of the entries based on the Date/view set above */
			$entries = $wpdb->get_results( "SELECT DAY( date_submitted ) AS Day, MONTH( date_submitted ) as Month, WEEK( date_submitted ) as Week, YEAR( date_submitted ) as Year, COUNT(*) as Count FROM $this->entries_table_name WHERE form_id = $form_id  GROUP BY $where ORDER BY $where" );

			/* Loop through entries and setup our array for JSON output */
			foreach ( $entries as $entry ) {
				$date[] = array(
					'date' => date( $d, mktime( 0, 0, 0, $entry->Month, $entry->Day, $entry->Year ) ),
					'count' => $entry->Count,
					'tooltip' => date( $tooltip, mktime( 0, 0, 0, $entry->Month, $entry->Day, $entry->Year ) )
				);
			}
			
			/* The beginning of the JSON string */
			echo '{"cols": [{"id":"","label":"' . $label . '","pattern":"","type":"string"},
					{"id":"","label":"Entries","pattern":"","type":"number"},
					{"id":"","label":"","pattern":"","type":"string","p":{"role":"tooltip"}
					}
				],"rows": [';
			
			/* Setup our JSON output array */
			$out = array();
			
			foreach ( $date as $val ) {
				$rows = '{"c":[{"v":"' . $val[ 'date' ] . '","f":null},{"v":' . $val[ 'count' ] . ',"f":null},{"v":"' . $val[ 'tooltip' ] . '\\nEntries: ' . $val[ 'count' ] . '"},]}';
				
				/* Push this row to the end of our output array */
				array_push( $out, $rows );
			}
			
			/* Comma separate each row */
			echo implode( ',', $out );
			
			/* The end of the JSON string */
			echo '],"p":{"className":"myDataTable"}}';
		}
		
		die(1);
	}
	
	/**
	 * The Google Chart % Change data table callback
	 * 
	 * @since 1.0
	 */
	public function build_table_callback() {
		global $wpdb;
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_build_table' ) {
			$form_id = absint( $_REQUEST['form'] );
			$view = esc_html( $_REQUEST['view'] );
			
			/* Setup variables based on which time period we want to see */
			switch( $view ) {
				case 'months' :
					$where = 'Year, Month';
					$label = 'Month';
					$d = $tooltip = 'M Y';
				break;
				
				case 'weeks' :
					$where = 'Year, Week';
					$label = 'Week';
					$d = '\W\e\e\k W';
					$tooltip = '\W\e\e\k W \o\f Y';
				break;
				
				case 'days' :
					$where = 'Year, Month, Day';
					$label = 'Day';
					$d = 'M j';
					$tooltip = 'l, F d, Y';
				break;
			}
			
			/* Get counts of the entries based on the Date/view set above */
			$entries = $wpdb->get_results( "SELECT DAY( date_submitted ) AS Day, MONTH( date_submitted ) as Month, WEEK( date_submitted ) as Week, YEAR( date_submitted ) as Year, COUNT(*) as Count FROM $this->entries_table_name WHERE form_id = $form_id  GROUP BY $where ORDER BY $where" );
			
			/* Initialize vars for % change */
			$change = '';
			$last = 0;
			
			foreach ( $entries as $entry ) {
				/* Store % change comparing last period to current one */
				$change = ( $entry->Count - $last ) / $last;
				
				$date[] = array(
					'date' => date( $d, mktime( 0, 0, 0, $entry->Month, $entry->Day, $entry->Year ) ),
					'count' => $entry->Count,
					'change' => $change
				);
				
				/* Store count for future comparison */
				$last = $entry->Count;			
			}

			/* The beginning of the JSON string */
			echo '{"cols": [{"id":"","label":"' . $label . '","pattern":"","type":"string"},
					{"id":"","label":"Entries","pattern":"","type":"number"},
					{"id":"","label":"% Change","pattern":"","type":"number"}
				],"rows": [';

			/* Setup our JSON output array */
			$out = array();
			
			foreach ( $date as $key ) {
				$rows = '{"c":[{"v":"' . $key[ 'date' ] . '","f":null},{"v":' . $key[ 'count' ] . ',"f":null},{"v":' . round( $key[ 'change' ] ) . ',"f":"' . round( $key[ 'change' ] * 100 ) . '%"},]}';
				
				/* Push this row to the end of our output array */
				array_push( $out, $rows );
			}
			
			/* Comma separate each row */
			echo implode( ',', $out );
			
			/* The end of the JSON string */
			echo '],"p":{"className":"percentChange"}}';
		}
		
		die(1);
	}
	
	/**
	 * The Google Chart CSV view data table callback
	 * 
	 * @since 1.0
	 */
	public function build_data_table_callback() {
		global $wpdb;
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_build_geo_chart' ) {
			$form_id = absint( $_REQUEST['form'] );
			$view = esc_html( $_REQUEST['view'] );
			
			/* Get all entries */
			$entries = $wpdb->get_results( "SELECT entries_id, date_submitted, data FROM $this->entries_table_name WHERE form_id = $form_id" );
			
			/* Setup our default columns */
			$cols = array(
				'entries_id' => array(
					'header' => __( 'Entries ID' , 'visual-form-builder-pro'),
					'type' => 'number',
					'data' => array()
					),
				'date_submitted' => array(
					'header' => __( 'Date Submitted' , 'visual-form-builder-pro'),
					'type' => 'string',
					'data' => array()
					)
			);
			
			/* Initialize row index at 0 */
			$row = 0;
			
			/* Loop through all entries */
			foreach ( $entries as $entry ) {
				/* Loop through each entry and its fields */
				foreach ( $entry as $key => $value ) {
					/* Handle each column in the entries table */
					switch ( $key ) {
						case 'entries_id':
							$cols[ $key ][ 'data' ][ $row ] = $value;
						break;
						case 'date_submitted':
							$cols[ $key ][ 'data' ][ $row ] = '"' . $value . '"';
						break;
						case 'data':
							/* Unserialize value only if it was serialized */
							$fields = maybe_unserialize( $value );
							
							/* Loop through our submitted data */
							foreach ( $fields as $field_key => $field_value ) {
								if ( !is_array( $field_value ) ) {

									/* Replace quotes for the header */
									$header = ucwords( $field_key );

									/* Replace all spaces for each form field name */
									$field_key = preg_replace( '/(\s)/i', '', $field_key );
									
									/* Find new field names and make a new column with a header */
									if ( !array_key_exists( $field_key, $cols ) ) {
										$cols[$field_key] = array(
											'header' => $header,
											'type' => 'string',
											'data' => array()
											);									
									}
									
									/* Load data, row by row */
									$cols[ $field_key ][ 'data' ][ $row ] = '"' . stripslashes( html_entity_decode( $field_value ) ) . '"';
								}
								else {
									/* Cast each array as an object */
									$obj = (object) $field_value;

									switch ( $obj->type ) {
										case 'fieldset' :
										case 'section' :
										case 'instructions' :
										case 'submit' :
										break;
										
										default :
											/* Find new field names and make a new column with a header */
											if ( !array_key_exists( $obj->name, $cols ) ) {
												
												$cols[$obj->name] = array(
													'header' => $obj->name,
													'type' => 'string',
													'data' => array()
													);									
											}
											
											/* Load data, row by row */
											$cols[ $obj->name ][ 'data' ][ $row ] = '"' . stripslashes( html_entity_decode( $obj->value ) ) . '"';

										break;
									}
								}
							}
						break;
					}
				}

				$row++;
			}

			echo '{"cols": [';
			
			/* Setup our CSV vars */
			$csv_headers = array();
			$csv_rows = $a = array();

			/* Loop through each column */
			foreach ( $cols as $data ) {
				
				$rows = '{"id":"","label":"' . $data['header'] . '","pattern":"","type":"' . $data['type'] . '"}';
				
				array_push( $csv_headers, $rows );
				
				/* Loop through each row of data and add to our CSV */
				for ( $i = 0; $i < $row; $i++ ) {
					
					/* If there's data at this point, add it to the row */
					if ( array_key_exists( $i, $data['data'] ) )
						$csv_rows[$i] .=  '{"v":' . $data['data'][$i]. '}';
					else
						$csv_rows[$i] .=  '{"v":""}';
					
					/* Add a closing quote for this row's data */
					if ( $i !== ($row) )
						$csv_rows[$i] .= ',';
				}
			}

			/* Setup our JSON output array */
			$out = array();
			
			/* Loop through our rows and add to the output array */
			foreach ( $csv_rows as $row ) {
				array_push( $out, '{"c":[' . rtrim( $row, ',' ) . ']}' );
			}
			
			/* Comma separate our headers */
			echo implode( ',', $csv_headers );
			
			echo '],"rows": [';
			
			/* Comma separate our rows */
			echo implode( ',', $out );			
			
			echo ']}';
		}
		
		die(1);
	}
	
	/**
	 * The jQuery PayPal Assign Price to Fields callback
	 * 
	 * @since 1.0
	 */
	public function paypal_price_callback() {
		global $wpdb;
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_paypal_price' ) {
			$form_id = absint( $_REQUEST['form_id'] );
			$field_id = absint( $_REQUEST['field_id'] );
			
			$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = $form_id AND field_id = $field_id" ) );
			$paypal_price_field = unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT form_paypal_field_price FROM $this->form_table_name WHERE form_id = $form_id" ) ) );

			foreach ( $fields as $field ) {
				/* If a text input field, only display a message */
				if ( in_array( $field->field_type, array( 'text', 'currency' ) ) )
					$price_option = '<p>Amount Based on User Input</p>';
				/* If field has options, let user assign prices to inputs */
				elseif ( in_array( $field->field_type, array( 'select', 'radio', 'checkbox' ) ) ) {
					$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
					
					/* Loop through each option and output */
					foreach ( $options as $option => $value ) {
						$price_option .= '<label>' . stripslashes( $value ) . '<input type="text" value="' . stripslashes( $paypal_price_field['prices'][$option]['amount'] ) . '" name="form_paypal_field_price[prices][' . $option . '][amount]" /></label><br>';
						echo '<input type="hidden" name="form_paypal_field_price[prices][' . $option . '][id]" value="' . stripslashes( $value ) . '" />';
					}
				}
				
				/* Store the name as vfb-field_key-field_id for comparison when setting up PayPal form redirection */
				echo '<input type="hidden" name="form_paypal_field_price[name]" value="vfb-' . $field->field_key . '-' . $field->field_id . '" />';
			}
			
			echo $price_option;
		}

		die(1);
	}
	
	
	
	public function form_settings_callback() {
		global $current_user;
		get_currentuserinfo();
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_form_settings' ) {
			$form_id = absint( $_REQUEST['form'] );
			$status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'opened';
			$accordion = isset( $_REQUEST['accordion'] ) ? $_REQUEST['accordion'] : 'general-settings';
			$user_id = $current_user->ID;
			
			$form_settings = get_user_meta( $user_id, 'vfb-form-settings', true );
			
			$array = array(
				'form_setting_tab' => $status,
				'setting_accordion' => $accordion
			);
			
			/* Set defaults if meta key doesn't exist */	
			if ( !$form_settings || $form_settings == '' ) {
				$meta_value[ $form_id ] = $array;
				
				update_user_meta( $user_id, 'vfb-form-settings', $meta_value );
			}
			else {
				$form_settings[ $form_id ] = $array;
				
				update_user_meta( $user_id, 'vfb-form-settings', $form_settings );
			}
		}
		
		die(1);
	}
	
	

	/**
	 * The jQuery create field callback
	 * 
	 * @since 1.9
	 */
	public function create_field_callback() {
		global $wpdb;
		
		$data = array();
		$field_options = '';
		
		foreach ( $_REQUEST['data'] as $k ) {
			$data[ $k['name'] ] = $k['value'];
		}

		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'toplevel_page_visual-form-builder-pro' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_create_field' ) {
			
			$form_id = absint( $data['form_id'] );
			$field_key = sanitize_title( $_REQUEST['field_type'] );
			$field_type = strtolower( sanitize_title( $_REQUEST['field_type'] ) );
			
			$parent = ( $_REQUEST['parent'] > 0 ) ? $_REQUEST['parent'] : 0;
			$previous = ( $_REQUEST['previous'] > 0 ) ? $_REQUEST['previous'] : 0;
															
			/* If a Page Break, the default name is Next, otherwise use the field type */
			$field_name = ( 'page-break' == $field_type ) ? 'Next' : esc_html( $_REQUEST['field_type'] );

			/* Set defaults for validation */
			switch ( $field_type ) {
				case 'email' :
				case 'url' :
				case 'phone' :
					$field_validation = $field_type;
				break;
				case 'currency' :
					$field_validation = 'number';
				break;
				case 'number' :
				case 'min' :
				case 'max' :
				case 'range' :
					$field_validation = 'digits';
				break;
				case 'time' :
					$field_validation = 'time-12';
				break;
				case 'file-upload' :
					$field_options = serialize( array( 'png|jpe?g|gif' ) );
				break;
				case 'ip-address' :
					$field_validation = 'ipv6';
				break;
				case 'credit-card' :
					$field_validation = 'card';
				break;
				case 'autocomplete' :
					$field_validation = 'auto';
				break;
			}

			check_ajax_referer( 'create-field-' . $data['form_id'], 'nonce' );
			
			/* Get fields info */
			$all_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = $form_id ORDER BY field_sequence ASC" ) );
			$field_sequence = 0;
			
			/* We only want the fields that FOLLOW our parent or previous item */						
			if ( $parent > 0 || $previous > 0 ) {
									
				$cut_off = ( $previous > 0 ) ? $previous : $parent;
										
				foreach( $all_fields as $field_index => $field ) {
				
					if ( $field->field_id == $cut_off ) {
						$field_sequence = $field->field_sequence + 1;
						break;
					}
					else
						unset( $all_fields[ $field_index ] );
				
				}
				array_shift( $all_fields );
				
				/* If the previous had children, we need to remove them so our item is placed correctly */
				if ( !$parent && $previous > 0 ) {
				
					foreach( $all_fields as $field_index => $field ) {
					
						if ( !$field->field_parent )
							break;
						else {
							$field_sequence = $field->field_sequence + 1;
							unset( $all_fields[ $field_index ] );
						}
						
					}
				
				}
				
			}
			
			/* Create the new field's data */
			$newdata = array(
				'form_id' => absint( $data['form_id'] ),
				'field_key' => $field_key,
				'field_name' => $field_name,
				'field_type' => $field_type,
				'field_options' => $field_options,
				'field_sequence' => $field_sequence,
				'field_validation' => $field_validation,
				'field_parent' => $parent
			);
			
			/* Create the field */
			$wpdb->insert( $this->field_table_name, $newdata );
			$insert_id = $wpdb->insert_id;
			
			/* VIP fields */			
			$vip_fields = array( 'verification', 'secret', 'submit' );
			
			/* Rearrange the fields that follow our new data */
			foreach( $all_fields as $field_index => $field ) {
				if ( !in_array( $field->field_type, $vip_fields ) ) {
					$field_sequence++;
					// Update each field with it's new sequence and parent ID
					$wpdb->update( $this->field_table_name, array( 'field_sequence' => $field_sequence ), array( 'field_id' => $field->field_id ) );
				}
			}
			
			/* Move the VIPs */			
			foreach ( $vip_fields as $update ) {
				$field_sequence++;
				$where = array(
					'form_id' => absint( $data['form_id'] ),
					'field_type' => $update
				);				
				$wpdb->update( $this->field_table_name, array( 'field_sequence' => $field_sequence ), $where );
				
			}
			
			echo $this->field_output( $data['form_id'], $insert_id );
		}
		
		die(1);
	}

	
	/**
	 * The jQuery delete field callback
	 * 
	 * @since 1.9
	 */
	public function delete_field_callback() {
		global $wpdb;

		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'toplevel_page_visual-form-builder-pro' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_delete_field' ) {
			$form_id = absint( $_REQUEST['form'] );
			$field_id = absint( $_REQUEST['field'] );
			
			check_ajax_referer( 'delete-field-' . $form_id, 'nonce' );
			
			if ( isset( $_REQUEST['child_ids'] ) ) {
				foreach ( $_REQUEST['child_ids'] as $children ) {
					$parent = absint( $_REQUEST['parent_id'] );
					
					/* Update each child item with the new parent ID */
					$wpdb->update( $this->field_table_name, array( 'field_parent' => $parent ), array( 'field_id' => $children ) );
				}
			}
			
			/* Delete the field */
			$wpdb->query( $wpdb->prepare( "DELETE FROM $this->field_table_name WHERE field_id = %d", $field_id ) );
		}
		
		die(1);
	}

	/**
	 * Build field output in admin
	 * 
	 * @since 1.9
	 */
	public function field_output( $form_nav_selected_id, $field_id = NULL ) {
		global $wpdb;
		
		$field_where = ( isset( $field_id ) && !is_null( $field_id ) ) ? "AND field_id = $field_id" : '';
		/* Display all fields for the selected form */
		$query_fields = "SELECT * FROM $this->field_table_name WHERE form_id = $form_nav_selected_id $field_where ORDER BY field_sequence ASC";
		$fields = $wpdb->get_results( $query_fields );
		
		$depth = 1;
		$parent = $last = 0;
		
		/* Loop through each field and display */
		foreach ( $fields as $field ) :		

			/* If we are at the root level */
			if ( !$field->field_parent && $depth > 1 ) {
				/* If we've been down a level, close out the list */
				while ( $depth > 1 ) {
					echo '</li>
						</ul>';
					$depth--;
				}
				
				/* Close out the root item */
				echo '</li>';
			}
			/* first item of <ul>, so move down a level */
			elseif ( $field->field_parent && $field->field_parent == $last ) {
				echo '<ul class="parent">';
				$depth++;				
			}
			/* Close up a <ul> and move up a level */
			elseif ( $field->field_parent && $field->field_parent != $parent ) {
				echo '</li>
					</ul>
				</li>';
				$depth--;
			}
			/* Same level so close list item */
			elseif ( $field->field_parent && $field->field_parent == $parent )
				echo '</li>';
			
			/* Store item ID and parent ID to test for nesting */										
			$last = $field->field_id;
			$parent = $field->field_parent;
	?>
			<li id="form_item_<?php echo $field->field_id; ?>" class="form-item<?php echo ( in_array( $field->field_type, array( 'submit', 'secret', 'verification' ) ) ) ? ' ui-state-disabled' : ''; ?><?php echo ( !in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ) ) ) ? ' ui-nestedSortable-no-nesting' : ''; ?>">
					<dl class="menu-item-bar">
						<dt class="menu-item-handle<?php echo ( $field->field_type == 'fieldset' ) ? ' fieldset' : ''; ?>">
							<span class="item-title"><?php echo stripslashes( htmlspecialchars_decode( $field->field_name ) ); ?><?php echo ( $field->field_required == 'yes' ) ? ' <span class="is-field-required">*</span>' : ''; ?></span>
                            <span class="item-controls">
								<span class="item-type"><?php echo strtoupper( str_replace( '-', ' ', $field->field_type ) ); ?></span>
								<a href="#" title="<?php _e( 'Edit Field Item' , 'visual-form-builder-pro'); ?>" id="edit-<?php echo $field->field_id; ?>" class="item-edit"><?php _e( 'Edit Field Item' , 'visual-form-builder-pro'); ?></a>
							</span>
						</dt>
					</dl>
		
					<div id="form-item-settings-<?php echo $field->field_id; ?>" class="menu-item-settings field-type-<?php echo $field->field_type; ?>" style="display: none;">
						<?php if ( in_array( $field->field_type, array( 'fieldset', 'section', 'page-break', 'verification' ) ) ) : ?>
						
							<p class="description description-wide">
								<label for="edit-form-item-name-<?php echo $field->field_id; ?>"><?php echo ( in_array( $field->field_type, array( 'fieldset', 'verification' ) ) ) ? 'Legend' : 'Name'; ?>
                                	<span class="vfb-tooltip" rel="For Fieldsets, a Legend is simply the name of that group. Use general terms that describe the fields included in this Fieldset." title="About Legend">(?)</span>
                                    <br />
									<input type="text" value="<?php echo stripslashes( $field->field_name ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
								</label>
                                
							</p>
                            <p class="description description-wide">
                                <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
                                    <?php _e( 'CSS Classes' , 'visual-form-builder-pro'); ?>
                                    <span class="vfb-tooltip" rel="For each field, you can insert your own CSS class names which can be used in your own stylesheets." title="About CSS Classes">(?)</span>
                                    <br />
                                    <input type="text" value="<?php echo stripslashes( htmlspecialchars_decode( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" />
                                </label>
                            </p>
						
						<?php elseif( $field->field_type == 'instructions' ) : ?>
							<!-- Instructions -->
							<p class="description description-wide">
								<label for="edit-form-item-name-<?php echo $field->field_id; ?>">
										<?php _e( 'Name' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Name" rel="A field's name is the most visible and direct way to describe what that field is for.">(?)</span>
                                    	<br />
										<input type="text" value="<?php echo stripslashes( htmlspecialchars_decode( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
								</label>
							</p>
							<p class="description description-wide">
								<label for="edit-form-item-description-<?php echo $field->field_id; ?>">
									<?php _e( 'Description (HTML tags allowed)', 'visual-form-builder-pro' ); ?>
                                	<span class="vfb-tooltip" title="About Instructions Description" rel="The Instructions field allows for long form explanations, typically seen at the beginning of Fieldsets or Sections. HTML tags are allowed.">(?)</span>
                                    <br />
									<textarea name="field_description-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-description-<?php echo $field->field_id; ?>" /><?php echo stripslashes( $field->field_description ); ?></textarea>
								</label>
							</p>
							<p class="description description-wide">
								<label for="edit-form-item-css-<?php echo $field->field_id; ?>">
									<?php _e( 'CSS Classes' , 'visual-form-builder-pro'); ?>
                                    <span class="vfb-tooltip" title="About CSS Classes" rel="For each field, you can insert your own CSS class names which can be used in your own stylesheets.">(?)</span>
                                    <br />
									<input type="text" value="<?php echo stripslashes( htmlspecialchars_decode( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" />
								</label>
							</p>
						
                        <?php elseif( $field->field_type == 'hidden' ) : ?>
							<!-- Hidden -->
							<p class="description description-wide">
								<label for="edit-form-item-name-<?php echo $field->field_id; ?>">
										<?php _e( 'Name' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Name" rel="A field's name is the most visible and direct way to describe what that field is for.">(?)</span>
                                    	<br />
										<input type="text" value="<?php echo stripslashes( htmlspecialchars_decode( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
								</label>
							</p>
                            <!-- Dynamic Variable -->
                            <p class="description description-wide">
                                <label for="edit-form-item-dynamicvar">
                                    <?php _e( 'Dynamic Variable' , 'visual-form-builder-pro'); ?>
                                    <span class="vfb-tooltip" title="About Dynamic Variable" rel="A Dynamic Variable will use a pre-populated value that is determined either by the form, the user, or the post/page viewed.">(?)</span>
                                   	<br />
                                    <?php
									/* If the options field isn't empty, unserialize and build array */
									if ( !empty( $field->field_options ) ) {
										if ( is_serialized( $field->field_options ) )
											$opts_vals = unserialize( $field->field_options );
									}
									?>
                                   <select name="field_options-<?php echo $field->field_id; ?>[]" class="widefat hidden-option" id="edit-form-item-dynamicvar-<?php echo $field->field_id; ?>">
                                        <option value="" <?php selected( $opts_vals[0], '' ); ?>><?php _e( 'Select a Variable or Custom to create your own' , 'visual-form-builder-pro'); ?></option>
                                        <option value="form_id" <?php selected( $opts_vals[0], 'form_id' ); ?>><?php _e( 'Form ID' , 'visual-form-builder-pro'); ?></option>
                                        <option value="form_title" <?php selected( $opts_vals[0], 'form_title' ); ?>><?php _e( 'Form Title' , 'visual-form-builder-pro'); ?></option>
                                        <option value="ip" <?php selected( $opts_vals[0], 'ip' ); ?>><?php _e( 'IP Address' , 'visual-form-builder-pro'); ?></option>
                                        <option value="uid" <?php selected( $opts_vals[0], 'uid' ); ?>><?php _e( 'Unique ID' , 'visual-form-builder-pro'); ?></option>
                                        <option value="post_id" <?php selected( $opts_vals[0], 'post_id' ); ?>><?php _e( 'Post/Page ID' , 'visual-form-builder-pro'); ?></option>
                                        <option value="post_title" <?php selected( $opts_vals[0], 'post_title' ); ?>><?php _e( 'Post/Page Title' , 'visual-form-builder-pro'); ?></option>
                                        <option value="custom" <?php selected( $opts_vals[0], 'custom' ); ?>><?php _e( 'Custom' , 'visual-form-builder-pro'); ?></option>
                                    </select>
                                </label>
                            </p>
                            <!-- Static Variable -->
                            <p class="description description-wide static-vars-<?php echo ( $opts_vals[0] == 'custom' ) ? 'active' : 'inactive'; ?>" id="static-var-<?php echo $field->field_id; ?>">
								<label for="edit-form-item-staticvar-<?php echo $field->field_id; ?>">
									<?php _e( 'Static Variable' , 'visual-form-builder-pro'); ?>
                                    <span class="vfb-tooltip" title="About Static Variable" rel="A Static Variable will always use the value that you enter.">(?)</span>
                                   	<br />
									<input type="text" value="<?php echo stripslashes( htmlspecialchars_decode( $opts_vals[1] ) ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-staticvar-<?php echo $field->field_id; ?>"<?php echo ( $opts_vals[0] !== 'custom' ) ? ' disabled="disabled"' : ''; ?> />
								</label>
							</p>
                            <?php unset( $opts_vals ); ?>

						<?php else: ?>
							
							<!-- Name -->
							<p class="description description-wide">
								<label for="edit-form-item-name-<?php echo $field->field_id; ?>">
									<?php _e( 'Name' , 'visual-form-builder-pro'); ?>
                                    <span class="vfb-tooltip" title="About Name" rel="A field's name is the most visible and direct way to describe what that field is for.">(?)</span>
                                    <br />
									<input type="text" value="<?php echo stripslashes( htmlspecialchars_decode( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
								</label>
							</p>
							<?php if ( $field->field_type !== 'submit' ) : ?>
								<!-- Description -->
								<p class="description description-wide">
									<label for="edit-form-item-description-<?php echo $field->field_id; ?>">
										<?php _e( 'Description' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Description" rel="A description is an optional piece of text that further explains the meaning of this field. Descriptions are displayed below the field. HTML tags are allowed.">(?)</span>
                                    	<br />
										<input type="text" value="<?php echo stripslashes( $field->field_description ); ?>" name="field_description-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-description-<?php echo $field->field_id; ?>" />
									</label>
								</p>
								
								<?php
									/* Display the Options input only for radio, checkbox, select, and autocomplete fields */
									if ( in_array( $field->field_type, array( 'radio', 'checkbox', 'select', 'autocomplete' ) ) ) :
								?>
									<!-- Options -->
									<p class="description description-wide">
										<?php _e( 'Options' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Options" rel="This property allows you to set predefined options to be selected by the user.  Use the plus and minus buttons to add and delete options.  At least one option must exist.">(?)</span>
                                    	<br />
									<?php
										/* If the options field isn't empty, unserialize and build array */
										if ( !empty( $field->field_options ) ) {
											if ( is_serialized( $field->field_options ) )
												$opts_vals = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
										}
										/* Otherwise, present some default options */
										else
											$opts_vals = array( 'Option 1', 'Option 2', 'Option 3' );
										
										/* Basic count to keep track of multiple options */
										$count = 1;
										
										/* Loop through the options */
										foreach ( $opts_vals as $options ) {
									?>
									<div id="clone-<?php echo $field->field_id . '-' . $count; ?>" class="option">
										<label for="edit-form-item-options-<?php echo $field->field_id . "-$count"; ?>" class="clonedOption">
											<input type="text" value="<?php echo stripslashes( $options ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id . "-$count"; ?>" />
										</label>
										
										<a href="#" class="addOption" title="Add an Option">Add</a> <a href="#" class="deleteOption" title="Delete Option">Delete</a>
									</div>
									   <?php 
											$count++;
										}
										?>
									</p>
								<?php
									/* Unset the options for any following radio, checkboxes, or selects */
									unset( $opts_vals );
									endif;
								?>
                                
                                <?php
									/* Display the Options input only for radio, checkbox, select, and autocomplete fields */
									if ( in_array( $field->field_type, array( 'min', 'max', 'range' ) ) ) :
								?>
                                	<!-- Min, Max, and Range -->
									<p class="description description-wide">
                                        <?php
										if ( 'min' == $field->field_type )
											_e( 'Minimum Value' , 'visual-form-builder-pro');
										elseif ( 'max' == $field->field_type )
											_e( 'Maximum Value' , 'visual-form-builder-pro');

										/* If the options field isn't empty, unserialize and build array */
										if ( !empty( $field->field_options ) ) {
											if ( is_serialized( $field->field_options ) )
												$opts_vals = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
										}
										else
											$opts_vals = ( in_array( $field->field_type, array( 'min', 'max' ) ) ) ? array( '10' ) : array( '1', '10' );

										$ranged = false;
										/* Loop through the options */
										foreach ( $opts_vals as $options ) {
											if ( 'range' == $field->field_type ) {
												if ( !$ranged )
													_e( 'Minimum Value' , 'visual-form-builder-pro');
												else
													_e( 'Maximum Value' , 'visual-form-builder-pro');
												
												$ranged = true;
											}
									?>
                                    	<span class="vfb-tooltip" title="About Minimum/Maxium Value" rel="Set a minimum and/or maximum value users must enter in order to successfully complete the field.">(?)</span>
                                    	<br />
										<label for="edit-form-item-options-<?php echo $field->field_id; ?>">
											<input type="text" value="<?php echo stripslashes( $options ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id . "-$count"; ?>" />
										</label>
									   <?php 
										}
										?>
                                    </p>
                                <?php
									/* Unset the options for any following radio, checkboxes, or selects */
									unset( $opts_vals );
									endif;
								?>
                                
                                <?php
									/* Display the Options input only for radio, checkbox, select, and autocomplete fields */
									if ( in_array( $field->field_type, array( 'file-upload' ) ) ) :
								?>
                                	<!-- File Upload Accepts -->
									<p class="description description-wide">
                                    	<?php _e( 'Accepted File Extensions' , 'visual-form-builder-pro'); ?>
                                        <?php
										$opts_vals = array( '' );
										
										/* If the options field isn't empty, unserialize and build array */
										if ( !empty( $field->field_options ) ) {
											if ( is_serialized( $field->field_options ) )
												$opts_vals = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : unserialize( $field->field_options );
										}
										
										/* Loop through the options */
										foreach ( $opts_vals as $options ) {
									?>
                                    	<span class="vfb-tooltip" title="About Accepted File Extensions" rel="Control the types of files allowed.  Enter extensions without periods and separate multiples using the pipe character ( | ).">(?)</span>
                                    	<br />
										<label for="edit-form-item-options-<?php echo $field->field_id; ?>">
											<input type="text" value="<?php echo stripslashes( $options ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id; ?>" />
										</label>
                                    </p>
                                <?php
										}
									/* Unset the options for any following radio, checkboxes, or selects */
									unset( $opts_vals );
									endif;
								?>
								
								<!-- Validation -->
								<p class="description description-thin">
									<label for="edit-form-item-validation">
										Validation
                                        <span class="vfb-tooltip" title="About Validation" rel="Ensures user-entered data is formatted properly. For more information on Validation, refer to the Help tab at the top of this page.">(?)</span>
                                    	<br />
									   <select name="field_validation-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-validation-<?php echo $field->field_id; ?>"<?php echo ( in_array( $field->field_type, array( 'radio', 'select', 'checkbox', 'address', 'date', 'textarea', 'html', 'file-upload', 'autocomplete', 'color-picker', 'secret' ) ) ) ? ' disabled="disabled"' : ''; ?>>
											<?php if ( $field->field_type == 'time' ) : ?>
											<option value="time-12" <?php selected( $field->field_validation, 'time-12' ); ?>><?php _e( '12 Hour Format' , 'visual-form-builder-pro'); ?></option>
											<option value="time-24" <?php selected( $field->field_validation, 'time-24' ); ?>><?php _e( '24 Hour Format' , 'visual-form-builder-pro'); ?></option>
											<?php elseif ( $field->field_type == 'ip-address' ) : ?>
                                            <option value="ipv4" <?php selected( $field->field_validation, 'ipv4' ); ?>><?php _e( 'IPv4' , 'visual-form-builder-pro'); ?></option>
                                            <option value="ipv6" <?php selected( $field->field_validation, 'ipv6' ); ?>><?php _e( 'IPv6' , 'visual-form-builder-pro'); ?></option>
											<?php elseif ( in_array( $field->field_type, array( 'min', 'max', 'range' ) ) ) : ?>
                                            <option value="number" <?php selected( $field->field_validation, 'number' ); ?>><?php _e( 'Number' , 'visual-form-builder-pro'); ?></option>
											<option value="digits" <?php selected( $field->field_validation, 'digits' ); ?>><?php _e( 'Digits' , 'visual-form-builder-pro'); ?></option>
											<?php else : ?>
											<option value="" <?php selected( $field->field_validation, '' ); ?>><?php _e( 'None' , 'visual-form-builder-pro'); ?></option>
											<option value="email" <?php selected( $field->field_validation, 'email' ); ?>><?php _e( 'Email' , 'visual-form-builder-pro'); ?></option>
											<option value="url" <?php selected( $field->field_validation, 'url' ); ?>><?php _e( 'URL' , 'visual-form-builder-pro'); ?></option>
											<option value="date" <?php selected( $field->field_validation, 'date' ); ?>><?php _e( 'Date' , 'visual-form-builder-pro'); ?></option>
											<option value="number" <?php selected( $field->field_validation, 'number' ); ?>><?php _e( 'Number' , 'visual-form-builder-pro'); ?></option>
											<option value="digits" <?php selected( $field->field_validation, 'digits' ); ?>><?php _e( 'Digits' , 'visual-form-builder-pro'); ?></option>
											<option value="phone" <?php selected( $field->field_validation, 'phone' ); ?>><?php _e( 'Phone' , 'visual-form-builder-pro'); ?></option>
                                            <option value="card" <?php selected( $field->field_validation, 'card' ); ?>><?php _e( 'Credit Card' , 'visual-form-builder-pro'); ?></option>
											<?php endif; ?>
										</select>
									</label>
								</p>
								
								<!-- Required -->
								<p class="field-link-target description description-thin">
									<label for="edit-form-item-required">
										<?php _e( 'Required' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Required" rel="Requires the field to be completed before the form is submitted. By default, all fields are set to No.">(?)</span>
                                    	<br />
										<select name="field_required-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-required-<?php echo $field->field_id; ?>">
											<option value="no" <?php selected( $field->field_required, 'no' ); ?>><?php _e( 'No' , 'visual-form-builder-pro'); ?></option>
											<option value="yes" <?php selected( $field->field_required, 'yes' ); ?>><?php _e( 'Yes' , 'visual-form-builder-pro'); ?></option>
										</select>
									</label>
								</p>
							   
								<?php if ( !in_array( $field->field_type, array( 'radio', 'checkbox', 'time' ) ) ) : ?>
									<!-- Size -->
									<p class="description description-thin">
										<label for="edit-form-item-size">
											<?php _e( 'Size' , 'visual-form-builder-pro'); ?>
                                            <span class="vfb-tooltip" title="About Size" rel="Control the size of the field.  By default, all fields are set to Medium.">(?)</span>
                                    		<br />
											<select name="field_size-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-size-<?php echo $field->field_id; ?>">
												<option value="small" <?php selected( $field->field_size, 'small' ); ?>><?php _e( 'Small' , 'visual-form-builder-pro'); ?></option>
                                                <option value="medium" <?php selected( $field->field_size, 'medium' ); ?>><?php _e( 'Medium' , 'visual-form-builder-pro'); ?></option>
												<option value="large" <?php selected( $field->field_size, 'large' ); ?>><?php _e( 'Large' , 'visual-form-builder-pro'); ?></option>
											</select>
										</label>
									</p>
                                <?php elseif ( in_array( $field->field_type, array( 'radio', 'checkbox', 'time' ) ) ) : ?>
									<!-- Options Layout -->
									<p class="description description-thin">
										<label for="edit-form-item-size">
											<?php _e( 'Options Layout' , 'visual-form-builder-pro'); ?>
                                            <span class="vfb-tooltip" title="About Options Layout" rel="Control the layout of radio buttons or checkboxes.  By default, options are arranged in One Column.">(?)</span>
                                    		<br />
											<select name="field_size-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-size-<?php echo $field->field_id; ?>"<?php echo ( $field->field_type == 'time' ) ? ' disabled="disabled"' : ''; ?>>
												<option value="" <?php selected( $field->field_size, '' ); ?>><?php _e( 'One Column' , 'visual-form-builder-pro'); ?></option>
                                                <option value="two-column" <?php selected( $field->field_size, 'two-column' ); ?>><?php _e( 'Two Columns' , 'visual-form-builder-pro'); ?></option>
												<option value="three-column" <?php selected( $field->field_size, 'three-column' ); ?>><?php _e( 'Three Columns' , 'visual-form-builder-pro'); ?></option>
                                                <option value="auto-column" <?php selected( $field->field_size, 'auto-column' ); ?>><?php _e( 'Auto Width' , 'visual-form-builder-pro'); ?></option>
											</select>
										</label>
									</p>
                                
								<?php endif; ?>
									<!-- Field Layout -->
									<p class="description description-thin">
										<label for="edit-form-item-layout">
											<?php _e( 'Field Layout' , 'visual-form-builder-pro'); ?>
                                            <span class="vfb-tooltip" title="About Field Layout" rel="Used to create advanced layouts. Align fields side by side in various configurations.">(?)</span>
	                                    <br />
											<select name="field_layout-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-layout-<?php echo $field->field_id; ?>">
                                            	
												<option value="" <?php selected( $field->field_layout, '' ); ?>><?php _e( 'Default' , 'visual-form-builder-pro'); ?></option>
                                                <optgroup label="------------">
                                                <option value="left-half" <?php selected( $field->field_layout, 'left-half' ); ?>><?php _e( 'Left Half' , 'visual-form-builder-pro'); ?></option>
                                                <option value="right-half" <?php selected( $field->field_layout, 'right-half' ); ?>><?php _e( 'Right Half' , 'visual-form-builder-pro'); ?></option>
                                                </optgroup>
                                                <optgroup label="------------">
												<option value="left-third" <?php selected( $field->field_layout, 'left-third' ); ?>><?php _e( 'Left Third' , 'visual-form-builder-pro'); ?></option>
                                                <option value="middle-third" <?php selected( $field->field_layout, 'middle-third' ); ?>><?php _e( 'Middle Third' , 'visual-form-builder-pro'); ?></option>
                                                <option value="right-third" <?php selected( $field->field_layout, 'right-third' ); ?>><?php _e( 'Right Third' , 'visual-form-builder-pro'); ?></option>
                                                </optgroup>
                                                <optgroup label="------------">
                                                <option value="left-two-thirds" <?php selected( $field->field_layout, 'left-two-thirds' ); ?>><?php _e( 'Left Two Thirds' , 'visual-form-builder-pro'); ?></option>
                                                <option value="right-two-thirds" <?php selected( $field->field_layout, 'right-two-thirds' ); ?>><?php _e( 'Right Two Thirds' , 'visual-form-builder-pro'); ?></option>
                                                </optgroup>
											</select>
										</label>
									</p>
								<p class="description description-wide">
                                    <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
                                        <?php _e( 'CSS Classes' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About CSS Classes" rel="For each field, you can insert your own CSS class names which can be used in your own stylesheets.">(?)</span>
                                    	<br />
                                        <input type="text" value="<?php echo stripslashes( htmlspecialchars_decode( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" maxlength="255" />
                                    </label>
								</p>
							<?php endif; ?>
						<?php endif; ?>
						
						<?php if ( !in_array( $field->field_type, array( 'verification', 'secret', 'submit' ) ) ) : ?>
							<div class="menu-item-actions description-wide submitbox">
								<a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=visual-form-builder-pro&amp;action=delete_field&amp;form=' . $form_nav_selected_id . '&amp;field=' . $field->field_id ), 'delete-field-' . $form_nav_selected_id ) ); ?>" class="item-delete submitdelete deletion"><?php _e( 'Remove' , 'visual-form-builder-pro'); ?></a>
							</div>
						<?php endif; ?>
						
					<input type="hidden" name="field_id[<?php echo $field->field_id; ?>]" value="<?php echo $field->field_id; ?>" />
					</div>
	<?php
		endforeach;
		
		/* This assures all of the <ul> and <li> are closed */
		if ( $depth > 1 ) {
			while( $depth > 1 ) {
				echo '</li>
					</ul>';
				$depth--;
			}
		}
		
		/* Close out last item */
		echo '</li>';
	}
	
	/**
	 * Builds the options settings page
	 * 
	 * @since 1.0
	 */
	public function admin() {
		global $wpdb;
		
		/* Set variables depending on which tab is selected */
		$form_nav_selected_id = ( isset( $_REQUEST['form'] ) ) ? $_REQUEST['form'] : '0';
		$action = ( isset( $_REQUEST['form'] ) && $_REQUEST['form'] !== '0' ) ? 'update_form' : 'create_form';
		$details_meta = ( isset( $_REQUEST['details'] ) ) ? $_REQUEST['details'] : 'email';
		
		/* Query to get all forms */
		$order = sanitize_sql_orderby( 'form_id DESC' );
		$query = "SELECT * FROM $this->form_table_name ORDER BY $order";
		
		/* Build our forms as an object */
		$forms = $wpdb->get_results( $query );
		
		/* Loop through each form and assign a form id, if any */
		foreach ( $forms as $form ) {
			$form_id = ( $form_nav_selected_id == $form->form_id ) ? $form->form_id : '';
			
			/* If we are on a form, set the form name for the shortcode box */
			if ( $form_nav_selected_id == $form->form_id )
				$form_name = stripslashes( $form->form_title );	
		}
		
	?>
	
		<div class="wrap">
			<?php screen_icon( 'options-general' ); ?>
			<h2><?php _e('Visual Form Builder Pro', 'visual-form-builder-pro'); ?></h2>            
            <ul class="subsubsub">
                <?php if ( current_user_can( 'create_users' ) ) : ?>
                    <li><a<?php echo ( !isset( $_REQUEST['view'] ) ) ? ' class="current"' : ''; ?> href="<?php echo admin_url( 'admin.php?page=visual-form-builder-pro' ); ?>"><?php _e( 'Forms' , 'visual-form-builder-pro'); ?></a> |</li>
                <?php endif; ?>
                
                <?php if ( current_user_can( 'manage_categories' ) ) : ?>
                    <li><a<?php echo ( isset( $_REQUEST['view'] ) && in_array( $_REQUEST['view'], array( 'entries' ) ) ) ? ' class="current"' : ''; ?> href="<?php echo add_query_arg( 'view', 'entries', admin_url( 'admin.php?page=visual-form-builder-pro' ) ); ?>"><?php _e( 'Entries' , 'visual-form-builder-pro'); ?></a> |</li>
                <?php endif; ?>
                
                <?php if ( current_user_can( 'create_users' ) ) : ?>
                    <li><a<?php echo ( isset( $_REQUEST['view'] ) && in_array( $_REQUEST['view'], array( 'design' ) ) ) ? ' class="current"' : ''; ?> href="<?php echo add_query_arg( 'view', 'design', admin_url( 'admin.php?page=visual-form-builder-pro' ) ); ?>"><?php _e( 'Email Design' , 'visual-form-builder-pro'); ?></a> |</li>
                <?php endif; ?>
                
                <?php if ( current_user_can( 'manage_categories' ) ) : ?>
                    <li><a<?php echo ( isset( $_REQUEST['view'] ) && in_array( $_REQUEST['view'], array( 'reports' ) ) ) ? ' class="current"' : ''; ?> href="<?php echo add_query_arg( 'view', 'reports', admin_url( 'admin.php?page=visual-form-builder-pro' ) ); ?>"><?php _e( 'Analytics' , 'visual-form-builder-pro'); ?></a></li>
                <?php endif; ?>
            </ul>
            
            <?php
				/* Display the Entries */
				if ( isset( $_REQUEST['view'] ) && in_array( $_REQUEST['view'], array( 'entries' ) ) && current_user_can( 'manage_categories' ) ) : 
				
					$entries_list = new VisualFormBuilder_Pro_Entries_List();
					$entries_detail = new VisualFormBuilder_Pro_Entries_Detail();
					
					if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'view' ) ) ) :
						$entries_detail->entries_detail();
					else :
						$entries_list->prepare_items();
			?>
                <form id="entries-filter" method="post" action="">
                    <?php $entries_list->display(); ?>
                </form>
            <?php
					endif;
				
				elseif ( isset( $_REQUEST['view'] ) && in_array( $_REQUEST['view'], array( 'design' ) )  && current_user_can( 'create_users' ) ) : 
					$design = new VisualFormBuilder_Pro_Designer();
					$design->design_options();
				
				elseif ( isset( $_REQUEST['view'] ) && in_array( $_REQUEST['view'], array( 'reports' ) ) && current_user_can( 'manage_categories' ) ) : 
					$analytics = new VisualFormBuilder_Pro_Analytics();
					$analytics->display();
				
				/* Display the Forms */
				elseif ( current_user_can( 'create_users' ) ):	
					echo ( isset( $this->message ) ) ? $this->message : ''; ?>          
            <div id="nav-menus-frame">
                <div id="menu-settings-column" class="metabox-holder<?php echo ( empty( $form_nav_selected_id ) ) ? ' metabox-holder-disabled' : ''; ?>">
                    <div id="side-sortables" class="metabox-holder">
                    <form id="form-items" class="nav-menu-meta" method="post" action="">
                        <input name="action" type="hidden" value="create_field" />
						<input name="form_id" type="hidden" value="<?php echo $form_nav_selected_id; ?>" />
                        <?php
							/* Security nonce */
							wp_nonce_field( 'create-field-' . $form_nav_selected_id );
							
							/* Disable the left box if there's no active form selected */
                        	$disabled = ( empty( $form_nav_selected_id ) ) ? ' disabled="disabled"' : '';
						?>
                            <div class="postbox">
                                <h3 class="hndle"><span><?php _e( 'Form Items' , 'visual-form-builder-pro'); ?></span></h3>
                                <div class="inside" >
                                    <div class="taxonomydiv">
                                        <p><strong><?php _e( 'Click or Drag' , 'visual-form-builder-pro'); ?></strong> <?php _e( 'to Add a Field' , 'visual-form-builder-pro'); ?> <img id="add-to-form" alt="" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="waiting" /></p>
                                        <ul class="posttype-tabs add-menu-item-tabs" id="vfb-field-tabs">
                                            <li class="tabs"><a href="#standard-fields" class="nav-tab-link vfb-field-types"><?php _e( 'Standard' , 'visual-form-builder-pro'); ?></a></li>
                                            <li><a href="#advanced-fields" class="nav-tab-link vfb-field-types"><?php _e( 'Advanced' , 'visual-form-builder-pro'); ?></a></li>
                                        </ul>
                                        <div id="standard-fields" class="tabs-panel tabs-panel-active">
                                            <ul>
                                                <li><input type="submit" id="form-element-fieldset" class="button-secondary" name="field_type" value="Fieldset"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-section" class="button-secondary" name="field_type" value="Section"<?php echo $disabled; ?> /></li>
                                                <li><input type="submit" id="form-element-text" class="button-secondary" name="field_type" value="Text"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-textarea" class="button-secondary" name="field_type" value="Textarea"<?php echo $disabled; ?> /></li>
                                                <li><input type="submit" id="form-element-checkbox" class="button-secondary" name="field_type" value="Checkbox"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-radio" class="button-secondary" name="field_type" value="Radio"<?php echo $disabled; ?> /></li>
                                                
                                                <li><input type="submit" id="form-element-select" class="button-secondary" name="field_type" value="Select"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-address" class="button-secondary" name="field_type" value="Address"<?php echo $disabled; ?> /></li>
                                                <li><input type="submit" id="form-element-datepicker" class="button-secondary" name="field_type" value="Date"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-email" class="button-secondary" name="field_type" value="Email"<?php echo $disabled; ?> /></li>
                                                
                                                <li><input type="submit" id="form-element-url" class="button-secondary" name="field_type" value="URL"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-currency" class="button-secondary" name="field_type" value="Currency"<?php echo $disabled; ?> /></li>
                                                <li><input type="submit" id="form-element-digits" class="button-secondary" name="field_type" value="Number"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-time" class="button-secondary" name="field_type" value="Time"<?php echo $disabled; ?> /></li>
                                                
                                                <li><input type="submit" id="form-element-phone" class="button-secondary" name="field_type" value="Phone"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-html" class="button-secondary" name="field_type" value="HTML"<?php echo $disabled; ?> /></li>
                                                
                                                <li><input type="submit" id="form-element-file" class="button-secondary" name="field_type" value="File Upload"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-instructions" class="button-secondary" name="field_type" value="Instructions"<?php echo $disabled; ?> /></li>
                                            </ul>
                                        </div>
                                        <div id="advanced-fields"class="tabs-panel tabs-panel-inactive">
                                            <ul>
                                                <li><input type="submit" id="form-element-username" class="button-secondary" name="field_type" value="Username"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-password" class="button-secondary" name="field_type" value="Password"<?php echo $disabled; ?> /></li>
                                                <li><input type="submit" id="form-element-hidden" class="button-secondary" name="field_type" value="Hidden"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-color" class="button-secondary" name="field_type" value="Color Picker"<?php echo $disabled; ?> /></li>
                                                <li><input type="submit" id="form-element-autocomplete" class="button-secondary" name="field_type" value="Autocomplete"<?php echo $disabled; ?> /></li>
	                                            <li class="no-right"><input type="submit" id="form-element-ip" class="button-secondary" name="field_type" value="IP Address"<?php echo $disabled; ?> /></li>
                                                
                                                <li><input type="submit" id="form-element-min" class="button-secondary" name="field_type" value="Min"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-max" class="button-secondary" name="field_type" value="Max"<?php echo $disabled; ?> /></li>
                                                
                                                <li><input type="submit" id="form-element-range" class="button-secondary" name="field_type" value="Range"<?php echo $disabled; ?> /></li>
                                                <li class="no-right"><input type="submit" id="form-element-pagebreak" class="button-secondary" name="field_type" value="Page Break"<?php echo $disabled; ?> /></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                      </form>
                            <div class="postbox">
                                <h3 class="hndle"><span><?php _e( 'Form Output' , 'visual-form-builder-pro'); ?></span></h3>
                                <div class="inside">
                                    <div id="customlinkdiv" class="customlinkdiv">
                                        <p><?php _e( 'Copy this shortcode and paste into any Post or Page.' , 'visual-form-builder-pro'); ?> <?php echo ( $form_nav_selected_id !== '0') ? "This will display the <strong>$form_name</strong> form." : ''; ?></p>
                                        <p id="menu-item-url-wrap">
                                		<form action="">      
                                            <label class="howto">
                                                <span>Shortcode</span>
                                                <input id="form-copy-to-clipboard" type="text" class="code menu-item-textbox" value="<?php echo ( $form_nav_selected_id !== '0') ? "[vfb id=$form_nav_selected_id]" : ''; ?>"<?php echo $disabled; ?> style="width:75%;" />
                                            </label>
                               			 </form>
                                        </p>
                                    </div>
                                </div>
                            </div> 
                	</div>
            	</div>
                
                <div id="menu-management-liquid">
                    <div id="menu-management">
                       	<div class="nav-tabs-nav">
                        	<div class="nav-tabs-arrow nav-tabs-arrow-left"><a>&laquo;</a></div>
                            <div class="nav-tabs-wrapper">
                                <div class="nav-tabs">
                                    <?php
										/* Loop through each for and build the tabs */
										foreach ( $forms as $form ) {
											
											/* Control selected tab */
											if ( $form_nav_selected_id == $form->form_id ) :
												echo '<span class="nav-tab nav-tab-active">' . stripslashes( $form->form_title ) . '</span>';
												$form_id = $form->form_id;
												$form_title = stripslashes( $form->form_title );
												$form_subject = stripslashes( $form->form_email_subject );
												$form_email_from_name = stripslashes( $form->form_email_from_name );
												$form_email_from = stripslashes( $form->form_email_from);
												$form_email_from_override = stripslashes( $form->form_email_from_override);
												$form_email_from_name_override = stripslashes( $form->form_email_from_name_override);
												$form_email_to = ( is_array( unserialize( $form->form_email_to ) ) ) ? unserialize( $form->form_email_to ) : explode( ',', unserialize( $form->form_email_to ) );
												$form_success_type = stripslashes( $form->form_success_type );
												$form_success_message = stripslashes( $form->form_success_message );
												$form_notification_setting = stripslashes( $form->form_notification_setting );
												$form_notification_email_name = stripslashes( $form->form_notification_email_name );
												$form_notification_email_from = stripslashes( $form->form_notification_email_from );
												$form_notification_email = stripslashes( $form->form_notification_email );
												$form_notification_subject = stripslashes( $form->form_notification_subject );
												$form_notification_message = stripslashes( $form->form_notification_message );
												$form_notification_entry = stripslashes( $form->form_notification_entry );
												
												$form_paypal_setting = stripslashes( $form->form_paypal_setting );
												$form_paypal_email = stripslashes( $form->form_paypal_email );
												$form_paypal_currency = stripslashes( $form->form_paypal_currency );
												$form_paypal_shipping = stripslashes( $form->form_paypal_shipping );
												$form_paypal_tax = stripslashes( $form->form_paypal_tax );
												$form_paypal_field_price = unserialize( $form->form_paypal_field_price );
												$form_paypal_item_name = stripslashes( $form->form_paypal_item_name );
												
												$form_label_alignment = stripslashes( $form->form_label_alignment );

												/* Only show required text fields for the sender name override */
												$sender_query 	= "SELECT * FROM $this->field_table_name WHERE form_id = $form_nav_selected_id AND field_type='text' AND field_validation = '' AND field_required = 'yes'";
												$senders = $wpdb->get_results( $sender_query );
												
												/* Only show required email fields for the email override */
												$email_query = "SELECT * FROM $this->field_table_name WHERE (form_id = $form_nav_selected_id AND field_type='text' AND field_validation = 'email' AND field_required = 'yes') OR (form_id = $form_nav_selected_id AND field_type='email' AND field_validation = 'email' AND field_required = 'yes')";
												$emails = $wpdb->get_results( $email_query );
												
												/* Only show required email fields for the email override */
												$paypal_query = "SELECT * FROM $this->field_table_name WHERE (form_id = $form_nav_selected_id AND (field_type='text' OR field_type='currency' OR field_type='select' OR field_type='radio' OR field_type='checkbox')) ORDER BY field_sequence ASC";
												$paypal_fields = $wpdb->get_results( $paypal_query );
											
											else :
												echo '<a href="' . esc_url( add_query_arg( array( 'form' => $form->form_id ), admin_url( 'admin.php?page=visual-form-builder-pro' ) ) ) . '" class="nav-tab" id="' . $form->form_key . '">' . stripslashes( $form->form_title ) . '</a>';
											endif;
											
										}
										
										/* Displays the build new form tab */
										if ( '0' == $form_nav_selected_id ) :
									?>
                                    	<span class="nav-tab menu-add-new nav-tab-active"><?php printf( '<abbr title="%s">+</abbr>', esc_html__( 'Add form' , 'visual-form-builder-pro') ); ?></span>
									<?php else : ?>
                                    	<a href="<?php echo esc_url( add_query_arg( array( 'form' => 0 ), admin_url( 'admin.php?page=visual-form-builder-pro' ) ) ); ?>" class="nav-tab menu-add-new"><?php printf( '<abbr title="%s">+</abbr>', esc_html__( 'Add form' , 'visual-form-builder-pro') ); ?></a>
									<?php endif; ?>
                                </div>
                            </div>
                            <div class="nav-tabs-arrow nav-tabs-arrow-right"><a>&raquo;</a></div>
                        </div>

                        <div class="menu-edit">
                        	<form method="post" id="visual-form-builder-update" action="">
                            	<input name="action" type="hidden" value="<?php echo $action; ?>" />
								<input name="form_id" type="hidden" value="<?php echo $form_nav_selected_id; ?>" />
                                <?php wp_nonce_field( "$action-$form_nav_selected_id" ); ?>
                            	<div id="nav-menu-header">
                                	<div id="submitpost" class="submitbox">
                                    	<div class="major-publishing-actions">
                                        	<label for="form-name" class="menu-name-label howto open-label">
                                                <span class="sender-labels"><?php _e( 'Form Name' , 'visual-form-builder-pro'); ?></span>
                                                <input type="text" value="<?php echo ( isset( $form_title ) ) ? $form_title : ''; ?>" title="Enter form name here" class="menu-name regular-text menu-item-textbox" id="form-name" name="form_title" />
                                            </label>
                                            <?php 
												/* Display sender details and confirmation message if we're on a form, otherwise just the form name */
												if ( $form_nav_selected_id !== '0' ) : 
											?>
                                            <br class="clear" />
                                            
                                            <?php
												/* Get the current user */
												global $current_user;
												get_currentuserinfo();
												
												/* Save current user ID */
												$user_id = $current_user->ID;
												
												/* Get the Form Setting drop down and accordion settings, if any */
												$user_form_settings = get_user_meta( $user_id, 'vfb-form-settings' );
												
												/* Setup defaults for the Form Setting tab and accordion */
												$settings_tab = 'closed';
												$settings_accordion = 'general-settings';
												
												/* Loop through the user_meta array */
												foreach( $user_form_settings as $set ) {
													/* If form settings exist for this form, use them instead of the defaults */
													if ( isset( $set[ $form_id ] ) ) {
														$settings_tab = $set[ $form_id ]['form_setting_tab'];
														$settings_accordion = $set[ $form_id ]['setting_accordion'];
													}
												}
												
												/* If tab is opened, set current class */
												$opened_tab = ( $settings_tab == 'opened' ) ? 'current' : '';
											?>
                                            
                                            
                                            <div class="button-group">
												<a href="#form-settings" id="form-settings-button" class="vfb-button vfb-first <?php echo $opened_tab; ?>"><?php _e( 'Form Settings' , 'visual-form-builder-pro'); ?><span class="button-icon arrow"></span></a>
                                                <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=visual-form-builder-pro&amp;action=copy_form&amp;form=' . $form_nav_selected_id ), 'copy-form-' . $form_nav_selected_id ) ); ?>" class="vfb-button vfb-duplicate"><?php _e( 'Duplicate Form' , 'visual-form-builder-pro'); ?><span class="button-icon plus"></span></a>
                                                <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=visual-form-builder-pro&amp;action=delete_form&amp;form=' . $form_nav_selected_id ), 'delete-form-' . $form_nav_selected_id ) ); ?>" class="vfb-button vfb-delete vfb-last menu-delete"><?php _e( 'Delete Form' , 'visual-form-builder-pro'); ?><span class="button-icon delete"></span></a>
                                            </div>
                                            
                                            <div id="form-settings" class="<?php echo $opened_tab; ?>">
                                                <!-- General settings section -->
                                                <a href="#general-settings" class="settings-links<?php echo ( $settings_accordion == 'general-settings' ) ? ' on' : ''; ?>">1. General<span class="arrow"></span></a>
                                                <div id="general-settings" class="form-details<?php echo ( $settings_accordion == 'general-settings' ) ? ' on' : ''; ?>">
                                                    <!-- Label Alignment -->
                                                    <p class="description description-wide">
                                                    <label for="form-label-alignment">
                                                        <?php _e( 'Label Alignment' , 'visual-form-builder-pro'); ?>
                                                        <span class="vfb-tooltip" title="About Label Alignment" rel="Set the field labels for this form to be aligned either on top, to the left, or to the right.  By default, all labels are aligned on top of the inputs.">(?)</span>
                                    					<br />
                                                     </label>
                                                        <select name="form_label_alignment" id="form-label-alignment" class="widefat">
                                                            <option value="" <?php selected( $form_label_alignment, '' ); ?>><?php _e( 'Top Aligned' , 'visual-form-builder-pro'); ?></option>
                                                            <option value="left-label" <?php selected( $form_label_alignment, 'left-label' ); ?>><?php _e( 'Left Aligned' , 'visual-form-builder-pro'); ?></option>
                                                            <option value="right-label" <?php selected( $form_label_alignment, 'right-label' ); ?>><?php _e( 'Right Aligned' , 'visual-form-builder-pro'); ?></option>                                                        
                                                        </select>
                                                    </p>
                                                    <br class="clear" />
                                                </div>
                                                
                                                <!-- Email section -->
                                                <a href="#email-details" class="settings-links<?php echo ( $settings_accordion == 'email-details' ) ? ' on' : ''; ?>">2. Email<span class="arrow"></span></a>
                                                <div id="email-details" class="form-details<?php echo ( $settings_accordion == 'email-details' ) ? ' on' : ''; ?>">
                                                    
                                                    <p><em><?php _e( 'The forms you build here will send information to one or more email addresses when submitted by a user on your site.  Use the fields below to customize the details of that email.' , 'visual-form-builder-pro'); ?></em></p>
    
                                                    <!-- E-mail Subject -->
                                                    <p class="description description-wide">
                                                    <label for="form-email-subject">
                                                        <?php _e( 'E-mail Subject' , 'visual-form-builder-pro'); ?>
                                                        <span class="vfb-tooltip" title="About E-mail Subject" rel="This option sets the subject of the email that is sent to the emails you have set in the E-mail(s) To field.">(?)</span>
                                    					<br />
                                                        <input type="text" value="<?php echo stripslashes( $form_subject ); ?>" class="widefat" id="form-email-subject" name="form_email_subject" />
                                                    </label>
                                                    </p>
                                                    <br class="clear" />
    
                                                    <!-- Sender Name -->
                                                    <p class="description description-thin">
                                                    <label for="form-email-sender-name">
                                                        <?php _e( 'Your Name or Company' , 'visual-form-builder-pro'); ?>
                                                        <span class="vfb-tooltip" title="About Your Name or Company" rel="This option sets the From display name of the email that is sent to the emails you have set in the E-mail(s) To field.">(?)</span>
                                    					<br />
                                                        <input type="text" value="<?php echo $form_email_from_name; ?>" class="widefat" id="form-email-sender-name" name="form_email_from_name"<?php echo ( $form_email_from_name_override != '' ) ? ' readonly="readonly"' : ''; ?> />
                                                    </label>
                                                    </p>
                                                    <p class="description description-thin">
                                                    	<label for="form_email_from_name_override">
                                                        	<?php _e( "User's Name (optional)" , 'visual-form-builder-pro'); ?>
                                                            <span class="vfb-tooltip" title="About User's Name" rel="Select a required text field from your form to use as the From display name in the email.">(?)</span>
                                    						<br />
                                                        <select name="form_email_from_name_override" id="form_email_from_name_override" class="widefat">
                                                            <option value="" <?php selected( $form_email_from_name_override, '' ); ?>><?php _e( 'Select a required text field' , 'visual-form-builder-pro'); ?></option>
                                                            <?php 
                                                            foreach( $senders as $sender ) {
                                                                echo '<option value="' . $sender->field_id . '"' . selected( $form_email_from_name_override, $sender->field_id ) . '>' . stripslashes( $sender->field_name ) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                        </label>
                                                    </p>
                                                    <br class="clear" />
                                                    
                                                    <!-- Sender E-mail -->
                                                    <p class="description description-thin">
                                                    <label for="form-email-sender">
                                                        <?php _e( 'Reply-To E-mail' , 'visual-form-builder-pro'); ?>
                                                        <span class="vfb-tooltip" title="About Reply-To Email" rel="Manually set the email address that users will reply to.">(?)</span>
                                    					<br />
                                                        <input type="text" value="<?php echo $form_email_from; ?>" class="widefat" id="form-email-sender" name="form_email_from"<?php echo ( $form_email_from_override != '' ) ? ' readonly="readonly"' : ''; ?> />
                                                    </label>
                                                    </p>
                                                    <p class="description description-thin">
                                                        <label for="form_email_from_override">
                                                        	<?php _e( "User's E-mail (optional)" , 'visual-form-builder-pro'); ?>
                                                            <span class="vfb-tooltip" title="About User's Email" rel="Select a required email field from your form to use as the Reply-To email.">(?)</span>
                                    						<br />
                                                        <select name="form_email_from_override" id="form_email_from_override" class="widefat">
                                                            <option value="" <?php selected( $form_email_from_override, '' ); ?>><?php _e( 'Select a required email field' , 'visual-form-builder-pro'); ?></option>
                                                            <?php 
                                                            foreach( $emails as $email ) {
                                                                echo '<option value="' . $email->field_id . '"' . selected( $form_email_from_override, $email->field_id ) . '>' . stripslashes( $email->field_name ) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                        </label>
                                                    </p>
                                                    <br class="clear" />
    												
                                                    <!-- E-mail(s) To -->
                                                    <?php
                                                        /* Basic count to keep track of multiple options */
                                                        $count = 1;
                                                        
                                                        /* Loop through the options */
                                                        foreach ( $form_email_to as $email_to ) {
                                                    ?>
                                                    <div id="clone-email-<?php echo $count; ?>" class="option">
                                                        <p class="description description-wide">
                                                            <label for="form-email-to-<?php echo "$count"; ?>" class="clonedOption">
                                                            <?php _e( 'E-mail(s) To' , 'visual-form-builder-pro'); ?>
                                                            <span class="vfb-tooltip" title="About E-mail(s) To" rel="This option sets single or multiple emails to send the submitted form data to. At least one email is required.">(?)</span>
                                    					<br />
                                                                <input type="text" value="<?php echo stripslashes( $email_to ); ?>" name="form_email_to[]" class="widefat" id="form-email-to-<?php echo "$count"; ?>" />
                                                            </label>
                                                            
                                                            <a href="#" class="addEmail" title="Add an Email">Add</a> <a href="#" class="deleteEmail" title="Delete Email">Delete</a>
                                                            
                                                        </p>
                                                        <br class="clear" />
                                                    </div>
                                                    <?php 
                                                            $count++;
                                                        }
                                                    ?>
                                                </div>
                                                
                                                <!-- Confirmation section -->
                                                <a href="#confirmation" class="settings-links<?php echo ( $settings_accordion == 'confirmation' ) ? ' on' : ''; ?>">3. Confirmation<span class="arrow"></span></a>
                                                <div id="confirmation-message" class="form-details<?php echo ( $settings_accordion == 'confirmation' ) ? ' on' : ''; ?>">
                                                    <p><em><?php _e( "After someone submits a form, you can control what is displayed. By default, it's a message but you can send them to another WordPress Page or a custom URL." , 'visual-form-builder-pro'); ?></em></p>
                                                    <label for="form-success-type-text" class="menu-name-label open-label">
                                                        <input type="radio" value="text" id="form-success-type-text" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'text' ); ?> />
                                                        <span><?php _e( 'Text' , 'visual-form-builder-pro'); ?></span>
                                                    </label>
                                                    <label for="form-success-type-page" class="menu-name-label open-label">
                                                        <input type="radio" value="page" id="form-success-type-page" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'page' ); ?>/>
                                                        <span><?php _e( 'Page' , 'visual-form-builder-pro'); ?></span>
                                                    </label>
                                                    <label for="form-success-type-redirect" class="menu-name-label open-label">
                                                        <input type="radio" value="redirect" id="form-success-type-redirect" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'redirect' ); ?>/>
                                                        <span><?php _e( 'Redirect' , 'visual-form-builder-pro'); ?></span>
                                                    </label>
                                                    <br class="clear" />
                                                    <p class="description description-wide">
                                                    <?php
                                                    /* If there's no text message, make sure there is something displayed by setting a default */
                                                    if ( $form_success_message === '' )
                                                        $default_text = sprintf( '<p id="form_success">%s</p>', __( 'Your form was successfully submitted. Thank you for contacting us.' , 'visual-form-builder-pro') );
                                                    ?>
                                                    <textarea id="form-success-message-text" class="form-success-message<?php echo ( 'text' == $form_success_type ) ? ' active' : ''; ?>" name="form_success_message_text"><?php echo $default_text; ?><?php echo ( 'text' == $form_success_type ) ? $form_success_message : ''; ?></textarea>
                                                    
                                                    <?php
                                                    /* Display all Pages */
                                                    wp_dropdown_pages( array(
                                                        'name' => 'form_success_message_page', 
                                                        'id' => 'form-success-message-page',
                                                        'class' => 'widefat',
                                                        'show_option_none' => __( 'Select a Page' , 'visual-form-builder-pro'),
                                                        'selected' => $form_success_message
                                                    ));
                                                    ?>
                                                    <input type="text" value="<?php echo ( 'redirect' == $form_success_type ) ? $form_success_message : ''; ?>" id="form-success-message-redirect" class="form-success-message regular-text<?php echo ( 'redirect' == $form_success_type ) ? ' active' : ''; ?>" name="form_success_message_redirect" placeholder="http://" />
                                                    </p>
                                                <br class="clear" />
    
                                                </div>
                                            
                                                <!-- Notification section -->
                                                <a href="#notification" class="settings-links<?php echo ( $settings_accordion == 'notification' ) ? ' on' : ''; ?>">4. Notification<span class="arrow"></span></a>
                                                <div id="notification" class="form-details<?php echo ( $settings_accordion == 'notification' ) ? ' on' : ''; ?>">
                                                    <p><em><?php _e( "When a user submits their entry, you can send a customizable notification email." , 'visual-form-builder-pro'); ?></em></p>
                                                    <label for="form-notification-setting">
                                                        <input type="checkbox" value="1" id="form-notification-setting" class="form-notification" name="form_notification_setting" <?php checked( $form_notification_setting, '1' ); ?> style="margin-top:-1px;margin-left:0;"/>
                                                        <?php _e( 'Send Confirmation Email to User' , 'visual-form-builder-pro'); ?>
                                                    </label>
                                                    <br class="clear" />
                                                    <div id="notification-email">
                                                        <p class="description description-wide">
                                                        <label for="form-notification-email-name">
                                                            <?php _e( 'Sender Name or Company' , 'visual-form-builder-pro'); ?>
                                                            <span class="vfb-tooltip" title="About Sender Name or Company" rel="Enter the name you would like to use for the email notification.">(?)</span>
                                    						<br />
                                                            <input type="text" value="<?php echo $form_notification_email_name; ?>" class="widefat" id="form-notification-email-name" name="form_notification_email_name" />
                                                        </label>
                                                        </p>
                                                        <br class="clear" />
                                                        <p class="description description-wide">
                                                        <label for="form-notification-email-from">
                                                            <?php _e( 'Reply-To E-mail' , 'visual-form-builder-pro'); ?>
                                                            <span class="vfb-tooltip" title="About Reply-To Email" rel="Manually set the email address that users will reply to.">(?)</span>
                                    						<br />
                                                            <input type="text" value="<?php echo $form_notification_email_from; ?>" class="widefat" id="form-notification-email-from" name="form_notification_email_from" />
                                                        </label>
                                                        </p>
                                                        <br class="clear" />
                                                        <p class="description description-wide">
                                                            <label for="form-notification-email">
                                                                <?php _e( 'E-mail To' , 'visual-form-builder-pro'); ?>
                                                                <span class="vfb-tooltip" title="About E-mail To" rel="Select a required email field from your form to send the notification email to.">(?)</span>
                                    							<br />
                                                                <select name="form_notification_email" id="form-notification-email" class="widefat">
                                                                    <option value="" <?php selected( $form_notification_email, '' ); ?>><?php _e( 'Select a required email field' , 'visual-form-builder-pro'); ?></option>
                                                                    <?php 
                                                                    foreach( $emails as $email ) {
                                                                        echo '<option value="' . $email->field_id . '"' . selected( $form_notification_email, $email->field_id ) . '>' . $email->field_name . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </label>
                                                        </p>
                                                        <br class="clear" />
                                                        <p class="description description-wide">
                                                        <label for="form-notification-subject">
                                                           <?php _e( 'E-mail Subject' , 'visual-form-builder-pro'); ?>
                                                           <span class="vfb-tooltip" title="About E-mail Subject" rel="This option sets the subject of the email that is sent to the emails you have set in the E-mail To field.">(?)</span>
                                    						<br />
                                                            <input type="text" value="<?php echo $form_notification_subject; ?>" class="widefat" id="form-notification-subject" name="form_notification_subject" />
                                                        </label>
                                                        </p>
                                                        <br class="clear" />
                                                        <p class="description description-wide">
                                                        <label for="form-notification-message"><?php _e( 'Message' , 'visual-form-builder-pro'); ?></label>
                                                        <span class="vfb-tooltip" title="About Message" rel="Insert a message to the user. This will be inserted into the beginning of the email body.">(?)</span>
                                    					<br />
                                                        <textarea id="form-notification-message" class="form-notification-message widefat" name="form_notification_message"><?php echo $form_notification_message; ?></textarea>
                                                        </p>
                                                        <br class="clear" />
                                                        <label for="form-notification-entry">
                                                        <input type="checkbox" value="1" id="form-notification-entry" class="form-notification" name="form_notification_entry" <?php checked( $form_notification_entry, '1' ); ?> style="margin-top:-1px;margin-left:0;"/>
                                                        <?php _e( "Include a Copy of the User's Entry" , 'visual-form-builder-pro'); ?>
                                                    </label>
                                                    </div>
                                                </div>
                                            
                                                <!-- PayPal section -->
                                                <a href="#paypal" class="settings-links<?php echo ( $settings_accordion == 'paypal' ) ? ' on' : ''; ?>">5. PayPal<span class="arrow"></span></a>
                                                <div id="paypal" class="form-details<?php echo ( $settings_accordion == 'paypal' ) ? ' on' : ''; ?>">
                                                    <p><em><?php _e( 'Forward successful form submissions to PayPal to collect simple payments, such as registration fees.' , 'visual-form-builder-pro'); ?></em></p>
                                                    
                                                    <label for="form-paypal-setting">
                                                        <input type="checkbox" value="1" id="form-paypal-setting" class="form-paypal" name="form_paypal_setting" <?php checked( $form_paypal_setting, '1' ); ?> style="margin-top:-1px;margin-left:0;"/>
                                                        <?php _e( 'Use this form as a PayPal form' , 'visual-form-builder-pro'); ?>
                                                    </label>
                                                    <br class="clear" />
                                                    <div id="paypal-setup">
                                                        <p class="description description-wide">
                                                            <label for="form-paypal-email">
                                                               <?php _e( 'Account Email' , 'visual-form-builder-pro'); ?>
                                                               	<span class="vfb-tooltip" title="About Account Email" rel="Insert your PayPal account email. This is not displayed to users.">(?)</span>
                                    							<br />
                                                                <input type="text" value="<?php echo $form_paypal_email; ?>" class="widefat" id="form-paypal-email" name="form_paypal_email" />
                                                            </label>
                                                        </p>                                             
                                                        <br class="clear" />
                                                        <p class="description description-wide">
                                                            <label for="form-paypal-currency">
                                                                <?php _e( 'Currency' , 'visual-form-builder-pro'); ?>
                                                                <span class="vfb-tooltip" title="About Currency" rel="Change the currency type to your region of choice. By default, it is set to U.S Dollars">(?)</span>
                                    							<br />
                                                                <?php
                                                                    /* Setup currencies array */
                                                                    $currencies = array( 'USD' => '&#36; - U.S. Dollar', 'AUD' => 'A&#36; - Australian Dollar', 'BRL' => 'R&#36; - Brazilian Real', 'GBP' => '&#163; - British Pound', 'CAD' => 'C&#36; - Candaian Dollar', 'CZK' => '&#75;&#269; - Czech Koruny', 'DKK' => '&#107;&#114; - Danish Kroner', 'EUR' => '&#8364; - Euro', 'HKD' => '&#36; - Hong Kong Dollar', 'HUF' => '&#70;&#116; - Hungarian Forint', 'ILS' => '&#8362; - Israeli Sheqel', 'JPY' => '&#165; - Japanese Yen', 'MXN' => '&#36; - Mexican Peso', 'TWD' => 'NT&#36; - Taiwan New Dollars', 'NZD' => 'NZ&#36; - New Zealand Dollar', 'NOK' => '&#107;&#114; - Norwegian Kroner', 'PHP' => '&#80;&#104;&#11; - Philippine Peso', 'PLN' => '&#122;&#322; - Polish Zloty', 'SGD' => 'S&#36; - Singapore Dollar', 'SEK' => '&#107;&#114; - Swedish Kronor', 'CHF' => '&#67;&#72;&#70; - Swiss Francs', 'THB' => '&#3647; - Thai Baht' );
                                                                ?>
                                                                <select name="form_paypal_currency" id="form-paypal-currency" class="widefat">
                                                                    <?php foreach( $currencies as $currency => $val ) : ?>
                                                                        <option value="<?php echo $currency; ?>" <?php selected( $form_paypal_currency, $currency, 1 ); ?>><?php echo $val; ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </label>
                                                        </p>
                                                        <br class="clear" />
                                                        <p class="description description-wide">
                                                            <label for="form-paypal-shipping">
																<?php _e( 'Shipping' , 'visual-form-builder-pro'); ?>
                                                                <span class="vfb-tooltip" title="About Shipping" rel="If shipping charges are required for your item, insert the amount here.">(?)</span>
                                    							<br />
                                                                <input type="text" value="<?php echo $form_paypal_shipping; ?>" class="widefat" id="form-paypal-shipping" name="form_paypal_shipping" />
                                                            </label>
                                                        </p>
                                                        <br class="clear" />
                                                        <p class="description description-wide">
                                                            <label for="form-paypal-tax">
																<?php _e( 'Tax Rate' , 'visual-form-builder-pro'); ?>
                                                                <span class="vfb-tooltip" title="About Tax Rate" rel="If you need to charge taxes on your item, insert the tax rate here. The % symbol is not necessary here.">(?)</span>
                                    							<br />
                                                                <input type="text" value="<?php echo $form_paypal_tax; ?>" class="widefat" id="form-paypal-tax" name="form_paypal_tax" />
                                                            </label>
                                                        </p>
                                                        <br class="clear" />
                                                        <p class="description description-wide">
                                                            <label for="form-paypal-item-name">
																<?php _e( 'Item Name' , 'visual-form-builder-pro'); ?>
                                                                <span class="vfb-tooltip" title="About Item Name" rel="This option inserts an item name when the user is checking out through PayPal.">(?)</span>
                                    							<br />
                                                                <input type="text" value="<?php echo $form_paypal_item_name; ?>" class="widefat" id="form-paypal-item-name" name="form_paypal_item_name" />
                                                            </label>
                                                        </p>
                                                        <br class="clear" />
                                                        <p class="description description-wide">
                                                            <label for="form-paypal-field-price"><?php _e( 'Assign Prices' , 'visual-form-builder-pro'); ?>
                                                            <span class="vfb-tooltip" title="About Assign Prices" rel="Assign prices to a field from your form.  Allowed field types are Text, Select, Radio, and Checkbox. Text inputs will automatically use the amount entered by the user.  Select, Radio, and Checkbox fields will allow you to enter amounts for the different options from those respective fields.">(?)</span>
                                    						<br />
                                                                <select name="form_paypal_field_price[id]" id="form-paypal-field-price" class="widefat">
                                                                <option value="" <?php selected( $form_paypal_field_price['id'], '' ); ?>><?php _e( 'Assign Prices to a Field' , 'visual-form-builder-pro'); ?></option>
                                                                <?php 
                                                                foreach( $paypal_fields as $paypal ) {
                                                                    echo '<option value="' . $paypal->field_id . '"' . selected( $form_paypal_field_price['id'], $paypal->field_id ) . '>' . stripslashes( $paypal->field_name ) . '</option>';
                                                                }
                                                                ?>
                                                                </select>
                                                                <img id="paypal-price-switch" alt="" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="waiting" />
                                                            </label>
                                                        
                                                        <br class="clear" />
                                                        <div class="assigned-price">
                                                            <?php
                                                                if ( $form_paypal_field_price['id'] !== '' ) {
                                                                    $fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE field_id = {$form_paypal_field_price['id']}" ) );
                                                                    $paypal_price_field = unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT form_paypal_field_price FROM $this->form_table_name WHERE form_id = $form_id" ) ) );
                                                                    
                                                                    foreach ( $fields as $field ) {
                                                                        if ( in_array( $field->field_type, array( 'text', 'currency' ) ) ) {
                                                                            $field_thing = '<p>Amount Based on User Input</p>';
                                                                        }
                                                                        elseif ( in_array( $field->field_type, array( 'select', 'radio', 'checkbox' ) ) ) {
                                                                            $options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
                                                                            
                                                                            /* Loop through each option and output */
                                                                            foreach ( $options as $option => $value ) {
                                                                                $field_thing .= '<label>' . stripslashes( $value ) . '<input type="text" value="' . stripslashes( $paypal_price_field['prices'][$option]['amount'] ) . '" name="form_paypal_field_price[prices][' . $option . '][amount]" /></label><br>';
                                                                                echo '<input type="hidden" name="form_paypal_field_price[prices][' . $option . '][id]" value="' . stripslashes( $value ) . '" />';
                                                                            }
                                                                        }
                                                                        echo '<input type="hidden" name="form_paypal_field_price[name]" value="vfb-' . $field->field_key . '-' . $field->field_id . '" />';
                                                                    }
                                                                    
                                                                    echo $field_thing;
                                                                }
                                                            ?>
                                                        </div>
                                                        </p>
                                                        <br class="clear" />
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <div class="publishing-action">
                                                <input type="submit" value="<?php echo ( $action == 'create_form' ) ? __( 'Create Form' , 'visual-form-builder-pro') : __( 'Save Form' , 'visual-form-builder-pro'); ?>" class="button-primary menu-save" id="save_form" name="save_form" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="post-body">
                                    <div id="post-body-content">
                                <?php if ( '0' == $form_nav_selected_id ) : ?>
                                        <div class="post-body-plain">
                                            <h3><?php _e( 'Getting Started' , 'visual-form-builder-pro'); ?></h3>
                                            <ol>
                                                <li><?php _e( 'Enter a name in the Form Name input above and click Create Form.' , 'visual-form-builder-pro'); ?></li>
                                                <li><?php _e( 'Click or Drag form items from the Form Item box on the left to add to your form.' , 'visual-form-builder-pro'); ?></li>
                                                <li><?php _e( 'After adding an item, drag and drop to put them in the order you want.' , 'visual-form-builder-pro'); ?></li>
                                                <li><?php _e( 'Click the down arrow on each item to reveal configuration options. Save after you make changes.' , 'visual-form-builder-pro'); ?></li>
                                                <li><?php _e( 'Configure the Form Settings section.' , 'visual-form-builder-pro'); ?></li>
                                                <li><?php _e( 'When you have finished building your form, click the Save Form button.' , 'visual-form-builder-pro'); ?></li>
                                            </ol>
                                            
                                            <h3><?php _e( 'Need more help?' , 'visual-form-builder-pro'); ?></h3>
                                            <ol>
                                            	<li><?php _e( 'Click on the Help tab at the top of this page.' , 'visual-form-builder-pro'); ?></li>
                                                <li><a href="http://vfb.matthewmuro.com/faq">FAQ</a></li>
                                                <li><a href="http://vfb.matthewmuro.com/forums/support">Support Forum</a></li>
                                            </ol>
                                            
                                            <ul id="promote-vfb">
                                            	<li id="twitter"><?php _e( 'Follow me on Twitter' , 'visual-form-builder-pro'); ?>: <a href="http://twitter.com/#!/matthewmuro">@matthewmuro</a></li>
                                            </ul>
                                        </div>
                               	<?php else : 
								
								if ( !empty( $form_nav_selected_id ) && $form_nav_selected_id !== '0' ) :
									/* Display help text for adding fields */
									printf( '<div class="post-body-plain" id="menu-instructions"><p>%s</p></div>', __( 'Select form inputs from the box at left to begin building your custom form. An initial fieldset has been automatically added to get you started.' , 'visual-form-builder-pro') );

									/* Output the fields for each form */
									echo '<ul id="menu-to-edit" class="menu ui-sortable droppable">';
									
									echo $this->field_output( $form_nav_selected_id );

									echo '</ul>';
									
								endif;
								?>
                                    
								<?php endif; ?>
                                    </div>
                                 </div>
                                <div id="nav-menu-footer">
                                	<div class="major-publishing-actions">
                                        <div class="publishing-action">
                                            <input type="submit" value="<?php echo ( $action == 'create_form' ) ? __( 'Create Form' , 'visual-form-builder-pro') : __( 'Save Form' , 'visual-form-builder-pro'); ?>" class="button-primary menu-save" id="save_form" name="save_form" />
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	<?php
		endif;
	}
	
	/**
	 * Handle confirmation when form is submitted
	 * 
	 * @since 1.3
	 */
	function confirmation(){
		global $wpdb;
		
		$form_id = ( isset( $_REQUEST['form_id'] ) ) ? $_REQUEST['form_id'] : '';
		
		if ( isset( $_REQUEST['visual-form-builder-submit'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'visual-form-builder-nonce' ) ) {
			/* Get forms */
			$order = sanitize_sql_orderby( 'form_id DESC' );
			$query 	= "SELECT * FROM $this->form_table_name WHERE form_id = $form_id ORDER BY $order";
			
			$forms 	= $wpdb->get_results( $query );
			
			foreach ( $forms as $form ) {
				
				/* If user wants this to redirect to PayPal */
				if ( $form->form_paypal_setting ) {
					
					/* The Assign Prices */
					$paypal_field = unserialize( $form->form_paypal_field_price );
					
					/* By default, amount based on user input */
					$amount = ( is_array( $_REQUEST[ $paypal_field['name'] ] ) ) ? $_REQUEST[ $paypal_field['name'] ][0] : stripslashes( $_REQUEST[ $paypal_field['name' ] ] );
					
					/* If multiple PayPal prices are set, loop through them */
					if ( $paypal_field['prices'] && is_array( $paypal_field['prices'] ) ) {
						/* Loop through prices and if multiple, amount is from select/radio/checkbox */
						foreach ( $paypal_field['prices'] as $prices ) {
							/* If it's a checkbox, account for that */
							$name = ( is_array( $_REQUEST[ $paypal_field['name'] ] ) ) ? $_REQUEST[ $paypal_field['name'] ][0] : $_REQUEST[ $paypal_field['name'] ];
							
							if ( $prices['id'] == $name )
								$amount = $prices['amount'];
						}
					}
					
					/* Output the jQuery that will submit our hidden PayPal form */
					$paypal = '<script type="text/javascript">
						jQuery(window).load( function() {
							jQuery("#processPayPal").submit();
						});
						</script>';
					
					/* The hidden PayPal form that sends our data */
					$paypal .= '<form id="processPayPal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_xclick">
							<input type="hidden" name="item_name" value="' . stripslashes( $form->form_paypal_item_name ) . '">
							<input type="hidden" name="amount" value="' . $amount . '">
							<input type="hidden" name="currency_code" value="' . stripslashes( $form->form_paypal_currency ) . '">
							<input type="hidden" name="tax_rate" value="' . stripslashes( $form->form_paypal_tax ) . '">
							<input type="hidden" name="shipping" value="' . stripslashes( $form->form_paypal_shipping ) . '">
							<input type="hidden" name="business" value="' . stripslashes( $form->form_paypal_email ) . '">';
					$paypal .= '</form>';
					
					/* Message that replaces the usual success message */
					$paypal .= '<p><strong>Please wait while you are redirected to PayPal...</strong></p>';

					return $paypal;
				}
				
				/* If text, return output and format the HTML for display */
				if ( 'text' == $form->form_success_type )
					return stripslashes( html_entity_decode( wp_kses_stripslashes( $form->form_success_message ) ) );
				/* If page, redirect to the permalink */
				elseif ( 'page' == $form->form_success_type ) {
					$page = get_permalink( $form->form_success_message );
					wp_redirect( $page );
					exit();
				}
				/* If redirect, redirect to the URL */
				elseif ( 'redirect' == $form->form_success_type ) {
					wp_redirect( $form->form_success_message );
					exit();
				}
			}
		}
	}
	
	/**
	 * Output form via shortcode
	 * 
	 * @since 1.0
	 */
	public function form_code( $atts ) {
		global $wpdb;
		
		/* Extract shortcode attributes, set defaults */
		extract( shortcode_atts( array(
			'id' => ''
			), $atts ) 
		);
		
		/* Get form id.  Allows use of [vfb id=1] or [vfb 1] */
		$form_id = ( isset( $id ) && !empty( $id ) ) ? $id : $atts[0];
		
		$open_fieldset = $open_section = $open_page = false;
		
		/* Default the submit value */
		$submit = 'Submit';
		
		/* If form is submitted, show success message, otherwise the form */
		if ( isset( $_REQUEST['visual-form-builder-submit'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'visual-form-builder-nonce' ) && isset( $_REQUEST['form_id'] ) && $_REQUEST['form_id'] == $form_id ) {
			$output = $this->confirmation();
		}
		else {
			/* Get forms */
			$order = sanitize_sql_orderby( 'form_id DESC' );
			$query 	= "SELECT * FROM $this->form_table_name WHERE form_id = $form_id ORDER BY $order";
			
			$forms 	= $wpdb->get_results( $query );
			
			/* Get fields */
			$order_fields = sanitize_sql_orderby( 'field_sequence ASC' );
			$query_fields = "SELECT * FROM $this->field_table_name WHERE form_id = $form_id ORDER BY $order_fields";
			
			$page_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( field_type ) + 1 FROM $this->field_table_name WHERE form_id = $form_id AND field_type = 'page-break';" ) );
			
			$fields = $wpdb->get_results( $query_fields );

			/* Setup count for fieldset and ul/section class names */
			$count = 1;
			$page_num = 0;
			$page = $total_page = $verification = '';

			foreach ( $forms as $form ) :
				$label_alignment = ( $form->form_label_alignment !== '' ) ? " $form->form_label_alignment" : '';
				$output = '<form id="' . $form->form_key . '" class="visual-form-builder' . $label_alignment . '" method="post" enctype="multipart/form-data">
							<input type="hidden" name="form_id" value="' . $form->form_id . '" />';
				$output .= wp_nonce_field( 'visual-form-builder-nonce', '_wpnonce', false, false );

				foreach ( $fields as $field ) {
					/* If field is required, build the span and add setup the 'required' class */
					$required_span = ( !empty( $field->field_required ) && $field->field_required === 'yes' ) ? ' <span>*</span>' : '';
					$required = ( !empty( $field->field_required ) && $field->field_required === 'yes' ) ? ' required' : '';
					$validation = ( !empty( $field->field_validation ) ) ? " $field->field_validation" : '';
					$css = ( !empty( $field->field_css ) ) ? " $field->field_css" : '';
					$layout = ( !empty( $field->field_layout ) ) ? " $field->field_layout" : '';
					
					/* Close each section */
					if ( $open_section == true ) {
						/* If this field's parent does NOT equal our section ID */
						if ( $sec_id && $sec_id !== $field->field_parent ) {
							$output .= '</div><div class="vfb-clear"></div>';
							$open_section = false;
						}
					}
					
					if ( $field->field_type == 'fieldset' ) {
						/* Close each fieldset */
						if ( $open_fieldset == true )
							$output .= '</ul><br /></fieldset>';
						
						if ( $open_page == true && $page !== '' )
							$open_page = false;
												
						$output .= '<fieldset class="fieldset fieldset-' . $count . ' ' . $field->field_key . $css . $page . '"><div class="legend"><h3>' . stripslashes( $field->field_name ) . '</h3></div><ul class="section section-' . $count . '">';
						$open_fieldset = true;
						$count++;
					}
					elseif ( $field->field_type == 'section' ) {
						$output .= '<div class="section-div vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '"><h4>' . stripslashes( $field->field_name ) . '</h4>';
						
						/* Save section ID for future comparison */
						$sec_id = $field->field_id;
						$open_section = true;
					}
					elseif ( $field->field_type == 'page-break' ) {
						$page_num += 1;
						
						$total_page = '<span class="vfb-page-counter">' . $page_num . ' / ' . $page_count . '</span>';
						
						$output .= '<li class="item item-' . $field->field_type . '"><a href="#" id="page-' . $page_num . '" class="vfb-page-next">' . stripslashes( $field->field_name ) . '</a> ' . $total_page . '</li>';
						$page = " vfb-page page-$page_num";
						$open_page = true;
					}
					elseif ( !in_array( $field->field_type, array( 'verification', 'secret', 'submit' ) ) ) {
						
						$columns_choice = ( in_array( $field->field_type, array( 'radio', 'checkbox' ) ) ) ? " $field->field_size" : '';
						
						if ( $field->field_type !== 'hidden' ) {
							$output .= '<li class="item item-' . $field->field_type . $columns_choice . $layout . '"><label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" class="desc">'. stripslashes( $field->field_name ) . $required_span . '</label>';
						}
					}
					elseif ( in_array( $field->field_type, array( 'verification', 'secret' ) ) ) {
						
						if ( $field->field_type == 'verification' )
							$verification .= '<fieldset class="fieldset fieldset-' . $count . ' ' . $field->field_key . $css . $page . '"><div class="legend"><h3>' . stripslashes( $field->field_name ) . '</h3></div><ul class="section section-' . $count . '">';
						
						if ( $field->field_type == 'secret' ) {
							/* Default logged in values */
							$logged_in_display = '';
							$logged_in_value = '';

							/* If the user is logged in, fill the field in for them */
							if ( is_user_logged_in() ) {
								/* Hide the secret field if logged in */
								$logged_in_display = ' style="display:none;"';
								$logged_in_value = 14;
								
								/* Get logged in user details */
								$user = wp_get_current_user();
								$user_identity = ! empty( $user->ID ) ? $user->display_name : '';
								
								/* Display a message for logged in users */
								$verification .= '<li class="item">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. Verification not required.', 'visual-form-builder-pro' ), admin_url( 'profile.php' ), $user_identity ) . '</li>';
							}
							
							$validation = ' {digits:true,maxlength:2,minlength:2}';
							$verification .= '<li class="item item-' . $field->field_type . '"' . $logged_in_display . '><label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" class="desc">'. stripslashes( $field->field_name ) . $required_span . '</label>';
							
							/* Set variable for testing if required is Yes/No */
							if ( $required == '' )
								$verification .= '<input type="hidden" name="_vfb-required-secret" value="0" />';
							
							$verification .= '<input type="hidden" name="_vfb-secret" value="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" />';
							
							if ( !empty( $field->field_description ) )
								$verification .= '<span><input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="' . $logged_in_value . '" class="text ' . $field->field_size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
							else
								$verification .= '<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="' . $logged_in_value . '" class="text ' . $field->field_size . $required . $validation . $css . '" />';
						}
					}
					
					switch ( $field->field_type ) {
						case 'text' :
						case 'email' :
						case 'url' :
						case 'currency' :
						case 'number' :
						case 'phone' :
						case 'ip-address' :
						case 'credit-card' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text ' . $field->field_size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
							else
								$output .= '<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text ' . $field->field_size . $required . $validation . $css . '" />';
								
						break;
						
						case 'textarea' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
	
							$output .= '<textarea name="vfb-'. esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-'. esc_html( $field->field_key ) . '-' . $field->field_id . '" class="textarea ' . $field->field_size . $required . $css . '"></textarea>';
								
						break;
						
						case 'select' :
							if ( !empty( $field->field_description ) )
								$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
									
							$output .= '<select name="vfb-'. esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-'. esc_html( $field->field_key ) . '-' . $field->field_id . '" class="select ' . $field->field_size . $required . $css . '">';
							
							$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
							
							/* Loop through each option and output */
							foreach ( $options as $option => $value ) {
								$output .= '<option value="' . trim( stripslashes( $value ) ) . '">'. trim( stripslashes( $value ) ) . '</option>';
							}
							
							$output .= '</select>';
							
						break;
						
						case 'radio' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
							
							$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
							
							$output .= '<div>';
							
							/* Loop through each option and output */
							foreach ( $options as $option => $value ) {
								$output .= '<span>
												<input type="radio" name="vfb-'. $field->field_key . '-' . $field->field_id . '" id="vfb-'. $field->field_key . '-' . $field->field_id . '-' . $option . '" value="'. trim( stripslashes( $value ) ) . '" class="radio' . $required . $css . '" />'. 
											' <label for="vfb-' . $field->field_key . '-' . $field->field_id . '-' . $option . '" class="choice">' . trim( stripslashes( $value ) ) . '</label>' .
											'</span>';
							}
							
							$output .= '<div style="clear:both"></div></div>';
							
						break;
						
						case 'checkbox' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
							
							$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
							
							$output .= '<div>';

							/* Loop through each option and output */
							foreach ( $options as $option => $value ) {
								
								$output .= '<span><input type="checkbox" name="vfb-'. $field->field_key . '-' . $field->field_id . '[]" id="vfb-'. $field->field_key . '-' . $field->field_id . '-' . $option . '" value="'. trim( stripslashes( $value ) ) . '" class="checkbox' . $required . $css . '" />'. 
									' <label for="vfb-' . $field->field_key . '-' . $field->field_id . '-' . $option . '" class="choice">' . trim( stripslashes( $value ) ) . '</label></span>';
							}
							
							$output .= '<div style="clear:both"></div></div>';
						
						break;
						
						case 'address' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
								
								$countries = array( "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombi", "Comoros", "Congo (Brazzaville)", "Congo", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor (Timor Timur)", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia, The", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepa", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe" );
							$output .= '<div>
								<span class="full">
					
									<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '[address]" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '-address" maxlength="150" class="text medium' . $required . $css . '" />
									<label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '-address">Address</label>
								</span>
								<span class="full">
									<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '[address-2]" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . 'address-2" maxlength="150" class="text medium' . $css . '" />
									<label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '-address-2">Address Line 2</label>
								</span>
								<span class="left">
					
									<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '[city]" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '-city" maxlength="150" class="text medium' . $required . $css . '" />
									<label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '-city">City</label>
								</span>
								<span class="right">
									<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '[state]" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '-state" maxlength="150" class="text medium' . $required . $css . '" />
									<label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '-state">State / Province / Region</label>
								</span>
								<span class="left">
					
									<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '[zip]" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '-zip" maxlength="150" class="text medium' . $required . $css . '" />
									<label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '-zip">Postal / Zip Code</label>
								</span>
								<span class="right">
								<select class="select' . $required . $css . '" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '[country]" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '-country">
								<option selected="selected" value=""></option>';
								
								foreach ( $countries as $country ) {
									$output .= "<option value='$country'>$country</option>";
								}
								
								$output .= '</select>
									<label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '-country">Country</label>
								</span>
							</div>';

						break;
						
						case 'date' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text vfb-date-picker ' . $field->field_size . $required . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
							else
								$output .= '<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text vfb-date-picker ' . $field->field_size . $required . $css . '" />';
							
						break;
						
						case 'time' :
							if ( !empty( $field->field_description ) )
								$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';

							/* Get the time format (12 or 24) */
							$time_format = str_replace( 'time-', '', $validation );
							/* Set whether we start with 0 or 1 and how many total hours */
							$hour_start = ( $time_format == '12' ) ? 1 : 0;
							$hour_total = ( $time_format == '12' ) ? 12 : 23;
							
							/* Hour */
							$output .= '<span class="time"><select name="vfb-'. $field->field_key . '-' . $field->field_id . '[hour]" id="vfb-'. $field->field_key . '-' . $field->field_id . '-hour" class="select' . $required . $css . '">';
							for ( $i = $hour_start; $i <= $hour_total; $i++ ) {
								/* Add the leading zero */
								$hour = ( $i < 10 ) ? "0$i" : $i;
								$output .= "<option value='$hour'>$hour</option>";
							}
							$output .= '</select><label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '-hour">HH</label></span>';
							
							/* Minute */
							$output .= '<span class="time"><select name="vfb-'. $field->field_key . '-' . $field->field_id . '[min]" id="vfb-'. $field->field_key . '-' . $field->field_id . '-min" class="select' . $required . $css . '">';
							for ( $i = 0; $i <= 55; $i+=5 ) {
								/* Add the leading zero */
								$min = ( $i < 10 ) ? "0$i" : $i;
								$output .= "<option value='$min'>$min</option>";
							}
							$output .= '</select><label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '-min">MM</label></span>';
							
							/* AM/PM */
							if ( $time_format == '12' )
								$output .= '<span class="time"><select name="vfb-'. $field->field_key . '-' . $field->field_id . '[ampm]" id="vfb-'. $field->field_key . '-' . $field->field_id . '-ampm" class="select' . $required . $css . '"><option value="AM">AM</option><option value="PM">PM</option></select><label for="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '-ampm">AM/PM</label></span>';
							$output .= '<div class="clear"></div>';		
						break;
						
						case 'html' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';

							$output .= '<script type="text/javascript">edToolbar("vfb-' . $field->field_key . '-' . $field->field_id . '");</script>';
							$output .= '<textarea name="vfb-'. $field->field_key . '-' . $field->field_id . '" id="vfb-'. $field->field_key . '-' . $field->field_id . '" class="textarea vfbEditor ' . $field->field_size . $required . $css . '"></textarea>';
								
						break;
						
						case 'file-upload' :
							
							$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
							$accept = ( !empty( $options[0] ) ) ? "{accept:'$options[0]'}" : '';

							if ( !empty( $field->field_description ) )
								$output .= '<span><input type="file" size="35" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text ' . $field->field_size . $required . $validation . $accept . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
							else
								$output .= '<input type="file" size="35" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text ' . $field->field_size . $required . $validation . $accept . $css . '" />';
						
									
						break;
						
						case 'instructions' :
							
							$output .= html_entity_decode( stripslashes( $field->field_description ) );
						
						break;
						
						case 'username' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text username ' . $field->field_size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
							else
								$output .= '<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text username ' . $field->field_size . $required . $validation . $css . '" />';
								
						break;
						
						case 'password' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><input type="password" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text password ' . $field->field_size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span><div class="password-meter"><div class="password-meter-message">Password Strength</div></div>';
							else
								$output .= '<input type="password" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text password ' . $field->field_size . $required . $validation . $css . '" /><div class="password-meter"><div class="password-meter-message">Password Strength</div></div>';
							
						break;
						
						case 'hidden' :
							/* If the options field isn't empty, unserialize and build array */
							if ( !empty( $field->field_options ) ) {
								if ( is_serialized( $field->field_options ) )
									$opts_vals = unserialize( $field->field_options );
									
									switch ( $opts_vals[0] ) {
										case 'form_id' :
											$val = $form_id;
										break;
										case 'form_title' :
											$val = stripslashes( $form->form_title );
										break;
										case 'ip' :
											$val = $_SERVER['REMOTE_ADDR'];
										break;
										case 'uid' :
											$val = uniqid();
										break;
										case 'post_id' :
											$val = $form_id;
										break;
										case 'post_title' :
											$val = get_the_title();
										break;
										case 'custom' :
											$val = trim( stripslashes( $opts_vals[1] ) );
										break;
									}
							}
							
							$output .= '<input type="hidden" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="' . $val . '" class="text ' . $field->field_size . $required . $validation . $css . '" />';
						break;
						
						case 'color-picker' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="#" class="text color ' . $field->field_size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span><div id="color-' . esc_html( $field->field_key )  . '-' . $field->field_id . '"class="colorPicker"></div>';
							else
								$output .= '<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="#" class="text color ' . $field->field_size . $required . $validation . $css . '" /><div id="color-' . esc_html( $field->field_key )  . '-' . $field->field_id . '"class="colorPicker"></div>';
							
						break;
						
						case 'autocomplete' :
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text auto ' . $field->field_size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
							else
								$output .= '<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text auto ' . $field->field_size . $required . $validation . $css . '" />';
							
						break;
						
						case 'min' :
						case 'max' :
							/* If the options field isn't empty, unserialize and build array */
							if ( !empty( $field->field_options ) ) {
								if ( is_serialized( $field->field_options ) )
									$opts_vals = unserialize( $field->field_options );
							}
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" ' . $field->field_type . '="' . $opts_vals[0] . '" class="text ' . $field->field_size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
							else
								$output .= '<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" ' . $field->field_type . '="' . $opts_vals[0] . '" class="text ' . $field->field_size . $required . $validation . $css . '" />';

						break;
						
						case 'range' :
							/* If the options field isn't empty, unserialize and build array */
							if ( !empty( $field->field_options ) ) {
								if ( is_serialized( $field->field_options ) ) {
									$opts_vals = unserialize( $field->field_options );
									$min = $opts_vals[0];
									$max = $opts_vals[1];
								}
							}
							
							if ( !empty( $field->field_description ) )
								$output .= '<span><input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text {range:[' . $min . ',' . $max . ']} ' . $field->field_size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
							else
								$output .= '<input type="text" name="vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . '" id="vfb-' . esc_html( $field->field_key )  . '-' . $field->field_id . '" value="" class="text {range:[' . $min . ',' . $max . ']} ' . $field->field_size . $required . $validation . $css . '" />';

						break;
						
						case 'submit' :
							
							$submit = $field->field_name;
							
						break;
						
						default:
							echo '';
					}

					/* Closing </li> */
					$output .= ( !in_array( $field->field_type , array( 'verification', 'secret', 'submit', 'fieldset', 'section', 'hidden', 'page-break' ) ) ) ? '</li>' : '';
				}
				
				/* Close user-added fields */
				$output .= '</ul><br /></fieldset>';
				
				if ( $total_page !== '' )
					$total_page = '<span class="vfb-page-counter">' . $page_count . ' / ' . $page_count . '</span>';
				
				/* Make sure the verification displays even if they have not updated their form */
				if ( $verification == '' ) {
					$verification = '<fieldset class="fieldset verification">
							<div class="legend">
								<h3>' . __( 'Verification' , 'visual-form-builder-pro') . '</h3>
							</div>
							<ul class="section section-' . $count . '">
								<li class="item item-text">
									<label for="vfb-secret" class="desc">' . __( 'Please enter any two digits with' , 'visual-form-builder-pro') . ' <strong>' . __( 'no' , 'visual-form-builder-pro') . '</strong> ' . __( 'spaces (Example: 12)' , 'visual-form-builder-pro') . '<span>*</span></label>
									<div>
										<input type="text" name="vfb-secret" id="vfb-secret" class="text medium" />
									</div>
								</li>';
				}
				
				/* Output our security test */
				$output .= $verification . '<li style="display:none;">
									<label for="vfb-spam">' . __( 'This box is for spam protection' , 'visual-form-builder-pro') . ' - <strong>' . __( 'please leave it blank' , 'visual-form-builder-pro') . '</strong>:</label>
									<div>
										<input name="vfb-spam" id="vfb-spam" />
									</div>
								</li>

								<li class="item item-submit">
									<input type="submit" name="visual-form-builder-submit" value="' . $submit . '" class="submit" id="sendmail" />' . $total_page . '
								</li>
							</ul>
						</fieldset></form>';			

			endforeach;
		}
		
		return $output;
	}
	
	/**
	 * Handle emailing the content
	 * 
	 * @since 1.0
	 * @uses wp_mail() E-mails a message
	 */
	public function email() {
		global $wpdb, $post;
		
		$required = ( isset( $_REQUEST['_vfb-required-secret'] ) && $_REQUEST['_vfb-required-secret'] == '0' ) ? false : true;
		$secret_field = ( isset( $_REQUEST['_vfb-secret'] ) ) ? $_REQUEST['_vfb-secret'] : '';
		
		/* If the verification is set to required, run validation check */
		if ( true == $required && !empty( $secret_field ) )
			if ( !is_numeric( $_REQUEST[ $secret_field ] ) && strlen( $_REQUEST[ $secret_field ] ) !== 2 )
				wp_die( __( 'Security check' , 'visual-form-builder-pro') );
		
		/* Test if it's a known SPAM bot */
		if ( $this->isBot() )
			wp_die( __( 'Security check' , 'visual-form-builder-pro') );
		
		/* Basic security check before moving any further */
		if ( isset( $_REQUEST['visual-form-builder-submit'] ) && $_REQUEST['vfb-spam'] == '' ) :
			$nonce = $_REQUEST['_wpnonce'];
			
			/* Security check to verify the nonce */
			if ( ! wp_verify_nonce( $nonce, 'visual-form-builder-nonce' ) )
				wp_die( __( 'Security check' , 'visual-form-builder-pro') );
			
			/* Set submitted action to display success message */
			$this->submitted = true;
			
			/* Tells us which form to get from the database */
			$form_id = absint( $_REQUEST['form_id'] );
			
			/* Query to get all forms */
			$order = sanitize_sql_orderby( 'form_id DESC' );
			$query = "SELECT * FROM $this->form_table_name WHERE form_id = $form_id ORDER BY $order";
			
			/* Build our forms as an object */
			$forms = $wpdb->get_results( $query );
			
			/* Get sender and email details */
			foreach ( $forms as $form ) {
				$form_title = stripslashes( html_entity_decode( $form->form_title, ENT_QUOTES, 'UTF-8' ) );
				$form_subject = stripslashes( html_entity_decode( $form->form_email_subject, ENT_QUOTES, 'UTF-8' ) );
				$form_to = ( is_array( unserialize( $form->form_email_to ) ) ) ? unserialize( $form->form_email_to ) : explode( ',', unserialize( $form->form_email_to ) );
				$form_from = stripslashes( $form->form_email_from );
				$form_from_name = stripslashes( $form->form_email_from_name );
				$form_notification_setting = stripslashes( $form->form_notification_setting );
				$form_notification_email_name = stripslashes( $form->form_notification_email_name );
				$form_notification_email_from = stripslashes( $form->form_notification_email_from );
				$form_notification_email = stripslashes( $form->form_notification_email );
				$form_notification_subject = stripslashes( $form->form_notification_subject );
				$form_notification_message = stripslashes( $form->form_notification_message );
				$form_notification_entry = stripslashes( $form->form_notification_entry );
				
				$email_design = unserialize( $form->form_email_design );
				
				/* Set email design variables */
				$color_scheme = stripslashes( $email_design['color_scheme'] ) . ';';
				$format = stripslashes( $email_design['format'] );
				$link_love = stripslashes( $email_design['link_love'] );
				$footer_text = stripslashes( $email_design['footer_text'] );
				$background_color = stripslashes( $email_design['background_color'] ) . ';';
				$header_color = stripslashes( $email_design['header_color'] ) . ';';
				$fieldset_color = stripslashes( $email_design['fieldset_color'] ) . ';';
				$section_color = stripslashes( $email_design['section_color'] ) . ';';
				$section_text_color = stripslashes( $email_design['section_text_color'] ) . ';';
				$text_color = stripslashes( $email_design['text_color'] ) . ';';
				$link_color = stripslashes( $email_design['link_color'] ) . ';';
				$row_color = stripslashes( $email_design['row_color'] ) . ';';
				$row_alt_color = stripslashes( $email_design['row_alt_color'] ) . ';';
				$border_color = stripslashes( $email_design['border_color'] ) . ';';
				$footer_color = stripslashes( $email_design['footer_color'] ) . ';';
				$footer_text_color = stripslashes( $email_design['footer_text_color'] ) . ';';
				$font_family = stripslashes( $email_design['font_family'] ) . ';';
				$header_font_size = stripslashes( $email_design['header_font_size'] ) . 'px;';
				$fieldset_font_size = stripslashes( $email_design['fieldset_font_size'] ) . 'px;';
				$section_font_size = stripslashes( $email_design['section_font_size'] ) . 'px;';
				$text_font_size = stripslashes( $email_design['text_font_size'] ) . 'px;';
				$footer_font_size = stripslashes( $email_design['footer_font_size'] ) . 'px;';
			}
			
			/* Sender name override query */
			$sender_query = "SELECT fields.field_id, fields.field_key FROM $this->form_table_name AS forms LEFT JOIN $this->field_table_name AS fields ON forms.form_email_from_name_override = fields.field_id WHERE forms.form_id = $form_id";
			$senders = $wpdb->get_results( $sender_query );

			/* Sender email override query */
			$email_query = "SELECT fields.field_id, fields.field_key FROM $this->form_table_name AS forms LEFT JOIN $this->field_table_name AS fields ON forms.form_email_from_override = fields.field_id WHERE forms.form_id = $form_id";
			$emails = $wpdb->get_results( $email_query );
			
			/* Notification send to email override query */
			$notification_query = "SELECT fields.field_id, fields.field_key FROM $this->form_table_name AS forms LEFT JOIN $this->field_table_name AS fields ON forms.form_notification_email = fields.field_id WHERE forms.form_id = $form_id";
			$notification = $wpdb->get_results( $notification_query );
			
			/* Loop through name results and assign sender name to override, if needed */
			foreach( $senders as $sender ) {
				if ( !empty( $sender->field_key ) )
					$form_from_name = $_POST[ 'vfb-' . $sender->field_key . '-' . $sender->field_id ];
			}

			/* Loop through email results and assign sender email to override, if needed */
			foreach ( $emails as $email ) {
				if ( !empty( $email->field_key ) )
					$form_from = $_POST[ 'vfb-' . $email->field_key . '-' . $email->field_id ];
			}
			
			/* Loop through email results and assign as blind carbon copy, if needed */
			foreach ( $notification as $notify ) {
				if ( !empty( $notify->field_key ) )
					$copy_email = $_POST[ 'vfb-' . $notify->field_key . '-' . $notify->field_id ];
			}

			/* Query to get all forms */
			$order = sanitize_sql_orderby( 'field_sequence ASC' );
			$query = "SELECT field_id, field_key, field_name, field_type, field_options, field_parent FROM $this->field_table_name WHERE form_id = $form_id ORDER BY $order";
			
			/* Build our forms as an object */
			$fields = $wpdb->get_results( $query );
			
			$open_fieldset = false;
			
			/* Setup counter for alt rows */
			$i = $points = 0;
			
			/* Setup HTML email vars */
			$header = $message = $footer = $html_email = $plain_text = $auto_response_email = '';
			
			/* Prepare the beginning of the content */
			$header = '<html>
						<head>
						<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
						<title>HTML Email</title>
						</head>
						<body style="background-color: ' . $background_color . '">
						<table class="bg1" cellspacing="0" border="0" style="background-color: ' . $background_color . '" cellpadding="0" width="100%">
						  <tr>
							<td align="center">
							<table class="bg2" cellspacing="0" border="0" style="background-color: #ffffff;" cellpadding="0" width="600">
								<tr>
								  <td class="permission" align="center" style="background-color: ' . $background_color . 'padding: 10px 20px 10px 20px;">&nbsp;</td>
								</tr>
								<tr>
								  <td class="header" align="left" style="background-color:' . $header_color . 'padding: 50px 20px 50px 20px;"><h1 style="font-family: ' . $font_family . 'font-size: ' . $header_font_size . 'font-weight:normal;margin:0;padding:0;color:#ffffff;">' . $form_title . '</h1></td>
								</tr>
								<tr>
								  <td class="body" valign="top" style="background-color: ' . $row_color . 'padding: 20px 20px 20px 20px;">
								  <table cellspacing="0" border="0" cellpadding="0" width="100%">
									  <tr>
										<td class="mainbar" align="left" valign="top">';
			
			/* Start setting up plain text email */
			$plain_text .= "============ $form_title =============\n";
			
			/* Loop through each form field and build the body of the message */
			foreach ( $fields as $field ) {
				$alt_row = ( $i % 2 == 0 ) ? 'background-color:' . $row_alt_color : '';
				
				/* Handle attachments */
				if ( $field->field_type == 'file-upload' ) {
					$value = $_FILES[ 'vfb-' . $field->field_key . '-' . $field->field_id ];
					
					if ( $value['size'] > 0 ) {
						/* 25MB is the max size allowed */
						$max_attach_size = 25 * 1048576;
						
						/* Display error if file size has been exceeded */
						if ( $value['size'] > $max_attach_size )
							wp_die( __( 'File size exceeds 25MB. Most email providers will reject emails with attachments larger than 25MB. Please decrease the file size and try again.', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
						
						/* Options array for the wp_handle_upload function. 'test_form' => false */
						$upload_overrides = array( 'test_form' => false ); 
						
						/* We need to include the file that runs the wp_handle_upload function */
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
						
						/* Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array */
						$uploaded_file = wp_handle_upload( $value, $upload_overrides );
						
						/* If the wp_handle_upload call returned a local path for the image */
						if ( isset( $uploaded_file['file'] ) ) {
							/* Retrieve the file type from the file name. Returns an array with extension and mime type */
							$wp_filetype = wp_check_filetype( basename( $uploaded_file['file'] ), null );
							
							/* Return the current upload directory location */
 							$wp_upload_dir = wp_upload_dir();
							
							$media_upload = array(
								'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $uploaded_file['file'] ), 
								'post_mime_type' => $wp_filetype['type'],
								'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $uploaded_file['file'] ) ),
								'post_content' => '',
								'post_status' => 'inherit'
							);
							
							/* Insert attachment into Media Library and get attachment ID */
							$attach_id = wp_insert_attachment( $media_upload, $uploaded_file['file'] );
							
							/* Include the file that runs wp_generate_attachment_metadata() */
							require_once( ABSPATH . 'wp-admin/includes/image.php' );
							
							/* Setup attachment metadata */
							$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );
							
							/* Update the attachment metadata */
							wp_update_attachment_metadata( $attach_id, $attach_data );
							
							
							$attachments[ 'vfb-' . $field->field_key . '-' . $field->field_id ] = $uploaded_file['file'];

							$data[] = array(
								'id' => $field->field_id,
								'slug' => $field->field_key,
								'name' => $field->field_name,
								'type' => $field->field_type,
								'options' => $field->field_options,
								'parent_id' => $field->field_parent,
								'value' => $uploaded_file['url']
							);
							
							$message .= '<tr>
										  <td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . 'border-bottom:1px solid ' . $border_color . '"><p style="font-size: ' . $text_font_size . ' font-weight: bold; margin: 14px 0 14px 5px; font-family: ' . $font_family . ' color: ' . $text_color . '; padding: 0;">' . stripslashes( $field->field_name ) . ':</p></td>
										  <td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . 'border-bottom:1px solid ' . $border_color . '"><p style="font-size: ' . $text_font_size . ' font-weight: normal; margin: 14px 0 14px 0; font-family: ' . $font_family . ' color: ' . $text_color . ' padding: 0;"><a href="' . $uploaded_file['url'] . '" style="font-size: 13px; font-weight: normal; font-family: ' . $font_family . ' color: ' . $link_color . '">' . $uploaded_file['url'] . '</a></p></td>
										</tr>';
							
							$plain_text .= stripslashes( $field->field_name ) . ": {$uploaded_file['url']}\n";
						}
					}
					else {
						$value = $_POST[ 'vfb-' . $field->field_key . '-' . $field->field_id ];
						$message .= '<tr>
										  <td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . 'border-bottom:1px solid ' . $border_color . '"><p style="font-size: ' . $text_font_size . ' font-weight: bold; margin: 14px 0 14px 5px; font-family: ' . $font_family . ' color: ' . $text_color . '; padding: 0;">' . stripslashes( $field->field_name ) . ':</p></td>
										  <td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . 'border-bottom:1px solid ' . $border_color . '"><p style="font-size: ' . $text_font_size . ' font-weight: normal; margin: 14px 0 14px 0; font-family: ' . $font_family . ' color: ' . $text_color . ' padding: 0;">' . $value . '</p></td>
										</tr>';
						
						$plain_text .= stripslashes( $field->field_name ) . ": $value\n";
					}
				}
				/* Everything else */
				else {
					$value = $_POST[ 'vfb-' . $field->field_key . '-' . $field->field_id ];
					
					/* If time field, build proper output */
					if ( is_array( $value ) && array_key_exists( 'hour', $value ) && array_key_exists( 'min', $value ) )
						$value = ( array_key_exists( 'ampm', $value ) ) ? substr_replace( implode( ':', $value ), ' ', 5, 1 ) : implode( ':', $value );
					/* If address field, build proper output */
					elseif ( is_array( $value ) && array_key_exists( 'address', $value ) && array_key_exists( 'address-2', $value ) ) {
						$address = '';
						
						if ( !empty( $value['address'] ) )
							$address .= $value['address'];
						
						if ( !empty( $value['address-2'] ) ) {
							if ( !empty( $address ) )
								$address .= '<br>';
							$address .= $value['address-2'];
						}
						
						if ( !empty( $value['city'] ) ) {
							if ( !empty( $address ) )
								$address .= '<br>';
							$address .= $value['city'];
						}
						if ( !empty( $value['state'] ) ) {
							if ( !empty( $address ) && empty( $value['city'] ) )
								$address .= '<br>';
							else if ( !empty( $address ) && !empty( $value['city'] ) )
								$address .= ', ';
							$address .= $value['state'];
						}
						if ( !empty( $value['zip'] ) ) {
							if ( !empty( $address ) && ( empty( $value['city'] ) && empty( $value['state'] ) ) )
								$address .= '<br>';
							else if ( !empty( $address ) && ( !empty( $value['city'] ) || !empty( $value['state'] ) ) )
								$address .= '. ';
							$address .= $value['zip'];
						}
						if ( !empty( $value['country'] ) ) {
							if ( !empty( $address ) )
								$address .= '<br>';
							$address .= $value['country'];
						}
						
						$value = $address;						
					}
					/* If multiple values, build the list */
					elseif ( is_array( $value ) )
						$value = implode( ', ', $value );
					/* Lastly, handle single values */
					else
						$value = html_entity_decode( stripslashes( esc_html( $value ) ), ENT_QUOTES, 'UTF-8' );
					
					/* Setup spam catcher RegEx */
					$exploits = '/(content-type|bcc:|cc:|document.cookie|onclick|onload|javascript|alert)/i';
					$profanity = '/(beastial|bestial|blowjob|clit|cock|cum|cunilingus|cunillingus|cunnilingus|cunt|ejaculate|fag|felatio|fellatio|fuck|fuk|fuks|gangbang|gangbanged|gangbangs|hotsex|jism|jiz|kock|kondum|kum|kunilingus|orgasim|orgasims|orgasm|orgasms|phonesex|phuk|phuq|porn|pussies|pussy|spunk|xxx)/i';
					$spamwords = '/(viagra|phentermine|tramadol|adipex|advai|alprazolam|ambien|ambian|amoxicillin|antivert|blackjack|backgammon|texas|holdem|poker|carisoprodol|ciara|ciprofloxacin|debt|dating|porn)/i';
					
					/* Add up points for each spam hit */
					if ( preg_match( $exploits, $value ) )
						$points += 2;
					elseif ( preg_match( $profanity, $value ) )
						$points += 1;
					elseif ( preg_match( $spamwords, $value ) )
						$points += 1;
					
					/* Validate input */
					$this->validate_input( $value, $field->field_type );
					
					/* Don't add Submits or Page Breaks to the email */
					if ( ! in_array( $field->field_type, array( 'verification', 'secret', 'submit', 'page-break', 'instructions' ) ) ) {
						if ( $field->field_type == 'fieldset' ) {
							/* Close each fieldset */
							if ( $open_fieldset == true )
								$message .= '</table>';
						
							$message .= '<h2 style="font-size: ' . $fieldset_font_size . ' font-weight: bold; margin: 10px 0 10px 0; font-family: ' . $font_family . ' color: ' . $fieldset_color . ' padding: 0;">' . stripslashes( $field->field_name ) . '</h2>
                  							<table cellspacing="0" border="0" cellpadding="0" width="100%">';
							
							$open_fieldset = true;
							
							$plain_text .= "————————————————————————————\n" . stripslashes( $field->field_name ) .  "————————————————————————————\n\n";
						}
						elseif ( $field->field_type == 'section' ) {
							$message .= '<tr><td colspan="2" style="background-color:' . $section_color . 'color:' . $section_text_color . '"><h3 style="font-size: ' . $section_font_size . ' font-weight: bold; margin: 14px 14px 14px 10px; font-family: ' . $font_family . ' color: ' . $section_text_color . ' padding: 0;">' . stripslashes( $field->field_name ) . '</h3></td></tr>';
							$plain_text .= "*** " . stripslashes( $field->field_name ) . "***\n";
						}
						else {
							$message .= '<tr><td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . 'border-bottom:1px solid ' . $border_color . '">
							<p style="font-size: ' . $text_font_size . ' font-weight: bold; margin: 14px 0 14px 5px; font-family: ' . $font_family . ' color: ' . $text_color . ' padding: 0;">' . stripslashes( $field->field_name ) . ':</p>
							</td><td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . 'border-bottom:1px solid ' . $border_color . '">
							<p style="font-size: ' . $text_font_size . ' font-weight: normal; margin: 14px 0 14px 0; font-family: ' . $font_family . ' color: ' . $text_color . ' padding: 0;">' . $value . '</p></td></tr>';
							
							$plain_text .= stripslashes( $field->field_name ) . ": $value\n";
						}

					}
				
					$data[] = array(
						'id' => $field->field_id,
						'slug' => $field->field_key,
						'name' => $field->field_name,
						'type' => $field->field_type,
						'options' => $field->field_options,
						'parent_id' => $field->field_parent,
						'value' => $value
					);
				}
				
				/* If the user accumulates more than 4 points, it might be spam */
				if ( $points > 4 )
					wp_die( __( 'Your responses look too much like spam and could not be sent at this time.', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
				
				/* Increment our alt row counter */
				$i++;
			}
			
			/* Setup our entries data */
			$entry = array(
				'form_id' => $form_id,
				'data' => serialize( $data ),
				'subject' => $form_subject,
				'sender_name' => $form_from_name,
				'sender_email' => $form_from,
				'emails_to' => serialize( $form_to ),
				'date_submitted' => date_i18n( 'Y-m-d G:i:s' ),
				'ip_address' => $_SERVER['REMOTE_ADDR']
			);
			
			/* Insert this data into the entries table */
			$wpdb->insert( $this->entries_table_name, $entry );
			
			/* Setup the link love */
			if ( $link_love == '' || $link_love == 'yes' ) {
				$html_link_love = 'This email was built and sent using <a href="http://vfb.matthewmuro.com" style="font-size: ' . $footer_font_size . 'font-family: ' . $font_family . 'color:' . $link_color . '">Visual Form Builder Pro</a>.';
				$plain_text_link_love = "This email was built and sent using\nVisual Form Builder Pro (http://vfb.matthewmuro.com)";
			}
			
			/* Close out the content */
			$footer = '</table></td>
								  </tr>
								</table>
								</td>
							</tr>
							<tr>
							  <td class="footer" height="61" align="left" valign="middle" style="background-color: ' . $footer_color . ' padding: 0 20px 0 20px; height: 61px; vertical-align: middle;"><p style="font-size: ' . $footer_font_size . ' font-weight: normal; margin: 0; font-family: ' . $font_family . ' line-height: 16px; color: ' . $footer_text_color . ' padding: 0;">' . $html_link_love . $footer_text . '</p>
							  </td>
							</tr>
						  </table></td>
					  </tr>
					  <tr>
						  <td class="permission" align="center" style="background-color: ' . $background_color . ' padding: 20px 20px 20px 20px;">&nbsp;</td>
						</tr>
					</table>
					</body>
					</html>';
			
			$plain_text .= "- - - - - - - - - - - -\n$plain_text_link_love\n$footer_text\n";
			
			/* Initialize header filter vars */
			$this->header_from_name = stripslashes( $form_from_name );
			$this->header_from = $form_from;
			$this->header_content_type = ( $format == '' || $format == 'html' ) ? 'text/html' : 'text/plain';
			
			/* Set wp_mail header filters to send an HTML email */
			add_filter( 'wp_mail_from_name', array( &$this, 'mail_header_from_name' ) );
			add_filter( 'wp_mail_from', array( &$this, 'mail_header_from' ) );
			add_filter( 'wp_mail_content_type', array( &$this, 'mail_header_content_type' ) );
			
			$html_email = $header . $message . $footer;
			
			/* Send the mail */
			foreach ( $form_to as $email ) {
				wp_mail( $email, $form_subject, $html_email, '', $attachments );
			}
			
			/* Kill the values stored for header name and email */
			unset( $this->header_from_name );
			unset( $this->header_from );
			
			/* Remove wp_mail header filters in case we need to override for notifications */
			remove_filter( 'wp_mail_from_name', array( &$this, 'mail_header_from_name' ) );
			remove_filter( 'wp_mail_from', array( &$this, 'mail_header_from' ) );
			
			/* Send auto-responder email */
			if ( $form_notification_setting !== '' ) :
				
				/* Assign notify header filter vars */
				$this->header_from_name = stripslashes( $form_notification_email_name );
				$this->header_from = $form_notification_email_from;
				
				/* Set the wp_mail header filters for notification email */
				add_filter( 'wp_mail_from_name', array( &$this, 'mail_header_from_name' ) );
				add_filter( 'wp_mail_from', array( &$this, 'mail_header_from' ) );
				
				/* Decode HTML for message so it outputs properly */
				$notify_message = ( $form_notification_message !== '' ) ? html_entity_decode( $form_notification_message ) : '';
				
				/* Either prepend the notification message to the submitted entry, or send by itself */				
				if ( $form_notification_entry !== '' )
					$auto_response_email = $header . '<p style="font-size: ' . $text_font_size . ' font-weight: normal; margin: 14px 0 14px 0; font-family: ' . $font_family . ' color: ' . $text_color . ' padding: 0;">' . $notify_message . '</p>' . $message . $footer;
				else
					$auto_response_email = $header . '<table cellspacing="0" border="0" cellpadding="0" width="100%"><tr><td colspan="2" class="mainbar" align="left" valign="top" width="600" style="' . $alt_row . '"><p style="font-size: ' . $text_font_size . ' font-weight: normal; margin: 14px 0 14px 0; font-family: ' . $font_family . ' color: ' . $text_color . ' padding: 0;">' . $notify_message . '</p></td></tr>' . $footer;
				
				$attachments = ( $form_notification_entry !== '' ) ? $attachments : '';
				
				/* Send the mail */
				wp_mail( $copy_email, $form_notification_subject, $auto_response_email, '', $attachments );
			endif;
			
		elseif ( isset( $_REQUEST['visual-form-builder-submit'] ) ) :
			/* If any of the security checks fail, provide some user feedback */
			if ( $_REQUEST['vfb-spam'] !== '' || !is_numeric( $_REQUEST['vfb-secret'] ) || strlen( $_REQUEST['vfb-secret'] ) !== 2 )
				wp_die( __( 'Ooops! Looks like you have failed the security validation for this form. Please go back and try again.' , 'visual-form-builder-pro'), '', array( 'back_link' => true ) );
		endif;
	}
	
	/**
	 * Validate the input
	 * 
	 * @since 1.3
	 */
	public function validate_input( $data, $type ) {
		if ( strlen( $data ) > 0 ) :
			switch( $type ) {
				
				case 'email' :
					if ( !is_email( $data ) )
						wp_die( __( 'Not a valid email address', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
				break;
				
				case 'number' :
					if ( !is_numeric( $data ) )
						wp_die( __( 'Not a valid number.', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
				break;
				
				case 'digits' :
					if ( !is_int( $data ) )
						wp_die( __( 'Not a valid digit. Please enter a number without a decimal point.', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
				break;
				
				case 'phone' :
					if ( strlen( $data ) > 9 && preg_match( '/^((\+)?[1-9]{1,2})?([-\s\.])?((\(\d{1,4}\))|\d{1,4})(([-\s\.])?[0-9]{1,12}){1,2}$/', $data ) )
						return true; 
					else
						wp_die( __( 'Not a valid phone number. Most US/Canada and International formats accepted.', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
				break;
				
				case 'url' :
					if ( !preg_match( '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $data ) )
						wp_die( __( 'Not a valid URL.', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
				break;
				
				default :
					return true;
				break;
			}
		endif;
	}
	
	/**
	 * Make sure the User Agent string is not a SPAM bot
	 * 
	 * @since 1.3
	 */
	public function isBot() {
		$bots = array( 'Indy', 'Blaiz', 'Java', 'libwww-perl', 'Python', 'OutfoxBot', 'User-Agent', 'PycURL', 'AlphaServer', 'T8Abot', 'Syntryx', 'WinHttp', 'WebBandit', 'nicebot');
	 
		$isBot = false;
		
		foreach ( $bots as $bot ) {
			if ( strpos( $_SERVER['HTTP_USER_AGENT'], $bot ) !== false )
				$isBot = true;
		}
	 
		if ( empty($_SERVER['HTTP_USER_AGENT'] ) || $_SERVER['HTTP_USER_AGENT'] == ' ' )
			$isBot = true;
	 
		return $isBot;
	}
	
	/**
	 * Set the wp_mail_from_name
	 * 
	 * @since 1.0
	 */
	public function mail_header_from_name() {
		return $this->header_from_name;		
	}
	
	/**
	 * Set the wp_mail_from
	 * 
	 * @since 1.0
	 */
	public function mail_header_from() {
		return $this->header_from;		
	}
	
	/**
	 * Set the wp_mail_content_type
	 * 
	 * @since 1.0
	 */
	public function mail_header_content_type() {
		return $this->header_content_type;		
	}
}

/* On plugin activation, install the databases and add/update the DB version */
register_activation_hook( __FILE__, array( 'Visual_Form_Builder_Pro', 'install_db' ) );
?>