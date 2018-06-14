<?php

namespace App\Library\CustomModel;

use App\Library\Database\Database;
use App\Library\Constraint\PrimaryKey;
use App\Model\ChangeRequestInput;

interface DBTargetInterface
{
    public function connect(): bool;
    public function getDBType(): string;
    public function getDBServer(): string;
    public function getDBName(): string;
    public function getAllTables(): array;
    public function getDistinctValues(string $tableName, array $columnName): array;
    public function getAllColumnsByTableName(string $tableName): array;
    public function getAllConstraintsByTableName(string $tableName): array;
    public function getPKConstraint(string $tableName): PrimaryKey;
    public function getFkConstraints(string $tableName): array;
    public function getCheckConstraints(string $tableName): array;
    public function getUniqueConstraints(string $tableName): array;
    public function getInstanceByTableName(string $tableName, array $columnList = [], string $condition = ''): array;
    public function getDuplicateInstance(string $tableName, array $checkColumns, array $pkColumns): array;
    public function getNumRows(string $tableName): int;
    public function updateDatabase(array $scImpacts, array $insImpacts, array $keyImpacts, Database $dbTarget): bool;

}
