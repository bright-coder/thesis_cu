<?php

namespace App\Library;

use App\User;
use App\FunctionalRequirement;

class GuardFunctionalRequirement {
    
    private $projectId = null;

    public function __construct(string $projectId) {
        $this->projectId = $projectId;
    }
    
    public function getAllFunctionalRequirement() {
        return FunctionalRequirement::where('projectId', $this->projectId)->get();
    }

    public function getFunctionalRequirement(string $functionalRequirementId) {
        return FunctionalRequirement::where([
                // ['userId', '=', $this->userId],
                ['id', $functionalRequirementId]
            ])->first();
    }

}