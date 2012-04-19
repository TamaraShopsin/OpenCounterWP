<?php
/**
 * Class that builds our Entries table
 * 
 * @since 1.2
 */
class VisualFormBuilder_Pro_Analytics {

	public function __construct(){
		global $wpdb;
		
		/* Setup global database table names */
		$this->field_table_name = $wpdb->prefix . 'vfb_pro_fields';
		$this->form_table_name = $wpdb->prefix . 'vfb_pro_forms';
		$this->entries_table_name = $wpdb->prefix . 'vfb_pro_entries';
		
		add_action( 'admin_init', array( &$this, 'display' ) );
	}
	
	public function display(){
		global $wpdb;
		
		$form_nav_selected_id = ( isset( $_REQUEST['form_id'] ) ) ? absint( $_REQUEST['form_id'] ) : '1';
		
		/* Query to get all forms */
		$order = sanitize_sql_orderby( 'form_id ASC' );
		$query = "SELECT * FROM $this->form_table_name ORDER BY $order";

		/* Build our forms as an object */
		$forms = $wpdb->get_results( $query );
		
		$entries = $wpdb->get_results( "SELECT DAY( date_submitted ) AS Day, MONTH( date_submitted ) AS Month, YEAR( date_submitted ) AS Year, COUNT(*) AS Count FROM $this->entries_table_name WHERE form_id = $form_nav_selected_id GROUP BY Day ORDER BY Count DESC" );

		?>
        
        <form method="post" id="analytics-switcher">
            <label for="form_id"><p style="margin:8px 0;"><em>Select which form analytics to view:</em></p></label> 
            <select name="form_id">
		<?php
		$count = $sum = $avg = 0;
		foreach ( $forms as $form ) {
			if ( $form_nav_selected_id == $form->form_id ) {
				$count = count( $entries );
				$busy_date = date( 'M d, Y', mktime( 0, 0, 0, $entries[0]->Month, $entries[0]->Day, $entries[0]->Year ) );
				$busy_count = $entries[0]->Count;
				
				foreach ( $entries as $entry ) {
					$sum += $entry->Count;
				}
				
				$avg = round( $sum / $count );
			}
			
			echo '<option value="' . $form->form_id . '"' . selected( $form->form_id, $form_nav_selected_id, 0 ) . ' id="' . $form->form_key . '">' . stripslashes( $form->form_title ) . '</option>';
		}
?>
		</select>
        <input type="submit" value="Select" class="button" id="Submit" name="Submit">
        </form>

        <div id="nav-menus-frame">
            <div id="menu-settings-column" class="metabox-holder">
                <div class="analytics-meta-boxes">
                    <h1>Entries Total</h1>
                    <h2><?php echo $sum; ?></h2>
                </div>
                <div class="analytics-meta-boxes">
                    <h1>Average per Day</h1>
                    <h2><?php echo $avg; ?></h2>
                </div>
                <div class="analytics-meta-boxes">
                    <h1>Your Busiest Day</h1>
                    <h2><?php echo $busy_date; ?></h2>
					<h3><?php echo $busy_count; ?> Entries</h3>
                </div>
            </div>
            
            <div id="menu-management-liquid" class="charts-container">
                <div class="charts-nav">
                    <a class="" href="#days">Days</a>
                    <a class="" href="#weeks">Weeks</a>
                    <a class="current" href="#months">Months</a>
                </div>
                
                <h2>Overview</h2>
                <div id="chart_div">
                	<div class="chart-loading">Loading... <img id="chart-loading" alt="" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="waiting" /></div>
                </div>
                <br class="clear" />
                
                <h2>Percentage Change Over Time</h2>
                <div id="data_table">
                	<div class="chart-loading">Loading... <img id="table-loading" alt="" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="waiting" /></div>
                </div>
            </div>
        </div>
<?php
	}
}
?>