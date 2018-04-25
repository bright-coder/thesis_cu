<?php

namespace App\Library;

class Node {
    /**
     * Undocumented variable
     *
     * @var string
     */
    private $tableName = "";
    /**
     * Undocumented variable
     *
     * @var string
     */
    private $columnName = "";
    
    /**
     * Undocumented variable
     *
     * @var array
     */
    private $links = [];

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $fkName = "";

    private $previousNode = null;

    public function __construct(string $tableName, string $columnName, string $fkName) {
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->fkName = $fkName;
    }

    public function getTableName() : string {
        return $this->tableName;
    }

    public function getColumnName() : string {
        return $this->columnName;
    }

    public function getFkName() : string {
        return $this->fkname;
    }

    public function getLinks() : array {
        return $this->links;
    }

    public function addLink(Node $node) : void{
        $this->links[] = $node;
    }

    public function setPrevious(Node $node) : void {
        $this->previousNode = $node;
    }

    public function getPrevious() {
        return $this->previousNode;
    }

}