<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Library\CustomModel\ModelFactory;

class CoreController extends Controller
{
    
    public function index(){

        $model = ModelFactory::create('sqlsrv','DESKTOP-NRK0H8C','customer','thesis','1234');
        $tables = $model->getAllTables();
        $pkColumns = [];
         foreach ($tables as $table) {
            $pkColumns[$table] = $model->getPkColumns($table);
         }

        return view('test',['tables' => $pkColumns]);
    }
}
