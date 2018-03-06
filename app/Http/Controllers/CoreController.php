<?php

namespace App\Http\Controllers;

use App\Library\Datatype\DatatypeFactory;
use App\Library\Builder\DatabaseBuilder;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\FunctionalRequirement\FR;
use App\Library\FunctionalRequirement\FRInput;
use App\Library\TestCase\TestCase;
use App\Library\TestCase\TestCaseInput;
use App\Library\ChangeRequest\ChangeRequest;
use App\Library\ChangeRequest\ChangeInputInfo;
use App\Library\Random\RandomContext;

class CoreController extends Controller
{

    public function index()
    {

        
        $dbCon = DBTargetConnection::getInstance('sqlsrv','DESKTOP-NRK0H8C','Company','thesis','1234');
        if( !$dbCon->Connect()) {
            $msg = "Cannot Connect to Target Database";
        }
        else {
            $databaseBuilder = new DatabaseBuilder($dbCon);
            $databaseBuilder->setUpTablesAndColumns();
        }
        $dbTarget = $databaseBuilder->getDatabase();

        return view('test', ['constraintInTable' => $databaseBuilder->getDatabase()]);
    }
}
