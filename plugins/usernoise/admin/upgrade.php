<?php
class UN_Upgrade {
	var $pro_url = 'http://codecanyon.net/item/usernoise-advanced-modal-feedback-debug/1420436?ref=karevn';
	var $h;

	function __construct(){
		$this->h = new HTML_Helpers_0_4;
		if (!$this->usernoisepro_active() || !$this->usernoisepro_installed()){
				add_filter('un_options', array(&$this, '_pro_options_stub'));
		}
		add_action('admin_notices', array(&$this, 'action_admin_notices'));
		if (!$this->plugin_installed('All in One Email'))
			add_action('pof_before_page_title', array(&$this, '_pof_before_page_title'));
	}
	
	public function _pro_options_stub($options){
		$images_url = usernoise_url('/images');
		$options []= array('type' => 'tab', 'title' => __('Pro Settings', 'usernoise'));
		$options []= array('type' => 'custom', 'title' => __('Upgrade to Pro', 'usernoise'),
			'html' => "<a href=\"%s\" class=\"un-upgrade-button\" target='_new'>" . __('Upgrade to Pro', 'usernoise') . "</a>" .
					sprintf(__("Please <a href=\"%s\" class=\"un-upgrade\" target='_new'>Upgrade to Pro</a> to enable the settings below.", 'usernoise'), $this->pro_url),
			'notitle' => true, 
			'nowrapper' => true
			 );
		$options []= array('type' => 'checkbox', 'name' => '',
			'title' => __('Enable feedback list &amp; discussions.', 'usernoise'),
			'label' => __('Enable feedback list &amp; discussions.', 'usernoise'),
			'default' => true,
			'disabled' => true,
			'legend' => __("Screenshots", 'usernoise') . 
				"<br><a href='$images_url/banner-feedback-list.png' class='thickbox'><img src='$images_url/banner-feedback-list.png' width='150' class='thickbox'></a> " . 
				"<a href='$images_url/banner-item-discussion.png' class='thickbox'><img src='$images_url/banner-item-discussion.png' width='150' class='thickbox'></a>",);
		$options []= array('type' => 'checkbox', 'name' => '',
			'title' => __('Gather advanced debug info', 'usernoise'),
			'label' => __('Gather advanced debug info', 'usernoise'),
			'legend' => __("Screenshots", 'usernoise') . 
				"<br><a href='$images_url/banner-debug-info-overview.png' class='thickbox'><img src='$images_url/banner-debug-info-overview.png' height='100' class='thickbox'></a> " .
				"<a href='$images_url/banner-debug-info-sql-queries.png' class='thickbox'><img src='$images_url/banner-debug-info-sql-queries.png' height='100' class='thickbox'></a> " . 
				"<a href='$images_url/banner-debug-info-filters.png' class='thickbox'><img src='$images_url/banner-debug-info-filters.png' height='100' class='thickbox'></a> ". 
				"<a href='$images_url/banner-debug-info-query_vars.png' class='thickbox'><img src='$images_url/banner-debug-info-query_vars.png' height='100' class='thickbox'></a>",
			'disabled' => true);
		$options []= array('type' => 'section', 'title' => __('Button', 'usernoise'));
		$options []= array('type' => 'checkbox', 'name' => '',
			'title' => __('Hide Feedback button', 'usernoise'),
			'label' => __('Hide Feedback button', 'usernoise'),
			'legend' => __('You can hide Feedback button to show it programmatically.', 'usernoise'),
			'disabled' => true);
		$options []= array('type' => 'text', 'name' => '',
			'title' => __('Alternate "Feedback" button ID', 'usernoise'),
			'class' => 'small',
			'legend' => __('Usernoise window will be shown upon click of element with that ID. For example, if you have the next link: <code>&lt;a href="#" id="my-link"&gt;some link&lt;/a&gt;</code>, put "my-link" here.', 'usernoise'),
			'disabled' => true);
		$options []= array('type' => 'textarea', 'name' => '',
			'title' => __('Custom CSS properties', 'usernoise-pro'),
			'legend' => __('Usernoise button custom CSS properties. Remove /* and */ to enable. Don\'t put here HTML tags.', 'usernoise-pro'),
			'default' => "/*\r\ncolor: white;\r\nbackground: #444;\r\nfont: 14px bold;\r\nborder: 2px solid white;\r\n*/",
			'rows' => '6',
			'disabled' => true);
		$options []= array('type' => 'section', 'title' => __('Form design', 'usernoise-pro'));
		$options []= array('type' => 'textarea', 'name' => '', 'disabled' => true, 
			'title' => __('Custom CSS for Usernoise window', 'usernoise-pro'),
			'rows' => 6,
			'legend' => __("You can override the form's style by applying your own CSS. Don't forget to remove comment symbols and do not put HTML tags here.", 'usernoise-pro'),
);
		return $options;
	}
	
	public function _pof_before_page_title($namespace){
		global $parent_file;
		if (!isset($parent_file) || $parent_file != 'options-general.php' || $_REQUEST['page'] != 'usernoise')
			return;
		$h = $this->h;
		$h->link_to($h->_img('http://cdn.karevn.com/usernoise/all-in-one-email.png'), 
			'http://codecanyon.net/item/all-in-one-email-for-wordpress/1290390?ref=karevn',
			array('id' => 'all-in-one-email'));
	}
	
	function action_admin_notices(){
		global $parent_file;
		if (!$this->usernoisepro_active()  && 
			isset($parent_file) && $parent_file == 'edit.php?post_type=' . FEEDBACK){
		?>
		<div class="error">
			<p>
				<?php if (!$this->usernoisepro_installed()): ?>
					<?php _e(sprintf('You are using Usernoise without Usernoise Pro installed. Consider installing <a href="%s">Usernoise Pro</a> - it adds really nice features.', $this->pro_url), 'usernoise') ?>
				<?php else: ?>
					<?php if (!$this->usernoisepro_active()): ?>
						<?php _e(sprintf('Usernoise Pro is installed, but is not active. You can activate it at <a href="%s">Plugins page</a>.', admin_url('plugins.php')), 'usernoise') ?>
					<?php endif ?>
				<?php endif ?>
			</p>
		</div>
		<?php
		}
	}
	
	function usernoisepro_active(){
		return defined('UNPRO_VERSION');
	}
	
	function plugin_installed($name){
		if (!function_exists('get_plugins')){
			require_once(ABSPATH . "/wp-admin/includes/plugin.php");
		}
		foreach(get_plugins() as $path => $info){
			if ($info['Name'] == $name){
				return true;
			}
		}
		return false;
	}
	
	function usernoisepro_installed(){
		return $this->plugin_installed('Usernoise Pro');
	}
}

$un_upgrade = new UN_Upgrade;
?>