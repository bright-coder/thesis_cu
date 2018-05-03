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


        return view('test', ['constraintInTable' => '555']);
    }
}
