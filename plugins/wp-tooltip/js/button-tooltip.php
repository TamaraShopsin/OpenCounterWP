<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Insert a Tooltip</title>
	
	<style type='text/css' src='/wp-includes/js/tinymce/themes/advanced/skins/wp_theme/dialog.css'></style>
	<style type='text/css'>
	body { background: #f1f1f1; }
	#button-dialog { }
	#button-dialog div { padding: 10px 0; }
	#button-dialog label { display: block; margin: 0 8px 8px 0; color: #333; }
	#button-dialog input[type=text] { display: block; padding: 3px 5px; width: 80%; }
	#button-dialog input[type=submit] { padding: 5px; } 
	</style>
	
	<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.js'></script>
	<script type='text/javascript' src='/wp-includes/js/tinymce/tiny_mce_popup.js'></script>
	
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('form').submit(function(e) {
			ButtonDialog.insert(ButtonDialog.local_ed)
			
			e.preventDefault();
		});
		
		var ButtonDialog = {
			local_ed : 'ed',
			init : function(ed) {
				ButtonDialog.local_ed = ed;
				tinyMCEPopup.resizeToInnerSize();
			},
			insert : function insertButton(ed) {
				// Try and remove existing style / blockquote
				tinyMCEPopup.execCommand('mceRemoveNode', false, null);
		 
				// set up variables to contain our input values
				var content = jQuery('input[name=tooltip]').val();
				var text = jQuery('input[name=text]').val();
				var url = jQuery('input[name=url]').val();
				
				var output = '';
		 
				// setup the output of our shortcode
				output = '[tooltip ';
					output += 'content="' + content + '" ';
					output += 'url="' + url + '" ';
					
				// check to see if the TEXT field is blank
				if(text) {	
					output += ']'+ text + '[/tooltip]';
				}
				// if it is blank, use the selected text, if present
				else {
					output += ']'+ButtonDialog.local_ed.selection.getContent() + '[/tooltip]';
				}
				tinyMCEPopup.execCommand('mceReplaceContent', false, output);
				
				// Return
				tinyMCEPopup.close();

				return false
			}
		};
		tinyMCEPopup.onInit.add(ButtonDialog.init, ButtonDialog);
	});
	</script>
</head>
<body>
	<div id="button-dialog">
		<form action="/" method="get" accept-charset="utf-8">
			<div>
				<label for="tooltip">Tooltip Content</label>
				<input type="text" name="tooltip" value="" id="tooltip" />
			</div>
			
			<div>
				<label for="text">Text (Leave blank if text is selected)</label>
				<input type="text" name="text" value="" id="text" />
			</div>
			
			<div>
				<label for="url">URL (Optional)</label>
				<input type="text" name="url" value="" id="url" />
			</div>
			
			<div>
				<input type='submit' value='Add a Tooltip' />
			</div>
		</form>
	</div>
</body>
</html>