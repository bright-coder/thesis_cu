<?php

namespace App\Library\State;

abstract class AbstractState {
    protected static $projectId;
    protected static $changeRequestId;
    protected $message;
    protected $statusCode;
    public function getStatusCode(): int{
        return $this->statusCode;
    }
    public function getMessage(): string{
        return $this->message;
    }
    public function getProjectId(): int {
        return AbstractState::$projectId;
    }
    public function getChangeRequestId(): int {
        return AbstractState::$changeRequestId;
    }
}