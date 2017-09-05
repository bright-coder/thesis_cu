<?php

class Column{
    
    private $name,$datatype,$isNull,$defaultValue,$numRow;
    private $constraint = ['Unique' => NULL,'Check' => NULL];

    public function construct(SchemaDB $info){

    }


}