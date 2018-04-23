<?php

namespace App\Http\Controllers\Api;

use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Http\Controllers\Controller;
use App\Library\GuardFunctionalRequirement;
use App\Library\GuardProject;
use App\Requests\ChangeRequestRequest;
use DB;
use Illuminate\Http\Request;

class ChangeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
    public function store(ChangeRequestRequest $request, $projectId)
    {
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($projectId);

        if (!$project) {
            return response()->json(['msg' => 'forbidden'], 400);
        }
        
        $request = $request->json()->all();

        $guard = new GuardFunctionalRequirement($projectId);
        $functionalRequirement = $guard->getAllFunctionalRequirement($request['functionalRequirementId']);
        if (!$functionalRequirement) {
            return response()->json(['msg' => 'forbidden'], 400);
        }

        DB::beginTransaction();
        try {
            $changeRequest = new ChangeRequest;
            $changeRequest->changeFunctionalRequirementId = $request['functionalRequirementId'];
            $changeRequest->statuts = 'imported';
            $changeRequest->save();
            $changeRequestInputList = [];
            foreach ($request['inputs'] as $input) {
                $changeRequestInput = new ChangeRequestInput;
                $changeRequestInput->changeRequestId = $changeRequest->id;
                $changeRequestInput->changeType = $input['changeType'];
                $changeRequestInput->name = $input['name'];
                $changeRequestInput->dataType = $input['dataType'];
                switch ($input['dataType']) {
                    case 'char':
                    case 'varchar':
                    case 'nchar':
                    case 'nvarchar':
                        $changeRequestInput->length = $input['length'];
                        break;
                    case 'float':
                        $changeRequestInput->precision = $input['precision'];
                        break;
                    case 'decimal':
                        $changeRequestInput->precision = $input['precision'];
                        if (\array_key_exists('scale', $input)) {
                            $changeRequestInput->scale = $input['scale'];
                        }
                        break;
                    default:
                        # code...
                        break;
                }

                if (\array_key_exists('default', $input)) {
                    $changeRequestInput->default = $input['default'];
                } else {
                    $changeRequestInput->default = null;
                }

                $changeRequestInput->nullable = $input['nullable'];
                $changeRequestInput->unique = $input['unique'];

                if (\array_key_exists('min', $input)) {
                    $changeRequestInput->min = $input['min'];
                }
                if (\array_key_exists('max', $input)) {
                    $changeRequestInput->max = $input['max'];
                }

                $changeRequestInput->tableName = $input['tableName'];
                $changeRequestInput->columnName = $input['columnName'];

                $changeRequestInput->save();
                $changeRequestInputList[] = $changeRequest;
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return resonse()->json(['msg' => 'Internal Server Error.'], 500);
        }

        
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
