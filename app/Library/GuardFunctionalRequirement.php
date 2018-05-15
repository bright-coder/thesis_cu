<?php

namespace App\Library;

use App\Model\User;
use App\Model\FunctionalRequirement;

class GuardFunctionalRequirement {
    
    private $projectId = null;

    public function __construct(string $projectId) {
        $this->projectId = $projectId;
    }
    
    public function getAllFunctionalRequirement() {
        return FunctionalRequirement::where('projectId', $this->projectId)->get();
    }

    public function getFunctionalRequirement(string $functionalRequirementNo) {
        return FunctionalRequirement::where([
                // ['userId', '=', $this->userId],
                ['projectId', $this->projectId],
                ['no', $functionalRequirementNo]
            ])->first();
    }

}