<?php
use Minwork\Database\MySql\Table;
use Minwork\Database\Object\Database;
use Minwork\Database\Object\Column;

require '../../../vendor/autoload.php';
require 'App/Config.php';

$table = new Table(new Database(DB_DRIVER, DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD), 'user', [
    new Column('id', 'INT', null, false, true, true),
    new Column('email', 'VARCHAR(255)'),
    new Column('first_name', 'VARCHAR(255)'),
    new Column('last_name', 'VARCHAR(255)'),
    new Column('created', 'DATETIME'),
    new Column('last_modified', 'DATETIME'),
]);
if ($table->create()) {
    echo 'User table successfully created';
} else {
    echo 'Error during user table creation. Make sure you have correct constants values in App/Config.php file';
}
