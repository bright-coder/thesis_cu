<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use App\Library\CustomModel\DBTargetConnection;
use App\Library\Builder\DatabaseBuilder;
use DB;
use Illuminate\Http\Request;

class DatabaseApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $projectId)
    {
        $user = User::select('id')->where('accessToken', '=', $request->bearerToken())->first();
        $project = DB::table('PROJECT')
            ->select('dbServer', 'dbName','dbUsername','dbPassword')
            ->where('userId', '=', $user->id)
            ->where('id','=', $projectId)
            ->get();
        if (count($project) <= 0) {
            return response()->json(['msg' => 'Not found your project.'], 200);
        }
        
        $project = $project[0];

        $dbCon = DBTargetConnection::getInstance('sqlsrv', $project->dbServer,$project->dbName,$project->dbUsername,$project->dbPassword);
        if( !$dbCon->Connect()) {
            return response()->json(['msg' => 'Cannot Connect to Target Database.'], 400);
        }
        else {
            $databaseBuilder = new DatabaseBuilder($dbCon);
            $databaseBuilder->setUpTablesAndColumns();
        }
        $dbTarget = $databaseBuilder->getDatabase();
        //dd($dbTarget);
        //$tables = [];
        $tables = [];
            foreach ($dbTarget->getAllTables() as $table) {

                foreach($table->getAllColumns() as $column) {
                    $tables[$table->getName()]['columns'][$column->getName()] = [
                        'type' => $column->getDataType()->getType(),
                        'length' => $column->getDataType()->getLength(),
                        'precision' => $column->getDataType()->getPrecision(),
                        'scale' => $column->getDataType()->getScale(),
                        'nullable' => $column->isNullable(),
                        'default' => $column->getDefault(),
                    ];
                }
                $tables[$table->getName()]['PK'][$table->getPK()->getName()] = [
                    'columnNames' => $table->getPK()->getColumns()
                ];
                foreach ($table->getAllFK() as $fk) {
                    $tables[$table->getName()]['FKs'][$fk->getName()] = [
                        'links' => $fk->getColumns(),
                    ];
                }
                foreach($table->getAllUniqueConstraint() as $unique) {
                    $tables[$table->getName()]['uniques'][$unique->getName()] = [
                        'columnNames' => $table->getColumns()
                    ];
                }
                foreach($table->getAllCheckConstraint() as $check) {
                    $tables[$table->getName()]['checks'][$check->getName()] = [
                        'columnNames' => $check->getColumns(),
                        'definition' => $check->getDetail()['definition'],
                        'mins' => $check->getDetail()['min'],
                        'maxs' => $check->getDetail()['max']
                    ];
                }

                //$table[$table->getName()] 
            }
        return response()->json($tables,200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
