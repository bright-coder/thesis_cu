<?php

namespace App\Library\CustomModel;

use App\Library\Database\Database;

interface DBTargetInterface
{
    public function getDBType(): string;
    public function getDBServer(): string;
    public function getDBName(): string;
    public function getAllTables(): array;
    public function getNumDistinctValues(string $tableName, string $columnName): int;
    public function getAllColumnsByTableName(string $tableName): array;
    public function getAllConstraintsByTableName(string $tableName): array;
}
