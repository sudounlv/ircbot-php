<?php
/**
 * MySQL adapter
 * @author Richard Hoppes
 */
class Phergie_Database_MySQL {

	const QUERY_TYPE_SELECT = 'QUERY_TYPE_SELECT';
	const QUERY_TYPE_DELETE = 'QUERY_TYPE_DELETE';
	const QUERY_TYPE_UPDATE = 'QUERY_TYPE_UPDATE';
	const QUERY_TYPE_INSERT = 'QUERY_TYPE_INSERT';

	protected $domain;
	protected $username;
	protected $password;
	protected $databaseName;
	protected $dbLink;

	private static $databaseMySQL;

	protected function __construct($domain, $username, $password, $databaseName) {
		$this->domain = $domain;
		$this->username = $username;
		$this->password = $password;
		$this->databaseName = $databaseName;

		$this->dbLink = @mysql_connect($this->domain, $this->username, $this->password);
		if(!$this->dbLink)
			throw new Exception("Unable to connect to host {$this->domain}");

		if(!@mysql_select_db($this->databaseName, $this->dbLink))
			throw new Exception("Unable to connect to database: {$this->databaseName}");
	}

	public static function getHandle($domain, $username, $password, $databaseName) {
		if(!self::$databaseMySQL) {
			self::$databaseMySQL = new self($domain, $username, $password, $databaseName);
		}
		return self::$databaseMySQL;
	}

	/**
	 * Execute select query
	 * @param $query select sql
	 * @param array $variables associative array of tokens (keys) that should be replaced in the query, and the values that should replace them
	 * @return array results
	 * @throws Exception
	 */
	public function select($query, $variables = array()) {
		return $this->query($query, $variables, self::QUERY_TYPE_SELECT);
	}

	/**
	 * Execute insert query
	 * @param $query insert sql
	 * @param array $variables associative array of tokens (keys) that should be replaced in the query, and the values that should replace them
	 * @return int insert id
	 * @throws Exception
	 */
	public function insert($query, $variables = array()) {
		return $this->query($query, $variables, self::QUERY_TYPE_INSERT);
	}

	/**
	 * Execute update query
	 * @param $query update sql
	 * @param array $variables associative array of tokens (keys) that should be replaced in the query, and the values that should replace them
	 * @throws Exception
	 */
	public function update($query, $variables = array()) {
		$this->query($query, $variables, self::QUERY_TYPE_UPDATE);
	}

	/**
	 * Execute delete query
	 * @param $query delete sql
	 * @param array $variables associative array of tokens (keys) that should be replaced in the query, and the values that should replace them
	 * @throws Exception
	 */
	public function delete($query, $variables = array()) {
		$this->query($query, $variables, self::QUERY_TYPE_DELETE);
	}

	/**
	 * Execute query
	 * @param $query sql
	 * @param array $variables associative array of tokens (keys) that should be replaced in the query, and the values that should replace them
	 * @param string $queryType type of query (select, update, delete, insert)
	 * @return array|bool|int
	 * @throws Exception
	 */
	protected function query($query, $variables = array(), $queryType = self::QUERY_TYPE_SELECT) {
		$query = $this->prepareQuery($query, $variables);

		$retVal = null;

		switch($queryType) {
			case self::QUERY_TYPE_SELECT:
				$results = array();
				if($result = mysql_query($query, $this->dbLink)) {
					while($row = mysql_fetch_assoc($result)) {
						$results[] = $row;
					}
				} else {
					throw new Exception("Select failed: " . mysql_error($this->dbLink) . " | Query: " . $query);
				}
				$retVal = $results;
				break;

			case self::QUERY_TYPE_INSERT:
				$result = mysql_query($query, $this->dbLink);
				if(!$result) {
					throw new Exception("Insert failed: " . mysql_error($this->dbLink) . " | Query: " . $query);
				}
				$retVal = mysql_insert_id($this->dbLink);
				break;

			case self::QUERY_TYPE_UPDATE:
			case self::QUERY_TYPE_DELETE:
				$result = mysql_query($query, $this->dbLink);
				if(!$result) {
					throw new Exception("Update or delete failed: " . mysql_error($this->dbLink) . " | Query: " . $query);
				}
				$retVal = true;
				break;

			// Unsupported query type
			default:
				throw new Exception("Unknown query type: {$queryType}");
				break;
		}

		return $retVal;
	}

	/**
	 * Prepares query to be executed.
	 * Replaces instances of val(**token**) with property formatted value.  Tokens (keys) and values are contained in the $variables associative array.
	 * @param $query sql
	 * @param $variables associative array of tokens (keys) that should be replaced in the query, and the values that should replace them
	 * @return string prepared query
	 */
	protected function prepareQuery($query, $variables) {
		// Find all tokens
		preg_match_all('/val\([A-Za-z0-9\s-_]*\)/i', $query, $tokens);
		if(isset($tokens[0]) && sizeof($tokens[0]) > 0) {

			// Loop all token matches
			foreach($tokens[0] as $tokenIndex => $token) {

				// Find actual token variable
				preg_match('/val\(([A-Za-z0-9\s-_]*)\)/i', $token, $tokenVar);
				if(isset($tokenVar[1])) {

					// Perform token replacement
					$tokenVar = trim($tokenVar[1]);

					if(is_string($variables[$tokenVar]) && $variables[$tokenVar] == "") {
						$query = str_replace($token, "'".mysql_real_escape_string($variables[$tokenVar])."'", $query);

					} elseif(is_string($variables[$tokenVar])) {
						$query = str_replace($token, "'".mysql_real_escape_string($variables[$tokenVar])."'", $query);

					} elseif (is_numeric($variables[$tokenVar])){
						$query = str_replace($token, mysql_real_escape_string($variables[$tokenVar]), $query);

					} elseif (is_null($variables[$tokenVar])) {
						$query = str_replace($token, "NULL", $query);

					}
				}
			}
		}
		return $query;
	}

}