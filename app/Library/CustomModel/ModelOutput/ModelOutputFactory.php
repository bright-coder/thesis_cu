<?php

namespace App\Library\CustomModel\ModelOutput;

use App\Library\Constraint\ConstraintFactory;
use App\Library\Constraint\ConstraintType;
use App\Library\CustomModel\ModelOutput\ModelOutputType;
use App\Library\Database\Column;
use App\Library\Database\Table;

final class ModelOutputFactory
{

    public static function createOutput(int $OutputType, array $queryResult): array
    {

        if (ModelOutputType::CONSTRAINT === $OutputType) {
            $constraints = [];
            foreach ($queryResult as $row) {
                if (!array_key_exists($row['name'], $constraints)) {

                    $constraints[$row['name']] = $row;

                    $tempColumnName = $constraints[$row['name']]['columnName'];

                    $constraints[$row['name']]['columnName'] = [$tempColumnName];
                } else {
                    array_push($constraints[$row['name']]['columnName'], $row['columnName']);
                }

            }

            $pk = ConstraintFactory::create(['name' => '', 'type' => ConstraintType::PRIMARY_KEY, 'columnName' => []]);
            $fks = [];
            $uniques = [];
            $checks = [];
            foreach ($constraints as $constraint) {
                if (ConstraintType::CHECK == $constraint['type']) {
                    $checks[$constraint['name']] = ConstraintFactory::create($constraint);
                } elseif (ConstraintType::FOREIGN_KEY == $constraint['type']) {
                    $fks[$constraint['name']] = ConstraintFactory::create($constraint);
                } elseif (ConstraintType::PRIMARY_KEY == $constraint['type']) {
                    $pk = ConstraintFactory::create($constraint);
                } elseif (ConstraintType::UNIQUE == $constraint['type']) {
                    $uniques[$constraint['name']] = ConstraintFactory::create($constraint);
                }
            }

            return ['pk' => $pk, 'fks' => $fks, 'uniques' => $uniques, 'checks' => $checks];

        } elseif (ModelOutputType::COLUMN === $OutputType) {
            $columns = [];
            foreach ($queryResult as $columnInfo) {
                $columns[$columnInfo['name']] = new Column($columnInfo);
            }
            return $columns;
        } elseif (ModelOutputType::TABLE === $OutputType) {
            $tables = [];
            foreach ($queryResult as $tableName) {
                $tables[$tableName] = new Table($tableName);
            }
            return $tables;
        }

    }

}
