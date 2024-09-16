<?php

class MOVIETRACKER {

	public $dbConnection;
	public $color_increment = 0;
	public $errors = array();
	protected $loggerArray = array();
	protected $currentUser = 0;
	protected $oldNewDiffString = '';
	protected $logSettings = array(
		'logType' => 'DB',
		'logName' => 'GENERAL',
		'loggerLevel' => 'MEDIUM',
		'movieId' => 0,
		'userId' => 0,
		'loggingIsActive' => 'Y'
	);
	protected $excludeFromExport = array(
		'errors',
		'dbConnection',
		'color_increment',
		'logArray',
		'currentUser',
		'loggerArray',
		'logSettings',
		'excludeFromExport',
		'oldNewDiffString'
	);


	/**
	 * Instantiates the Object
	 */
	public function __construct() {
		$this->dbConnection = DBCONN::getInstance();
		$this->currentUser = (MOVIETRACKER::getCurrentUserId() > 0) ? MOVIETRACKER::getCurrentUserId() : 0;
		$this->logSettings['userId'] = $this->currentUser;
		$this->logSettings['loggerLevel'] = $_SERVER['LOGGER_LEVEL'];
		$this->logSettings['logName'] = get_class($this);
	}


	/**
	 * A generic method to keep track of alternating table row colors. Used by most classes that draw tables.
	 * @see $color_increment
	 * @param string $id - Allows adding a row ID so it can be referenced in the DOM later
	 * @param string $additionalClass - Allows additional CSS classes for styling
	 * @return string
	 */
	public function row_color($id = "", $additionalClass = "") {
		$rowId = ($id != "") ? "id='".$id."'" : "" ;
		$this->color_increment++;
		$counter = ($this->color_increment / 2);
		if (is_integer($counter)) { $row_color = "<tr ".$rowId." class='row_color1 ".$additionalClass."'>"; }
		else { $row_color = "<tr ".$rowId." class='row_color2 ".$additionalClass."'>"; }
		return $row_color;
	}


	/**
	 * A generic method to create a "mailto" link
	 * @param string $email The email address the link should go to
	 * @param string $name The name of the person receiving the email
	 * @param string $color The color of the link
	 * @return string
	 */
	public static function make_mailto_link($email, $name, $color = '') {
		if ($email == '' || $name == '') { return "&nbsp;"; }
		else {
			$color = ($color != '') ? "color: ".$color.";" : '';
			return (string) "<a href='mailto:".$email."' title='Send email' style='text-decoration: none; ".$color."'>".$name."</a>";
		}
	}


	/**
	 * A generic method to build HTML Form Select Menus based on passed in values
	 * @param array $choices
	 * @param string $selected
	 * @return string
	 */
	public function build_select_menu($choices = array(), $selected = '') {
		$returnString = '';
		if (count($choices) == 0) { return "<option value=''>No Values</option>\n"; }
		else {
			foreach ($choices as $k => $v) {
				$isSelected = ($k == $selected) ? "selected='selected'" : "";
				$returnString .= "<option value='".$k."' label='".$v."' ".$isSelected.">".$v."</option>\n";
			}
		}
		return $returnString;
	}


	// Try and change header messages to use an array instead of single messages
	// Find all the places that try to set the old header message and replace with this
	public static function add_to_header_messages($messageString) {
		if (!isset($_SESSION[$_SERVER['VHOST']]['headerMessages']) || !is_array($_SESSION[$_SERVER['VHOST']]['headerMessages'])) {
			$_SESSION[$_SERVER['VHOST']]['headerMessages'] = array($messageString => $messageString);
		}
		else { $_SESSION[$_SERVER['VHOST']]['headerMessages'][$messageString] = $messageString; }
	}


	// Try and change header messages to use an array instead of single messages
	// Find all the places that try to call handle_save_submit_message and replace with this
	public static function display_header_messages() {
		$rs = '';
		if (isset($_SESSION[$_SERVER['VHOST']]['headerMessages']) && count($_SESSION[$_SERVER['VHOST']]['headerMessages']) > 0) {
			$rs = "<div id='saveSubmitMessageBox' class='mod_error'>";
			$rs .= "<img src='/eas/web/images/btn_close.gif' height='15' width='15' border='0' title='Close' alt='Close' style='cursor: pointer; float: right;' onclick='jQuery(\"#saveSubmitMessageBox\").remove();' />";
			foreach ($_SESSION[$_SERVER['VHOST']]['headerMessages'] as $msg) { $rs .= $msg."<br />\n"; }
			$rs .= "</div>";
		}
		$_SESSION[$_SERVER['VHOST']]['headerMessages'] = array();
		return $rs;
	}


