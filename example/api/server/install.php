<?php

use Example\ApiServer\App\Main\Utility\Factory;
use Minwork\Database\Exceptions\DatabaseException;
use Minwork\Database\Interfaces\ColumnInterface;
use Minwork\Database\Prototypes\AbstractTable;

require '../../../vendor/autoload.php';
require 'App/Config.php';

/**
 * @var AbstractTable $table
 */
try {
    $table = Factory::getUserStorage();
    echo 'Table already created';
} catch (DatabaseException $e) {
    $table->setColumns([
        $table::getColumnInstance('id', ColumnInterface::TYPE_INTEGER, null, false, true, true),
        $table::getColumnInstance('email', ColumnInterface::TYPE_STRING),
        $table::getColumnInstance('first_name', ColumnInterface::TYPE_STRING),
        $table::getColumnInstance('last_name', ColumnInterface::TYPE_STRING),
        $table::getColumnInstance('created', ColumnInterface::TYPE_DATETIME),
        $table::getColumnInstance('last_modified', ColumnInterface::TYPE_DATETIME, null, true),
    ]);

    try {
        if ($table->create()) {
            echo 'User table successfully created';
        } else {
            echo 'Error during user table creation. Make sure you have correct constants values in App/Config.php file';
        }
    } catch (Exception $e) {
        echo "Error during table creation:\n\n{$e}";
    }
}