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
    public function dropConstraint(string $tableName, string $constraint) : bool;
    public function dropColumn(string $tableName, string $columnName): bool;
    public function addColumn(array $columnDetail): bool;
    public function updateColumn(array $columnDetail): bool;
    public function updateInstance(string $tableName, string $newColumnName, array $refValues, array $newData, $default): bool;
    public function updateColumnName(string $tableName, string $oldColumnName, string $newColumnName) : bool;
    public function addUniqueConstraint(string $tableName, string $columnName) : bool;
    public function addPrimaryKeyConstraint(string $tableName, array $columnName, string $constraintName) : bool;
    public function addForeignKeyConstraint(string $tableName, array $links, string $constraintName) : bool;
    public function addCheckConstraint(string $tableName, string $columnName, $min, $max) : bool;
    public function setNullable(string $tableName, string $columnName, array $columnDetail): bool;
    public function getInstanceByTableName(string $tableName, array $columnList = [], string $condition = ''): array;
    public function getDuplicateInstance(string $tableName, array $checkColumns, array $pkColumns): array;
    public function getNumRows(string $tableName): int;
    public function disableConstraint(string $tableName): bool;
    public function enableConstraint(string $tableName): bool;

}
