<?php

namespace App\Library\CustomModel;

interface DBConnector
{
    public function getDBType(): string;
    public function getDBServer(): string;
    public function getDBName(): string;
    public function getAllTables(): array;
    public function getAllColumns(string $tableName): array;
    public function getPkColumns(string $tableName): array;
    public function getFKColumns(string $tableName): array;
    public function getCheckConstraint(string $columnName): array;
    public function getUniqueConstraint(string $columnName): array;
}
