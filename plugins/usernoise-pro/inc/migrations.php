<?php 
class UNPRO_Migrations {
	public function __construct(){
		add_action('plugins_loaded', array(&$this, 'migrate'));
	}
	
	public function migrate(){
		$db_version = get_option('unpro_version');
		if ($db_version == UNPRO_VERSION)
			return;
		update_option('unpro_version', UNPRO_VERSION);
	}
}
$unpro_migrations = new UNPRO_Migrations();