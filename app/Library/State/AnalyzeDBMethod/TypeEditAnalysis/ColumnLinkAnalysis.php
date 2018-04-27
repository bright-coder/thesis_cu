<?php

use App\Library\State\AnalyzeDBMethod\TypeEditAnalysis\ColumnAnalysis;

class ColumnLinkAnalysis extends ColumnAnalysis
{   

    private function findImpactAllTable(Node $primaryColumn) : array
    {
        $travel = $primaryColumn;
        $listImpact = [
            ['tableName' => $node->getTableName(), 'columnName' => $node->getColumnName]
        ];
        while (count($travel->getLinks()) > 0) {
            foreach ($travel->getLinks() as $node) {
                $listImpact = ['tableName' => $node->getTableName(), 'columnName' => $node->getColumnName];
                if (count($node->getLinks()) > 0) {
                    $listImpact[] = $this->findImpactAllTable($node->getTableName(), $node->getColumName());
                }
            }
        }
        return $listImpact;
    }

    private function findPrimaryColumnNode(string $tableName, string $columnName) : Node
    {
        $node = $this->database->getHashFks()[$tableName][$columnName];
        $travel = $node;
        while ($travel->getPrevious() != null) {
            $travel = $travel->getPrevious();
        }

        return $travel;
    }
    
}