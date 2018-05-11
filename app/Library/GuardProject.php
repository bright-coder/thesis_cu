<?php

namespace App\Library;

use App\Model\User;
use App\Model\Project;
use DB;

class GuardProject {
    private $userId = null;

    public function __construct(string $accessToken) {
        $this->userId = User::select('id')->where('accessToken', '=', $accessToken)->first()->id;
    }
    
    public function getAllProject() {
        return Project::where('userId', '=', $this->userId)->get();
    }

    public function getProject(string $projectName) {
        return Project::where([
                ['userId', '=', $this->userId],
                ['name', $projectName]
            ])->first();
    }

}