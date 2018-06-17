<?php

namespace App\Library\State;

use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactTCState;
use App\Model\ChangeRequestInput;
use App\Model\ChangeRequest;
use App\Model\FunctionalRequirement;
use App\Model\FunctionalRequirementInput;
use App\Library\ChangeAnalysis;

class AnalyzeImpactFRState implements StateInterface
{
    public function getStateName(): string
    {
        return 'AnalyzeImpactFRState';
    }

    public function analyze(ChangeAnalysis $changeAnalysis): void
    {
        
        $result = [];
        foreach ($changeAnalysis->getSchemaImpactResult() as $tableName => $columnList) {
            foreach ($columnList as $columnName => $info) {
                if (!isset($result[$tableName])) {
                    $result[$tableName] = [];
                }
                $result[$tableName][$columnName] = $info['changeType'];
            }
        }

        $frResult = [];
        foreach ($changeAnalysis->getAllChangeRequestInput() as $crInput) {
            if ($crInput->changeType == 'add') {
                if (!isset($result[$crInput->tableName])) {
                    $result[$crInput->tableName] = [];
                }
                $result[$crInput->tableName][$crInput->columnName] = $info['changeType'];
            } elseif ($crInput->changeType == 'delete') {
                $frInput = FunctionalRequirementInput::where('id', $crInput->frInputId)->first();
                if (!isset($result[$frInput->tableName])) {
                    if (!isset($frResult[$frNo])) {
                        $frResult[$frNo] = [];
                    }
                    $frResult[$frNo][$frInputName] = [
                        'tableName' => $tableName,
                        'columnName' => $columnName,
                        'changeType' => 'delete'
                    ];
                } else {
                    if (!isset($result[$frInput->tableName][$frInput->columnName])) {
                        if (!isset($frResult[$frNo])) {
                            $frResult[$frNo] = [];
                        }
                        $frResult[$frNo][$frInputName] = [
                            'tableName' => $tableName,
                            'columnName' => $columnName,
                            'changeType' => 'delete'
                        ];
                    }
                }
            }
        }
        
        foreach ($result as $tableName => $columnList) {
            foreach ($columnList as $columnName => $changeType) {
                if ($changeType == 'add') {
                    $frNo = FunctionalRequirement::where('id', $changeAnalysis->getChangeFrId())->first()->no;
                    $frInputName = ChangeRequestInput::where([
                        ['crId', $changeAnalysis->getChangeRequest()->id],
                        ['tableName', $tableName],
                        ['columnName', $columnName],
                        ['changeType', 'add']

                    ])->first()->name;
                    
                    if (!isset($frResult[$frNo])) {
                        $frResult[$frNo] = [];
                    }
                    $frResult[$frNo][$frInputName] = [
                        'tableName' => $tableName,
                        'columnName' => $columnName,
                        'changeType' => $changeType
                    ];
                } else {
                    $frList = FunctionalRequirement::where('projectId', $changeAnalysis->getProjectId())->get();
                    foreach($frList as $fr) {
                        $frInputList = FunctionalRequirementInput::where([
                            ['frId', $fr->id],
                            ['tableName', $tableName],
                            ['columnName', $columnName]
                        ])->get();
                        
                        $frNo = $fr->no;
                        foreach ($frInputList as $frInput) {
                            if (!isset($frResult[$frNo])) {
                                $frResult[$frNo] = [];
                            }
                            $frResult[$frNo][$frInput->name] = [
                                'tableName' => $tableName,
                                'columnName' => $columnName,
                                'changeType' => $changeType
                            ];
                        }
                    }
                    
                }
            }
        }

        $changeAnalysis->addFRImpactResult($frResult);
    
        foreach($frResult as $frNo => $frInputList) {

            $frId = FunctionalRequirement::where([
                ['projectId', $changeAnalysis->getProjectId()],
                ['no', $frNo]
            ])->first()->id;

            foreach($frInputList as $name => $info) {
                if($info['changeType'] == 'add') {
                    $frInput = new FunctionalRequirementInput;
                    $frInput->name = $name;
                    $frInput->frId = $frId;
                    $frInput->tableName = $info['tableName'];
                    $frInput->columnName = $info['columnName'];
                    $frInput->save();
                }
                else if($info['changeType'] == 'delete') {
                    FunctionalRequirementInput::where([
                        ['frId', $frId],
                        ['name', $name]
                    ])->delete();
                }
            }
        }
        //dd($changeAnalysis->getFrImpactResult());
        $changeAnalysis->setState(new AnalyzeImpactTCState);
        $changeAnalysis->analyze();
    }

}