	/**
	 * Removes leading and trailing space from string
	 * Sets all text to lower case
	 * Sets first letters of each word to upper case
	 * @param string $stringText
	 * @return string
	 */
	public function clean_name_strings($stringText) { return utf8_encode(ucwords(strtolower(trim($stringText)))); }

	public function ucname($string) {
		$string = ucwords(strtolower(trim($string)));
		foreach (array('-', '\'') as $delimiter) {
			if (strpos($string, $delimiter) !== false) {
				$string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
			}
		}
		return utf8_encode($string);
	}



	/**
	 * Takes an array, cleans and preps it for using in a query for when a
	 *	prepared statement cannot be used such as when using "in".
	 * @param array $inArray
	 * @param string $typeAs the type the elements should be
	 * @return string
	 */
	public function array_to_string($inArray = array(), $typeAs = 'STRING') {
		$rs = '';
		if (is_array($inArray)) {
			if ($typeAs == 'INT') {
				$tmpArray = array();
				foreach ($inArray as $myInt) { $tmpArray[] = (string) intval($myInt); }
				$rs = implode(",",$tmpArray);
			}
			elseif ($typeAs == 'FLOAT') {
				$tmpArray = array();
				foreach ($inArray as $myFloat) { $tmpArray[] = (string) floatval($myFloat); }
				$rs = implode(",",$tmpArray);
			}
			else {
				$tmpArray = array();
				foreach ($inArray as $myStr) { $tmpArray[] = (string) preg_replace("/'/", "''", $myStr); }
				$rs = "'".implode("','",$tmpArray)."'";
			}
		}
		return (string) $rs;
	}


	/**
	 * Gets the current User ID from the SESSION
	 * @static
	 * @return int
	 */
	public static function getCurrentUserId() {
		$userId = 0;
		if (isset($_SESSION[$_SERVER['VHOST']]['user_login']['userId'])) { $userId = $_SESSION[$_SERVER['VHOST']]['user_login']['userId']; }
		return intval($userId);
	}
	public static function setCurrentUserId($userId) { if (is_numeric($userId)) { $_SESSION[$_SERVER['VHOST']]['user_login']['userId'] = intval($userId); } }

	/**
	 * @static
	 * @return int
	 */
	public static function getCurrentMovieId() {
		$movieId = 0;
		if (isset($_SESSION[$_SERVER['VHOST']]['current_movie']['movieId'])) { $movieId = $_SESSION[$_SERVER['VHOST']]['current_movie']['movieId']; }
		return intval($movieId);
	}
	public static function setCurrentMovieId($movieId) { if (is_numeric($movieId)) { $_SESSION[$_SERVER['VHOST']]['current_movie']['movieId'] = intval($movieId); } }




	/**
	 * Builds a string of JavaScript files to import
	 *  if used, the files names can be changed for version updates without changing all the files
	 * @static
	 * @param array $include - Array of names of JS files
	 * @return string
	 */
	public static function importJS($include = array()) {
		$rs = '';
		$availableScripts = array(
			'jquery' => 				'jquery-2.1.0.min.js',
			'jquery.ui' => 				'jquery-ui-1.10.4.custom.min.js',
			'dhfCombined' => 			'dhfCombined.js',
			'chosen' => 				'chosen_v1.4.2/chosen.jquery.min.js'
		);
		if (is_array($include) && count($include) > 0) {
			foreach ($include as $scriptName) {
				if (isset($availableScripts[$scriptName])) {
					$rs .= '<script type="text/javascript" src="/js/'.$availableScripts[$scriptName].'"></script>'."\n";
				}
			}
		}
		return $rs;
	}


	public static function importCSS($include = array()) {
		$rs = '';
		$availableStyleSheets = array(
			'base' 		=> 	'base.css',
			'jquery.ui' => 	'redmond/jquery-ui-1.10.4.custom.min.css',
			'chosen'	=>	'chosen_v1.4.2/chosen.min.css'
		);
		if (is_array($include) && count($include) > 0) {
			foreach ($include as $sheetName) {
				if ($sheetName == 'chosen') {
					$rs .= '<link type="text/css" rel="stylesheet" href="/js/chosen_v1.4.2/chosen.css" />'."\n";
				}
				elseif (isset($availableStyleSheets[$sheetName])) {
					$rs .= '<link type="text/css" rel="stylesheet" href="/css/'.$availableStyleSheets[$sheetName].'" />'."\n";
				}
			}
		}
		return $rs;
	}


