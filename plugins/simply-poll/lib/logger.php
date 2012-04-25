<?php

/**
 * Logger
 * A basic logging class to write logs to a file
 * 
 * @author Neil Sweeney <neil@wolfiezero.com>
 * @license Creative Commons Attribution-ShareAlike 3.0 Unported License
 * @link https://github.com/WolfieZero/logger
 */

class logger {
	
	private $fp;
	private $logFile	= 'log';
	private $mode		= 'a';
	private $dateFormat	= 'm.d.y @ H:i:s';
	private $seperator	= ' - ';
	
	/**
	 * Logger
	 * 
	 * @access public
	 * @param String $location Where the log file should be located
	 */
	function __construct($location='', $display=true) {
		$this->fp = fopen($location.$this->logFile, $this->mode);
		$this->display = $display;
	}
	
	
	/**
	 * Log
	 * Adds a record to the log file
	 * 
	 * @access public
	 * @param String $log The log entry
	 * @return bool
	 */
	public function log($log) {
		if($this->display) {
			
			$write = date($this->dateFormat).$this->seperator.$log."\r\n";
			fwrite($this->fp, $write);
			
			return true;
			
		} else {
			return false;
		}
	}
	
	
	/**
	 * Log Variable
	 * Takes a variable and does a print_r
	 * 
	 * @access public
	 * @param mixed $var The varible that is to be printed
	 * @param String $log The prefix to the variable print
	 * @return bool
	 */	
	public function logVar($var, $log=null) {
		if($this->display) {
			
			$write = date($this->dateFormat).$this->seperator;
			
			if ($log) $write .= $log.$this->seperator;
			
			$write .= print_r($var, true)."\r\n";
			fwrite($this->fp, $write);
			
			return true;
			
		} else {
			return false;
		}
	}
	
	
	/**
	 * Close
	 * Closes the log file
	 * 
	 * @access public
	 * @return bool
	 */
	public function close() {
		fclose($this->fp);

		return true;
	}
	
}