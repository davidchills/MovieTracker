<?php
/**
 * @author Dave Hills
 */
class AUTH {

	public function __construct() {
		if (!isset($_SESSION[$_SERVER['VHOST']]['user_login']['userId']) || !($_SESSION[$_SERVER['VHOST']]['user_login']['userId'] > 0)) {
			header("Location: /users/login.php");
			exit();
		}
	}

    public function __clone() { trigger_error('Clone is not allowed.', E_USER_ERROR); }
}
?>