	/**
	 * Pass in an array of variable names that should not be exported to a temporary object for logging etc
	 * @return void
	 */
	public function add_to_excludeFromExport($additionalExclude = array()) {
		if (count($additionalExclude) > 0) {
			foreach ($additionalExclude as $xAdd) {
				if (!in_array($xAdd, $this->excludeFromExport) && $xAdd != '') { $this->excludeFromExport[] = $xAdd; }
			}
		}
	}


	/**
	 * Takes an existing object and sets all the variables to another object to get around the "Private", "Protected" issues
	 * @param object $sourceClass
	 * @return object
	 */
	public function logableObj($sourceClass, $excludeArray = array()) {
		$tmpObj = new stdClass();
		if (!(is_array($excludeArray) && count($excludeArray) > 0)) { $excludeArray = $this->excludeFromExport; }
		foreach ($sourceClass as $k => $v) { if (!in_array($k, $excludeArray) && !is_object($v) && !is_resource($v)) { $tmpObj->$k = $v; } }
		return $tmpObj;
	}


	/**
	 * Method used instead of the default PHP "mail" function
	 * Takes the same inputs plus an optional DB Connection Object
	 * Allows over riding addresses, headers etc for different addresses
	 * @param string $toEmail
	 * @param string $subject
	 * @param string $body
	 * @param string $headers
	 */
	public function send_email($toEmail, $subject, $body, $headers = '', $ruleFilter = '') {
		if ($_SERVER['CAN_SEND_EMAIL'] == 'YES') {
			$body .= "\n\nProprietary (Internal Use Only)\nNot for use or disclosure outside the companies except under written agreement.";
			if ($_SERVER['EMAIL_TO_TYPE'] != 'PROD') {
				$body = $body."\n\nAppended Headers:\nTo: ".$toEmail."\n".$headers;
				$toEmail = $_SERVER['DEFAULT_TO_EMAIL_ADDRESS'];
				$fromEmail = $_SERVER['DEFAULT_FROM_EMAIL_ADDRESS'];
				$headers = "From: ".$fromEmail."\n";
				$headers .= "Reply-To: ".$fromEmail."\n";
				if ($ruleFilter != '') { $headers .= "X-Rule-Filter: ".$ruleFilter."\n"; }
				else { $headers .= "X-Rule-Filter: ".$_SERVER['EMAIL_X_HEADER']."\n"; }
			}
			try {
				$rc = mail($toEmail, $subject, $body, $headers);
				if (!$rc) {
					$errorString = "Could not send email:\n";
					$errorString .= "Headers: ".$headers."\n";
					$errorString .= "Sent to: ".$toEmail."\n";
					$errorString .= "Subject: ".$subject."\n";
					$errorString .= "Body:\n".$body."\n";
					$this->addToLog('ERROR', $errorString);
				}
			}
			catch(Exception $e) {
				$errorString = "Could not send email:\n";
				$errorString .= "Headers: ".$headers."\n";
				$errorString .= "Sent to: ".$toEmail."\n";
				$errorString .= "Subject: ".$subject."\n";
				$errorString .= "Body:\n".$body."\n";
				$errorString .= $e->getMessage();
				throw new Exception($errorString);
			}
		}
		else {
			$loggerArray = array();
			if ($headers != '') { $loggerArray[] = "Headers:\n".$headers."\n"; }
			$loggerArray[] = "Sent to: ".$toEmail;
			$loggerArray[] = "Subject: ".$subject."\n";
			$loggerArray[] = $body."\n";
			$this->addToLog('ERROR', implode("\n", $loggerArray));
		}
	}


	/**
	 * Takes a string to log along with the level to log it under
	 * @param string $logLevel
	 * @param string $logEntry
	 * @return void
	 */
	public function addToLog($logLevel, $logEntry) {
		if (!isset($this->loggerArray[$logLevel]) || !is_array($this->loggerArray[$logLevel])) { $this->loggerArray[$logLevel] = array($logEntry); }
		else { array_unshift($this->loggerArray[$logLevel], $logEntry); }
	}


