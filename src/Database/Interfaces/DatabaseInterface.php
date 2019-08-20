<?php
/*
 * This file is part of the Minwork package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Minwork\Database\Interfaces;

/**
 * Interface for database (fitted for PDO implementation)
 *
 * @author Christopher Kalkhoff
 *        
 */
interface DatabaseInterface
{

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $statement
     *            The SQL statement to prepare and execute.<br>Data inside the query should be properly escaped.
     * @return mixed
     */
    public function exec($statement);

    /**
     * Executes an SQL statement, returning a result specific to database implementation
     *
     * @param string $statement
     *            The SQL statement to prepare and execute.<br>Data inside the query should be properly escaped.
     * @return mixed           
     */
    public function query($statement);

    /**
     * Cross-platform string escaping for preventing SQL injection (usually by sanitizing data and surrounding it with quotes)
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

    /**
     * Get last insert id from automatically incremented column
     * 
     * @return mixed
     */
    public function getLastInsertId();

    /**
     * Method to start database transaction
     *
     * @return mixed
     */
    public function beginTransaction();
    /**
     * Method to commit database transaction
     */
    public function commit();
    /**
     * Method to abort database transaction
     */
    public function rollBack();
    /**
     * If database has active transaction
     *
     * @return mixed
     */
    public function inTransaction();

}