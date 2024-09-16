<?php
/**
 * @author Dave Hills
 * @subpackage LOGGER
 */
class LOGGER_REPORT extends MOVIETRACKER {

	private $logLevelChoiceArray = array(
		'ALL' => 0,
		'EDIT' => 100,
		'ERROR' => 200,
		'HIGH' => 300,
		'MEDIUM' => 400,
		'LOW' => 500,
		'DEBUG' => 600,
		'SQL' => 700,
		'NONE' => 800,
		'TMP1D' => 10000,
		'TMP7D' => 11000,
		'TMP30D' => 12000,
		'TMP180D' => 13000,
		'TMP365D' => 14000
	);

	private $logLevelChoiceReverse = array(
		0 => 'ALL',
		100 => 'EDIT',
		200 => 'ERROR',
		300 => 'HIGH',
		400 => 'MEDIUM',
		500 => 'LOW',
		600 => 'DEBUG',
		700 => 'SQL',
		800 => 'NONE',
		10000 => 'TMP1D',
		11000 => 'TMP7D',
		12000 => 'TMP30D',
		13000 => 'TMP180D',
		14000 => 'TMP365D'
	);

	private $logNameArray = array('ALL');

	private $loggedByArray = array('ALL' => 'ALL');

	private $loggerObj;


