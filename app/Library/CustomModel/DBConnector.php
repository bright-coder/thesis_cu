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
    public function getConstraintsByTableName(string $tableName,  array $constraintsType = [Constraint::PRIMARY_KEY, Constraint::FOREIGN_KEY, Constraint::UNIQUE, Constraint::CHECK]): array;
    public function updateDatabase(Database $db): bool;
}
