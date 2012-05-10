<?php
/*
Plugin Name: Gravity Forms Signature Add-On
Plugin URI: http://www.gravityforms.com
Description: Creates a Gravity Forms signature field that allows users to sign online using a mouse or stylus.
Version: 1.0.beta1
Author: Rocketgenius
Author URI: http://www.rocketgenius.com

------------------------------------------------------------------------
Copyright 2012 Rocketgenius, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

add_action('parse_request',  array('GFSignature', 'display_signature'));
add_action('init',  array('GFSignature', 'init'), 9);

register_activation_hook( __FILE__, array("GFSignature", "add_permissions"));

class GFSignature {

    private static $path = "gravityformssignature/signature.php";
    private static $url = "http://www.gravityforms.com";
    private static $slug = "gravityformssignature";
    private static $version = "1.0.beta1";
    private static $min_gravityforms_version = "1.6.1";

    //Plugin starting point. Will load appropriate files
    public static function init(){

        if(RG_CURRENT_PAGE == "plugins.php"){
            //loading translations
            load_plugin_textdomain('gravityformssignature', FALSE, '/gravityformssignature/languages' );

            add_action('after_plugin_row_' . self::$path, array('GFSignature', 'plugin_row') );

           //force new remote request for version info on the plugin page
            self::flush_version_info();
        }

        if(!self::is_gravityforms_supported()){
           return;
        }

        if(is_admin()){
            //loading translations
            load_plugin_textdomain('gravityformssignature', FALSE, '/gravityformssignature/languages' );

            add_filter("gform_entry_field_value", array("GFSignature", "signature_entry_detail"), 10, 5);
            add_filter('gform_entries_field_value', array("GFSignature", "signature_entry_list"), 10, 3);

            add_filter("transient_update_plugins", array('GFSignature', 'check_update'));
            add_filter("site_transient_update_plugins", array('GFSignature', 'check_update'));
            add_action('install_plugins_pre_plugin-information', array('GFSignature', 'display_changelog'));

            add_filter("gform_add_field_buttons",array('GFSignature', 'add_signature_field'));
            add_action("gform_editor_js", array('GFSignature','editor_script'));
            add_filter('gform_tooltips', array('GFSignature','tooltips'));
            add_action("gform_field_standard_settings", array('GFSignature','field_settings'), 10, 2);
            add_action("gform_field_advanced_settings", array('GFSignature','advanced_field_settings'), 10, 2);
            add_filter("gform_field_type_title", array('GFSignature', "signature_title"), 10, 2);
        }
        else
        {
            add_action("gform_pre_submission",array("GFSignature", "pre_submission_handler"));
            add_filter("gform_field_validation", array("GFSignature", "signature_validation"), 10, 4);
            add_filter("gform_merge_tag_filter", array("GFSignature", "merge_tag_filter"), 10, 4);
            add_action("gform_enqueue_scripts",  array("GFSignature","enqueue_scripts"), 10, 2);
        }

        //renders signature field. admin and front end
        add_action("gform_field_input", array("GFSignature", "signature_input"), 10, 5);

        //integrating with Members plugin
        if(function_exists('members_get_capabilities'))
            add_filter('members_get_capabilities', array("GFSignature", "members_get_capabilities"));

        if(self::is_signature_page()){

            //loading upgrade lib
            require_once("plugin-upgrade.php");

            //loading Gravity Forms tooltips
            require_once(GFCommon::get_base_path() . "/tooltips.php");
            add_filter('gform_tooltips', array('GFSignature', 'tooltips'));

            //runs the setup when version changes
            self::setup();

        }
    }

    public static function enqueue_scripts($form, $is_ajax){
        $signatures = GFCommon::get_fields_by_type($form, array("signature"));
        if(!empty($signatures)){
            wp_enqueue_script("super_signature_script", self::get_base_url() . "/super_signature/ss.js", array("jquery"), self::$version, false);
        }
    }

    public static function signature_title($title, $field_type){
        if($field_type == "signature")
            $title = __("Signature", "gravityformssignature");

        return $title;
    }


    //--------------   Automatic upgrade ---------------------------------------------------

    public static function flush_version_info(){
        require_once("plugin-upgrade.php");
        RGSignatureUpgrade::set_version_info(false);
    }

    public static function plugin_row(){
        if(!self::is_gravityforms_supported()){
            $message = sprintf(__("Gravity Forms " . self::$min_gravityforms_version . " is required. Activate it now or %spurchase it today!%s"), "<a href='http://www.gravityforms.com'>", "</a>");
            RGSignatureUpgrade::display_plugin_message($message, true);
        }
        else{
            $version_info = RGSignatureUpgrade::get_version_info(self::$slug, self::get_key(), self::$version);

            if(!$version_info["is_valid_key"]){
                $new_version = version_compare(self::$version, $version_info["version"], '<') ? __('There is a new version of Gravity Forms Signature Add-On available.', 'gravityformssignature') .' <a class="thickbox" title="Gravity Forms Signature Add-On" href="plugin-install.php?tab=plugin-information&plugin=' . self::$slug . '&TB_iframe=true&width=640&height=808">'. sprintf(__('View version %s Details', 'gravityformssignature'), $version_info["version"]) . '</a>. ' : '';
                $message = $new_version . sprintf(__('%sRegister%s your copy of Gravity Forms to receive access to automatic upgrades and support. Need a license key? %sPurchase one now%s.', 'gravityformssignature'), '<a href="admin.php?page=gf_settings">', '</a>', '<a href="http://www.gravityforms.com">', '</a>') . '</div></td>';
                RGSignatureUpgrade::display_plugin_message($message);
            }
        }
    }

    //Displays current version details on Plugin's page
    public static function display_changelog(){
        if($_REQUEST["plugin"] != self::$slug)
            return;

        //loading upgrade lib
        require_once("plugin-upgrade.php");

        RGSignatureUpgrade::display_changelog(self::$slug, self::get_key(), self::$version);
    }

    public static function check_update($update_plugins_option){
        require_once("plugin-upgrade.php");

        return RGSignatureUpgrade::check_update(self::$path, self::$slug, self::$url, self::$slug, self::get_key(), self::$version, $update_plugins_option);
    }

    private static function get_key(){
        if(self::is_gravityforms_supported())
            return GFCommon::get_key();
        else
            return "";
    }
    //---------------------------------------------------------------------------------------

    //Returns true if the current page is an Feed pages. Returns false if not
    private static function is_signature_page(){
        $current_page = trim(strtolower(rgget("page")));
        $signature_pages = array("gf_signature");

        return in_array($current_page, $signature_pages);
    }

    //Creates or updates datasignature tables. Will only run when version changes
    private static function setup(){
        update_option("gf_signature_version", self::$version);
    }

    //Adds feed tooltips to the list of tooltips
    public static function tooltips($tooltips){
        $signature_tooltips = array(
            "signature_background_color" => "<h6>" . __("Background Color", "gravityformssignature") . "</h6>" . __("Select the color to be used for the background of the signature area", "gravityformssignature"),
            "signature_border_color" => "<h6>" . __("Border Color", "gravityformssignature") . "</h6>" . __("Select the color to be used for the border around the signature area", "gravityformssignature"),
            "signature_pen_color" => "<h6>" . __("Pen Color", "gravityformssignature") . "</h6>" . __("Select the color of the pen to be used for the signature", "gravityformssignature"),
            "signature_box_width" => "<h6>" . __("Width", "gravityformssignature") . "</h6>" . __("Enter the width for the signature area in pixels.", "gravityformssignature"),
            "signature_border_style" => "<h6>" . __("Border Style", "gravityformssignature") . "</h6>" . __("Select the border style to be used around the signature area", "gravityformssignature"),
            "signature_pen_size" => "<h6>" . __("Pen Size", "gravityformssignature") . "</h6>" . __("Select the width of the pen to be used for the signature", "gravityformssignature"),
            "signature_border_width" => "<h6>" . __("Border Width", "gravityformssignature") . "</h6>" . __("Select the border width to be used around the signature area", "gravityformssignature"),
            "signature_message" => "<h6>" . __("Message", "gravityformssignature") . "</h6>" . __("Write the message you would like to be sent. You can insert fields submitted by the user by selecting them from the 'Insert Variable' drop down.", "gravityformssignature")
        );
        return array_merge($tooltips, $signature_tooltips);
    }

    public static function advanced_field_settings($position, $form_id){
        if($position == 50){
            wp_enqueue_script("maskedinput", GFCommon::get_base_url() . "/js/jquery.maskedinput-1.3.min.js?version=" . GFCommon::$version, array("jquery"));
            ?>
            <li class="box_width_setting field_setting">
                <label for="field_signature_box_width">
                    <?php _e("Field Width", "gravityformssignature"); ?>
                    <?php gform_tooltip("signature_box_width") ?>
                </label>
                <input id="field_signature_box_width" type="text" style="width:40px" onkeyup="SetSignatureBoxWidth(jQuery(this).val());" onchange="SetSignatureBoxWidth(jQuery(this).val());"/> px
            </li>
            <?php
        }
    }

    public static function field_settings($position, $form_id){

        //create settings on position 25 (right after Field Label)
        if($position == 25){
            ?>
            <li class="background_color_setting field_setting gform_setting_left_half">
                <label for="field_signature_background_color">
                    <?php _e("Background Color", "gravityformssignature"); ?>
                    <?php gform_tooltip("signature_background_color") ?>
                </label>
                <?php GFFormDetail::color_picker("field_signature_background_color", "SetSignatureBackColor") ?>
            </li>
            <li class="border_color_setting field_setting gform_setting_right_half">
                <label for="field_signature_border_color">
                    <?php _e("Border Color", "gravityformssignature"); ?>
                    <?php gform_tooltip("signature_border_color") ?>
                </label>
                <?php GFFormDetail::color_picker("field_signature_border_color", "SetSignatureBorderColor") ?>
            </li>
            <li class="border_width_setting field_setting gform_setting_left_half">
                <label for="field_signature_border_width">
                    <?php _e("Border Width", "gravityformssignature"); ?>
                    <?php gform_tooltip("signature_border_width") ?>
                </label>
                <select id="field_signature_border_width"  onchange="SetSignatureBorderWidth(jQuery(this).val());">
                    <option value="0">None</option>
                    <option value="1">Small</option>
                    <option value="2">Medium</option>
                    <option value="3">Large</option>
                </select>
            </li>
            <li class="border_style_setting field_setting gform_setting_right_half">
                <label for="field_signature_border_style">
                    <?php _e("Border Style", "gravityformssignature"); ?>
                    <?php gform_tooltip("signature_border_style") ?>
                </label>
                <select id="field_signature_border_style"  onchange="SetSignatureBorderStyle(jQuery(this).val());">
                    <option>Dotted</option>
                    <option>Dashed</option>
                    <option>Groove</option>
                    <option>Ridge</option>
                    <option>Inset</option>
                    <option>Outset</option>
                    <option>Double</option>
                    <option>Solid</option>
                </select>
            </li>

            <li class="pen_color_setting field_setting gform_setting_left_half">
                <label for="field_signature_pen_color">
                    <?php _e("Pen Color", "gravityformssignature"); ?>
                    <?php gform_tooltip("signature_pen_color") ?>
                </label>
                <?php GFFormDetail::color_picker("field_signature_pen_color", "SetSignaturePenColor") ?>
            </li>
            <li class="pen_size_setting field_setting gform_setting_right_half">
                <label for="field_signature_pen_size">
                    <?php _e("Pen Size", "gravityformssignature"); ?>
                    <?php gform_tooltip("signature_pen_size") ?>
                </label>
                <select id="field_signature_pen_size"  onchange="SetSignaturePenSize(jQuery(this).val());">
                    <option value="1">Small</option>
                    <option value="2">Medium</option>
                    <option value="3">Large</option>
                </select>
            </li>

            <?php
        }
    }

    public static function editor_script(){
        ?>
        <script type='text/javascript'>

            //adding setting to signature fields
            fieldSettings["signature"] = ".pen_size_setting, .border_width_setting, .border_style_setting, .box_width_setting, .pen_color_setting, .border_color_setting, .background_color_setting, .conditional_logic_field_setting, .error_message_setting, .label_setting, .admin_label_setting, .rules_setting, .visibility_setting, .description_setting, .css_class_setting";

            //binding to the load field settings event to initialize the checkbox
            jQuery(document).bind("gform_load_field_settings", function(event, field, form){

                var backColor = field.backgroundColor == undefined ? "" :  field.backgroundColor;
                jQuery("#field_signature_background_color").val(backColor);
                SetColorPickerColor("field_signature_background_color", backColor);

                var borderColor = field.borderColor == undefined ? "" :  field.borderColor;
                jQuery("#field_signature_border_color").val(borderColor);
                SetColorPickerColor("field_signature_border_color", borderColor);

                var penColor = field.penColor == undefined ? "" :  field.penColor;
                jQuery("#field_signature_pen_color").val(penColor);
                SetColorPickerColor("field_signature_pen_color", penColor);

                var boxWidth = field.boxWidth == undefined || field.boxWidth.trim().length == 0 ? "300" :  field.boxWidth;
                jQuery("#field_signature_box_width").val(boxWidth);

                var borderStyle = field.borderStyle == undefined ? "" :  field.borderStyle;
                jQuery("#field_signature_border_style").val(borderStyle);

                var borderWidth = field.borderWidth == undefined ? "" :  field.borderWidth;
                jQuery("#field_signature_border_width").val(borderWidth);

                var penSize = field.penSize == undefined ? "" :  field.penSize;
                jQuery("#field_signature_pen_size").val(penSize);

            });

            jQuery(document).ready(function(){
                jQuery('#field_signature_box_width').mask('?9999', {placeholder:' '});
            });

            function SetSignatureBackColor(color){
                //saving selected color
                SetFieldProperty("backgroundColor", color);

                //updating UI to match new color
                jQuery(".field_selected .gf_signature_container").css("background-color", color);
            }

            function SetSignatureBorderColor(color){
                //saving selected color
                SetFieldProperty("borderColor", color);

                //updating UI to match new color
                jQuery(".field_selected .gf_signature_container").css("border-color", color);
            }

            function SetSignaturePenColor(color){
                //saving selected color
                SetFieldProperty("penColor", color);
            }

            function SetSignatureBoxWidth(size){
                //saving selected box width
                SetFieldProperty("boxWidth", size);
            }

            function SetSignatureBorderStyle(style){
                //saving selected color
                SetFieldProperty("borderStyle", style);

                //updating UI to match new color
                jQuery(".field_selected .gf_signature_container").css("border-style", style);
            }

            function SetSignatureBorderWidth(size){
                //saving selected border width
                SetFieldProperty("borderWidth", size);

                //updating UI to match border width
                jQuery(".field_selected .gf_signature_container").css("border-width", size + "px");
            }

            function SetSignaturePenSize(size){
                //saving selected pen size
                SetFieldProperty("penSize", size);
            }

        </script>
        <?php
    }

    public static function merge_tag_filter($value, $merge_tag, $options, $field){
        if($merge_tag == "all_fields" && $field["type"] == "signature")
            return false;
        else if($merge_tag == $field["id"] && $field["type"] == "signature")
        {
            $path_info = pathinfo($value);
            $filename = $path_info["filename"];
            $signature_path = home_url()."?page=gf_signature&signature={$filename}";
            $newvalue= $signature_path;
            return $newvalue;
        }
        else
            return $value;
    }

    public static function signature_entry_list($value, $form_id, $field_id){
        $form = RGFormsModel::get_form_meta($form_id);
        $field = RGFormsModel::get_field($form, $field_id);
        if($field["type"]=="signature" && !empty($value))
        {
            $path_info = pathinfo($value);
            $filename = $path_info["filename"];
            $signature_path = home_url()."?page=gf_signature&signature={$filename}";
            $thumb = GFCommon::get_base_url() . "/images/doctypes/icon_image.gif";
            $newvalue="<a href='$signature_path' target='_blank' title='" . __("Click to view", "gravityforms") . "'><img src='$thumb'/></a>";
            return $newvalue;
        }
        else
        {
            return $value;
        }
    }

    public static function get_signature_url($filename){
        $path_info = pathinfo($filename);
        $filename = $path_info["filename"];
        return home_url()."?page=gf_signature&signature={$filename}";
    }

    public static function signature_entry_detail($value, $field, $lead, $form){
        if($field["type"]=="signature" && !empty($value))
        {
            $signature_path = self::get_signature_url($value);
            $newvalue="<a href='$signature_path' target='_blank' title='" . __("Click to view", "gravityforms") . "'><img width='100' src='$signature_path'></a>";
            return $newvalue;
        }
        else
        {
            return $value;
        }
    }

    public static function display_signature($wp){
        $is_signature = rgget("page") == "gf_signature";
        if(!$is_signature)
            return;

        $imagename = rgget("signature") . ".png";

        $folder = RGFormsModel::get_upload_root() . "signatures/";
        $image = imagecreatefrompng($folder . $imagename);

        header("Content-type: image/png");
        imagepng($image);
        imagedestroy($image);

        exit;
    }

    public static function add_signature_field($field_groups){
        foreach($field_groups as &$group){
            if($group["name"] == "advanced_fields"){
                $group["fields"][] = array("class"=>"button", "value" => __("Signature", "gravityforms"), "onclick" => "StartAddField('signature');");
                break;
            }
        }
        return $field_groups;
    }

    public static function signature_input($input, $field, $value, $lead_id, $form_id){
        if($field["type"] == "signature"){

            $unique_id = IS_ADMIN || $form_id == 0 ? "input_{$field["id"]}" : "input_" . $form_id . "_{$field["id"]}";

            $supports_canvas = true;

            require_once('super_signature/Browser.php');
            $browser = new Browser();
            if($browser->getBrowser() == Browser::BROWSER_IE)
                $supports_canvas = $browser->getVersion() >= 9;

            $bgcolor = rgempty("backgroundColor", $field) ? "#FFFFFF" : rgar($field, "backgroundColor");
            $bordercolor = rgempty("borderColor", $field) ? "#DDDDDD" : rgar($field, "borderColor");
            $pencolor = rgempty("penColor", $field) ? "#000000" : rgar($field, "penColor");
            $boxwidth = rgblank(rgget("boxWidth", $field)) ? "300" : rgar($field, "boxWidth");
            $borderstyle = rgempty("borderStyle", $field) ? "Dashed" : rgar($field, "borderStyle");
            $borderwidth = rgblank(rgget("borderWidth", $field)) ? "2" : rgar($field, "borderWidth");
            $pensize = rgblank(rgget("penSize", $field)) ? "2" : rgar($field, "penSize");

            if(is_admin()){
                $boxwidth = "460"; //box width is hardcoded to 460 in the admin
                $input = "<div class='gf_signature_container' style='height:180px; width:{$boxwidth}px; border: {$borderwidth}px {$borderstyle} {$bordercolor}; background-color:{$bgcolor};'></div>";
            }
            else{
                $input = "";

                $signature_filename = !empty($value) ? $value : rgpost("{$unique_id}_signature_filename");
                if(!empty($signature_filename)){
                    $input .= "<div id='{$unique_id}_signature_image'>".
                                "<img src='" . self::get_signature_url($signature_filename) . "' width='{$boxwidth}px'/>".
                                "   <div>".
                                "       <a href='#' onclick='jQuery(\"#{$unique_id}_signature_filename\").val(\"\"); jQuery(\"#{$unique_id}_signature_image\").hide(); jQuery(\"#{$unique_id}_Container\").show(); return false;'>" . __("sign again", "gravityformssignature") . "</a>" .
                                "   </div>" .
                             "</div>" .
                             "<input type='hidden' value='{$signature_filename}' name='{$unique_id}_signature_filename' id='{$unique_id}_signature_filename'/>";
                }

                $style = !empty($signature_filename) ? "style='display:none'" : "";
                $input .= "<div id='{$unique_id}_Container' {$style}>
                                <input type='hidden' class='gform_hidden' name='{$unique_id}_valid' id='{$unique_id}_valid'/>";

                if ($supports_canvas){
                    $input .= "<canvas id='{$unique_id}' width='{$boxwidth}' height='180'></canvas>";
                }
                else{
                    $input .= "<div id='{$unique_id}' style='width:{$boxwidth}px; height:180px; border:{$borderstyle} {$borderwidth}px {$bordercolor}; background-color:{$bgcolor};'></div>";
                }

                $input .= "
                          </div>
                          <script type='text/javascript'>" .
                                "var obj{$unique_id} = new SuperSignature({SignObject:'{$unique_id}',BackColor: '{$bgcolor}', PenSize: '{$pensize}', PenColor: '{$pencolor}',SignWidth: '{$boxwidth}',SignHeight: '180' ,BorderStyle:'{$borderstyle}',BorderWidth: '{$borderwidth}px',BorderColor: '{$bordercolor}', RequiredPoints: '15',ClearImage:'" . self::get_base_url() . "/super_signature/refresh.png', PenCursor:'" . self::get_base_url() . "/super_signature/pen.cur', Visible: 'true', ErrorMessage: '', StartMessage: '', SuccessMessage: ''});".
                                "obj{$unique_id}.Init();".
                                "jQuery('#gform_{$form_id}').submit(function(){".
                                "    jQuery('#{$unique_id}_valid').val(obj{$unique_id}.IsValid() ? '1' : '');".
                                "});".
                          "</script>";
            }
        }
        return $input;
    }

    public static function signature_validation($result, $value, $form, $field){
        if($field["type"] != "signature")
            return $result;

        $unique_id = "input_" . $form["id"] . "_{$field["id"]}";
        $is_invalid = $field["isRequired"] && rgempty("{$unique_id}_valid");

        $result["is_valid"] = !$is_invalid;
        $result["message"] = $is_invalid ? __("Please enter your signature.", "gravityformssignature") : "";

        //create image if signature is valid
        if(!$is_invalid){
            $filename = self::save_signature($unique_id . "_data", "temp_");
            $_POST["input_{$field["id"]}"] = $filename;
        }

        return $result;
    }

    public static function pre_submission_handler($form){

        $signature_fields = GFCommon::get_fields_by_type($form, array("signature"));
        foreach($signature_fields as $field){
            $input_name = "input_{$field["id"]}";
            $filename = !rgempty($input_name) ? rgpost($input_name) : rgpost("input_{$form["id"]}_{$field["id"]}_signature_filename");
            if(empty($filename)){
                //create signature file
                $filename = self::save_signature("input_{$form["id"]}_{$field["id"]}_data");
                $_POST["input_{$field["id"]}"] = $filename;
            }
            else{
                //rename signature file
                $new_name = str_replace("temp_", "", $filename);
                $folder = RGFormsModel::get_upload_root() . "/signatures/";

                rename($folder . $filename, $folder . $new_name);
                $_POST["input_{$field["id"]}"] = $new_name;
            }
        }
    }

    public static function save_signature($input_name, $name_prefix=""){
        require_once("super_signature/license.php");

        $signature_data = rgpost($input_name);

        $image = GetSignatureImage($signature_data);
        if(!$image)
            return "";

        $folder = RGFormsModel::get_upload_root() . "/signatures/";

        //abort if folder can't be created.
        if(!wp_mkdir_p($folder))
            return;

        $filename = $name_prefix . uniqid("", true) . ".png";
        $path = $folder . $filename;
        imagepng($image, $path, 4);
        imagedestroy($image);

        return $filename;
    }

    public static function add_permissions(){
        global $wp_roles;
        $wp_roles->add_cap("administrator", "gravityforms_signature");
        $wp_roles->add_cap("administrator", "gravityforms_signature_uninstall");
    }

    //Target of Member plugin filter. Provides the plugin with Gravity Forms lists of capabilities
    public static function members_get_capabilities( $caps ) {
        return array_merge($caps, array("gravityforms_signature", "gravityforms_signature_uninstall"));
    }

    public static function uninstall(){

        //removing options
        delete_option("gf_signature_version");

        //Deactivating plugin
        $plugin = "gravityformssignature/signature.php";
        deactivate_plugins($plugin);
        update_option('recently_activated', array($plugin => time()) + (array)get_option('recently_activated'));
    }

    private static function is_gravityforms_installed(){
        return class_exists("RGForms");
    }

    private static function is_gravityforms_supported(){
        if(class_exists("GFCommon")){
            $is_correct_version = version_compare(GFCommon::$version, self::$min_gravityforms_version, ">=");
            return $is_correct_version;
        }
        else{
            return false;
        }
    }

    private static function has_access($required_permission){
        $has_members_plugin = function_exists('members_get_capabilities');
        $has_access = $has_members_plugin ? current_user_can($required_permission) : current_user_can("level_7");
        if($has_access)
            return $has_members_plugin ? $required_permission : "level_7";
        else
            return false;
    }

    //Returns the physical path of the plugin's root folder
    private function get_base_path(){
        $folder = basename(dirname(__FILE__));
        return WP_PLUGIN_DIR . "/" . $folder;
    }

     //Returns the url of the plugin's root folder
    private function get_base_url(){
        $folder = basename(dirname(__FILE__));
        return plugins_url($folder);
    }
}
?>
