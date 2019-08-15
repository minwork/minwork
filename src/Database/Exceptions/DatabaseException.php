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
}