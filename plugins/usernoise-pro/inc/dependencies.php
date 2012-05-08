<?php
function usernoise_present(){
	if (!function_exists('get_plugins')){
		require_once(ABSPATH . "/wp-admin/includes/plugin.php");
	}
	foreach(get_plugins() as $path => $info)
		if ($info['Name'] == 'Usernoise') return true;
	return false;
}

function usernoise_active(){
	if (!function_exists('get_plugins')){
		require_once(ABSPATH . "/wp-admin/includes/plugin.php");
	}
	foreach(get_plugins() as $path => $info)
		if ($info['Name'] == 'Usernoise' && is_plugin_active($path)) return true;
	return false;
}

function usernoise_version_matches(){
	if (!function_exists('get_plugins')){
		require_once(ABSPATH . "/wp-admin/includes/plugin.php");
	}
	foreach(get_plugins() as $path => $info)
		if ($info['Name'] == 'Usernoise' && version_compare($info['Version'], REQUIRED_UN_VERSION) > -1)
			return true;
	return false;
}
function usernoise_dependency_ok(){
	return usernoise_present() && usernoise_active() && usernoise_version_matches();
	return false;
}

if (!usernoise_present() || !usernoise_version_matches())
	add_action('admin_notices', 'unpro_admin_notices_dependencies');

if (usernoise_present() && usernoise_version_matches() && !usernoise_active()){
	add_action('admin_notices', 'unpro_admin_notices_usernoise_activation');
}

function unpro_admin_notices_usernoise_activation(){
	?>
	<div class="error">
		<p>
			<?php _e(sprintf('Usernoise Pro requires Usernoise plugin that is not active. Please activate Usernoise at <a href="%s">%s</a> page.', admin_url('plugins.php'), __('Plugins'))) ?>
		</p>
	</div>
	<?php
}

function unpro_admin_notices_dependencies(){
	?>
	<div class="error">
		<p>
			<?php _e(sprintf('Usernoise Pro requires Usernoise v.%s plugin that is not installed. Please visit <a href="http://wordpress.org/extend/plugins/usernoise">WordPress plugin repository</a> and make sure you are using the required version.', REQUIRED_UN_VERSION)) ?>
		</p>
	</div>
	<?php
}
