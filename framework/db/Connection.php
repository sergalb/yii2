<?php
/**
 * Connection class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\db\Exception;

/**
 * Connection represents a connection to a database via [PDO](http://www.php.net/manual/en/ref.pdo.php).
 *
 * Connection works together with [[Command]], [[DataReader]] and [[Transaction]]
 * to provide data access to various DBMS in a common set of APIs. They are a thin wrapper
 * of the [[PDO PHP extension]](http://www.php.net/manual/en/ref.pdo.php).
 *
 * To establish a DB connection, set [[dsn]], [[username]] and [[password]], and then
 * call [[open]] or set [[active]] to be true.
 *
 * The following example shows how to create a Connection instance and establish
 * the DB connection:
 *
 * ~~~
 * $connection = new \yii\db\Connection(array(
 *     'dsn' => $dsn,
 *     'username' => $username,
 *     'password' => $password,
 * ));
 * $connection->active = true;  // same as: $connection->open();
 * ~~~
 *
 * After the DB connection is established, one can execute SQL statements like the following:
 *
 * ~~~
 * $command = $connection->createCommand('SELECT * FROM tbl_post');
 * $posts = $command->queryAll();
 * $command = $connection->createCommand('UPDATE tbl_post SET status=1');
 * $command->execute();
 * ~~~
 *
 * One can also do prepared SQL execution and bind parameters to the prepared SQL.
 * When the parameters are coming from user input, you should use this approach
 * to prevent SQL injection attacks. The following is an example:
 *
 * ~~~
 * $command = $connection->createCommand('SELECT * FROM tbl_post WHERE id=:id');
 * $command->bindValue(':id', $_GET['id']);
 * $post = $command->query();
 * ~~~
 *
 * For more information about how to perform various DB queries, please refer to [[Command]].
 *
 * If the underlying DBMS supports transactions, you can perform transactional SQL queries
 * like the following:
 *
 * ~~~
 * $transaction = $connection->beginTransaction();
 * try {
 *	 $connection->createCommand($sql1)->execute();
 *	 $connection->createCommand($sql2)->execute();
 *	 // ... executing other SQL statements ...
 *	 $transaction->commit();
 * } catch(Exception $e) {
 *	 $transaction->rollBack();
 * }
 * ~~~
 *
 * Connection is often used as an [[\yii\base\ApplicationComponent|application component]] and configured in the application
 * configuration like the following:
 *
 * ~~~
 * array(
 *	 'components' => array(
 *		 'db' => array(
 *			 'class' => '\yii\db\Connection',
 *			 'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
 *			 'username' => 'root',
 *			 'password' => '',
 *			 'charset' => 'utf8',
 *		 ),
 *	 ),
 * )
 * ~~~
 *
 * @property boolean $active Whether the DB connection is established.
 * @property Transaction $currentTransaction The currently active transaction. Null if no active transaction.
 * @property Driver $driver The database driver for the current connection.
 * @property QueryBuilder $queryBuilder The query builder.
 * @property string $lastInsertID The row ID of the last row inserted, or the last value retrieved from the sequence object.
 * @property string $driverName Name of the DB driver currently being used.
 * @property string $clientVersion The version information of the DB driver.
 * @property array $stats The statistical results of SQL executions.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Connection extends \yii\base\ApplicationComponent
{
	/**
	 * @var string the Data Source Name, or DSN, contains the information required to connect to the database.
	 * Please refer to the [PHP manual](http://www.php.net/manual/en/function.PDO-construct.php) on
	 * the format of the DSN string.
	 * @see charset
	 */
	public $dsn;
	/**
	 * @var string the username for establishing DB connection. Defaults to empty string.
	 */
	public $username = '';
	/**
	 * @var string the password for establishing DB connection. Defaults to empty string.
	 */
	public $password = '';
	/**
	 * @var array PDO attributes (name=>value) that should be set when calling [[open]]
	 * to establish a DB connection. Please refer to the
	 * [PHP manual](http://www.php.net/manual/en/function.PDO-setAttribute.php) for
	 * details about available attributes.
	 */
	public $attributes;
	/**
	 * @var \PDO the PHP PDO instance associated with this DB connection.
	 * This property is mainly managed by [[open]] and [[close]] methods.
	 * When a DB connection is active, this property will represent a PDO instance;
	 * otherwise, it will be null.
	 */
	public $pdo;
	/**
	 * @var boolean whether to enable schema caching.
	 * Note that in order to enable truly schema caching, a valid cache component as specified
	 * by [[schemaCacheID]] must be enabled and [[schemaCacheEnabled]] must be set true.
	 * @see schemaCacheDuration
	 * @see schemaCacheExclude
	 * @see schemaCacheID
	 */
	public $enableSchemaCache = false;
	/**
	 * @var integer number of seconds that table metadata can remain valid in cache.
	 * Use 0 to indicate that the cached data will never expire.
	 * @see enableSchemaCache
	 */
	public $schemaCacheDuration = 3600;
	/**
	 * @var array list of tables whose metadata should NOT be cached. Defaults to empty array.
	 * The table names may contain schema prefix, if any. Do not quote the table names.
	 * @see enableSchemaCache
	 */
	public $schemaCacheExclude = array();
	/**
	 * @var string the ID of the cache application component that is used to cache the table metadata.
	 * Defaults to 'cache'.
	 * @see enableSchemaCache
	 */
	public $schemaCacheID = 'cache';
	/**
	 * @var boolean whether to enable query caching.
	 * Note that in order to enable query caching, a valid cache component as specified
	 * by [[queryCacheID]] must be enabled and [[queryCacheEnabled]] must be set true.
	 *
	 * Methods [[beginCache()]] and [[endCache()]] can be used as shortcuts to turn on
	 * and off query caching on the fly.
	 * @see queryCacheDuration
	 * @see queryCacheID
	 * @see queryCacheDependency
	 * @see beginCache()
	 * @see endCache()
	 */
	public $enableQueryCache = false;
	/**
	 * @var integer number of seconds that query results can remain valid in cache.
	 * Defaults to 3600, meaning one hour.
	 * Use 0 to indicate that the cached data will never expire.
	 * @see enableQueryCache
	 */
	public $queryCacheDuration = 3600;
	/**
	 * @var \yii\caching\Dependency the dependency that will be used when saving query results into cache.
	 * Defaults to null, meaning no dependency.
	 * @see enableQueryCache
	 */
	public $queryCacheDependency;
	/**
	 * @var string the ID of the cache application component that is used for query caching.
	 * Defaults to 'cache'.
	 * @see enableQueryCache
	 */
	public $queryCacheID = 'cache';
	/**
	 * @var string the charset used for database connection. The property is only used
	 * for MySQL and PostgreSQL databases. Defaults to null, meaning using default charset
	 * as specified by the database.
	 *
	 * Note that if you're using GBK or BIG5 then it's highly recommended to
	 * update to PHP 5.3.6+ and to specify charset via DSN like
	 * 'mysql:dbname=mydatabase;host=127.0.0.1;charset=GBK;'.
	 */
	public $charset;
	/**
	 * @var boolean whether to turn on prepare emulation. Defaults to false, meaning PDO
	 * will use the native prepare support if available. For some databases (such as MySQL),
	 * this may need to be set true so that PDO can emulate the prepare support to bypass
	 * the buggy native prepare support.
	 * The default value is null, which means the PDO ATTR_EMULATE_PREPARES value will not be changed.
	 */
	public $emulatePrepare;
	/**
	 * @var boolean whether to enable profiling for the SQL statements being executed.
	 * Defaults to false. This should be mainly enabled and used during development
	 * to find out the bottleneck of SQL executions.
	 * @see getStats
	 */
	public $enableProfiling = false;
	/**
	 * @var string the default prefix for table names. Defaults to null, meaning not using table prefix.
	 * By setting this property, any token like '{{TableName}}' in [[Command::sql]] will
	 * be replaced with 'prefixTableName', where 'prefix' refers to this property value.
	 * For example, '{{post}}' becomes 'tbl_post', if 'tbl_' is set as the table prefix.
	 *
	 * Note that if you set this property to be an empty string, then '{{post}}' will be replaced
	 * with 'post'.
	 */
	public $tablePrefix;
	/**
	 * @var array a list of SQL statements that should be executed right after the DB connection is established.
	 */
	public $initSQLs;
	/**
	 * @var array mapping between PDO driver names and [[Driver]] classes.
	 * The keys of the array are PDO driver names while the values the corresponding
	 * driver class name or configuration. Please refer to [[\Yii::createObject]] for
	 * details on how to specify a configuration.
	 *
	 * This property is mainly used by [[getDriver()]] when fetching the database schema information.
	 * You normally do not need to set this property unless you want to use your own
	 * [[Driver]] class to support DBMS that is not supported by Yii.
	 */
	public $driverMap = array(
		'pgsql' => 'yii\db\pgsql\Driver', // PostgreSQL
		'mysqli' => 'yii\db\mysql\Driver', // MySQL
		'mysql' => 'yii\db\mysql\Driver', // MySQL
		'sqlite' => 'yii\db\sqlite\Driver', // sqlite 3
		'sqlite2' => 'yii\db\sqlite\Driver', // sqlite 2
		'mssql' => 'yi\db\dao\mssql\Driver', // Mssql driver on windows hosts
		'dblib' => 'yii\db\mssql\Driver', // dblib drivers on linux (and maybe others os) hosts
		'sqlsrv' => 'yii\db\mssql\Driver', // Mssql
		'oci' => 'yii\db\oci\Driver', // Oracle driver
	);
	/**
	 * @var Transaction the currently active transaction
	 */
	private $_transaction;
	/**
	 * @var Driver the database driver
	 */
	private $_driver;

	/**
	 * Closes the connection when this component is being serialized.
	 * @return array
	 */
	public function __sleep()
	{
		$this->close();
		return array_keys(get_object_vars($this));
	}

	/**
	 * Returns a list of available PDO drivers.
	 * @return array list of available PDO drivers
	 * @see http://www.php.net/manual/en/function.PDO-getAvailableDrivers.php
	 */
	public static function getAvailableDrivers()
	{
		return \PDO::getAvailableDrivers();
	}

	/**
	 * Returns a value indicating whether the DB connection is established.
	 * @return boolean whether the DB connection is established
	 */
	public function getActive()
	{
		return $this->pdo !== null;
	}

	/**
	 * Opens or closes the DB connection.
	 * @param boolean $value whether to open or close the DB connection
	 * @throws Exception if there is any error when establishing the connection
	 */
	public function setActive($value)
	{
		$value ? $this->open() : $this->close();
	}

	/**
	 * Turns on query caching.
	 * This method is provided as a shortcut to setting two properties that are related
	 * with query caching: [[queryCacheDuration]] and [[queryCacheDependency]].
	 * @param integer $duration the number of seconds that query results may remain valid in cache.
	 * See [[queryCacheDuration]] for more details.
	 * @param \yii\caching\Dependency $dependency the dependency for the cached query result.
	 * See [[queryCacheDependency]] for more details.
	 */
	public function beginCache($duration = null, $dependency = null)
	{
		$this->enableQueryCache = true;
		if ($duration !== null) {
			$this->queryCacheDuration = $duration;
		}
		$this->queryCacheDependency = $dependency;
	}

	/**
	 * Turns off query caching.
	 */
	public function endCache()
	{
		$this->enableQueryCache = false;
	}

	/**
	 * Establishes a DB connection.
	 * It does nothing if a DB connection has already been established.
	 * @throws Exception if connection fails
	 */
	public function open()
	{
		if ($this->pdo === null) {
			if (empty($this->dsn)) {
				throw new Exception('Connection.dsn cannot be empty.');
			}
			try {
				\Yii::trace('Opening DB connection: ' . $this->dsn, __CLASS__);
				$this->pdo = $this->createPdoInstance();
				$this->initConnection($this->pdo);
			}
			catch (\PDOException $e) {
				\Yii::error("Failed to open DB connection ({$this->dsn}): " . $e->getMessage(), __CLASS__);
				$message = YII_DEBUG ? 'Failed to open DB connection: ' . $e->getMessage() : 'Failed to open DB connection.';
				throw new Exception($message, (int)$e->getCode(), $e->errorInfo);
			}
		}
	}

	/**
	 * Closes the currently active DB connection.
	 * It does nothing if the connection is already closed.
	 */
	public function close()
	{
		if ($this->pdo !== null) {
			\Yii::trace('Closing DB connection: ' . $this->dsn, __CLASS__);
			$this->pdo = null;
			$this->_driver = null;
			$this->_transaction = null;
		}
	}

	/**
	 * Creates the PDO instance.
	 * This method is called by [[open]] to establish a DB connection.
	 * The default implementation will create a PHP PDO instance.
	 * You may override this method if the default PDO needs to be adapted for certain DBMS.
	 * @return \PDO the pdo instance
	 */
	protected function createPdoInstance()
	{
		$pdoClass = '\PDO';
		if (($pos = strpos($this->dsn, ':')) !== false) {
			$driver = strtolower(substr($this->dsn, 0, $pos));
			if ($driver === 'mssql' || $driver === 'dblib' || $driver === 'sqlsrv') {
				$pdoClass = 'mssql\PDO';
			}
		}
		return new $pdoClass($this->dsn, $this->username, $this->password, $this->attributes);
	}

	/**
	 * Initializes the DB connection.
	 * This method is invoked right after the DB connection is established.
	 * The default implementation sets the database [[charset]] and executes SQLs specified
	 * in [[initSQLs]].
	 */
	protected function initConnection()
	{
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		if ($this->emulatePrepare !== null && constant('\PDO::ATTR_EMULATE_PREPARES')) {
			$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
		}
		if ($this->charset !== null && in_array($this->getDriverName(), array('pgsql', 'mysql', 'mysqli'))) {
			$this->pdo->exec('SET NAMES ' . $this->pdo->quote($this->charset));
		}
		if (!empty($this->initSQLs)) {
			foreach ($this->initSQLs as $sql) {
				$this->pdo->exec($sql);
			}
		}
	}

	/**
	 * Creates a command for execution.
	 * @param string $sql the SQL statement to be executed
	 * @param array $params the parameters to be bound to the SQL statement
	 * @return Command the DB command
	 */
	public function createCommand($sql = null, $params = array())
	{
		$this->open();
		return new Command($this, $sql, $params);
	}

	/**
	 * Returns the currently active transaction.
	 * @return Transaction the currently active transaction. Null if no active transaction.
	 */
	public function getCurrentTransaction()
	{
		if ($this->_transaction !== null && $this->_transaction->active) {
			return $this->_transaction;
		} else {
			return null;
		}
	}

	/**
	 * Starts a transaction.
	 * @return Transaction the transaction initiated
	 */
	public function beginTransaction()
	{
		\Yii::trace('Starting transaction', __CLASS__);
		$this->open();
		$this->pdo->beginTransaction();
		return $this->_transaction = new Transaction($this);
	}

	/**
	 * Returns the metadata information for the underlying database.
	 * @return Driver the metadata information for the underlying database.
	 */
	public function getDriver()
	{
		if ($this->_driver !== null) {
			return $this->_driver;
		} else {
			$driver = $this->getDriverName();
			if (isset($this->driverMap[$driver])) {
				return $this->_driver = \Yii::createObject($this->driverMap[$driver], $this);
			} else {
				throw new Exception("Connection does not support reading metadata for '$driver' database.");
			}
		}
	}

	/**
	 * Returns the query builder for the current DB connection.
	 * @return QueryBuilder the query builder for the current DB connection.
	 */
	public function getQueryBuilder()
	{
		return $this->getDriver()->getQueryBuilder();
	}

	/**
	 * Obtains the metadata for the named table.
	 * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
	 * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
	 * @return TableSchema table metadata. Null if the named table does not exist.
	 */
	public function getTableSchema($name, $refresh = false)
	{
		return $this->getDriver()->getTableSchema($name, $refresh);
	}

	/**
	 * Returns the ID of the last inserted row or sequence value.
	 * @param string $sequenceName name of the sequence object (required by some DBMS)
	 * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
	 * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php
	 */
	public function getLastInsertID($sequenceName = '')
	{
		$this->open();
		return $this->pdo->lastInsertId($sequenceName);
	}

	/**
	 * Quotes a string value for use in a query.
	 * Note that if the parameter is not a string, it will be returned without change.
	 * @param string $str string to be quoted
	 * @return string the properly quoted string
	 * @see http://www.php.net/manual/en/function.PDO-quote.php
	 */
	public function quoteValue($str)
	{
		if (!is_string($str)) {
			return $str;
		}

		$this->open();
		if (($value = $this->pdo->quote($str)) !== false) {
			return $value;
		} else { // the driver doesn't support quote (e.g. oci)
			return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
		}
	}

	/**
	 * Quotes a table name for use in a query.
	 * If the table name contains schema prefix, the prefix will also be properly quoted.
	 * @param string $name table name
	 * @param boolean $simple if this is true, then the method will assume $name is a table name without schema prefix.
	 * @return string the properly quoted table name
	 */
	public function quoteTableName($name, $simple = false)
	{
		return $simple ? $this->getDriver()->quoteSimpleTableName($name) : $this->getDriver()->quoteTableName($name);
	}

	/**
	 * Quotes a column name for use in a query.
	 * If the column name contains table prefix, the prefix will also be properly quoted.
	 * @param string $name column name
	 * @param boolean $simple if this is true, then the method will assume $name is a column name without table prefix.
	 * @return string the properly quoted column name
	 */
	public function quoteColumnName($name, $simple = false)
	{
		return $simple ? $this->getDriver()->quoteSimpleColumnName($name) : $this->getDriver()->quoteColumnName($name);
	}

	/**
	 * Prefixes table names in a SQL statement with [[tablePrefix]].
	 * By calling this method, tokens like '{{TableName}}' in the given SQL statement will
	 * be replaced with 'prefixTableName', where 'prefix' refers to [[tablePrefix]].
	 * Note that if [[tablePrefix]] is null, this method will do nothing.
	 * @param string $sql the SQL statement whose table names need to be prefixed with [[tablePrefix]].
	 * @return string the expanded SQL statement
	 * @see tablePrefix
	 */
	public function expandTablePrefix($sql)
	{
		if ($this->tablePrefix !== null && strpos($sql, '{{') !== false) {
			return preg_replace('/{{(.*?)}}/', $this->tablePrefix . '\1', $sql);
		} else {
			return $sql;
		}
	}

	/**
	 * Determines the PDO type for the give PHP data type.
	 * @param string $type The PHP type (obtained by `gettype()` call).
	 * @return integer the corresponding PDO type
	 * @see http://www.php.net/manual/en/pdo.constants.php
	 */
	public function getPdoType($type)
	{
		static $typeMap = array(
			'boolean' => \PDO::PARAM_BOOL,
			'integer' => \PDO::PARAM_INT,
			'string' => \PDO::PARAM_STR,
			'NULL' => \PDO::PARAM_NULL,
		);
		return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
	}

	/**
	 * Returns the name of the DB driver for the current [[dsn]].
	 * @return string name of the DB driver
	 */
	public function getDriverName()
	{
		if (($pos = strpos($this->dsn, ':')) !== false) {
			return strtolower(substr($this->dsn, 0, $pos));
		} else {
			return strtolower($this->getAttribute(\PDO::ATTR_DRIVER_NAME));
		}
	}

	/**
	 * Obtains a specific DB connection attribute information.
	 * @param integer $name the attribute to be queried
	 * @return mixed the corresponding attribute information
	 * @see http://www.php.net/manual/en/function.PDO-getAttribute.php
	 */
	public function getAttribute($name)
	{
		$this->open();
		return $this->pdo->getAttribute($name);
	}

	/**
	 * Sets an attribute on the database connection.
	 * @param integer $name the attribute to be set
	 * @param mixed $value the attribute value
	 * @see http://www.php.net/manual/en/function.PDO-setAttribute.php
	 */
	public function setAttribute($name, $value)
	{
		$this->open();
		$this->pdo->setAttribute($name, $value);
	}

	/**
	 * Returns the statistical results of SQL executions.
	 * The results returned include the number of SQL statements executed and
	 * the total time spent.
	 * In order to use this method, [[enableProfiling]] has to be set true.
	 * @return array the first element indicates the number of SQL statements executed,
	 * and the second element the total time spent in SQL execution.
	 * @see \yii\logging\Logger::getProfiling()
	 */
	public function getStats()
	{
		$logger = \Yii::getLogger();
		$timings = $logger->getProfiling(array('yii\db\Command::query', 'yii\db\Command::execute'));
		$count = count($timings);
		$time = 0;
		foreach ($timings as $timing) {
			$time += $timing[1];
		}
		return array($count, $time);
	}
}
