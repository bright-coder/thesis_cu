<?php

namespace App\Library\CustomModel;

use App\Library\Database\Database;

interface DBTargetInterface
{
    public function connect(): bool;
    public function getDBType(): string;
    public function getDBServer(): string;
    public function getDBName(): string;
    public function getAllTables(): array;
    public function getDistinctValues(string $tableName, string $columnName): array;
    public function getAllColumnsByTableName(string $tableName): array;
    public function getAllConstraintsByTableName(string $tableName): array;
}
