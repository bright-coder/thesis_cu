<?php

namespace App\Library\Database;

use App\Library\Node;
use App\Library\Constraint\Constraint;

class Database
{
    /**
    * @var string
    */
    private $server;
    /**
    * @var string
    */
    private $name;
    /**
    * @var array
    */
    private $tables;

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $hashFks = [];
    /**
    * @param string $server
    * @param string $name
    */
    public function __construct(string $server, string $name)
    {
        $this->server = $server;
        $this->name = $name;

        $this->tables = [];
    }

    public function setTables(array $tables): void
    {
        $this->tables = $tables;
    }

    public function getAllTables(): array
    {
        return $this->tables;
    }

    public function getTableByName(string $name): Table
    {
        return $this->tables[$name];
    }

    public function getColumnByTableAndColumnName(string $tableName, string $columnName)
    {
        return $this->getTableByName($tableName)->getColumnByName($columnName);
    }

    public function createFkPaths() : void
    {
        $hashMap = [];
        $tables = $this->getAllTables();
        foreach ($tables as $table) {
            $fks = $table->getAllFK();
            foreach ($fks as $fk) {
                foreach ($fk->getColumns() as $link) {
                    if (!\array_key_exists($link['from']['tableName'], $hashMap)) {
                        $hashMap[$link['from']['tableName']] = [];
                    }
                    if (!\array_key_exists($link['from']['columnName'], $hashMap[$link['from']['tableName']])) {
                        $hashMap[$link['from']['tableName']][$link['from']['columnName']] = new Node(
                            $link['from']['tableName'],
                            $link['from']['columnName'],
                            $fk->getName()
                        );
                    }

                    if (!\array_key_exists($link['to']['tableName'], $hashMap)) {
                        $hashMap[$link['to']['tableName']] = [];
                    }

                    if (!\array_key_exists($link['to']['columnName'], $hashMap[$link['to']['tableName']])) {
                        $hashMap[$link['to']['tableName']][$link['to']['columnName']] = new Node(
                            $link['to']['tableName'],
                            $link['to']['columnName'],
                            $fk->getName()
                        );
                    }

                    $hashMap[$link['from']['tableName']][$link['from']['columnName']]->setPrevious($hashMap[$link['to']['tableName']][$link['to']['columnName']]);
                    $hashMap[$link['to']['tableName']][$link['to']['columnName']]->addLink($hashMap[$link['from']['tableName']][$link['from']['columnName']]);
                }
            }
        }
        $this->hashFks = $hashMap;
    }

    public function isLinked(string $tableName, string $columnName): bool
    {
        if (\array_key_exists($tableName, $this->hashFks)) {
            if (\array_key_exists($columnName, $this->hashFks[$tableName])) {
                return true;
            }
        }
        
        return false;
    }

    public function getHashFks() : array
    {
        return $this->hashFks;
    }

    public function toArray(): array
    {
        $tables = [];
        foreach ($this->getAllTables() as $table) {
            if (count($table->getInstance()) > 0) {
                $tables[$table->getName()]['instance'] = $table->getInstance();
            }
            foreach ($table->getAllColumns() as $column) {
                $tables[$table->getName()]['columns'][$column->getName()] = [
                    'dataType' => $column->getDataType()->getType(),
                    'length' => $column->getDataType()->getLength(),
                    'precision' => $column->getDataType()->getPrecision(),
                    'scale' => $column->getDataType()->getScale(),
                    'nullable' => $column->isNullable() ? 'Y' : 'N',
                    'unique' => $table->isUnique($column->getName()) || $table->isPK($column->getName()) ? 'Y' : 'N',
                    'min' => $table->getMin($column->getName()),
                    'max' => $table->getMax($column->getName()),
                    'default' => $column->getDefault(),
                    'isPK' => $table->isPK($column->getName()),
                    'isFK' => $table->isFK($column->getName())
                ];
            }
            $tables[$table->getName()]['constraints'] = [];
            $tables[$table->getName()]['constraints']['PK'] = [
                'name' => $table->getPK()->getName(),
                'columns' => $table->getPK()->getColumns()
            ];
            foreach ($table->getAllFK() as $fk) {
                $tables[$table->getName()]['constraints']['FKs'][] = [
                    'name' => $fk->getName(),
                    'links' => $fk->getColumns(),
                ];
            }
            foreach ($table->getAllUniqueConstraint() as $unique) {
                $tables[$table->getName()]['constraints']['uniques'][] = [
                    'name' => $unique->getName(),
                    'columns' => $unique->getColumns(),
                ];
            }
            foreach ($table->getAllCheckConstraint() as $check) {
                $tables[$table->getName()]['constraints']['checks'][] = [
                    'name' => $check->getName(),
                    'columns' => $check->getColumns(),
                    'definition' => $check->getDetail()['definition'],
                    'mins' => $check->getDetail()['min'],
                    'maxs' => $check->getDetail()['max']
                ];
            }
        }
        return $tables;
    }
}
