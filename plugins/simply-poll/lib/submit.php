<?php
global $wp, $logger;

// Check if poll is set (also can be used to check for direct access)
if( isset($_POST['poll']) ) {

	$logger->logVar($_POST, '$_POST');

	// Set our poll variables
	$pollID		= (int)$_POST['poll'];
	$simplyPoll	= new SimplyPoll();	
	$answer		= null;
	
	
	// A vote has been made
	if( isset($_POST['answer']) ) {
			
		$logger->log('The int `'.$_POST['answer'].'` has been accepted');
		
		$answer = $_POST['answer'];
	
		// Check if we have the 'sptaken' cookie before trying to get data
		if(isset($_COOKIE['sptaken']))
			$taken	= $_COOKIE['sptaken'];
		else
			$taken	= null;

		$taken		= unserialize($taken);	// Unsearlize $taken to get an array
		$taken[]	= $pollID;				// Add this poll's ID to the $taken array
		$taken		= serialize($taken);	// Serialize $taken array ready to be stored again
		
		setcookie('sptaken', $taken, time()+315569260, '/');

	} else {
		$logger->log('The no answer accepted');
	}

	// No back url has been set so treat it as a Javascript call
	if( !isset($_POST['backurl']) ) {
		
		$return = array(
			'answer'	=> $simplyPoll->submitPoll($pollID, $answer), // This function will add the results
			'pollid'	=> $pollID
		);
		$json = json_encode($return);
		
		$logger->logVar($json, '$json');
		
		echo $json;

	} else {
		
		/**
		 * This block of code is pretty useless till I have a solution for none JS users
		 */
		$simplyPoll->submitPoll($pollID, $answer);
		
		$regex = '/(.[^\?]*)/';		
		$querystring = preg_replace($regex, '', $_POST['backurl']);

		if( $querystring ) {
			preg_match($regex, $_POST['backurl'], $matches);
			$url = $matches[0].$querystring.'&';
			
		} else {
			$url = $_POST['backurl'].'?';
		}
		
		$location = $url.'simply-poll-return='.$answer;

		header('Location: '.$location);

	}

} else {
	echo SP_DIRECT_ACCESS;
}