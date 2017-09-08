<?php

class Column{
    
    private $name,$datatype,$isNull,$defaultValue,$numRow;
    private $constraint = ['Unique' => NULL,'Check' => NULL
        ,'isFK' => FALSE ,'isPK' => FALSE ];

    public function construct(SchemaDB $info){

    }

    public function getName(){
        return $this->name;
    }

    public function get


}