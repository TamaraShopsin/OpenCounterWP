<?php
/* Include the WP header so we can use WP functions and run a PHP page */
require_once ( preg_replace( '/wp-content.*/', 'wp-blog-header.php', __FILE__) );

/* If you don't have permission, get lost */
if ( !current_user_can( 'install_plugins' ) )
	wp_die('<p>'.__('You do not have sufficient permissions to view the preview for this site.').'</p>');


global $wpdb;

$form_table_name = $wpdb->prefix . 'vfb_pro_forms';

/* Tells us which form to get from the database */
$form_id = absint( $_REQUEST['form'] );

/* Query to get all forms */
$order = sanitize_sql_orderby( 'form_id DESC' );
$query = "SELECT * FROM $form_table_name WHERE form_id = $form_id ORDER BY $order";

/* Build our forms as an object */
$forms = $wpdb->get_results( $query );

/* Get sender and email details */
foreach ( $forms as $form ) {
	$form_title = $form->form_title;
	$form_subject = $form->form_email_subject;
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

/* Setup the link love */
if ( $link_love == '' || $link_love == 'yes' ) {
	$html_link_love = 'This email was built and sent using <a href="http://vfb.matthewmuro.com" style="font-size: ' . $footer_font_size . 'font-family: ' . $font_family . 'color:' . $link_color . '">Visual Form Builder Pro</a>.';
	$plain_text_link_love = 'This email was built and sent using<br/>Visual Form Builder Pro (http://vfb.matthewmuro.com)';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title><?php echo $form_title; ?></title>
</head>
<?php if ( $format == '' || $format == 'html' ): ?>
<body style="background-color: <?php echo $background_color; ?>">
<table class="bg1" cellspacing="0" border="0" style="background-color: <?php echo $background_color; ?>" cellpadding="0" width="100%">
    <tr>
        <td align="center">
            <table class="bg2" cellspacing="0" border="0" style="background-color: #ffffff;" cellpadding="0" width="600">
                <tr>
                    <td class="permission" align="center" style="background-color: <?php echo $background_color; ?>padding: 10px 20px 10px 20px;">&nbsp;</td>
                </tr>
                <tr>
                    <td class="header" align="left" style="background-color:<?php echo $header_color; ?>padding: 50px 20px 50px 20px;"><h1 style="font-family: <?php echo $font_family; ?>font-size: <?php echo $header_font_size; ?>font-weight:normal;margin:0;padding:0;color:#ffffff;"><?php echo $form_subject; ?></h1></td>
                </tr>
                <tr>
                    <td class="body" valign="top" style="background-color: <?php echo $row_color; ?>padding: 0 20px 20px 20px;">
                        <table cellspacing="0" border="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="mainbar" align="left" valign="top"><h2 style="font-size: <?php echo $fieldset_font_size; ?> font-weight: bold; margin: 10px 0 10px 0; font-family: <?php echo $font_family; ?> color: <?php echo $fieldset_color; ?> padding: 0;">Fieldset</h2>
                                    <table cellspacing="0" border="0" cellpadding="0" width="100%">
                                        <tr>
                                            <td colspan="2" style="background-color:<?php echo $section_color; ?>color:<?php echo $section_text_color; ?>"><h3 style="font-size: <?php echo $section_font_size; ?> font-weight: bold; margin: 14px 14px 14px 10px; font-family: <?php echo $font_family; ?> color: <?php echo $section_text_color; ?> padding: 0;">Section</h3></td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="mainbar" align="left" valign="top" width="100" style="border-bottom:1px solid <?php echo $border_color; ?>"><p style="font-size: <?php echo $text_font_size; ?> font-weight: bold; margin: 14px 0 14px 5px; font-family: <?php echo $font_family; ?> color: <?php echo $text_color; ?> padding: 0;">First Row:</p></td>
                                            <td class="mainbar" align="left" valign="top" width="300" style="border-bottom:1px solid <?php echo $border_color; ?>"><p style="font-size: <?php echo $text_font_size; ?> font-weight: normal; margin: 14px 0 14px 0; font-family: <?php echo $font_family; ?> color: <?php echo $text_color; ?> padding: 0;">Lorem ipsum</p></td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="mainbar" align="left" valign="top" width="100" style="background-color:#eeeeee;border-bottom:1px solid <?php echo $border_color; ?>"><p style="font-size: <?php echo $text_font_size; ?> font-weight: bold; margin: 14px 0 14px 5px; font-family: <?php echo $font_family; ?> color: <?php echo $text_color; ?> padding: 0;">Second Row:</p></td>
                                            <td class="mainbar" align="left" valign="top" width="300" style="background-color:#eeeeee;border-bottom:1px solid <?php echo $border_color; ?>"><p style="font-size: <?php echo $text_font_size; ?>font-weight: normal; margin: 14px 0 14px 0; font-family: <?php echo $font_family; ?> color: <?php echo $text_color; ?> padding: 0;">Lorem Ipsum</p></td>
                                        </tr>
                                        
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="footer" height="61" align="left" valign="middle" style="background-color: <?php echo $footer_color; ?> padding: 0 20px 0 20px; height: 61px; vertical-align: middle;"><p style="font-size: <?php echo $footer_font_size; ?> font-weight: normal; margin: 0; font-family: <?php echo $font_family; ?>line-height: 16px; color: <?php echo $footer_text_color; ?>padding: 0;"><!--This email was built and sent using <a href="http://visualformbuilder.com" style="font-size:<?php echo $footer_font_size; ?>color:<?php echo $link_color; ?>">Visual Form Builder</a>.-->
                    <?php echo $html_link_love; ?>
                    <?php echo $footer_text; ?>
                    </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="permission" align="center" style="background-color: <?php echo $background_color; ?> padding: 10px 20px 10px 20px;">&nbsp;</td>
    </tr>
</table>
</body>
<?php elseif ( $format == 'text' ): ?>
<body>
<table class="bg1" cellspacing="0" border="0" style="background-color: white;font-size:12px; font-family: 'Bitstream Vera Sans Mono',monaco,'Courier New',courier,monospace;" cellpadding="0" width="100%">
    <tr>
        <td>
        ============ <?php echo $form_subject; ?> =============
		</td>
    </tr>
    <tr>
        <td>
        ————————————————————————————<br />
        Fieldset<br />
        ————————————————————————————
		</td>
    </tr>
    <tr>
        <td>
        *** Section ***
		</td>
    </tr>
    <tr>
        <td>
        First Row: Lorem ipsum
		</td>
    </tr>
    <tr>
        <td>
        Second Row: Lorem ipsum
		</td>
    </tr>
    <tr>
        <td>
        - - - - - - - - - - - -<br />
        <?php echo $plain_text_link_love; ?>
		</td>
    </tr>
    <tr>
        <td>
        <?php echo $footer_text; ?>
		</td>
    </tr>
</table>
<?php endif; ?>
</html>