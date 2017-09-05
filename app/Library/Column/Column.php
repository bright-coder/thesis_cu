<?php

class Column{
    
    private $name,$datatype,$isNull,$defaultValue,$numRow;
    private $constraint = ['FK' => NULL,'Unique' => NULL,'Check' => NULL];

    public function construct(SchemaDB $info){

    }


}