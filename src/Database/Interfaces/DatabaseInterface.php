<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Interfaces;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Interface for database object<br/>
 * <br/>
 * <i>It forces implementation of all PDO methods (with stricter return types) plus few utility ones.</i>
 *
 * @author Christopher Kalkhoff
 *        
 */
interface DatabaseInterface
{

    /**
     * Prepares a statement for execution and returns a statement object
     * @link https://php.net/manual/en/pdo.prepare.php
     * @param string $statement <p>
     * This must be a valid SQL statement for the target database server.
     * </p>
     * @param array $driver_options [optional] <p>
     * This array holds one or more key=&gt;value pairs to set
     * attribute values for the <b>PDOStatement</b> object that this method
     * returns. You would most commonly use this to set the
     * <b>PDO::ATTR_CURSOR</b> value to
     * <b>PDO::CURSOR_SCROLL</b> to request a scrollable cursor.
     * Some drivers have driver specific options that may be set at
     * prepare-time.
     * </p>
     * @return PDOStatement|bool If the database server successfully prepares the statement,
     * <b>PDO::prepare</b> returns a
     * <b>PDOStatement</b> object.
     * If the database server cannot successfully prepare the statement,
     * <b>PDO::prepare</b> returns <b>FALSE</b> or emits
     * <b>PDOException</b> (depending on error handling).
     * </p>
     * <p>
     * Emulated prepared statements does not communicate with the database server
     * so <b>PDO::prepare</b> does not check the statement.
     */
    public function prepare($statement, array $driver_options = []);

    /**
     * Initiates a transaction
     * <p>
     * Turns off autocommit mode. While autocommit mode is turned off,
     * changes made to the database via the PDO object instance are not committed
     * until you end the transaction by calling {@link PDO::commit()}.
     * Calling {@link PDO::rollBack()} will roll back all changes to the database and
     * return the connection to autocommit mode.
     * </p>
     * <p>
     * Some databases, including MySQL, automatically issue an implicit COMMIT
     * when a database definition language (DDL) statement
     * such as DROP TABLE or CREATE TABLE is issued within a transaction.
     * The implicit COMMIT will prevent you from rolling back any other changes
     * within the transaction boundary.
     * </p>
     * @link https://php.net/manual/en/pdo.begintransaction.php
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     * @throws PDOException If there is already a transaction started or
     * the driver does not support transactions <br/>
     * <b>Note</b>: An exception is raised even when the <b>PDO::ATTR_ERRMODE</b>
     * attribute is not <b>PDO::ERRMODE_EXCEPTION</b>.
     */
    public function beginTransaction(): bool;

    /**
     * Commits a transaction
     * @link https://php.net/manual/en/pdo.commit.php
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     * @throws PDOException if there is no active transaction.
     */
    public function commit(): bool;

    /**
     * Rolls back a transaction
     * @link https://php.net/manual/en/pdo.rollback.php
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     * @throws PDOException if there is no active transaction.
     */
    public function rollBack(): bool;

    /**
     * Checks if inside a transaction
     * @link https://php.net/manual/en/pdo.intransaction.php
     * @return bool <b>TRUE</b> if a transaction is currently active, and <b>FALSE</b> if not.
     */
    public function inTransaction(): bool;