	/**
	 * Takes a string to log along with the level to log it under
	 *	inputs the log items opposite of addToLog()
	 * @param string $logLevel
	 * @param string $logEntry
	 * @return void
	 */
	public function pushToLog($logLevel, $logEntry) {
		if (!isset($this->loggerArray[$logLevel]) || !is_array($this->loggerArray[$logLevel])) { $this->loggerArray[$logLevel] = array($logEntry); }
		else { array_push($this->loggerArray[$logLevel], $logEntry); }
	}


	/**
	 * Takes the array of logs and writes them to File or DB depending on setting. Entries are grouped by level
	 * @param array $passVars
	 * @return void
	 */
	protected function save_log_array($passVars = array()) {
		$logType = (isset($passVars['logType'])) ? $passVars['logType'] : $this->logSettings['logType'];
		$logName = (isset($passVars['logName'])) ? $passVars['logName'] : $this->logSettings['logName'];
		$loggerLevel = (isset($passVars['loggerLevel'])) ? $passVars['loggerLevel'] : $this->logSettings['loggerLevel'];
		$movieId = (isset($passVars['movieId'])) ? $passVars['movieId'] : $this->logSettings['movieId'];

		if ($this->logSettings['loggingIsActive'] == 'Y') {
			if (isset($this->loggerArray['DEBUG']) && count($this->loggerArray['DEBUG']) > 0) {
				$debugLoggerObj = new LOGGER('FILE', $logName, $loggerLevel);
				$stringToLog = " New Entry for Level DEBUG\n\n";
				$stringToLog .= implode("\n\n", $this->loggerArray['DEBUG'])."\n\n";
				$stringToLog .= "--------------------------------------------\n";
				if (count($this->loggerArray['DEBUG']) > 0) { $debugLoggerObj->log_string($stringToLog, 'DEBUG', $movieId); }
				unset($this->loggerArray['DEBUG']);
				$debugLoggerObj->__destruct();
				unset($debugLoggerObj);
			}

			if (count($this->loggerArray) > 0) {
				$loggerObj = new LOGGER($logType, $logName, $loggerLevel);
				foreach ($this->loggerArray as $eventLevel => $eventEntries) {
					if ($eventLevel != 'DEBUG' && count($eventEntries) > 0) {
						if ($logType == 'FILE') {
							$stringToLog = " New Entry for Level ".$eventLevel."\n\n";
							$stringToLog .= implode("\n\n", $eventEntries)."\n\n";
							$stringToLog .= "--------------------------------------------\n";
							if (count($eventEntries) > 0) { $loggerObj->log_string($stringToLog, $eventLevel, $movieId); }
						}
						elseif ($logType == 'DB') {
							$stringToLog = implode("\n", $eventEntries);
							$loggerObj->set_userId($this->logSettings['userId']);
							$loggerObj->log_string(implode("\n\n", $eventEntries), $eventLevel, $movieId);
						}
					}
				}
				$loggerObj->__destruct();
				unset($loggerObj);
			}
		}
		$this->loggerArray = array();
	}


	/**
	 * Compares a fresh copy of the object from the database with the current values
	 * in order to log the differences
	 * @return void
	 */
	protected function compare_old_new_objects($objId) {
		$className = get_class($this);
		$oldObj = new $className($objId);
		foreach ($oldObj as $k => $v) {
			if (!in_array($k, $this->excludeFromExport) && !is_object($v) && !is_array($v) && !is_resource($v)) {
				if ($oldObj->$k != $this->$k) {
					$this->oldNewDiffString .= "Variable \"".$k."\" changed from \"".$oldObj->$k."\" to \"".$this->$k."\"\n";
				}
			}
		}
	}

	/**
	 * Used to provide the suffix for numbers such as 1st
	 * @return string
	 */
	public function ordinal_suffix($num){
		if($num < 11 || $num > 13){
			 switch($num % 10){
				case 1: return 'st';
				case 2: return 'nd';
				case 3: return 'rd';
			}
		}
		return 'th';
	}


	/**
	 * When the object goes out of scope, handles any closing actions
	 * Makes sure that any logging events get saved
	 * @return void
	 */
	public function __destruct() { if (count($this->loggerArray) > 0) { $this->save_log_array(); } }
}

// Auto load function used to auto include class files when a class is instantiated.
function movieTrackerAutoload($class_name) {
	$directory = $_SERVER["DOCUMENT_ROOT"].'/../php_classes/';
	if (file_exists($directory.$class_name.'.inc')) { require_once($directory.$class_name.'.inc'); return; }
}
spl_autoload_register('movieTrackerAutoload');
?>