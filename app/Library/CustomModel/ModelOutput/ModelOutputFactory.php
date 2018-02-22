<?php

namespace App\Library\CustomModel\ModelOutput;

use App\Library\Constraint\ConstraintFactory;
use App\Library\Constraint\ConstraintType;
use App\Library\Database\Column;
use App\Library\Database\Table;
use App\Library\Constraint\PrimaryKey;

final class ModelOutputFactory
{
    public static function createPrimaryKey(array $queryResult): PrimaryKey
    {
        $queryResult = ModelOutputFactory::modifyResult($queryResult, ConstraintType::PRIMARY_KEY);
        return ConstraintFactory::create(array_shift($queryResult));
    }

    public static function createForeignKey(array $queryResult): array
    {
        $queryResult = ModelOutputFactory::modifyResult($queryResult, ConstraintType::FOREIGN_KEY);
        $foreignKeys = [];
        foreach ($queryResult as $foreignKey) {
            $foreignKeys[$foreignKey['name']] = ConstraintFactory::create($foreignKey);
        }
        return $foreignKeys;
    }

    public static function createCheckConstraint(array $queryResult): array
    {
        $queryResult = ModelOutputFactory::modifyResult($queryResult, ConstraintType::CHECK);
        $checkConstraints = [];
        foreach ($queryResult as $checkConstraint) {
            $checkConstraints[$checkConstraint['name']] = ConstraintFactory::create($checkConstraint);
        }
        return $checkConstraints;
    }

    public static function createUniqueConstraint(array $queryResult): array
    {
        $queryResult = ModelOutputFactory::modifyResult($queryResult, ConstraintType::UNIQUE);
        $uniqueConstraints = [];
        foreach ($queryResult as $uniqueConstraint) {
            $uniqueConstraints[$uniqueConstraint['name']] = ConstraintFactory::create($uniqueConstraint);
        }
        return $uniqueConstraints;
    }

    public static function createColumn(array $queryResult): array
    {
        $columns = [];
        foreach ($queryResult as $columnInfo) {
            $columns[$columnInfo['name']] = new Column($columnInfo);
        }
        return $columns;
    }

    public static function createTable(array $queryResult): array
    {
        $tables = [];
        foreach ($queryResult as $tableName) {
            $tables[$tableName] = new Table($tableName);
        }
        return $tables;
    }

    private static function modifyResult(array $queryResult, string $constraintType): array
    {
        $constraints = [];

        if (ConstraintType::FOREIGN_KEY !== $constraintType) {
            foreach ($queryResult as $row) {
                if (!array_key_exists($row['name'], $constraints)) {

                    $constraints[$row['name']] = $row;

                    $tempColumnName = $constraints[$row['name']]['columnName'];

                    $constraints[$row['name']]['columnName'] = [$tempColumnName];
                } else {
                    array_push($constraints[$row['name']]['columnName'], $row['columnName']);
                }
            }
        } else {
            foreach ($queryResult as $row) {
                if (!array_key_exists($row['FK_NAME'], $constraints)) {
                    $constraints[$row['FK_NAME']] = [
                        'name' => $row['FK_NAME'],
                        'type' => ConstraintType::FOREIGN_KEY,
                        'links' =>
                        [
                            [
                                'primary' => [
                                    'tableName' => $row['PKTABLE_NAME'],
                                    'columnName' => $row['PKCOLUMN_NAME']
                                ],
                                'reference' => [
                                    'tableName' => $row['FKTABLE_NAME'],
                                    'columnName' => $row['FKCOLUMN_NAME']
                                ]
                            ]
                        ]
                    ];

                } else {
                    $link = [
                        'primary' => [
                            'tableName' => $row['PKTABLE_NAME'],
                            'columnName' => $row['PKCOLUMN_NAME']
                        ],
                        'reference' => [
                            'tableName' => $row['FKTABLE_NAME'],
                            'columnName' => $row['FKCOLUMN_NAME']
                        ]
                    ];
                    array_push($constraints[$row['FK_NAME']]['links'], $link);

                }
            }
        }
        return $constraints;
    }

}
