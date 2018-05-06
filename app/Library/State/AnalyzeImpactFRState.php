<?php

namespace App\Library\State;

use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactTCState;
use App\Model\ChangeRequestInput;
use App\Model\ChangeRequest;
use App\Model\FunctionalRequirementInput;

class AnalyzeImpactFRState implements StateInterface
{
    public function nextState()
    {
        return new AnalyzeImpactTCState();
    }

    public function getStateName(): String
    {
        return 'AnalyzeImpactFRState';
    }

    public function analyze(ChangeRequestInput $changeRequestInput): array
    {
        $schemaImpactResult = DB::table('SCHEMA_IMPACT')::
            where('changeRequestInputId', $changeRequestInput)->get();
        
        $frImpactResult = [];
        
        $changeType = $changeRequestInput->changeType;
        if ($changeType == 'add') {
            $frInput = new FunctionalRequirementInput;
            $frId = ChangeRequest::select('changeFunctionalRequirementId')
            ->where('id', $changeRequestInput->changeRequestId)->first()
            ->changeFunctionalRequirementId;

            if ($schemaImpactResult) {
                $frInput->functionalRequirementId = $frId;
                $frInput->name = $changeRequestInput->name;
                $frInput->tableName = $schemaImpactResult[0]->tableName;
                $frInput->columnName = $schemaImpactResult[0]->columnName;
                $frInput->length = $schemaImpactResult[0]->length;
                $frInput->precision = $schemaImpactResult[0]->precision;
                $frInput->scale = $schemaImpactResult[0]->scale;
                $frInput->default = $schemaImpactResult[0]->default;
                $frInput->nullable = $schemaImpactResult[0]->nullable;
                $frInput->unique = $schemaImpactResult[0]->unique;
                $frInput->max = $schemaImpactResult[0]->max;
                $frInput->min = $schemaImpactResult[0]->min;
                $frInput->activeFlag = 'Y';
                $frInput->save();
            } else {
                $frInput->functionalRequirementId = $frId;
                $frInput->name = $changeRequestInput->name;
                $frInput->tableName = $changeRequestInput->tableName;
                $frInput->columnName = $changeRequestInput->columnName;
                $frInput->length = $changeRequestInput->length;
                $frInput->precision = $changeRequestInput->precision;
                $frInput->scale = $changeRequestInput->scale;
                $frInput->default = $changeRequestInput->default;
                $frInput->nullable = $changeRequestInput->nullable;
                $frInput->unique = $changeRequestInput->unique;
                $frInput->max = $changeRequestInput->max;
                $frInput->min = $changeRequestInput->min;
                $frInput->activeFlag = 'Y';
                $frInput->save();
            }
            $frInputImpactResult[] = [
                'oldFunctionalRequirementInputId' => null,
                'newFunctionalRequirementInputId' => $frInput->id
            ];

        } elseif ($changeType == 'edit') {
            if ($schemaImpactResult) {
                foreach ($schemaImpactResult as $schema) {
                    $oldFrInputs = FunctionalRequriementInput::where([
                        ['tableName' => $schema['tableName']],
                        ['columnName' => $schema['columnName']],
                        ['activeFlag' => 'Y']
                    ])->get();
                    foreach ($oldFrInputs as $oldFrInput) {
                        $oldFrInput->activeFlag = 'N';
                        $oldFrInput->save();
                        $frInput = new FunctionalRequirementInput;
                        $frInput->functionalRequirementId = $oldFrInput->functionalRequirementId;
                        $frInput->tableName = $oldFrInput->tableName;
                        $frInput->columnName = $oldFrInput->columnName;
                        $frInput->length = $schema['newLength'];
                        $frInput->precision = $schema['newprecision'];
                        $frInput->scale = $schema['newScale'];
                        $frInput->default = $schema['newDefault'];
                        $frInput->nullable = $schema['newNullable'];
                        $frInput->unique = $schema['newUnique'];
                        $frInput->min = $schema['newMin'];
                        $frInput->max = $schema['newMax'];
                        $frInput->activeFlag = 'Y';
                        $frInput->save();
                        
                        $frInputImpactResult[] = [
                            'oldFunctionalRequirementInputId' => $oldFrInput->id,
                            'newFunctionalRequirementInputId' => $frInput->id
                        ];

                    }
                }
            }
        } elseif ($changeType == 'delete') {
            if ($schemaImpactResult) {
                foreach ($schemaImpactResult as $schema) {
                    $oldFrInputs = FunctionalRequriementInput::where([
                        ['tableName' => $schema['tableName']],
                        ['columnName' => $schema['columnName']],
                        ['activeFlag' => 'Y']
                    ])->get();
                    foreach ($oldFrInputs as $oldFrInput) {
                        $oldFrInput->activeFlag = 'N';
                        $oldFrInput->save();
                    }
                }
            } else {
                $oldFrInput = FunctionalRequriementInput::find($changeRequestInput->functionalRequirementInputId);
                $oldFrInput->activeFlag = "N";
                $oldFrInput->save();

                $frInputImpactResult[] = [
                    'oldFunctionalRequirementInputId' => $oldFrInput->id,
                    'newFunctionalRequirementInputId' => null
                ];
            }
        }
        return $frInputImpactResult;
    }
    
}
