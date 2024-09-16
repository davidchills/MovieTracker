<?php
/**
 * @todo Need to finish adding all the comments for documentation
 * @author Dave Hills
 * @subpackage LOGGER
 */
class LOGGER extends MOVIETRACKER {

	private $loggingType = 'FILE';
	private $logFileFullPath;
	private $loggingName = 'GENERAL.log';
	private $fileHandle;
	private $objectInstance = '';
	private $loggingIsActive = 'N';
	private $numberOfLinesLogged = 0;

	private $logLevelChoiceArray = array(
		'ALL' => 0,
		'EDIT' => 100,
		'ERROR' => 200,
		'HIGH' => 300,
		'MEDIUM' => 400,
		'LOW' => 500,
		'DEBUG' => 600,
		'SQL' => 700,
		'NONE' => 800
	);

	private	$logLevel = 'MEDIUM';

	private $userId = '';


	/**
	 * Constructor
	 * @param string $logType
	 * @param string $logName
	 * @param string $logLevel
	 * @return void
	 */
	public function __construct($logType = 'FILE', $logName = 'GENERAL', $logLevel = 'MEDIUM') {
		parent::__construct();
		$this->loggingIsActive = $_SERVER['MASTER_LOG_SWITCH'];
		$this->set_logType($logType);
		$this->set_logName($logName);
		$this->set_logLevel($logLevel);
		$this->setInstanceTimeStamp();
	}


	/**
	 * Opens a file handle for object to be able to write to
	 * @return void
	 */
	private function open_file_handle() {
		if (!is_resource($this->fileHandle)) {
			$this->set_log_file_full_path();
			$this->fileHandle = fopen($this->logFileFullPath, 'a');
		}
	}


	/**
	 * Creates a micro-second stamp to group log events together when writing to a file
	 * @return void
	 */
	private function setInstanceTimeStamp() {
		list($usec, $sec) = explode(" ", microtime());
		$this->objectInstance = intval((float)$usec + (float)$sec);
	}


	/**
	 * Logs the string to a file or database
	 * @param string $inString
	 * @param string $logLevel
	 * @param int $userId
	 * @return void
	 */
	public function log_string($inString, $logLevel = 'MEDIUM', $movieId = 0) {

		/*	Only logs if set to log and the incomming event level is >= the level setting	*/
		if ($this->get_loggingIsActive() == 'Y' && $this->compare_logLevel($logLevel) == 'YES') {
			if ($this->loggingType == 'FILE') {
				$this->numberOfLinesLogged++;
				$this->writeLogEntryToFile($inString);
			}
			elseif ($this->loggingType == 'DB') {

				$tmpLogName = $this->get_logDbName();
				$tmpLogLevel = $this->logLevelChoiceArray[$logLevel];
				$userId = $this->get_userId();

				$statement = $this->dbConnection->prepare("insert into class_logs (
				log_name,
				log_level,
				user_id,
				movie_id,
				log_desc
				) values (
				:logName,
				:logLevel,
				:userId,
				:movieId,
				:logDesc)");
				$statement->bindParam(':logName', $tmpLogName);
				$statement->bindParam(':logLevel', $tmpLogLevel);
				$statement->bindParam(':userId', $userId);
				$statement->bindParam(':movieId', $movieId);
				$statement->bindParam(':logDesc', $inString, PDO::PARAM_STR, strlen($inString));
				try { $statement->execute(); }
				catch (PDOException $e) {
					$errorString = date('Y-m-d H:i:s', time())." -\n";
					$errorString .= "Log Name: ".$tmpLogName."\n";
					$errorString .= "Log Level: ".$tmpLogLevel."\n";
					$errorString .= "Log Entry: ".$inString."\n";
					$errorString .= "Logged By: ".$userId."\n";
					$errorString .= "Movie ID: ".$movieId."\n";
					$errorString .= $e->getMessage();
					//	Backup - if trying to log to the DB fails - write to a file
					$this->write_db_errors_to_file($errorString."\n\n");
				}
			}
		}
	}


