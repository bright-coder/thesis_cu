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
        $tableImpactList = TableImpact::where('changeRequestId', $this->changeRequestId);
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
                    $columnResult[$column] = [
                        'name' => $column->name
                    ];
                }
                $columnResult[$column][$column->versionType] = $column;
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
            $impact = [
                'tableName' => '',
                'columnName' => '',
                'changeType' => $crInput->changeType,
                'records' => []
            ];

            foreach (InstanceImpact::where('changeRequestInputId', $crInput->id)->get() as $instanceImpact) {
                $impact['tableName'] = $instanceImpact->tableName;
                $impact['columnName'] = $instanceImpact->columnName;
                if ($crInput->changeType != 'delete') {
                    $impact['records']['newValue'][] = $instanceImpact->newValue;
                }
                $record = [];
                foreach (OldInstance::where('instanceImpactId', $instanceImpact->id)->get() as $oldInstance) {
                    $record[$oldInstance->columnName] = $oldInstance->value;
                }
                $impact['records']['old'][] = $record;
            }
            $instanceResult[] = $impact;
        }
        return $instanceResult;
    }

    private function getFrImpact(): array
    {
        $frResult = [];
       
        foreach (FrImpact::where('changeRequestId', $this->changeRequest->id)->get() as $frImpact) {
            $impact = [
                'functionalRequirementNo' => $frImpact->no,
                'inputs' => []
            ];
            
            $frInputMem = [];
            foreach (FrInputImact::where('frImpactId', $frImpact->id)->get() as $frInputImpact) {
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
                $frInputMem[$frInputImpact->name][$frInputImpact->versionType] = $arrFrImpact;
            }

            foreach($frInputMem as $input) {
                $impact['inputs'][] = $input;
            }
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
