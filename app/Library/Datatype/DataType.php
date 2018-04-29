<?php

namespace App\Library\Datatype;

final class DataType {

    const CHAR = 'char';
    const VARCHAR = 'varchar';
    const NCHAR = 'nchar';
    const NVARCHAR = 'nvarchar';
    const INTEGER = 'int';
    const FLOAT = 'float';
    const DECIMAL = 'decimal';
    const DATE = 'date';
    const DATETIME = 'datetime';

    public static function isStringType(string $dataType) : bool
    {
        
        switch (\strtolower($dataType)) {
            case DataType::CHAR :
            case DataType::NCHAR :
            case DataType::VARCHAR :
            case DataType::NVARCHAR :
                return true;
        
            default:
                return false;
        }
    }

    public static function isNumericType(string $dataType) : bool
    {
        switch (\strtolower($dataType)) {
            case DataType::INTEGER :
            case DataType::FLOAT :
            case DataType::DECIMAL :
                return true;

            default:
                return false;
        }
    }

    public static function isFloatType(string $dataType) : bool
    {
        switch (\strtolower($dataType)) {
            case DataType::FLOAT :
            case DataType::DECIMAL :
                return true;
            
            default:
                return false;
        }
    }

}