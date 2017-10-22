<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Library\CustomModel\ModelFactory;

use App\Library\DatabaseBuilder\DatabaseBuilder;

class CoreController extends Controller
{
    
    public function index(){

        //$dbBuilder = new DatabaseBuilder(ModelFactory::create('sqlsrv','DESKTOP-NRK0H8C','customer','thesis','1234'));
        //$dbBuilder->setTable();
        $model = ModelFactory::create('sqlsrv','DESKTOP-NRK0H8C','customer','thesis','1234');
        return view('test',['constraintInTable' => $model->getConstraintsByTableName('profile')]);
    }
}
