<?php

namespace Minwork\Database\Exceptions;

use Exception;

class DatabaseException extends Exception {
    public static function noTransaction() {
        return new self('Cannot abort or finish transaction because no transaction is started');
    }

    public static function transactionRollbackOnly() {
        return new self('Cannot commit transaction because one of the nested transactions was rolled back');
    }

    public static function invalidColumnType($type) {
        return new self("Column was provided with invalid type '{$type}', see Minwork\Database\Interfaces\ColumnInterface for list of possible values");
    }

    public static function invalidDateTime(string $message) {
        return new self("Column couldn't be mapped to DateTime object because it's constructor threw error: {$message}");
    }
}