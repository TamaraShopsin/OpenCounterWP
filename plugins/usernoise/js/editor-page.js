jQuery(function($){
	if ( typeof QTags != 'undefined' ){
		usernoise_reply = new QTags('usernoise_reply', 'replybody', 'replywrapper', 'more,fullscreen');
	}
	$('#un-reply-submit').click(function(){
		$('#un-reply-loader').show();
		$.post(ajaxurl, 
				{action: 'un_feedback_reply', message: $('#replybody').val(), id: $('#post_ID').val(), 
					subject: $('#subject').val()},
					function(response){
						$('#un-reply-loader').hide();
						$('#replybody').val('');
						alert(response);
			});
	});
});