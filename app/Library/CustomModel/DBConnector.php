<?php

namespace App\Library\CustomModel;

use App\Library\Database\Database;

interface DBConnector
{
    public function getDBType(): string;
    public function getDBServer(): string;
    public function getDBName(): string;
    public function getAllTables(): array;
    public function getAllColumnsByTableName(string $tableName): array;
    public function getAllConstraintsByTableName(string $tableName): array;
}