	/**
	 * Constructor
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		$this->add_to_excludeFromExport(array('logLevelChoiceArray','logLevelChoiceArray','logNameArray','loggedByArray','loggerObj'));

		$this->set_log_name_array();
		$this->set_loggged_by_array();
	}


	/**
	 * Sets an array of possible log names. Limit to last 30 days to speed up query and HTML building.
	 * @return void
	 */
	private function set_log_name_array() {
		$statement = $this->dbConnection->prepare("select distinct log_name
		from class_logs
		where log_time > date_sub(curdate(), interval 1 year)
		order by log_name");
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Failed to select from database:\n";
			$errorString .= "Error in Class::Method - ".__METHOD__." near line: ".__LINE__."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement) { while ($row = $statement->fetch(PDO::FETCH_OBJ)) { $this->logNameArray[] = $row->log_name; } }
	}


	/**
	 * Sets an array of all user ATTUID's that have logged events. Limit to last 30 days to speed up query and HTML building.
	 * @return void
	 */
	private function set_loggged_by_array() {
		$statement = $this->dbConnection->prepare("select
		user_id,
		concat(first_name, ' ', last_name) as username
		from users
		where user_id in (
		select distinct user_id
			from class_logs
			where log_time > date_sub(curdate(), interval 1 year)
		)
		order by 2");
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Failed to select from database:\n";
			$errorString .= "Error in Class::Method - ".__METHOD__." near line: ".__LINE__."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement) { while ($row = $statement->fetch(PDO::FETCH_OBJ)) { $this->loggedByArray[$row->user_id] = $row->username; } }
	}


	/**
	 * Builds an HTML select menu with all the log names
	 * @see set_log_name_array()
	 * @param array $selected - All the names of logs that are selected
	 * @return string
	 */
	public function build_log_name_select_menu($selected = array('MOVIE','ACTOR')) {
		$returnMenu = '';
		foreach ($this->logNameArray as $logName) {
			$isSelected = (in_array($logName, $selected)) ? " selected='selected'" : "";
			$returnMenu .= "<option value='".$logName."'".$isSelected.">".$logName."</option>\n";
		}
		return $returnMenu;
	}


	/**
	 * Builds an HTML select menu with all the user names that have logged events
	 * @see set_loggged_by_array()
	 * @param array $selected - All the ATTUID's of users that are selected
	 * @return string
	 */
	public function build_logged_by_select_menu($selected = array('ALL')) {
		$returnMenu = "";
		foreach ($this->loggedByArray as $userId => $userName) {
			$isSelected = (in_array($userId, $selected)) ? " selected='selected'" : "";
			$returnMenu .= "<option value='".$userId."'".$isSelected.">".$userName."</option>\n";
		}
		return $returnMenu;
	}


	/**
	 * Builds an HTML select menu with all the log level choices
	 * @param array $selected - All the log levels that are selected
	 * @return string
	 */
	public function build_log_level_select_menu($selected = array('0')) {
		$returnMenu = '';
		foreach ($this->logLevelChoiceArray as $k => $v) {
			if ($k == 'NONE' || $k == 'DEBUG') { continue; }
			$isSelected = (in_array($v, $selected)) ? " selected='selected'" : "";
			$returnMenu .= "<option value='".$v."'".$isSelected.">".$k."</option>\n";
		}
		return $returnMenu;
	}


	/**
	 * Get the list of log entries
	 * @param array $requestVars - An array of all the values to search against
	 * @return array - A collection of log event objects
	 */
	public function get_log_entries($requestVars) {
		$rowsRetrieved = 0;
		$movieIdString = '';
		$logNameString = '';
		$logLevelString = '';
		$loggedByString = '';


		$returnResults = array();

		/*	Sets which movie to limit to if applicable	*/
		if (isset($requestVars['movieId']) && is_numeric($requestVars['movieId'])) {
			$movieIdString = "cl.movie_id = :movieId and";
		}


		/*	Sets which logs to include in query	*/
		if (isset($requestVars['logName'])) {
			$logNameArray = json_decode($requestVars['logName']);
			if (!in_array('ALL', $logNameArray)) {
				$logNameExclude = (isset($requestVars['logNameExclude']) && $requestVars['logNameExclude'] == 'Y') ? 'not in' : 'in';
				$logNameString = "cl.log_name ".$logNameExclude." ('".implode("','", $logNameArray)."') and";
			}
		}

		/*	Sets which log levels to include in query	*/
		if (isset($requestVars['logLevel'])) {
			$logLevelArray = json_decode($requestVars['logLevel']);
			if (!in_array('0', $logLevelArray)) {
				$logLevelExclude = (isset($requestVars['logLevelExclude']) && $requestVars['logLevelExclude'] == 'Y') ? 'not in' : 'in';
				$logLevelString = "cl.log_level ".$logLevelExclude." (".implode(',', $logLevelArray).") and";
			}
		}

		/*	Sets which users to include in query	*/
		if (isset($requestVars['loggedBy'])) {
			$loggedByArray = json_decode($requestVars['loggedBy']);
			if (!in_array('ALL', $loggedByArray)) {
				$loggedByExclude = (isset($requestVars['loggedByExclude']) && $requestVars['loggedByExclude'] == 'Y') ? 'not in' : 'in';
				$loggedByString = "cl.user_id ".$loggedByExclude." (".implode(',', $loggedByArray).") and";
			}
		}



		/*	Sets the date range to include in query	*/
		$startDate = (isset($requestVars['startDate'])) ? $requestVars['startDate'] : date('Y-m-d');
		$endDate = (isset($requestVars['endDate'])) ? $requestVars['endDate'] : date('Y-m-d');

		$statement = $this->dbConnection->prepare("select
		cl.log_id,
		date_format(cl.log_time, '%Y-%m-%d %H:%i:%s') as event_time,
		cl.log_name,
		cl.log_level,
		cl.user_id,
		concat(u.first_name, ' ', u.last_name) as username,
		cl.movie_id,
		cl.log_desc
		from class_logs as cl, users as u
		where
		cl.user_id = u.user_id and
		".$movieIdString."
		".$logNameString."
		".$logLevelString."
		".$loggedByString."
		date(cl.log_time) >= str_to_date(:startDate, '%Y-%m-%d') and
		date(cl.log_time) <= str_to_date(:endDate, '%Y-%m-%d')
		order by cl.log_time desc");
		$rowsRetrieved = 0;

		if ($movieIdString != '') {
			$statement->bindParam(':movieId', $requestVars['movieId'], PDO::PARAM_INT);
		}
		$statement->bindParam(':startDate', $startDate);
		$statement->bindParam(':endDate', $endDate);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method: ".__METHOD__." near line: ".__LINE__."\n";
			$errorString .= $e->getMessage()."\n";
			$errorString .= $statement->queryString."\n";
		}

		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
  			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$rowsRetrieved++;
				$eventData = new stdClass();
				$eventData->logId = utf8_encode($row->log_id);
				$eventData->eventDate = utf8_encode($row->event_time);
				$eventData->logName = utf8_encode($row->log_name);
				$eventData->logLevel = utf8_encode($this->logLevelChoiceReverse[$row->log_level]);
				$eventData->logDescription = utf8_encode(nl2br(htmlentities($row->log_desc, ENT_QUOTES)));
				$eventData->loggedBy = utf8_encode($row->username);
				$eventData->userId = utf8_encode($row->user_id);
				$eventData->movieId = utf8_encode($row->movie_id);
				$returnResults[] = $eventData;
			}
		}
		return $returnResults;
	}

}
?>