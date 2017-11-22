<?php

namespace App\Library\Datatype;

use App\Library\Datatype\DataType;
use App\Library\Datatype\DataTypeInterface;

final class DataTypeFactory
{

    public function create(string $type = "", array $detail = ['length' => 0, 'precision' => 1, 'scale' => 0]): DataTypeInterface
    {
        switch ($type) {
            case DataType::CHAR:
            case DataType::VARCHAR:
                return new Char($type, $detail['length']);
                break;
            case DataType::NCHAR:
            case DataType::NVARCHAR:
                return new Nchar($type, $detail['length']);
                break;
            case DataType::FLOAT:
                return new _Float($detail['precision']);
                break;
            case DataType::DECIMAL:
                return new Decimal($detail['precision'], $detail['scale']);
                break;
            case DataType::INT:
            case DataType::DATE:
            case DataType::DATETIME:
            default:
                return new _Default($type);
                break;
        }

    }

}
