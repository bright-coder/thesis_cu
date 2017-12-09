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

class CoreController extends Controller
{

    public function index()
    {

        
        $dbCon = DBTargetConnection::getInstance('sqlsrv','DESKTOP-NRK0H8C','customer','thesis','1234');

        $fr = new FR();
        $fr->setNo("FR-01");
        $fr->setDescription("Test FR");
        $fr->setVersion("1");
        
        $frInput = new FRInput();
        $frInput->setId("1");
        $frInput->setName("firstName");
        $frInput->setDataType(DataTypeFactory::create('varchar',['length' => 50]));
        $frInput->setNullable(true);
        $frInput->setUnique(false);
        $frInput->setTable("test");
        $frInput->setColumn("testChar");

        $tc = new TestCase();
        $tc->setNo("TC-01");
        $tc->setVersion(1);
        $tc->setType("valid");
        $tc->setDescription("test TC");
        
        $tcInput = new TestCaseInput();
        $tcInput->setId(1);
        $tcInput->setName("firstName");
        $tcInput->setValue("Kritsada");

        $tc->addTestCaseInput($tcInput);

        $rtm = ['FR-01' => ['TC-01'=> true]];

        $cr = new ChangeRequest();
        $cr->setFrNo($fr->getNo());
        $cr->setFrVersion($fr->getVersion());

        $crInput = new ChangeInputInfo();
        $crInput->setInputName("firstName");
        $crInput->setChangeType("edit");
        $crInput->setChangeInfo(['dataLength' => 40]);
        $crInput->setModifyFlag(true);

        $cr->addChangeInputInfo($crInput);

        if( !$dbCon->Connect()) {
            $msg = "Cannot Connect to Target Database";
        }
        else {
            $databaseBuilder = new DatabaseBuilder($dbCon);
            $databaseBuilder->setUpTablesAndColumns();
        }
        $dbTarget = $databaseBuilder->getDatabase();


        foreach ($cr->getAllChangeInputInfo as $changeInputInfo) {
            $inputName = $changeInputInfo->getInputName();
            $frInput = $fr->getInputByName($inputName);
            
            if($changeInputInfo->getChangeInfo()['dataLength'] < $frInput->getDataType()->getDetails()['length']) {
                
            }
            
        }
        $column = $dbTarget->getTableByName($frInput->getTable())->getColumnByName($frInput->getColumn());


        
            
        
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
        return view('test', ['constraintInTable' => $cr]);
    }
}
