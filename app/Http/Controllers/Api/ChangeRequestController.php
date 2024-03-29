<?php

namespace App\Http\Controllers\Api;

use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Http\Controllers\Controller;
use App\Library\GuardFunctionalRequirement;
use App\Library\GuardProject;
use App\Http\Requests\ChangeRequestRequest;
use App\Model\FunctionalRequirementInput;
use DB;
use Illuminate\Http\Request;
use App\Library\ChangeAnalysis;
use App\Model\User;
use App\Library\ImpactResult;
use App\Model\FunctionalRequirement;
use App\Model\Project;
use App\Library\CancelChangeRequest;

class ChangeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $projectName)
    {
        $user = User::where('accessToken', $request->bearerToken())->first();

        if (!$user) {
            return response()->json(['msg' => 'forbidden'], 401);
        }

        $guard = new GuardProject($request->bearerToken());
        //$projects = $guard->getAllProject();
        if ($projectName == 'all') {
            $projects = $guard->getAllProject();
        } else {
            $projects = $guard->getProject($projectName);
            $projects = [
                $projects
            ];
        }

        if (!count($projects) > 0 ) {
            return response()->json(['msg' => 'not found project'], 204);
        }
        $projectIdList = [];

        foreach ($projects as $project) {
            $projectIdList[] = $project->id;
        }
        
        $changeRequests = ChangeRequest::whereIn('projectId', $projectIdList)->orderBy('id', 'desc')->get();

        if (empty($changeRequests)) {
            return response()->json(['msg' => 'not found change request.'], 204);
        }

        $result = [];
        
        foreach ($changeRequests as $changeRequest) {
            $status = 'success';
            foreach(ChangeRequestInput::where('crId' , $changeRequest->id)->get() as $crInput) {
                if($crInput->status == 0) {
                    $status = 'failed';
                }
            }
            $result[] = [
                'id' => $changeRequest->id,
                'frNo' => FunctionalRequirement::where('id', $changeRequest->changeFrId)->first()->no,
                'projectName' => Project::where('id', $changeRequest->projectId)->first()->name,
                'status' => $status
            ];

        }
        
        return response()->json($result, 200);
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
    public function store(ChangeRequestRequest $request, $projectName)
    {
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($projectName);

        if (!$project) {
            return response()->json(['msg' => 'forbidden'], 400);
        }
        
        $request = $request->json()->all();

        $guard = new GuardFunctionalRequirement($project->id);
        $functionalRequirement = $guard->getFunctionalRequirement($request['functionalRequirementNo']);
        if (!$functionalRequirement) {
            return response()->json(['msg' => 'forbidden'], 400);
        }

        DB::beginTransaction();
        try {
            $total = count(ChangeRequest::where('projectId', $project->id)->get());
            $changeRequest = new ChangeRequest;
            $changeRequest->projectId = $project->id;
            $changeRequest->status = -1;
            $changeRequest->changeFrId = $functionalRequirement->id;
            $changeRequest->no = 'CR-'.$project->prefix.'-'.($total+1);
            $changeRequest->save();
            $changeRequestInputList = [];
            foreach ($request['inputs'] as $input) {
                $changeRequestInput = new ChangeRequestInput;
                $changeRequestInput->crId = $changeRequest->id;
                $changeRequestInput->changeType = $input['changeType'];
                // if (\array_key_exists('frId', $input)) {
                //     $changeRequestInput->frInputId = $input['frId'];
                // }
                if ($changeRequestInput->changeType == 'add') {
                    // must change
                    if (\array_key_exists('frId', $input)) {
                        //$changeRequestInput->frId = $input['frId'];
                    } else {
                        $changeRequestInput->name = $input['name'];
                        $changeRequestInput->dataType = $input['dataType'];
                        switch ($changeRequestInput->dataType) {
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
                            if ($input['default'] == null) {
                                $changeRequestInput->default = "#NULL";
                            } else {
                                $changeRequestInput->default = $input['default'];
                            }
                        }
    
                        $changeRequestInput->nullable = $input['nullable'];
                        $changeRequestInput->unique = $input['unique'];
    
                        if (\array_key_exists('min', $input)) {
                            if ($input['min'] == null) {
                                $changeRequestInput->default = "#NULL";
                            } else {
                                $changeRequestInput->min = $input['min'];
                            }
                        }
                        if (\array_key_exists('max', $input)) {
                            if ($input['max'] == null) {
                                $changeRequestInput->max = "#NULL";
                            } else {
                                $changeRequestInput->max = $input['max'];
                            }
                        }
    
                        $changeRequestInput->tableName = $input['tableName'];
                        $changeRequestInput->columnName = $input['columnName'];
                        $fr = FunctionalRequirement::where('projectId', $project->id)->get();
                        foreach($fr as $frDB) {
                            $frInput = FunctionalRequirementInput::where([
                                ['frId', $frDB->id],
                                ['name', $input['name']]
                            ])->first();
                            if($frInput) {
                                $changeRequestInput->frInputId = $frInput->id;
                                break;
                            }
                        }
                    }
                } elseif ($changeRequestInput->changeType == 'edit') {
                    if (\array_key_exists('dataType', $input)) {
                        $changeRequestInput->dataType = $input['dataType'];
                    }
                    if (\array_key_exists('length', $input)) {
                        $changeRequestInput->length = $input['length'];
                    }
                    if (\array_key_exists('precision', $input)) {
                        $changeRequestInput->precision = $input['precision'];
                    }
                    if (\array_key_exists('scale', $input)) {
                        $changeRequestInput->scale = $input['scale'];
                    }
                    if (\array_key_exists('unique', $input)) {
                        $changeRequestInput->unique = $input['unique'];
                    }
                    if (\array_key_exists('nullable', $input)) {
                        $changeRequestInput->nullable = $input['nullable'];
                    }
                    if (\array_key_exists('min', $input)) {
                        $changeRequestInput->min = $input['min'];
                    }
                    if (\array_key_exists('max', $input)) {
                        $changeRequestInput->max = $input['max'];
                    }
                    if (\array_key_exists('default', $input)) {
                        $changeRequestInput->default = $input['default'];
                    }
                    
                    $changeRequestInput->frInputId = FunctionalRequirementInput::where([
                        ['frId', $functionalRequirement->id],
                        ['name' , $input['name']]
                        ])->first()->id;
                } elseif ($changeRequestInput->changeType == 'delete') {
                    $changeRequestInput->name = $input['name'];
                    $changeRequestInput->frInputId = FunctionalRequirementInput::where([
                        ['frId', $functionalRequirement->id],
                        ['name' , $input['name']]
                        ])->first()->id;
                }

                $changeRequestInput->save();
                $changeRequestInputList[] = $changeRequestInput;
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return resonse()->json(['msg' => 'Internal Server Error.'], 500);
        }

        $changeAnalysis = new ChangeAnalysis($project->id, $changeRequest, $changeRequestInputList);
        $changeAnalysis->analyze();
        // $changeAnalysis->saveSchemaImpact();
        // $changeAnalysis->saveInstanceImpact();
        // $changeAnalysis->saveFrImpact();
        // $changeAnalysis->saveTcImpact();
        // $changeAnalysis->saveRtmRelationImpact();

        return response()->json(['changeRequestId' => $changeAnalysis->getChangeRequest()->id], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $projectName, $changeRequestId)
    {
        //
        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($projectName);

        if (!$project) {
            return response()->json(['msg' => 'forbidden'], 403);
        }

        $changeRequest = ChangeRequest::where([['projectId', $project->id],['id', $changeRequestId]])->first();
        if (!$changeRequest) {
            return response()->json(['msg' => 'forbidden'], 403);
        }
        $changeRequestId = $changeRequest->id;

        $changeRequestInputList = ChangeRequestInput::where('crId', $changeRequestId)->get();
        $result = [
            // 'id' => $changeRequestId,
            // 'projectName' => $projectName,
            'changeFrNo' => FunctionalRequirement::where('id', $changeRequest->changeFrId)->first()->no,
            'status' => 'success',
            'crInputList' => [],
            'impactList' => (new ImpactResult($changeRequestId))->getImpact('json')
        ];
        foreach($changeRequestInputList as $crInput) {
            if($crInput->changeType == 'edit') {
                //dd($crInput);
                $crInput->name = FunctionalRequirementInput::where('id', $crInput->frInputId)->first()->name;
            }
            if($crInput->status == 0) {
                $result['status'] = 'failed';
            }
            $result['crInputList'][] = $crInput;
        }

        return response()->json($result, 200);
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
    public function destroy(Request $request, $projectName, $id)
    {
        $user = User::where('accessToken', $request->bearerToken())->first();

        if (!$user) {
            return response()->json(['msg' => 'forbidden'], 401);
        }

        $guard = new GuardProject($request->bearerToken());
        $project = $guard->getProject($projectName);

        if(empty($project)) {
            return response()->json(['msg' => 'not found project : '.$projectName], 400);
        }
        
        $changeRequest = ChangeRequest::where([
            ['id' , $id],
            ['projectId', $project->id]
        ])->first();

        if(empty($changeRequest)) {
            return response()->json(['msg' => 'not found Change Request ID : '.$id], 400);
        }

        $canellation = new CancelChangeRequest($project->id, $id);
        $canellation->cancel();

        return response()->json(['msg' => 'success'], 200);
    }
}
