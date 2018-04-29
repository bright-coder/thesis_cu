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
            case 'char':
            case 'varchar':
            case 'nchar':
            case 'nvarchar':
                return true;
        
            default:
                return false;
        }
    }

    public static function isNumericType(string $dataType) : bool
    {
        switch (\strtolower($dataType)) {
            case 'int':
            case 'float':
            case 'decimal':
                return true;

            default:
                return false;
        }
    }

    public static function isFloatType(string $dataType) : bool
    {
        switch (\strtolower($dataType)) {
            case 'float':
            case 'decimal':
                return true;
            
            default:
                return false;
        }
    }

}