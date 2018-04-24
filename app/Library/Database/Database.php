<?php

namespace App\Library\Database;

use App\Library\Node;

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
    
    private $fkPaths = [];

    private $hashFks = [];
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

    public function createFkPaths() : void {
        $hashMap = [];
        $headers = [];
        $tables = $this->getAllTables();
        foreach ($tables as $table) {
            $fks = $table->getAllFK();
            foreach( $fks as $fk) {
                foreach ( $fk->getColumns() as $link) {

                    if(!\array_key_exists($link['from']['tableName'],$hashMap)) {
                        $hashMap[$link['from']['tableName']] = [];
                    }
                    if(!\array_key_exists($link['from']['columnName'],$hashMap[$link['from']['tableName']])) {
                        $hashMap[$link['from']['tableName']][$link['from']['columnName']] = new Node(
                            $link['from']['tableName'],
                            $link['from']['columnName'],
                            $fk->getName()
                        );
                    }

                    if(\array_key_exists($link['from']['tableName'],$headers)) {
                        if(\array_key_exists($link['from']['columnName'],$headers[$link['from']['tableName']])) {
                            $headers[$link['from']['tableName']][$link['from']['columnName']] = false;
                        }
                    }

                    if(!\array_key_exists($link['to']['tableName'],$hashMap)) {
                        $hashMap[$link['to']['tableName']] = [];
                        $headers[$link['to']['tableName']] = [];
                    }

                    if(!\array_key_exists($link['to']['columnName'],$hashMap[$link['to']['tableName']])) {
                        $hashMap[$link['to']['tableName']][$link['to']['columnName']] = new Node(
                            $link['to']['tableName'],
                            $link['to']['columnName'],
                            $fk->getName()
                        );
                        $headers[$link['to']['tableName']][$link['to']['columnName']] = true;
                    }

                    $hashMap[$link['from']['tableName']][$link['from']['columnName']]->setPrevious($hashMap[$link['to']['tableName']][$link['to']['columnName']]);
                    $hashMap[$link['to']['tableName']][$link['to']['columnName']]->addLink($hashMap[$link['from']['tableName']][$link['from']['columnName']]);

                }
            }
        }
        $this->hashFks = $hashMap;
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