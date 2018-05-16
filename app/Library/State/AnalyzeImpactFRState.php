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
    private $frImpactResult = [];

    public function getStateName(): string
    {
        return 'AnalyzeImpactFRState';
    }

    private function addFrImpactResult(string $frId, string $changeType, array $frInput) : void
    {
        $frInput['changeType'] = $changeType;
        $frNo = FunctionalRequirement::where('id', $frId)->first()->no;
        if (\array_key_exists($frId, $this->frImpactResult)) {
            $this->frImpactResult[$frId]['inputList'][] = $frInput;
        } else {
            $this->frImpactResult[$frId] = [
                'id' => $frId,
                'no' => $frNo,
                'inputList' => [
                    $frInput
                ],
            ];
        }
    }

    private function getFunctionalRequirementInput(string $projectId, string $tableName, string $columnName)
    {
        $frIdList = FunctionalRequirement::select('id')->where('projectId', $projectId)->get();
        
        $arrayFrId = [];
        foreach ($frIdList as $fr) {
            $arrayFrId[] = $fr->id;
        }
        return FunctionalRequirementInput::where([
            ['columnName', $columnName],
            ['tableName', $tableName],
            ['activeFlag', 'Y']
            ])
            ->whereIN('functionalRequirementId', $arrayFrId)
            ->get();
    }

    public function analyze(ChangeAnalysis $changeAnalysis): void
    {
        foreach ($changeAnalysis->getAllChangeRequestInput() as $changeRequestInput) {
            if ($changeRequestInput->changeType == 'add') {
                // add New input
                $frInput = [
                    'new' => null,
                    'old' => null,
                ];
                if ($changeRequestInput->functionalRequirementInputId != null) {
                    $frInput['new'] = FunctionalRequirementInput::where('id', $changeRequestInput->functionalRequirementInputId)
                    ->first()->toArray();
                }
                // add already exist input int database
                else {
                    $frInput['new'] = $changeRequestInput->toArray();
                }
                $this->addFrImpactResult(
                    $changeAnalysis->getChangeFunctionalRequirementId(),
                    'add',
                    $frInput
                );
            } elseif ($changeRequestInput->changeType == 'edit') {
                $schemaImpactResult = $changeAnalysis->getDBImpactResult()[$changeRequestInput->id]['schema'];

                foreach ($schemaImpactResult as $schema) {
                    $frInputList = $this->getFunctionalRequirementInput(
                        $changeAnalysis->getProjectId(),
                        $schema['tableName'],
                        $schema['columnName']
                    );
                    
                    foreach ($frInputList as $oldFrInput) {
                        $frInput =[
                            'new' => $schema['newSchema'],
                            'old' => $oldFrInput->toArray()
                        ];
                        $this->addFrImpactResult(
                            $oldFrInput->functionalRequirementId,
                            'edit',
                            $frInput
                        );
                    }
                }
            } elseif ($changeRequestInput->changeType == 'delete') {
                $schemaImpactResult = $changeAnalysis->getDBImpactResult()[$changeRequestInput->id]['schema'];
                
                foreach ($schemaImpactResult as $schema) {
                    $frInputList = $this->getFunctionalRequirementInput(
                        $changeAnalysis->getProjectId(),
                        $schema['tableName'],
                        $schema['columnName']
                    );
                    
                    foreach ($frInputList as $oldFrInput) {
                        $frInput =[
                            'new' => null,
                            'old' => $oldFrInput->toArray()
                        ];
                        $this->addFrImpactResult(
                            $oldFrInput->functionalRequirementId,
                            'delete',
                            $frInput
                        );
                        
                    }
                }
            }
        }
       // $this->modify();
        $changeAnalysis->setFRImpactResult($this->frImpactResult);
       // dd($changeAnalysis->getFrImpactResult());
        $changeAnalysis->setState(new AnalyzeImpactTCState);
        $changeAnalysis->analyze();
    }

    private function modify(): void
    {
        foreach($this->frImpactResult as $frImpact) {
            foreach ($frImpact['inputList'] as $frInput) {
                if($frInput['changeType'] == 'add') {
                    $frNew = new FunctionalRequirementInput;
                    $frNew->functionalRequirementId = $frImpact['id'];
                    $frNew->name = $frInput['new']['name'];
                    $frNew->dataType = $frInput['new']['dataType'];
                    $frNew->length = $frInput['new']['length'];
                    $frNew->precision = $frInput['new']['precision'];
                    $frNew->scale = $frInput['new']['scale'];
                    $frNew->default = $frInput['new']['default'];
                    $frNew->nullable = $frInput['new']['nullable'];
                    $frNew->unique = $frInput['new']['unique'];
                    $frNew->tableName = $frInput['new']['tableName'];
                    $frNew->columnName = $frInput['new']['columnName'];
                    $frNew->activeFlag = 'Y';
                    $frNew->save();
                }
                elseif($frInput['changeType'] == 'edit') {
                    $oldFrInput = FunctionalRequirementInput::find($frInput['old']['id']);
                    $oldFrInput->activeFlag = 'N';
                    $oldFrInput->save();

                    $frNew = new FunctionalRequirementInput;
                    $frNew->functionalRequirementId = $frImpact['id'];
                    $frNew->name = $frInput['old']['name'];
                    $frNew->dataType = \array_key_exists('dataType',$frInput['new']) ? $frInput['new']['dataType'] : $frInput['old']['dataType'];
                    $frNew->length =  \array_key_exists('length',$frInput['new']) ? $frInput['new']['length'] : $frInput['old']['length'];
                    $frNew->precision = \array_key_exists('precision',$frInput['new']) ? $frInput['new']['precision'] : $frInput['old']['precision'];
                    $frNew->scale = \array_key_exists('scale',$frInput['new']) ? $frInput['new']['scale'] : $frInput['old']['scale'];
                    $frNew->default = \array_key_exists('default',$frInput['new']) ? $frInput['new']['default'] : $frInput['old']['default'];
                    $frNew->nullable = \array_key_exists('nullable',$frInput['new']) ? $frInput['new']['nullable'] : $frInput['old']['nullable'];
                    $frNew->unique = \array_key_exists('unique',$frInput['new']) ? $frInput['new']['unique'] : $frInput['old']['unique'];
                    $frNew->tableName = $frInput['old']['tableName'];
                    $frNew->columnName = $frInput['old']['columnName'];
                    $frNew->activeFlag = 'Y';
                    $frNew->save();
                }
                elseif($frInput['changeType'] == 'delete') {
                    $oldFrInput = FunctionalRequirementInput::find($frInput['old']['id']);
                    $oldFrInput->activeFlag = 'N';
                    $oldFrInput->save();
                }   
            }
        }

    }

}
