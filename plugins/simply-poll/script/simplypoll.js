jQuery(function() {

	var $ = jQuery;							// Because `$` is easier than using `jQuery`
	$('.sp-poll form').submit(formProcess);	// Access formProcess() when the poll is submitted

	/**
	 * Form Process
	 * Process through the form 
	 * 
	 * @param object e
	 */
	function formProcess(e) {
		
		e.preventDefault();
		
		var poll	= $('input[name=poll]').val(),
			answer	= $('input[name=answer]:checked').val(),
			div		= $(this).parent(),
			action	= $(this).attr('action');

		$(this).slideUp('slow', function() {
			updatePoll(action, poll, answer);
		});
	}

	/**
	 * Update Poll
	 * Update the results from our AJAX query
	 * 
	 * @param string action
	 * @param int pollID
	 * @param int answer
	 */
	function updatePoll(action, pollID, answer) {
		
		var postData;

		if (answer > 0) {
			postData = {
				action:	'spAjaxSubmit',
				poll:	pollID,
				answer:	answer
			};

		} else {
			postData = {
				action:	'spAjaxSubmit',
				poll:	pollID
			};
		}
		
		
		var ajax = $.ajax({
			type:		'POST',
			url:		spAjax.url,
			data:		postData,
			dataType:	'JSON',
			success:	displayResults,
			error:		function(e, textStatus, errorThrown) {
							console.log('An error occured with `updatePoll()`', ajax);
							console.log(textStatus, errorThrown, e);
						}
		});
	
	}

	/**
	 * Display Results
	 * Shows the results when requested
	 * 
	 * @param object data
	 */
	function displayResults(data) {
		
		var postData = {
				action: 'spAjaxResults',
				pollid: data.pollid
			},
		
			html = $.ajax({
				type:		'POST',
				async:		false,
				url:		spAjax.url,
				data:		postData,
				dataType:	'html',
				error:		function(e, textStatus, errorThrown) {
								console.log('An error occured with `displayResults()`');
								console.log(textStatus, errorThrown, e);
							}
			}).responseText,
		
			pollID = '#poll-'+data.pollid;
		
		$(pollID).append(html);
	}

});