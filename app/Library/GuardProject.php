<?php

namespace App\Library;

use App\User;
use DB;

class GuardProject {
    private $userId = null;

    public function __construct(string $accessToken) {
        $this->userId = User::select('id')->where('accessToken', '=', $accessToken)->first()->id;
    }
    
    public function getAllProject() {
        return DB::table('PROJECT')->where('userId', '=', $this->userId)->get();
    }

    public function getProject(string $projectId) {
        return DB::table('PROJECT')
            ->where([
                ['userId', '=', $this->userId],
                ['id', '=', $projectId]
            ])->first();
    }

}