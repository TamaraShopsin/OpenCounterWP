<?php

class SimplyPoll {

	private	$pollData;
	private	$pollDB;		// Stores the DB class
	private $pollStrings;	// Store the custom strings


	/**
	 * Simply Poll construct
	 * Access the Simply Poll's database
	 * 
	 * @param bool $enque Set enqued files
	 */
	public function __construct($enque=true) {
		// Establish our DB class
		$this->pollDB = new SimplyPollDB();
	}
	
	
	/*************************************************************************/
	
	
	/**
	 * Poll Database
	 * Access the Simply Poll's database
	 * 
	 * @return object
	 */
	public function pollDB() {
		return $this->pollDB;
	}
	
	
	/*************************************************************************/
	
	
	/**
	 * Display Poll
	 * Gives the HTML for the poll to display on the front-end
	 * 
	 * @param array $args
	 * @return string
	 */
	public function displayPoll(array $args) {
		
		$limit = get_option('sp_limit');

		if( isset($args['id']) ) {
			$pollid		= $args['id'];
			$poll		= $this->grabPoll($pollid);
			
			if( isset($poll['question']) ) {
				$question	= stripcslashes($poll['question']);
				$answers	= $poll['answers'];
				$totalvotes = $poll['totalvotes'];
				
				foreach( $answers as $key => $answer ) {
					$answers[$key]['answer'] = stripcslashes($answer['answer']);
				}
				ob_start();

				$postFile = plugins_url(SP_SUBMIT, dirname(__FILE__));
				$thisPage = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

				$userCannotTakePoll = false;

				if(
					(
						$limit == 'yes' && isset($_COOKIE['sptaken']) && 
						in_array($args['id'], unserialize($_COOKIE['sptaken']))
					) || 
					isset($_GET['simply-poll-return'])  
				) {
					$userCannotTakePoll = true;
				}

				include(SP_DIR.SP_DISPLAY);
				$content = ob_get_clean();
				return $content;
			}
			
		}
		
	}
	
	
	/*************************************************************************/
	
	
	/**
	 * Submit Poll
	 * Passes back the poll results to return a JSON feed of responses. Can
	 * also just pass back previous results without passing an answer.
	 * 
	 * @param int $pollID
	 * @param int $answer
	 * @return int
	 */
	public function submitPoll($pollID, $answer=null) {
		
		global $logger;
	
		// The user has provided an answer
		if( isset($answer) ) {
			
			$poll = $this->grabPoll($pollID); // Grab the current results
			
			$totalVotes = 0;
			
			// Update the count of the answer
			$current = $poll['answers'][$answer]['vote'];
			++$current;
			$poll['answers'][$answer]['vote'] = $current;


			// Count the total votes
			foreach($poll['answers'] as $key => $thisAnswer){
				$totalVotes = $totalVotes + $thisAnswer['vote'];
			}
			
			
			$poll['totalvotes']	= $totalVotes;						// Update the total count
			$success			= $this->pollDB->setPollDB($poll);	// Push the results back to store
			$answer				= $poll['answers'][$answer];		// Provide feedback on answer
			
			$logger->logVar($answer, '$answer');
			
			return $answer;
			
		} else{
			return null;
		}
	}
	
	
	/*************************************************************************/


	/**
	 * Grab Poll
	 * Gets the current state of the the poll
	 *
	 * @param int $id
	 * @return array
	 */
	public function grabPoll($id=null) {
		
		$poll = $this->pollDB->getPollDB($id); // get the results from the the DB
		
		// If we set an ID then only return the single node
		if (isset($poll[0])) {
			$poll = $poll[0];
			$poll['answers'] = unserialize($poll['answers']);
		}
		
		return $poll;
	}
	
	
	/*************************************************************************/


	/**
	 * Grab String
	 * Pulls the stored string
	 *
	 * @param	string $name
	 * @return	string
	 */
	public function grabString($string) {

	}
	

}