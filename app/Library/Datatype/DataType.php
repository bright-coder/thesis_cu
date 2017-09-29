<?php

namespace App\Library\Datatype;

class DataType{
    private $datatype;

    public function __construct(string $type = "", array $detail = ['length' => 0, 'precision' => 1, 'scale' => 0]){
        switch ($type) {
            case 'char':
            case 'varchar':
                $this->datatype = new Char($type,$detail['length']);
                break;
            case 'nchar':
            case 'nvarchar':
                $this->datatype = new Nchar($type,$detail['length']);
                break;
            case 'float':
                $this->datatype = new _Float($detail['precision']);
                break;
            case 'decimal':
                $this->datatype = new Decimal($detail['precision'],$detail['scale']);
                break;
            case 'int':
            case 'date':
            case 'datetime':
            default:
                $this->datatype = new _Default($type);
                break;
        }

    }

    public function getDatatype(){
        return $this->datatype;
    }
}