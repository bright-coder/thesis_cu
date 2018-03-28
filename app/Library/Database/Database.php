<?php

namespace App\Library\Database;

class Database{
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
    * @param string $server
    * @param string $name
    */
    public function __construct(string $server, string $name){
        $this->server = $server;
        $this->name = $name;

        $tables = [];
    }

    public function setTables(array $tables): void{
        $this->tables = $tables;
    }

    public function getAllTables(): array{
        return $this->tables;
    }

    public function getTableByName(string $name): Table{
        return $this->tables[$name];
    }

    public function getColumnByTableAndColumnName(string $tableName, string $columnName) {
        return $this->getTableByName($tableName)->getColumnByName($columnName);
    }

    public function toArray(): array {
        $tables = [];
        foreach ($this->getAllTables() as $table) {
                $tables[$table->getName()]['instance'] = $table->getInstance();
            foreach($table->getAllColumns() as $column) {
                $tables[$table->getName()]['columns'][$column->getName()] = [
                    'type' => $column->getDataType()->getType(),
                    'length' => $column->getDataType()->getLength(),
                    'precision' => $column->getDataType()->getPrecision(),
                    'scale' => $column->getDataType()->getScale(),
                    'nullable' => $column->isNullable(),
                    'default' => $column->getDefault(),
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
            foreach($table->getAllUniqueConstraint() as $unique) {
                $tables[$table->getName()]['constraints']['uniques'][] = [
                    'name' => $unique->getName(),
                    'columns' => $unique->getColumns(),
                ];
            }
            foreach($table->getAllCheckConstraint() as $check) {
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