<?php

namespace App\Library;

use App\Model\TableImpact;
use App\Model\ColumnImpact;
use App\Model\InstanceImpact;
use App\Model\OldInstance;
use App\Model\FrImpact;
use App\Model\FrInputImpact;
use App\Model\TcImpact;
use App\Model\TcInputImpact;
use App\Model\TestCase;
use App\Model\RtmRelationImpact;
use App\Model\ChangeRequestInput;

class ImpactResult
{
    private $changeRequestId;

    public function __construct($changeRequestId)
    {
        $this->changeRequestId = $changeRequestId;
    }

    public function getImpact(): array
    {
        return [
            'schema' => $this->getSchemaImpact(),
            'instance' => $this->getInstanceImpact(),
            'functionalRequirments' => $this->getFrImpact(),
            'testCases' => $this->getTcImpact(),
            'rtm' => $this->getRtmImpact()
        ];
    }

    private function getSchemaImpact(): array
    {
        $tableImpactList = TableImpact::where('changeRequestId', $this->changeRequestId)->get();
        $tableResult = [];
        foreach ($tableImpactList as $tableImpact) {
            $table = [
                'name' => $tableImpact->name,
                'columnList' => null
            ];
            $columnResult = [];
            $columnList = ColumnImpact::where('tableImpactId', $tableImpact->id)->get();
            foreach ($columnList as $column) {
                if (!array_key_exists($column->name, $columnResult)) {
                    $columnResult[$column->name] = [
                        'name' => $column->name,
                        'changeType' => $column->changeType
                    ];
                }
                $columnResult[$column->name][$column->versionType] = $column;
            }
            if (!empty($columnResult)) {
                foreach ($columnResult as $result) {
                    $table['columnList'][] = $result;
                }
            }
            $tableResult[] = $table;
        }
        
        return $tableResult;
    }

    private function getInstanceImpact(): array
    {
        $changeRequestInputList = ChangeRequestInput::where('changeRequestId', $this->changeRequestId)->get();
        $instanceResult = [];
        foreach ($changeRequestInputList as $crInput) {
            $instanceResult[$crInput->id]['crInputId'] = $crInput->id;
            $tableImpactList = [];
            $impact = [
                'tableName' => '',
                'columnName' => '',
                'columnOrder' => [],
                'changeType' => $crInput->changeType,
                'records' => []
            ];

            foreach (InstanceImpact::where('changeRequestInputId', $crInput->id)->get() as $instanceImpact) {
                $impact['tableName'] = $instanceImpact->tableName;
                if ($crInput->changeType != 'delete') {
                    $impact['records']['new'][] = $instanceImpact->newValue;
                }
                $record = [];
                foreach (OldInstance::where('instanceImpactId', $instanceImpact->id)->orderBy('id','asc')->get() as $index => $oldInstance) {
                    if(!array_key_exists($oldInstance->columnName, $impact['columnOrder'])) {
                        $impact['columnOrder'][$oldInstance->columnName] = $index;
                    }
                    $record[] = $oldInstance->value;
                }
                $impact['records']['old'][] = $record;
                if(array_key_exists($instanceImpact->tableName,$tableImpactList)) {
                    $tableImpactList[$instanceImpact->tableName]['records']['new'][] = $instanceImpact->newValue;
                    $tableImpactList[$instanceImpact->tableName]['records']['old'][] = $record;
                }
                else {
                    $tableImpactList[$instanceImpact->tableName] = [
                        'tableName' => $instanceImpact->tableName,
                        'columnName' => $instanceImpact->columnName,
                        'records' => ['new' => [$instanceImpact->newValue], 'old' => [$record] ],
                        'changeType' => $crInput->changeType,
                        'columnOrder' => array_flip($impact['columnOrder'])
                    ];
                }
            
            }
            //$tableImpactList[$impact['tableName']]['columnOrder'] = array_flip($impact['columnOrder']);
            //$impact['columnOrder'] = array_flip($impact['columnOrder']);
            foreach($tableImpactList as $tableImpact) {
                $instanceResult[$crInput->id]['tableImpactList'][] = $tableImpact;
            }
            if(empty($tableImpactList)) {
                unset($crInput->id, $instanceResult);
            }
            
        }
        $result = [];
        foreach ($instanceResult as $crInputImpact) {
            $result[] = $crInputImpact;
        }
        return $result;
    }

    private function getFrImpact(): array
    {
        $frResult = [];
       
        foreach (FrImpact::where('changeRequestId', $this->changeRequestId)->get() as $frImpact) {
            $impact = [
                'functionalRequirementNo' => $frImpact->no,
                'inputs' => []
            ];
            
            $frInputMem = [];
            foreach (FrInputImpact::where('frImpactId', $frImpact->id)->get() as $frInputImpact) {
                $arrFrImpact = $frInputImpact->toArray();
                unset($arrFrImpact['id']);
                unset($arrFrImpact['frImpactId']);
                unset($arrFrImpact['versionType']);
                unset($arrFrImpact['changeType']);
                unset($arrFrImpact['name']);
                if (!\array_key_exists($frInputImpact->name, $frInputMem)) {
                    $frInputMem[$frInputImpact->name] = [
                        'name' => $frInputImpact->name,
                        'changeType' => $frInputImpact->changeType
                    ];
                }
                else {
                    if($frInputImpact->changeType == 'edit' && $frInputImpact->versionType == 'old') {
                        $frInputMem[$frInputImpact->name]['name'] = $frInputImpact->name;
                    }
                }
                $frInputMem[$frInputImpact->name][$frInputImpact->versionType] = $arrFrImpact;
            }

            foreach($frInputMem as $input) {
                $impact['inputs'][] = $input;
            }
            $frResult[] = $impact;
        }
        return $frResult;
    }

    private function getTcImpact(): array
    {
        $tcResult = [];
        foreach(TcImpact::where('changeRequestId', $this->changeRequestId)->get() as $tcImpact) {
            $input = [];
            foreach(TcInputImpact::where('tcImpactId', $tcImpact->id)->get() as $tcInputImpact) {
                $input[] = [
                    'name' => $tcInputImpact->inputName,
                    'oldData' => $tcInputImpact->testDataOld,
                    'newData' => $tcInputImpact->testDataNew
                ];
            }
            $tcResult[] = [
                'changeType' => $tcImpact->changeType,
                'no' => $tcImpact->no,
                'inputs' => $input
            ];
        }
        return $tcResult;
    }

    private function getRtmImpact() : array
    {
        $rtmResult = [];
        foreach(RtmRelationImpact::where('changeRequestId',  $this->changeRequestId)->get() as $rtmImpact) {
            unset($rtmImpact['id']);
            $rtmResult[] = $rtmImpact;
        }
        return $rtmResult;
    }
}