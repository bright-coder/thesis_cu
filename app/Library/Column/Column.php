<?php

class Column{
    
    private $name,$datatype,$isNull,$defaultValue,$numRow;
    private $constraint = ['PK' => NULL,'FK' => NULL,'Unique' => NULL];

    public function construct(SchemaDB $info){

    }


}