	/**
	 * Handles writing the error log to file if writing to the DB fails
	 * @return void
	 */
	private function write_db_errors_to_file($text) {
		$dbErrorLogFile = $_SERVER["DOCUMENT_ROOT"]."/../logs/db_logger_error.log";
		if (!file_exists($dbErrorLogFile)) { touch($dbErrorLogFile); }
		$tmpFileHandle = fopen($dbErrorLogFile, 'a');
		fwrite($tmpFileHandle, $text);
		fclose($tmpFileHandle);
	}


	/**
	 * Handles the normal write to file when that is set in the preference
	 * @return void
	 */
	private function writeLogEntryToFile($string) {
		if (!is_resource($this->fileHandle)) { $this->open_file_handle(); }
		if (is_resource($this->fileHandle)) {
			fwrite($this->fileHandle, $this->objectInstance." - ".date('Y-m-d H:i:s')." - ".$string);
		}
	}


	/**
	 * Sets the location of the file to write to, in case logging to file is set
	 * @return void
	 */
	private function set_log_file_full_path() {
		$this->logFileFullPath = $_SERVER["DOCUMENT_ROOT"]."/../logs/".$this->loggingName;
		if (!file_exists($this->logFileFullPath)) { touch($this->logFileFullPath); }
	}


	/*	Accessors for setting/getting wether to log to DB or File	*/
	public function set_logType($loggingType) { $this->loggingType = ($loggingType == 'DB') ? 'DB' : 'FILE'; }
	public function get_logType() { return ($this->loggingType == 'DB') ? 'DB' : 'FILE'; }


	/*	Sets the Log Name which also affects the file name if logging to file	*/
	public function set_logName($loggingName) {
		if ($this->loggingType == 'FILE') {
			if (empty($loggingName)) { $this->loggingName = 'GENERAL.log'; }
			elseif (substr($loggingName, -4) == '.log') { $this->loggingName = $loggingName; }
			else { $this->loggingName = $loggingName.'.log'; }
		}
		else {
			if (empty($loggingName)) { $this->loggingName = 'GENERAL'; }
			elseif (substr($loggingName, -4) == '.log') { $this->loggingName = preg_replace('/\.log$/', '', $loggingName); }
			else { $this->loggingName = $loggingName; }
		}
	}
	public function get_logName() {
		return (empty($this->loggingName)) ? 'GENERAL.log' : $this->loggingName;
	}
	public function get_logDbName() {
		return (empty($this->loggingName)) ? 'GENERAL' : preg_replace('/\.log$/', '', $this->loggingName);
	}


	public function set_logLevel($logLevel) {
		$this->logLevel = (array_key_exists($logLevel, $this->logLevelChoiceArray)) ? $logLevel : 'MEDIUM';
	}
	public function get_logLevel() {
		return (array_key_exists($this->logLevel, $this->logLevelChoiceArray)) ? $this->logLevel : 'MEDIUM';
	}

	private function compare_logLevel($logLevel) {
		$returnValue = 'NO';
		$alwaysLog = array('ALL','EDIT','ERROR');
		if (in_array($logLevel, $alwaysLog)) { $returnValue = 'YES'; }
		elseif ($logLevel == 'NONE') { $returnValue = 'NO'; }
		elseif (array_key_exists($logLevel, $this->logLevelChoiceArray)) {
			if ($this->logLevelChoiceArray[$logLevel] <= $this->logLevelChoiceArray[$this->logLevel]) {
				$returnValue = 'YES';
			}
		}
		return $returnValue;
	}


	public function set_loggingIsActive($loggingIsActive) { $this->loggingIsActive = ($loggingIsActive == 'Y') ? 'Y' : 'N'; }
	public function get_loggingIsActive() { return ($this->loggingIsActive == 'Y') ? 'Y' : 'N'; }

	public function set_userId($userId) { $this->userId = strtoupper(trim($userId)); }
	public function get_userId() { return $this->userId; }

	public function __destruct() {
		if ($this->loggingType == 'FILE' && $this->get_loggingIsActive() == 'Y' && $this->numberOfLinesLogged > 1) {
			$this->writeLogEntryToFile("\n");
		}
		if (is_resource($this->fileHandle)) { fclose($this->fileHandle); }
	}
}
?>