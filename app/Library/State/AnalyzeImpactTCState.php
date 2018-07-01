<?php

namespace App\Library\State;

use App\Library\State\StateInterface;
use App\Library\State\AnalyzeImpactRTMState;
use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Model\TestCase;
use App\Model\TestCaseInput;
use App\Model\RequirementTraceabilityMatrix;
use App\Model\FunctionalRequirement;
use App\Model\FunctionalRequirementInput;
use App\Model\Project;
use DB;
use App\Library\ChangeAnalysis;
use App\Library\Builder\DatabaseBuilder;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\CustomModel\DBTargetInterface;
use App\Library\Database\Database;

class AnalyzeImpactTCState implements StateInterface
{
    private $tcImpactResult = [];
    
    /**
     * Undocumented variable
     *
     * @var DBTargetInterface
     */
    private $dbTargetConnection = null;
    /**
     * Undocumented variable
     *
     * @var Database
     */
    private $dbTarget = null;

    public function getStateName(): String
    {
        return 'AnalyzeImpactTCState';
    }

    public function analyze(ChangeAnalysis $changeAnalysis): void
    {
        $projectId = $changeAnalysis->getProjectId();
        $this->connectTargetDB($projectId);
        $this->getDbSchema();

        $tcNoList = [];
        $tcAll = TestCase::where('projectId', $projectId)->get();

        $tcResult = [];
        foreach ($changeAnalysis->getFrImpactResult() as $frNo => $frInputList) {
            $frId = FunctionalRequirement::where([
                ['projectId', $changeAnalysis->getProjectId()],
                ['no', $frNo]
            ])->first()->id;
            
            $isDelete = false;
            foreach ($frInputList as $name => $info) {
                if ($info['changeType'] == 'add' || $info['changeType'] == 'delete') {
                    $isDelete = true;
                    break;
                }
            }

            $tcList = RequirementTraceabilityMatrix::where('frId', $frId)->get();
            foreach ($tcList as $tc) {
                $tcNo = TestCase::where('id', $tc->tcId)->first()->no;
                if (!isset($tcResult[$tcNo])) {
                    $tcResult[$tcNo] = ['changeType' => $isDelete ? 'delete' : 'edit', 'tcInputList' => [] ,'frId' => $frId, 'tcId' => $tc->tcId] ;
                    foreach (TestCaseInput::where('tcId', $tc->tcId)->get() as $tcInput) {
                        $tcResult[$tcNo]['tcInputList'][$tcInput->name] = ['old' => $tcInput->testData, 'new' => null];
                    }
                }
            }
        }

        $tcNewResult = [];
        $frImpact = $changeAnalysis->getFrImpactResult();
        foreach ($tcResult as $tcNo => $tcInfo) {
            if ($tcInfo['changeType'] == 'delete') {
                $last = TestCase::where('projectId', $projectId)->orderBy('id', 'desc')->first();
                $last = intval(explode("-", $last)[2]);
                $tcOld = TestCase::where([
                    ['projectId', $projectId],
                    ['no', $tcNo]
                ])->first();
                $prefix = Project::where('id', $projectId)->first()->prefix;
                $tcNew = new TestCase;
                $tcNew->projectId = $projectId;
                $tcNew->no = $prefix."-TC-".($last+1);
                $tcNew->type = $tcOld->type;
                $tcNew->save();
                $tcNewResult[$tcNew->no] = ['changeType' => 'add', 'tcInputList' => [] ,'frId' => $tcInfo['frId'], 'tcId' => $tcNew->id];
                $frNo = FunctionalRequirement::where('id', $tcInfo['frId'])->first()->no;
                $frInputList = FunctionalRequirementInput::where('frId', $tcInfo['frId'])->get();
                
                foreach ($frInputList as $frInput) {
                    $tcInputOld = TestCaseInput::where([
                        ['tcId', $tcOld->id],
                        ['name', $frInput->name]
                    ])->first();
                    if ($tcInputOld) {
                        $testData = $tcInputOld->testData;
                        $tcInput = new TestCaseInput;
                        $tcInput->tcId = $tcNew->id;
                        $tcInput->name = $frInput->name;
                        $testData = TestCaseInput::where([
                        ['tcId', $tcOld->id],
                        ['name', $frInput->name]
                            ])->first()->testData;
                        if ($this->isCanUse($frInput->frId, $frInput->name, $testData)) {
                            $isAdd = false;
                            if (isset($changeAnalysis->getInstanceImpactResult()[$frInput->tableName])) {
                                
                                foreach ($changeAnalysis->getInstanceImpactResult()[$frInput->tableName] as $row) {
                                    if (isset($row['columnList'][$frInput->columnName])) {
                                        $info = $row['columnList'][$frInput->columnName];
                                        if ($info['changeType'] == 'edit') {
                                            if ($info['oldValue'] == $testData) {
                                                //     $tcInput = new TestCaseInput;
                                                // $tcInput->tcId = $tcNew->id;
                                                // $tcInput->name = $name;
                                                $tcInput->testData = $info['newValue'] ;
                                                $tcInput->save();
                                                $tcNewResult[$tcNew->no]['tcInputList'][$tcInput->name] = ['old' => null, 'new' => $tcInput->testData];
                                                $isAdd = true;
                                                break;
                                            }
                                        }
                                    }
                                    if ($isAdd) {
                                        break;
                                    }
                                }
                            }

                            if ($isAdd == false) {
                                $tcInput->testData = $testData;
                                $tcNewResult[$tcNew->no]['tcInputList'][$frInput->name] = ['old' => null, 'new' => $testData];
                                $tcInput->save();
                                DB::unprepared("update TEST_CASE_INPUT set testData = N'{$testData}' WHERE id = '$tcInput->id'");
                            }
                        } else {
                            if (isset($changeAnalysis->getInstanceImpactResult()[$frInput->tableName])) {
                                $isAdd = false;
                                foreach ($changeAnalysis->getInstanceImpactResult()[$frInput->tableName] as $row) {
                                    if (isset($row['columnList'][$frInput->columnName])) {
                                        $info = $row['columnList'][$frInput->columnName];
                                        if ($info['changeType'] == 'edit') {
                                            if ($info['oldValue'] == $testData) {
                                                //     $tcInput = new TestCaseInput;
                                                // $tcInput->tcId = $tcNew->id;
                                                // $tcInput->name = $name;
                                                $tcInput->testData = $info['newValue'] ;
                                                $tcInput->save();
                                                DB::unprepared("update TEST_CASE_INPUT set testData = N'{$info['newValue']}' WHERE id = '$tcInput->id'");
                                                $tcNewResult[$tcNew->no]['tcInputList'][$tcInput->name] = ['old' => null, 'new' => $tcInput->testData];
                                                $isAdd = true;
                                                break;
                                            }
                                        }
                                    }
                                    if ($isAdd) {
                                        break;
                                    }
                                }
                                if ($isAdd == false) {
                                    $newData = $this->dbTargetConnection->getInstanceByTableName($frInput->tableName, [$frInput->columnName]);
                                    $total = count($newData);
                                    $pickAt = rand(0, $total-1);
                                    // $tcInput = new TestCaseInput;
                                    // $tcInput->tcId = $tcNew->id;
                                    // $tcInput->name = $name;
                                    $td = trim($newData[$pickAt][$frInput->columnName]);
                                    $tcInput->testData = trim($newData[$pickAt][$frInput->columnName]) ;
                                    $tcInput->save();
                                   DB::unprepared("update TEST_CASE_INPUT set testData = N'{$td}' WHERE id = '$tcInput->id'");
                                    $tcNewResult[$tcNew->no]['tcInputList'][$tcInput->name] = ['old' => null, 'new' => $tcInput->testData];
                                }
                            } else {
                                $newData = $this->dbTargetConnection->getInstanceByTableName($frInput->tableName, [$frInput->columnName]);
                                $total = count($newData);
                                $pickAt = rand(0, $total-1);
                                // $tcInput = new TestCaseInput;
                                // $tcInput->tcId = $tcNew->id;
                                // $tcInput->name = $name;
                                $td = trim($newData[$pickAt][$frInput->columnName]);
                                $tcInput->testData = trim($newData[$pickAt][$frInput->columnName]);
                                $tcInput->save();
                                DB::unprepared("update TEST_CASE_INPUT set testData = N'{$td}' WHERE id = '$tcInput->id'");
                                $tcNewResult[$tcNew->no]['tcInputList'][$tcInput->name] = ['old' => null, 'new' => $tcInput->testData];
                            }
                        }
                    } else {
                        $newData = $this->dbTargetConnection->getInstanceByTableName($frInput->tableName, [$frInput->columnName]);
                        $total = count($newData);
                        $pickAt = rand(0, $total-1);
                        $tcInput = new TestCaseInput;
                        $tcInput->tcId = $tcNew->id;
                        $tcInput->name = $frInput->name;
                        $td = trim($newData[$pickAt][$frInput->columnName]);
                        $tcInput->testData = $newData[$pickAt][$frInput->columnName] ;
                        $tcInput->save();
                        DB::unprepared("update TEST_CASE_INPUT set testData = N'{$td}' WHERE id = '$tcInput->id'");
                        $tcNewResult[$tcNew->no]['tcInputList'][$frInput->name] = ['old' => null, 'new' => $tcInput->testData];
                    }
                    
                    //$tcInput->testData = $testData['old'] ;
                }
                if ($tcOld->type == 'invalid') {
                    $tcInputInvalid = TestCaseInput::where('tcId', $tcNew->id)->orderBy('id', 'asc')->first();
                    $tcInputInvalid->testData = $this->genInvalidTestData($tcInfo['frId'], $tcInputInvalid->name);
                    $tcInputInvalid->save();
                    $tcNewResult[$tcNew->no]['tcInputList'][$tcInputInvalid->name]['new'] = $tcInputInvalid->testData;
                }
                $tcOld->delete();
            } else {
                $tcOld = TestCase::where([
                    ['projectId', $projectId],
                    ['no', $tcNo]
                ])->first();
                foreach ($tcInfo['tcInputList'] as $name => $testData) {
                    if (!$this->isCanUse($tcInfo['frId'], $name, $testData['old'])) {
                        //dd($testData['old']);
                        $frInput = FunctionalRequirementInput::where([
                            ['frId', $tcInfo['frId']],
                            ['name', $name]
                        ])->first();
                        if (isset($changeAnalysis->getInstanceImpactResult()[$frInput->tableName])) {
                            $isEdit = false;
                            foreach ($changeAnalysis->getInstanceImpactResult()[$frInput->tableName] as $row) {
                                if (isset($row['columnList'][$frInput->columnName])) {
                                    $info = $row['columnList'][$frInput->columnName];
                                    if ($info['changeType'] == 'edit') {
                                        $tcInput = TestCaseInput::where([
                                            ['tcId', $tcOld->id],
                                            ['name', $name]
                                        ])->first();
                                        if ($info['oldValue'] == $tcInput->testData) {
                                            $tcInput->testData = $info['newValue'];
                                            $tcInput->save();
                                           DB::unprepared("update TEST_CASE_INPUT set testData = N'{$info['newValue']}' WHERE id = '$tcInput->id'");
                                            $tcResult[$tcNo]['tcInputList'][$name]['new'] = $info['newValue'];
                                            $isEdit = true;
                                        }
                                    }
                                }
                                if ($isEdit == false) {
                                    $newData = $this->dbTargetConnection->getInstanceByTableName($frInput->tableName, [$frInput->columnName]);
                                    $total = count($newData);
                                    $pickAt = rand(0, $total-1);
                                    $tcInput = TestCaseInput::where([
                                ['tcId', $tcOld->id],
                                ['name', $name]
                            ])->first();
                                    $td = trim($newData[$pickAt][$frInput->columnName]);
                                    $tcInput->testData = trim($newData[$pickAt][$frInput->columnName]);
                                    $tcInput->save();
                                   DB::unprepared("update TEST_CASE_INPUT set testData = N'{$td}' WHERE id = '$tcInput->id'");
                                    $tcResult[$tcNo]['tcInputList'][$name]['new'] = trim($newData[$pickAt][$frInput->columnName]);
                                }
                            }
                        } else {
                            $newData = $this->dbTargetConnection->getInstanceByTableName($info['tableName'], [$info['columnName']]);
                            $total = count($newData);
                            $pickAt = rand(0, $total-1);
                            $tcInput = TestCaseInput::where([
                                ['tcId', $tcOld->id],
                                ['name', $name]
                            ])->first();
                            $td = trim($newData[$pickAt][$info['columnName']]);
                            $tcInput->testData = trim($newData[$pickAt][$info['columnName']]);
                            $tcInput->save();
                           DB::unprepared("update TEST_CASE_INPUT set testData = N'{$td}' WHERE id = '$tcInput->id'");
                            $tcResult[$tcNo]['tcInputList'][$name]['new'] = trim($newData[$pickAt][$info['columnName']]);
                        }
                    } else {
                        $frInput = FunctionalRequirementInput::where([
                            ['frId', $tcInfo['frId']],
                            ['name', $name]
                        ])->first();
                        if (isset($changeAnalysis->getInstanceImpactResult()[$frInput->tableName])) {
                            foreach ($changeAnalysis->getInstanceImpactResult()[$frInput->tableName] as $row) {
                                if (isset($row['columnList'][$frInput->columnName])) {
                                    $info = $row['columnList'][$frInput->columnName];
                                    if ($info['changeType'] == 'edit') {
                                        $tcInput = TestCaseInput::where([
                                            ['tcId', $tcOld->id],
                                            ['name', $name]
                                        ])->first();
                                        if ($info['oldValue'] == $tcInput->testData) {
                                            $tcInput->testData = $info['newValue'];
                                            $tcInput->save();
                                            DB::unprepared("update TEST_CASE_INPUT set testData = N'{$info['newValue']}' WHERE id = '$tcInput->id'");
                                            $tcResult[$tcNo]['tcInputList'][$name]['new'] = $info['newValue'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ($tcOld->type == 'invalid') {
                    $tcInputInvalid = TestCaseInput::where('tcId', $tcOld->id)->orderBy('id', 'asc')->first();
                    $tcInputInvalid->testData = $this->genInvalidTestData($tcInfo['frId'], $tcInputInvalid->name);
                    $tcInputInvalid->save();
                    $tcResult[$tcNo]['tcInputList'][$tcInputInvalid->name]['new'] = $tcInputInvalid->testData;
                }
            }
        }
        $result = array_merge($tcResult, $tcNewResult);
        foreach ($result as $tcNo => $tcInfo) {
            if ($tcInfo['changeType'] != 'delete') {
                $isDelete = true;
                foreach ($tcInfo['tcInputList'] as $tcName => $data) {
                    if ($data['new'] != null) {
                        $isDelete = false;
                        break;
                    }
                }
                if ($isDelete) {
                    unset($result[$tcNo]);
                }
            }
        }
        $changeAnalysis->addTcImpactResult($result);
        $changeAnalysis->saveTcImpact();
        $changeAnalysis->setState(new AnalyzeImpactRTMState);
        $changeAnalysis->analyze();
    }

    private function genInvalidTestData(int $frId, string $name)
    {
        $frInput = FunctionalRequirementInput::where([
            ['frId', $frId],
            ['name', $name]
        ])->first();
        $table = $this->dbTarget->getTableByName($frInput->tableName);
        $column = $table->getColumnByName($frInput->columnName);

        $length = 10;

        switch ($column->getDataType()->getType()) {
            case 'varchar':
            case 'char':
            case 'nchar':
            case 'nvarchar':
                $length =  $column->getDataType()->getLength()+15 ;
                break;
            case 'int':
            case 'float':
            case 'real':
            case 'decimal':
                $length = 10;
                break;
            case 'date':
            case 'datetime':
                $length = 10;
                break;
            default:
                $length = 10;
                
                break;
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function isCanUse(int $frId, string $name, $testData): bool
    {
        $frInput = FunctionalRequirementInput::where([
            ['frId', $frId],
            ['name', $name]
        ])->first();
        
        $table = $this->dbTarget->getTableByName($frInput->tableName);
        $column = $table->getColumnByName($frInput->columnName);

        switch (strtolower($column->getDataType()->getType())) {
            case 'varchar':
            case 'char':
                if (strlen($testData) != strlen(utf8_decode($testData))) {
                    return false;
                } elseif (strlen($testData) > $column->getDataType()->getLength()) {
                    return false;
                } else {
                    return true;
                }
            
                // no break
                case 'nchar':
                case 'nvarchar':
                if (strlen($testData) > $column->getDataType()->getLength()) {
                    return false;
                } else {
                    return true;
                }
                // no break
            case 'int':
                if (is_numeric($testData)) {
                    if (strpos($testData, '.') !== false) {
                        return false;
                    }
                    $min = $table->getMin($column->getName())['value'];
                    $max = $table->getMax($column->getName())['value'];
                    if ($min != null) {
                        if (intval($testData) < $min) {
                            return false;
                        }
                    }
                    if ($max != null) {
                        if (intval($testData) > $max) {
                            return false;
                        }
                    }
                    return true;
                } else {
                    return false;
                }
                // no break
            case 'float':
            case 'real':
                if (is_numeric($testData)) {
                    $mantissaPrecisionMax = $column->getDataType()->getPrecision();
                    //$maxP = $mantissaPrecision < 24 ? 7 : 15;
                    $mantissaPrecision = strlen(explode('.', $testData)[1]);
                    if ($mantissaPrecision > $mantissaPrecisionMax) {
                        return false;
                    }

                    $min = $table->getMin($column->getName())['value'];
                    $max = $table->getMax($column->getName())['value'];
                    if ($min != null) {
                        if (floatval($testData) < floatval($min)) {
                            return false;
                        }
                    }
                    if ($max != null) {
                        if (floatval($testData) > floatval($max)) {
                            return false;
                        }
                    }
                    return true;
                } else {
                    return false;
                }
                // no break
            case 'decimal':
                if (is_numeric($testData)) {
                    $precisionMax = $column->getDataType()->getPrecision();
                    if (strlen($testData)-1 > $precisionMax) {
                        return false;
                    }
                    $scaleMax = $column->getDataType()->scale();
                    $mantissaPrecision = strlen(explode('.', $testData)[1]);
                    if ($mantissaPrecision > $scaleMax) {
                        return false;
                    }

                    $min = $table->getMin($column->getName())['value'];
                    $max = $table->getMax($column->getName())['value'];
                    if ($min != null) {
                        if (floatval($testData) < floatval($min)) {
                            return false;
                        }
                    }
                    if ($max != null) {
                        if (floatval($testData) > floatval($max)) {
                            return false;
                        }
                    }
                    return true;
                } else {
                    return false;
                }
                // no break
            case 'date':
            
                if ($this->validateDateTime($testData, 'Y-m-d') === false) {
                    return false;
                } else {
                    return true;
                }
                // no break
            case 'datetime':
                if ($this->validateDateTime($testData, 'Y-m-d H:i:s') === false) {
                    return false;
                } else {
                    return true;
                }
                // no break
            default:
                return true;
                break;
        }
    }

    private function validateDateTime($dateStr, $format)
    {
        //dd($dateStr);
        date_default_timezone_set('UTC');
        $date = \DateTime::createFromFormat($format, $dateStr);
        return $date && ($date->format($format) === $dateStr);
    }



    private function connectTargetDB(string $projectId): bool
    {
        $project = Project::where('id', $projectId)->first();
        $this->dbTargetConnection = DBTargetConnection::getInstance(
            $project->dbType,
            $project->dbServer,
            $project->dbPort,
            $project->dbName,
            $project->dbUsername,
            $project->dbPassword
        );

        if (!$this->dbTargetConnection->connect()) {
            return false;
        }

        return true;
    }
    
    private function getDbSchema(): void
    {
        $databaseBuilder = new DatabaseBuilder($this->dbTargetConnection);
        $databaseBuilder->setUpTablesAndColumns();
        $this->dbTarget = $databaseBuilder->getDatabase();
    }
}
