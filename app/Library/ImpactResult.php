<?php

namespace App\Library;

use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Model\TableImpact;
use App\Model\ColumnImpact;
use App\Model\InstanceImpact;
use App\Model\PkRecord;
use App\Model\RecordImpact;
use App\Model\FrImpact;
use App\Model\FrInputImpact;
use App\Model\TcImpact;
use App\Model\TcInputImpact;
use App\Model\TestCase;
use App\Model\RtmImpact;
use App\Model\CompositeCandidateKeyColumn;
use App\Model\CompositeCandidateKeyImpact;
use App\Model\ForeignKeyColumn;
use App\Model\ForeignKeyImpact;

class ImpactResult
{
    private $changeRequestId;

    public function __construct($changeRequestId)
    {
        $this->changeRequestId = $changeRequestId;
    }

    public function getImpact(string $type = 'array'): array
    {
        return [
            'schema' => $this->getSchemaImpact($type),
            'instance' => $this->getInstanceImpact($type),
            'keys' => $this->getKeyImpact(),
            'functionalRequirments' => $this->getFrImpact($type),
            'testCases' => $this->getTcImpact($type),
            'rtm' => $this->getRtmImpact($type)
        ];
    }

    private function getSchemaImpact(string $type): array
    {
        $table = [];
        foreach(ColumnImpact::where('crId', $this->changeRequestId)->get() as $colImpact) {
            if(!isset($table[$colImpact->tableName])) {
                $table[$colImpact->tableName] = ['changeType' => $colImpact->changeType ,'old' => null, 'new' => null]; 
            }
            if($colImpact->versionType == 'old') {
                $table[$colImpact->tableName]['old'] = $colImpact;
            }
            else{
                $table[$colImpact->tableName]['new'] = $colImpact;
            }
        }

        if($type == 'json') {
            $tableJson = [];
            foreach($table as $tableName => $colList) {
                $aTable = ['tableName' => $tableName, 'colList' => []];
                foreach($colList as $columnname => $info) {
                    $aTable['colList'][] = ['columnName' => $columnname, 
                        'changeType' => $info['changeType'],
                        'old' => $info['old'],
                        'new' => $info['new']
                    ];
                }
                $tableJson[] = $aTable;
            }
            return $tableJson;
        }
        return $table;
    }

    private function getInstanceImpact(string $type): array
    {
       $table = [];
       foreach(RecordImpact::where('crId', $this->changeRequestId)->get() as $recImpact) {
           if(!isset($table[$recImpact->tableName])) {
               $table[$recImpact->tableName] = [];
           }
            $pkRecordList = [];
           foreach(PkRecord::where('recImpactId', $recImpact->id)->get() as $pkRecord) {
                $pkRecordList[$pkRecord->columnName] = $value;
           }
           $insImpactList = [];
           foreach(InstanceImpact::where('recImpactId', $recImpact->id)->get() as $insImpact) {
               $insImpactList[$insImpact->columnName] = ['old' => $insImpact->old, 'new' => $insImpact->new];
           }
           $table[$recImpact->tableName][] = ['pkRecord' => $pkRecordList, 'columnList' => $insImpactList];
       }

       if($type == 'json') {
           $tableJson = [];
           foreach($table as $tableName => $recordList) {
               $tableJson[] = ['tableName' => $tableName, 'recordList' => $recordList];
           }
           return $tableJson;
       }

       return $table;
    }

    private function getFrImpact(string $type): array
    {
       $fr = [];
       foreach(FrInputImpact::where('crId', $this->changeRequestId)->get() as $frInputImpact) {
           if(!isset($fr[$frInputImpact->frNo])) {
               $fr[$frInputImpact->frNo] = [];
           }
           $fr[$frInputImpact->frNo][$frInputImpact->name] = ['changeType' => $frInputImpact->changeType,
            'tableName' => $frInputImpact->tableName,
            'columnName' => $frInputImpact->columnName
          ];
       }

       if($type == 'json') {
           $frJson = [];
           foreach($fr as $frNo => $frInputList) {
               $aFr = ['no' => $frNo, 'frInputList' => []];
                foreach($frInputList as $name => $info) {
                    $aFr['frInputList'][] = ['name' => $name, 
                        'changeType' => $info['changeType'],
                        'tableName' => $info['tableName'],
                        'columnName' => $info['columnName']
                    ];
                }
                $frJson[] = $aFr;
           }
           return $frJson;
       }

       return $fr;
    }

