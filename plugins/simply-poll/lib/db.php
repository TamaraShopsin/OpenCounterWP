<?php

class SimplyPollDB {
	
	private $pollData;
	
	public function __construct() {
		return true;
	}
	
	
	/**
	 * Save poll data to DB for a new poll
	 * 
	 * @param	array	$pollData
	 * @return	bool
	 *************************************************************************/
	public function newPollDB(array $pollData) {
		global $wpdb;
		
		$answers = serialize($pollData['answers']);
		
		$sql = '
			INSERT INTO 
				`'.SP_TABLE.'` (
					`question`,
					`answers`,
					`added`,
					`active`,
					`totalvotes`,
					`updated`
				) 
				VALUES (
					"'.mysql_escape_string($pollData['question']).'",
					"'.mysql_escape_string($answers).'",
					"'.(int)$pollData['time'].'",
					"'.(int)$pollData['active'].'",
					"'.(int)$pollData['totalvotes'].'",
					"'.(int)$pollData['time'].'"
				)
		';
			
		if( $wpdb->query($sql) ) {
			return $wpdb->insert_id;
		} else {
			return false;
		}
		
	}


	/**
	 * Save poll data to DB when updating a poll
	 *
	 * @param	$pollData
	 * @return	bool
	 *************************************************************************/
	public function updatePollDB(array $pollData) {
		global $wpdb;
		
		$answers = serialize($pollData['answers']);
		
		$sql = '
			UPDATE 
				`'.SP_TABLE.'` 
			SET 
				`question`	= \''.$pollData['question'].'\',
				`answers`	= \''.mysql_escape_string($answers).'\', 
				`updated`	= \''.(int)$pollData['time'].'\'
			WHERE 
				`id`		= '.$pollData['id'].'
		';
		
		if( $wpdb->query($sql) ) {
			return $pollData['id'];
		} else {
			return false;
		}
		
		return $wpdb->query($sql);
	}
	
	
	
	/**
	 * Grab poll data from DB
	 *
	 * @param	int		$id
	 * @return	array
	 *************************************************************************/
	public function getPollDB($id=null) {
		global $wpdb;
		
		if (isset($id)) {
			$sql = '
				SELECT
					*
				FROM
					`'.SP_TABLE.'`
				WHERE
					`id`	= '.$id.'
			';
			
			$data = $wpdb->get_results($sql, ARRAY_A);
			
			return $data;
			
		} else {

			if($this->pollData){
				
				return $this->pollData;
	
			} else {
				
				$sql = '
					SELECT 
						`id`, 
						`question` 
					FROM
						`'.SP_TABLE.'`
					ORDER BY 
						`id` ASC
				';
				
				$polls['polls'] = $wpdb->get_results($sql, ARRAY_A);
				
				if(!is_array($polls)){
					$polls = array();
				}
				$this->pollData = $polls;
				return $polls;
			}
		}
	}
	
	
	/**
	 * Save poll data to DB
	 *
	 * @param	$pollData
	 * @return	bool
	 *************************************************************************/
	public function setPollDB(array $pollData) {
		global $wpdb;
		
		$answers = serialize($pollData['answers']);
		
		$sql = '
			UPDATE 
				`'.SP_TABLE.'` 
			SET 
				`answers`		= \''.mysql_escape_string($answers).'\', 
				`totalvotes`	= '.(int)$pollData['totalvotes'].' 
			WHERE 
				`id`			= '.$pollData['id'].'
		';
		
		return $wpdb->query($sql);
	}
	
	
	/**
	 * Delete Poll
	 * 
	 * @param	int	$id
	 * @return	bool
	 *************************************************************************/
	public function deletePoll($id) {
		global $wpdb;
		
		$sql = '
			DELETE FROM 
				`'.SP_TABLE.'` 
			WHERE `id`			= '.$id.'
		';
		
		return $wpdb->query($sql);
	}	
	
	
	
	/**
	 * Reset Poll
	 * 
	 * @param	array	$pollData
	 * @return	bool
	 *************************************************************************/
	public function resetPoll(array $pollData) {
		global $wpdb;
		
		$answers = array();
		
		foreach($pollData['answers'] as $key => $answer) {
			$answers[$key] = array(
				'answer'	=> 	$answer['answer'],
				'vote'		=> 0
			);
		}
		
		$answers = serialize($answers);
		
		$sql = '
			UPDATE 
				`'.SP_TABLE.'` 
			SET 
				`answers`		= \''.mysql_escape_string($answers).'\', 
				`totalvotes`	= 0,
				`updated`		= '.(int)time().'
			WHERE 
				`id`			= '.$pollData['id'].'
		';
		
		return $wpdb->query($sql);
		
	}
}