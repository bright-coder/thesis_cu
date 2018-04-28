<?php

use App\Library\State\AnalyzeDBMethod\TypeEditAnalysis\ColumnAnalysis;

class ColumnNormalAnalysis extends ColumnAnalysis
{
    public function analyzeColumn() {
        if ($this->changeRequestInput->dataType != null) {
            if($this->findInstanceImpactByDataType($this->changeRequestInput->dataType, $column->getDataType()->getType()) ) {
                $this->instanceImpactResult[] = $this->dbTargetConnection->getInstanceByTableName($this->functinoalRequirementInput->tableName);
                return false;
            }
            
            $dataTypeRef = $this->changeRequestInput->dataType;
        }

        $this->findInstanceImpactByDataTypeDetail($dataTypeRef);
        
        if ($this->changeRequestInput->nullable != null) {
            $this->findInstanceImpactByNullable(\strtoupper($this->changeRequestInput->nullable) == 'N' ? false : true, $column->isNullable());
        }
        
        if ($this->changeRequestInput->unqiue != null) {
            $this->findInstanceImpactByUnique($this->isUnique(), $this->isDBColumnUnique($table->getName(), $column->getName()));
        }
    }

}