    /**
     * Set an attribute
     * @link https://php.net/manual/en/pdo.setattribute.php
     * @param int $attribute
     * @param mixed $value
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function setAttribute($attribute, $value);

    /**
     * Execute an SQL statement and return the number of affected rows
     * @link https://php.net/manual/en/pdo.exec.php
     * @param string $statement <p>
     * The SQL statement to prepare and execute.
     * </p>
     * <p>
     * Data inside the query should be properly escaped.
     * </p>
     * @return int|false <b>PDO::exec</b> returns the number of rows that were modified
     * or deleted by the SQL statement you issued. If no rows were affected,
     * <b>PDO::exec</b> returns 0.
     * </p>
     * This function may
     * return Boolean <b>FALSE</b>, but may also return a non-Boolean value which
     * evaluates to <b>FALSE</b>. Please read the section on Booleans for more
     * information. Use the ===
     * operator for testing the return value of this
     * function.
     * <p>
     * The following example incorrectly relies on the return value of
     * <b>PDO::exec</b>, wherein a statement that affected 0 rows
     * results in a call to <b>die</b>:
     * <code>
     * $db->exec() or die(print_r($db->errorInfo(), true));
     * </code>
     */
    public function exec($statement);

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     * @link https://php.net/manual/en/pdo.query.php
     * @param string $statement <p>
     * The SQL statement to prepare and execute.
     * </p>
     * <p>
     * Data inside the query should be properly escaped.
     * </p>
     * @param int $mode <p>
     * The fetch mode must be one of the PDO::FETCH_* constants.
     * </p>
     * @param mixed $arg3 <p>
     * The second and following parameters are the same as the parameters for PDOStatement::setFetchMode.
     * </p>
     * @param array $ctorargs [optional] <p>
     * Arguments of custom class constructor when the <i>mode</i>
     * parameter is set to <b>PDO::FETCH_CLASS</b>.
     * </p>
     * @return PDOStatement|false <b>PDO::query</b> returns a PDOStatement object, or <b>FALSE</b>
     * on failure.
     * @see PDOStatement::setFetchMode For a full description of the second and following parameters.
     */
    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = []);

    /**
     * Returns the ID of the last inserted row or sequence value
     * @link https://php.net/manual/en/pdo.lastinsertid.php
     * @param string $name [optional] <p>
     * Name of the sequence object from which the ID should be returned.
     * </p>
     * @return string If a sequence name was not specified for the <i>name</i>
     * parameter, <b>PDO::lastInsertId</b> returns a
     * string representing the row ID of the last row that was inserted into
     * the database.
     * </p>
     * <p>
     * If a sequence name was specified for the <i>name</i>
     * parameter, <b>PDO::lastInsertId</b> returns a
     * string representing the last value retrieved from the specified sequence
     * object.
     * </p>
     * <p>
     * If the PDO driver does not support this capability,
     * <b>PDO::lastInsertId</b> triggers an
     * IM001 SQLSTATE.
     */
    public function lastInsertId($name = null);

    /**
     * Fetch the SQLSTATE associated with the last operation on the database handle
     * @link https://php.net/manual/en/pdo.errorcode.php
     * @return mixed an SQLSTATE, a five characters alphanumeric identifier defined in
     * the ANSI SQL-92 standard. Briefly, an SQLSTATE consists of a
     * two characters class value followed by a three characters subclass value. A
     * class value of 01 indicates a warning and is accompanied by a return code
     * of SQL_SUCCESS_WITH_INFO. Class values other than '01', except for the
     * class 'IM', indicate an error. The class 'IM' is specific to warnings
     * and errors that derive from the implementation of PDO (or perhaps ODBC,
     * if you're using the ODBC driver) itself. The subclass value '000' in any
     * class indicates that there is no subclass for that SQLSTATE.
     * </p>
     * <p>
     * <b>PDO::errorCode</b> only retrieves error codes for operations
     * performed directly on the database handle. If you create a PDOStatement
     * object through <b>PDO::prepare</b> or
     * <b>PDO::query</b> and invoke an error on the statement
     * handle, <b>PDO::errorCode</b> will not reflect that error.
     * You must call <b>PDOStatement::errorCode</b> to return the error
     * code for an operation performed on a particular statement handle.
     * </p>
     * <p>
     * Returns <b>NULL</b> if no operation has been run on the database handle.
     */
    public function errorCode();

    /**
     * Fetch extended error information associated with the last operation on the database handle
     * @link https://php.net/manual/en/pdo.errorinfo.php
     * @return array <b>PDO::errorInfo</b> returns an array of error information
     * about the last operation performed by this database handle. The array
     * consists of the following fields:
     * <tr valign="top">
     * <td>Element</td>
     * <td>Information</td>
     * </tr>
     * <tr valign="top">
     * <td>0</td>
     * <td>SQLSTATE error code (a five characters alphanumeric identifier defined
     * in the ANSI SQL standard).</td>
     * </tr>
     * <tr valign="top">
     * <td>1</td>
     * <td>Driver-specific error code.</td>
     * </tr>
     * <tr valign="top">
     * <td>2</td>
     * <td>Driver-specific error message.</td>
     * </tr>
     * </p>
     * <p>
     * If the SQLSTATE error code is not set or there is no driver-specific
     * error, the elements following element 0 will be set to <b>NULL</b>.
     * </p>
     * <p>
     * <b>PDO::errorInfo</b> only retrieves error information for
     * operations performed directly on the database handle. If you create a
     * PDOStatement object through <b>PDO::prepare</b> or
     * <b>PDO::query</b> and invoke an error on the statement
     * handle, <b>PDO::errorInfo</b> will not reflect the error
     * from the statement handle. You must call
     * <b>PDOStatement::errorInfo</b> to return the error
     * information for an operation performed on a particular statement handle.
     */
    public function errorInfo();

    /**
     * Retrieve a database connection attribute
     * @link https://php.net/manual/en/pdo.getattribute.php
     * @param int $attribute <p>
     * One of the PDO::ATTR_* constants. The constants that
     * apply to database connections are as follows:
     * PDO::ATTR_AUTOCOMMIT
     * PDO::ATTR_CASE
     * PDO::ATTR_CLIENT_VERSION
     * PDO::ATTR_CONNECTION_STATUS
     * PDO::ATTR_DRIVER_NAME
     * PDO::ATTR_ERRMODE
     * PDO::ATTR_ORACLE_NULLS
     * PDO::ATTR_PERSISTENT
     * PDO::ATTR_PREFETCH
     * PDO::ATTR_SERVER_INFO
     * PDO::ATTR_SERVER_VERSION
     * PDO::ATTR_TIMEOUT
     * </p>
     * @return mixed A successful call returns the value of the requested PDO attribute.
     * An unsuccessful call returns null.
     */
    public function getAttribute($attribute);

    /**
     * Quotes a string for use in a query.
     * @link https://php.net/manual/en/pdo.quote.php
     * @param string $string <p>
     * The string to be quoted.
     * </p>
     * @param int $parameter_type [optional] <p>
     * Provides a data type hint for drivers that have alternate quoting styles.
     * </p>
     * @return string|false a quoted string that is theoretically safe to pass into an
     * SQL statement. Returns <b>FALSE</b> if the driver does not support quoting in
     * this way.
     */
    public function quote($string, $parameter_type = PDO::PARAM_STR);

    /**
     * Return an array of available PDO drivers
     * @link https://php.net/manual/en/pdo.getavailabledrivers.php
     * @return array <b>PDO::getAvailableDrivers</b> returns an array of PDO driver names. If
     * no drivers are available, it returns an empty array.
     */
    public static function getAvailableDrivers();

    /**
     * Cross-platform string escaping for preventing SQL injection (usually by sanitizing data and surrounding it with quotes).<br>
     * Usually you should use prepared statements (via expression builders or directly) instead of manually escaping values.
     *
     * @param mixed $value
     * @param mixed|null $type
     * @return string
     */
    public function escape($value, $type = null): string;

    /**
     * Get database host address
     *
     * @return string
     */
    public function getHost(): string;

    /**
     * Get database name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get database charset
     *
     * @return string
     */
    public function getCharset(): string;

    /**
     * Get database options used during initialization
     *
     * @return array
     */
    public function getOptions(): array;

}