<?php
/**
 * Handles all the data access actions for a user
 * @author Dave Hills
 */
class USERS extends MOVIETRACKER {

	protected $userId = 0;
	protected $firstName = '';
	protected $lastName = '';
	protected $userName = '';
	protected $email = '';
	protected $phone = '';
	protected $userStatus = 'DISABLED';
	protected $password = '';


	/**
	 * Constructor
	 * @param int $appId
	 * @return void
	 */
	public function __construct($userId = null) {
		parent::__construct();
		$this->add_to_excludeFromExport(array('oldNewDiffString','password'));
		if (is_numeric($userId) && $userId > 0) {
			$results = $this->dbConnection->prepare("select
			user_id,
			first_name,
			last_name,
			email,
			phone,
			user_status
			from users where user_id = ?");
			if ($results->execute(array($userId))) {
				while ($row = $results->fetch(PDO::FETCH_OBJ)) {
					$this->set_userId($row->user_id);
					$this->set_firstName($row->first_name);
					$this->set_lastName($row->last_name);
					$this->set_email($row->email);
					$this->set_phone($row->phone);
					$this->set_userStatus($row->user_status);
				}
			}
		}
	}


	/**
	 * Saves the object back to the database
	 * @return void
	 */
	public function save_user() {

		$this->compare_old_new_objects($this->userId);

		$statement = $this->dbConnection->prepare("update users set
		first_name = :firstName,
		last_name = :lastName,
		email = :email,
		phone = :phone,
		user_status = :userStatus
		where user_id = :userId");
		$statement->bindValue(':firstName', $this->firstName, PDO::PARAM_STR);
		$statement->bindValue(':lastName', $this->lastName, PDO::PARAM_STR);
		$statement->bindValue(':email', $this->email, PDO::PARAM_STR);
		$statement->bindValue(':phone', $this->phone, PDO::PARAM_STR);
		$statement->bindValue(':userStatus', $this->userStatus, PDO::PARAM_STR);
		$statement->bindValue(':userId', $this->userId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1 && $this->oldNewDiffString != '') {
			$this->addToLog('EDIT', "User \"".$this->firstName." ".$this->lastName." (".$this->userId.")\" Updated:\n".$this->oldNewDiffString);
		}
	}


	/**
	 * Inserts the object into the database as a new entry
	 * @return void
	 */
	public function create_user() {
		$encKey = $_SERVER['ENCKEY'];
		if ($this->check_if_user_exists() > 0) { $this->save_user(); }
		else {
			$statement = $this->dbConnection->prepare("insert into users (
			first_name,
			last_name,
			email,
			phone,
			user_status,
			password
			) values (
			:firstName,
			:lastName,
			:email,
			:phone,
			:userStatus,
			aes_encrypt(:password, :EncStr))");
			$statement->bindValue(':firstName', $this->firstName, PDO::PARAM_STR);
			$statement->bindValue(':lastName', $this->lastName, PDO::PARAM_STR);
			$statement->bindValue(':email', $this->email, PDO::PARAM_STR);
			$statement->bindValue(':phone', $this->phone, PDO::PARAM_STR);
			$statement->bindValue(':userStatus', $this->userStatus, PDO::PARAM_STR);
			$statement->bindValue(':password', $this->password, PDO::PARAM_STR);
			$statement->bindValue(':EncStr', $encKey, PDO::PARAM_STR);
			try { $statement->execute(); }
			catch (PDOException $e) {
				$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
				$errorString .= print_r($this->logableObj($this), true)."\n";
				$errorString .= $e->getMessage();
				$this->addToLog('ERROR', $errorString);
			}
			if ($statement->rowCount() == 1) {
				$userId = $this->dbConnection->lastInsertId();
				$this->set_userId($userId);
				$this->addToLog('EDIT', "Created User Obj:\n".print_r($this->logableObj($this), true));
			}
		}
	}


	private function check_if_user_exists() {
		$kount = 0;
		$results = $this->dbConnection->prepare("select count(*) as kount from users where upper(email) = ? limit 1");
		if ($results->execute(array(strtoupper(trim($this->email))))) {
			while ($row = $results->fetch(PDO::FETCH_OBJ)) { $kount = $row->kount; }
		}
		return $kount;
	}


	public function delete_user() {
		$statement = $this->dbConnection->prepare("update users set user_status = 'DISABLED' where user_id = :userId");
		$statement->bindValue(':userId', $this->userId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) {
			$this->addToLog('EDIT', "Deleted User Obj:\n".print_r($this->logableObj($this), true));
		}
	}


	public function update_password() {
		$encKey = $_SERVER['ENCKEY'];
		$statement = $this->dbConnection->prepare("update users set password = aes_encrypt(:password, :EncStr) where user_id = :userId limit 1");
		$statement->bindValue(':password', $this->password, PDO::PARAM_STR);
		$statement->bindValue(':EncStr', $encKey, PDO::PARAM_STR);
		$statement->bindValue(':userId', $this->userId, PDO::PARAM_INT);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Failed to Update User Password:\n";
			$errorString .= "Error in Class::Method - ".__METHOD__." near line: ".__LINE__."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if ($statement->rowCount() == 1) { $this->addToLog('EDIT', "Updated Password:\n".print_r($this->logableObj($this), true)); }
	}


	public function login($email, $password) {
		$encKey = $_SERVER['ENCKEY'];
		$rowsFound = 0;
		$statement = $this->dbConnection->prepare("select user_id from users where
		user_status = 'NORMAL'
		and upper(email) = :email
		and password = aes_encrypt(:password, :EncStr)
		limit 1");
		$statement->bindValue(':email', $email, PDO::PARAM_STR);
		$statement->bindValue(':password', $password, PDO::PARAM_STR);
		$statement->bindValue(':EncStr', $encKey, PDO::PARAM_STR);
		try { $statement->execute(); }
		catch (PDOException $e) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= "Failed Login for  '".$email."' / '".$password."'\nRemote IP: ".$_SERVER['REMOTE_ADDR']."\nBrowser: ".$_SERVER['HTTP_USER_AGENT']."\n";
			$errorString .= print_r($this->logableObj($this), true)."\n";
			$errorString .= $e->getMessage();
			$this->addToLog('ERROR', $errorString);
		}
		if (is_object($statement) && get_class($statement) == 'PDOStatement') {
			while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
				$rowsFound++;
				if ($row->user_id > 0) {
					self::__construct($row->user_id);
					$this->logSettings['userId'] = $this->userId;
					$this->setup_userEnvironment();
				}
				else {
					$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
					$errorString .= "Failed Login for  '".$email."' / '".$password."'\nRemote IP: ".$_SERVER['REMOTE_ADDR']."\nBrowser: ".$_SERVER['HTTP_USER_AGENT'];
					$this->addToLog('ERROR', $errorString);
					$this->save_log_array();
					$_SESSION[$_SERVER['VHOST']] = array();
					header("Location: /users/login.php");
					exit();
				}
			}
		}
		if ($rowsFound < 1) {
			$errorString = "Error in Class::Method - ".__METHOD__." near line: ".__LINE__.":\n";
			$errorString .= "Failed Login for  '".$email."' / '".$password."'\nRemote IP: ".$_SERVER['REMOTE_ADDR']."\nBrowser: ".$_SERVER['HTTP_USER_AGENT'];
			$this->addToLog('ERROR', $errorString);
			$this->save_log_array();
			$_SESSION[$_SERVER['VHOST']] = array();
			header("Location: /users/login.php");
			exit();
		}
	}


	private function setup_userEnvironment() {
		$_SESSION[$_SERVER['VHOST']]['user_login'] = array(
			'userId' => $this->userId,
			'firstName' => $this->firstName,
			'lastName' => $this->lastName,
			'email' => $this->email,
			'phone' => $this->phone,
			'userStatus' => $this->userStatus
		);

		$logString = "User ".$this->get_name_for_logging()." - Logged In\nRemote IP: ".$_SERVER['REMOTE_ADDR']."\nBrowser: ".$_SERVER['HTTP_USER_AGENT'];
		$this->addToLog('MEDIUM', $logString);
		$this->save_log_array();
		header("Location: /index.php");
		exit();
	}


	public function set_userId($inVal) { $this->userId = intval($inVal); }
	public function get_userId() { return intval($this->userId); }

	public function set_firstName($inVal) {
		$this->firstName = $this->ucname(substr(trim($inVal), 0, 255));
		$this->set_userName();
	}
	public function get_firstName() { return $this->firstName; }

	public function set_lastName($inVal) {
		$this->lastName = $this->ucname(substr(trim($inVal), 0, 255));
		$this->set_userName();
	}
	public function get_lastName() { return $this->lastName; }

	public function set_email($inVal) { $this->email = substr(trim($inVal), 0, 255); }
	public function get_email() { return $this->email; }

	public function set_phone($inVal) { $this->phone = preg_replace("/^(\d{0,3})(\d{3})(\d{3})(\d{4})$/","\$2.\$3.\$4", preg_replace("/[^\d]/","", $inVal)); }
	public function get_phone() { return preg_replace("/^(\d\d\d).*(\d\d\d).*(\d\d\d\d)/","\$1.\$2.\$3", $this->phone); }

	public function set_userStatus($inVal) { $this->userStatus = ($inVal == 'NORMAL') ? 'NORMAL' : 'DISABLED'; }
	public function get_userStatus() { return $this->userStatus; }

	public function set_password($inVal) { $this->password = substr(trim($inVal), 0, 100); }
	public function get_password() { return trim($this->password); }

	public function get_userName() { return trim($this->firstName." ".$this->lastName); }
	public function set_userName() { $this->userName = trim($this->firstName." ".$this->lastName); }

	public function get_name_for_logging() { return '"'.$this->firstName.' '.$this->lastName.' ('.$this->userId.')"'; }


	public function __set($inName, $inVal) {
		if ($inName == 'userId') { $this->userId = intval($inVal); }
		elseif ($inName == 'firstName') { $this->firstName = substr(trim($inVal), 0, 255); }
		elseif ($inName == 'lastName') { $this->lastName = substr(trim($inVal), 0, 255); }
		elseif ($inName == 'email') { $this->email = substr(trim($inVal), 0, 255); }
		elseif ($inName == 'userStatus') { $this->userStatus = ($inVal == 'NORMAL') ? 'NORMAL' : 'DISABLED'; }
		elseif ($inName == 'password') { $this->password = substr(trim($inVal), 0, 100); }
		else { $this->$inName = $inVal; }
	}

	public function __get($inName) {

		$outVal = null;

		if ($inName == 'userId') { $outVal = (integer) intval($this->userId); }
		elseif ($inName == 'firstName') { $outVal = (string) trim($this->firstName); }
		elseif ($inName == 'lastName') { $outVal = (string) trim($this->lastName); }
		elseif ($inName == 'email') { $outVal = (string) trim($this->email); }
		elseif ($inName == 'userStatus') { $outVal = (string) ($this->userStatus == 'NORMAL') ? 'NORMAL' : 'DISABLED'; }
		elseif ($inName == 'password') { $outVal = (string) trim($this->password); }
		elseif ($inName == 'nameForLogging') { $outVal = (string) '"'.$this->firstName.' '.$this->lastName.' ('.$this->userId.')"'; }
		elseif ($inName == 'userName') { $outVal = (string) trim($this->userName); }

		return $outVal;
	}

	public function __isset($inName) {

		$outVal = false;

		if ($inName == 'userId') { $outVal = (intval($this->userId) > 0) ? true : false; }
		elseif ($inName == 'firstName') { $outVal = (trim($this->firstName) != '') ? true : false; }
		elseif ($inName == 'lastName') { $outVal = (trim($this->lastName) != '') ? true : false; }
		elseif ($inName == 'email') { $outVal = (trim($this->email) != '') ? true : false; }
		elseif ($inName == 'userStatus') { $outVal = true; }
		elseif ($inName == 'password') { $outVal = (trim($this->password) != '') ? true : false; }
		elseif ($inName == 'userName') { $outVal = (trim($this->userName) != '') ? true : false; }

		return $outVal;
	}

	public function __unset($inName) {
		if ($inName == 'userId') { $this->userId = 0; }
		elseif ($inName == 'firstName') { $this->firstName = ''; }
		elseif ($inName == 'lastName') { $this->lastName = ''; }
		elseif ($inName == 'email') { $this->email = ''; }
		elseif ($inName == 'userStatus') { $this->userStatus = 'DISABLED'; }
		elseif ($inName == 'password') { $this->password = ''; }
	}


	public function __destruct() { parent::__destruct(); }

}
?>
