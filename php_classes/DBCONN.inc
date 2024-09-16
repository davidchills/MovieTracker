<?php
/**
 * Handles passing back a database connection from a static method call to this class
 * @author Dave Hills
 */
class DBCONN {

	/**
	 * @var object - Instance of PDO
	 */
	private static $_instance;

	/**
	 * Private constructor so that class can't be instantiated
	 */
	private function __construct() {}

	/**
	 * The only way to access the class externally
	 * @static
	 */
    public static function getInstance() {
        if (!isset(self::$_instance)) {
        	try {
            	self::$_instance = new pdo("mysql:host=".$_SERVER['DB_HOST'].";port=".$_SERVER['DB_PORT'].";dbname=".$_SERVER['DB_SCHEMA'], $_SERVER['DB_USER'], $_SERVER['DB_PASS']);
				self::$_instance->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
				self::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$_instance->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
			catch(PDOException $e) { print "DB Eror: ".$e; }
        }
        return self::$_instance;
    }

    /**
     * Prevents the returned instance from being copied
     */
    public function __clone() { trigger_error('Clone is not allowed.', E_USER_ERROR); }
}
?>