    private function getTcImpact(string $type): array
    {
        $tc = [];
        foreach(TcImpact::where('crId', $this->changeRequestId)->get() as $tcImpact) {
            if(!isset($tc[$tcImpact->no])) {
                $tc[$tcImpact->no] = ['changeType' => $tcImpact->changeType, 'tcInputList' => []];
            }
            foreach(TcInputImpact::where('tcImpactId', $tcImpact->id)->get() as $tcInputImpact) {
                $tc[$tcImpact->no]['tcInputList'][$tcInputImpact->no] = [
                    'old' => $tcInputImpact->testDataOld,
                    'new' => $tcInputImpact->testDataNew
                ];
            }
        }

        if($type == 'json') {
            $tcJson = [];
            foreach($tc as $no => $info) {
                $aTc = ['no' => $no, 'changeType' => $info['changeType'], 'tcInputList' => []];
                
                foreach($info['tcInputList'] as $tcInput) {
                    $aTc['tcInputList'][] = ['name' => $tcInput['name'],
                        'old' => $tcInput['old'],
                        'new' => $tcInput['new']
                    ];
                }
            }
            
            return $tcJson;
        }
        

        return $tc;
    }

    private function getRtmImpact(string $type) : array
    {
        $rtm = [];
        foreach(RtmImpact::where('crId', $this->changeRequestId)->get() as $rtmImpact) {
            if(!isset($rtm[$rtmImpact->frNo])) {
                $rtm[$rtmImpact->frNo] = [];
            }
            $rtm[$rtmImpact->frNo][$rtmImpact->tcNo] = $rtmImpact->changeType;
        }

        if($type == 'json') {
            $rtmJson = [];
            foreach($rtm as $frNo => $tcNoList) {
                foreach($tcNoList as $tcNo => $changeType) {
                    $rtmJson[] = ['frNo' => $frNo, 'tcNo' => $tcNo, 'changeType' => $changeType];
                }
            }
            return $rtmJson;
        }

        return $rtm;
    }

    private function getKeyImpactResult(string $type): array 
    {
        $table = [];
        foreach(CompositeCandidateKeyImpact::where('crId', $this->changeRequestId)->get() as $cckImpact) {
            if(!isset($table[$cckImpact->cckTable])) {
                $table[$cckImpact->cckTable] = [];
            }
            $table[$cckImpact->cckTable][$cckImpact->cckName] = ['type' => 'UNIQUE', 'columns' => []];
            
            foreach(CompositeCandidateKeyColumn::where('cckImpactId', $cckImpact->id)->get() as $cckColumn) {
                $table[$cckImpact->cckTable][$cckImpact->cckName]['columns'][] = $cckColumn->columnName;
            }

        }

        foreach(ForeignKeyImpact::where('crId', $this->changeRequestId)->get() as $fkImpact) {
            if(!isset($table[$fkImpact->fkTableName])) {
                $table[$fkImpact->fkTableName] = [];
            }
            $table[$fkImpact->fkTableName][$fkImpact->fkName] = ['type' => 'FK', 'columns' => []];

            foreach(ForeignKeyColumn::where('fkImpactId', $fkImpact->id)->get() as $link) {
                $table[$fkImpact->fkTableName][$fkImpact->fkName]['columns'][] = [
                    'from' => ['tableName' => $fkTableName, 'columnName' => $link->referencingColumnName],
                    'to' => ['tableName' => $link->referencedTable, 'columnName' => $link->referencedColumnName]
                ];
            }
        }

        if($type == 'json') {
            $tableJson = [];
            foreach($table as $tableName => $keyList) {
                $aTable = ['tableName' => $tableName, 'keyList' =>[]];
                foreach($keyList as $consName => $info) {
                    $aTable['keyList'][] = ['keyName' => $consName, 'type' => $info['type'], 'columns' => $info['columns']];
                }
            }
            return $tableJson;
        }

        return $table;
    }
}
