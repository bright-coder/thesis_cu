<?php

namespace App\Library\CustomModel\ModelOutput;

use App\Library\Constraint\ConstraintFactory;
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

            foreach ($constraints as $constraint) {
                $constraints[$constraint['name']] = ConstraintFactory::create($constraint);
            }

            return $constraints;

        } elseif (ModelOutputType::COLUMN === $OutputType) {
            $columns = [];
            foreach ($queryResult as $columnInfo) {
                $columns[$columnInfo['name']] = new Column($columnInfo);
            }
            return $columns;
        } elseif (ModelOutputType::TABLE === $OutputType) {
            $tables = [];
            foreach($queryResult as $tableName) {
                $tables[$tableName] = new Table($tableName);
            }
            return $tables;
        }

    }

}
