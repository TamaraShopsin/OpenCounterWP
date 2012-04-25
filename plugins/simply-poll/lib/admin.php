<?php

class SimplyPollAdmin extends SimplyPoll{
	
	private $pollEdit;
	
	public function __construct(){
		
		parent::__construct(false);
		
		// Establish our DB class
//		$this->pollDB = new SimplyPollDB();
		
		add_action('admin_init',	array($this, 'enqueueFiles'));
		add_action('admin_menu',	array($this, 'addSimplyPollMenu'));
		
	}
	
	public function enqueueFiles() {		
		wp_enqueue_script('validator',	plugins_url('/script/validator.min.js',			dirname(__FILE__)),	false,	SP_VERSION);
		wp_enqueue_script('jqPlotMain',	plugins_url('/script/jqplot.min.js',			dirname(__FILE__)),	false,	SP_VERSION);
		wp_enqueue_script('jqPlotPie',	plugins_url('/script/jqplot.pieRenderer.js',	dirname(__FILE__)),	false,	SP_VERSION);
		wp_enqueue_script('masonry',	plugins_url('/script/masonry.min.js',			dirname(__FILE__)),	false,	SP_VERSION);
		
		wp_enqueue_script('jSimplyPollAdmin',	SP_JS_ADMIN,	false,	SP_VERSION);
		wp_register_style('spAdminCSS',			SP_CSS_ADMIN,	false,	SP_VERSION);
		
		wp_enqueue_style('jqplotcss');
		wp_enqueue_style('spAdminCSS');
	}

	/**
	 * Add menu items to admin
	 */
	public function addSimplyPollMenu() {
		
		$capability = 'manage_options';
		$parentPage = 'sp-poll';
		
		add_menu_page('Simply Poll', 'Polls', $capability, $parentPage, array($this, 'getAdminPageMain'),'', 6);
		
		add_submenu_page($parentPage,	__('Settings'),		__('Settings'),		$capability,	'sp-settings',	array($this, 'getAdminPageSettings'));
		add_submenu_page($parentPage,	__('Add New Poll'), __('Add New'),		$capability,	'sp-add',		array($this, 'getAdminPageAdd'));
		add_submenu_page('',			__('View Poll'),	__('View Poll'),	$capability,	'sp-view',		array($this, 'getAdminPageView'));
		add_submenu_page('',			__('Update Poll'),	__('Update Poll'),	$capability,	'sp-update',	array($this, 'getAdminPageUpdate'));
		add_submenu_page('',			__('Delete Poll'),	__('Delete Poll'),	$capability,	'sp-delete',	array($this, 'getAdminPageDelete'));
		add_submenu_page('',			__('Reset Poll'),	__('Reset Poll'),	$capability,	'sp-reset',		array($this, 'getAdminPageReset'));
	}	
	
	
	public function getAdminPageMain(){
		require(SP_DIR.'/'.SP_ADMIN_FOLDER.'/main.php');
	}
	public function getAdminPageSettings() {
		require(SP_DIR.'/'.SP_ADMIN_FOLDER.'/settings.php');
	}
	public function getAdminPageView(){
		require(SP_DIR.'/'.SP_ADMIN_FOLDER.'/view.php');
	}
	public function getAdminPageAdd(){
		require(SP_DIR.'/'.SP_ADMIN_FOLDER.'/edit.php');
	}
	public function getAdminPageUpdate(){
		require(SP_DIR.'/'.SP_ADMIN_FOLDER.'/edit.php');
	}
	public function getAdminPageDelete(){
		require(SP_DIR.'/'.SP_ADMIN_FOLDER.'/delete.php');
	}
	public function getAdminPageReset(){
		require(SP_DIR.'/'.SP_ADMIN_FOLDER.'/reset.php');
	}
	
	
	/**
	 * Add or Update a Poll
	 * 
	 * @param	array	$pollData
	 * @return	array
	 *************************************************************************/
	public function setEdit($pollData){
		
		$question		= $pollData['question'];
		$answers		= $pollData['answers'];
		$posted			= $pollData;
		$countAnswers	= 0;
		$error			= array();
		$newPoll		= false;
		$editPoll		= false;
		
		// Check to see if all required fields are entered
		
		
		// Does question have a value?
		if( $question ) {
			
			$pollForDB['question'] = $question;
			$pollForDS['question'] = htmlspecialchars( stripcslashes($question), ENT_QUOTES, get_bloginfo('charset') );
			unset($pollData['question']);
			
		} else {
			$error[] = __('No question given');
		}
		
		
		// Do we have answers
		if( $answers ) {
			
			$cntAnswers = 0;
			
			// Sort the data out
			foreach($answers as $key => $answer) {
				
				// Unset either way to clean the array
				unset($pollData['answers'][$key]); 
					
				if($answer['answer']){
					// We have an answer so build that back into the array with new values
					++$cntAnswers;
					
					// Add vote node if not there already
					if(isset($answer['vote'])){
						$vote = $answer['vote'];
					} else {
						$vote = 0;
					}
					
					$pollForDB['answers'][$cntAnswers]['answer']	= htmlspecialchars( stripcslashes($answer['answer']), ENT_QUOTES, get_bloginfo('charset') );
					$pollForDB['answers'][$cntAnswers]['vote']		= $vote;
					$pollForDS['answers'][$cntAnswers]['answer']	= stripcslashes($answer['answer']);
					$pollForDS['answers'][$cntAnswers]['vote']		= $vote;
					
				}
					
			}
			
			// Quick clean of the array node
			unset($pollData['answers']);
			
			// Do we have enough answers
			if( $cntAnswers <= 1 ) {
				$error[] = __('Need at least 2 answers');
			}
			
		} else {
			$error[] = __('No answers given');
		}
		
			
		// If we have no error then all good to go
		if( count($error) == 0 ) {
			
			$return = array();
			
			$pollID = $this->pushPollToDB($pollForDB, $pollData['polledit']);
			
			if( $pollID > 0 ){
				if ($pollData['polledit'] == 'new') {
					$return['success'] = __('New Poll Added');
				} elseif($pollData['polledit'] > 0) {
					$return['success'] = __('Poll Updated');
				}
				$return['pollid'] = $pollID;
			} else {
				$return['error'] = __('adding to the DB failed');
			}
			
			$pollForDS['return'] = $return;
			
			return $pollForDS;
			
		} else {
			
			$pollForDS['error'] = $error;
			return $pollForDS;
			
		}
			
		
	}

	
	
	/**
	 * Add New Poll to DB
	 * 
	 * @param	array	$poll
	 * @param	array	$editPoll
	 * @return	int
	 *************************************************************************/
	private function pushPollToDB($poll, $pollEdit){
		
		$pollData	= parent::pollDB()->getPollDB();
		
		$poll['active']		= true;
		$poll['totalvotes']	= 0;
		$poll['time']		= time();
		
		$pollData['polls'][] = $poll;
		
		if( $pollEdit == 'new' ) {
			// Add new poll
			return parent::pollDB()->newPollDB($poll);
			
		} elseif( $pollEdit > 0 ) {
			// Update poll
			$poll['id'] = $pollEdit;
			return parent::pollDB()->updatePollDB($poll);
		}
	}
}
