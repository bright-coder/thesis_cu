<?php

namespace App\Library;

use App\Model\ChangeRequest;
use App\Model\ChangeRequestInput;
use App\Library\ImpactResult;

class CancelChangeRequest
{
    private $cancelCrId;
    private $projectId;

    public function __construct(int $projectId, int $cancelCrId) {
        $this->projectId = $projectId;
        $this->cancelCrId = $cancelCrId;
    }
    
    public function cancel(){
        $changeRequestList = ChangeRequest::where([
            ['id', '>=', $this->cancelCrId],
            ['projectId', $this->projectId],
            //['cancelStatus', 0]
        ])->orderBy('id', 'desc')->get();

        foreach($changeRequestList as $changeRequest) {
            
            if($this->isStatusSuccess($changeRequest->id)) {
                $impactResult = (new ImpactResult($changeRequest->id))->getImpact();
            }
        }


    }

    private function isStatusSuccess(int $changeRequestId): bool {

        $changeRequestInputList = ChangeRequestInput::where('changeRequestId', $changeRequestId)->get();
        foreach($changeRequestInputList as $crInput) {
            if($crInput->status == 0) {
                return false;
            }
        }
        return true;

    }




}