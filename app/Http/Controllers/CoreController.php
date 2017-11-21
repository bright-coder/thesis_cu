<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Library\CustomModel\ModelFactory;

use App\Library\DatabaseBuilder\DatabaseBuilder;

use App\Library\Random\RandomContext;
use Faker;

class CoreController extends Controller
{
    
    public function index(){

        //$dbBuilder = new DatabaseBuilder(ModelFactory::create('sqlsrv','DESKTOP-NRK0H8C','customer','thesis','1234'));
        //$dbBuilder->setTable();
        //$model = ModelFactory::create('sqlsrv','DESKTOP-NRK0H8C','customer','thesis','1234');
        //return view('test',['constraintInTable' => $model->getAllConstraintsByTableName('profile')]);
       // return view('test',['constraintInTable' => $model->getNumDistinctValue('profile','id')]);
       $r = new RandomContext('float');
       $r->random(10,['min'=>1,'max'=>1000,'precision'=>4],true);
       //$faker = Faker\Factory::create();
       $random;
       try {
           $random = $r->getRandomData();
       } catch (Exception $e){
            $random = $e;
       }
       return view('test',['constraintInTable' =>$random]);
    }
}
