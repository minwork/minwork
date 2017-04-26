<?php
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
     *            
     */
    public function exec($statement);

    /**
     * Executes an SQL statement, returning a result specific to database implementation
     *
     * @param string $statement
     *            The SQL statement to prepare and execute.<br>Data inside the query should be properly escaped.
     *            
     */
    public function query($statement);

    /**
     * Cross-platform string escaping for preventing SQL injection (usually by sanitizing data and surrounding it with quotes)
     *
     * @param mixed $value            
     */
    public function escape($value): string;

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
     */
    public function getLastInsertId();
}