<?php

namespace App\Http\Controllers;

use App\Library\Random\RandomContext;
use App\Library\CustomModel\ModelFactory;

class CoreController extends Controller
{

    public function index()
    {

        //$dbBuilder = new DatabaseBuilder(ModelFactory::create('sqlsrv','DESKTOP-NRK0H8C','customer','thesis','1234'));
        //$dbBuilder->setTable();
        $model = ModelFactory::create('sqlsrv','DESKTOP-NRK0H8C','customer','thesis','1234');
        //return view('test',['constraintInTable' => $model->getAllConstraintsByTableName('profile')]);
        // return view('test',['constraintInTable' => $model->getNumDistinctValue('profile','id')]);
        // $r = new RandomContext('datetime');
        // $r->random(10, ['min' => 99, 'max' => 1000, 'precision' => 4, 'scale' => 2], true);
        // //$faker = Faker\Factory::create();
        // $random;
        // try {
        //     $random = $r->getRandomData();
        // } catch (Exception $e) {
        //     $random = $e;
        // }
        return view('test', ['constraintInTable' => $model->getAllTables()]);
    }
}
