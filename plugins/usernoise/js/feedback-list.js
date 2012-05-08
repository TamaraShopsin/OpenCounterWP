jQuery(function($){
	$('select[name=_status] option[value=draft], select[name=_status] option[value=private]').remove();
	$('input[type=text][name=post_password]').parent().parent().parent().remove();
	$('a.editinline, span.edit a').parent().remove();
	$('a.add-new-h2, select[name=action] option[value=edit]').remove();
})