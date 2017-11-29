<?php

namespace App\Library\CustomModel;

use App\Library\Database\Database;

interface DBConnector
{
    public static function getInstance(): \PDO;
    public static function getDBType(): string;
    public static function getDBServer(): string;
    public static function getDBName(): string;
    public static function getAllTables(): array;
    public static function getNumDistinctValues(string $tableName, string $columnName): int;
    public static function getAllColumnsByTableName(string $tableName): array;
    public static function getAllConstraintsByTableName(string $tableName): array;
}
