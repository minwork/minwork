<?php
/** @noinspection DuplicatedCode */

namespace Minwork\Database\MySql;

use Minwork\Database\Interfaces\ColumnInterface;
use Minwork\Database\Prototypes\AbstractColumn;

class Column extends AbstractColumn
{
    public static function createFromDefinition($definition): ColumnInterface
    {
        return new self($definition['COLUMN_NAME'], self::mapType($definition['COLUMN_TYPE']), $definition['COLUMN_DEFAULT'], $definition['IS_NULLABLE'] == 'YES', $definition['COLUMN_KEY'] == 'PRI', strpos($definition['EXTRA'], 'auto_increment') !== false);
    }

    public function getDatabaseType(): string
    {
        if (!is_null($this->databaseType)) {
            return $this->databaseType;
        }

        $length = $this->getLength();
        $unsigned = $this->getProperties()['unsigned'] ?? false;

        switch ($this->getType()) {
            case self::TYPE_DATETIME:
                return 'DATETIME';

            case self::TYPE_FLOAT:

                if (is_string($length)) {
                    return "DECIMAL({$length})";
                } elseif (!is_null($length)) {
                    return "FLOAT({$length})";
                }
                return $unsigned ? "FLOAT(10)" : "FLOAT(11)";

            case self::TYPE_INTEGER:
                $length = $this->getLength();
                if (!is_null($length)) {
                    return "INT({$length})";
                }
                return $unsigned ? "INT(10)" : "INT(11)";

            case self::TYPE_BOOLEAN:
                return "BOOLEAN";

            case self::TYPE_TEXT:
                return "TEXT";

            case self::TYPE_STRING:
            default:
                return !is_null($length) ? "VARCHAR({$length})" : "VARCHAR(255)";
        }
    }
}