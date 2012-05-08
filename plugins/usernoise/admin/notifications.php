<?php
class UN_Notifications {
	function __construct(){
		if (is_admin())
			add_action('init', array(&$this, '_init'));
	}
	
	public function _init(){
		if (current_user_can('manage_options') && !un_get_option(UN_ENABLED))
			add_action('admin_notices', array(&$this, '_notice_usernoise_disabled'));
	}
	
	public function _notice_usernoise_disabled(){
		global $parent_file;
		if ($parent_file == 'options-general.php' && isset($_REQUEST['page']) && $_REQUEST['page'] == 'usernoise')
			return;
		?>
		<div class="error">
			<p>
				<?php echo sprintf(__('Usernoise is disabled now. You can enable and configure it at the <a href="%s">settings page</a>.', 'usernoise'), admin_url('options-general.php?page=usernoise'))?>
			</p>
		</div>
		<?php
	}
}
new UN_Notifications;
