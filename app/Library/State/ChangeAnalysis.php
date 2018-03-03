<?php

namespace App\Library\State;

use App\Library\State\ImportState;
use App\Library\State\StateInterface;

class ChangeAnalysis
{
    private $state;

    private $request;
    private $projectId = null;
    private $changeRequestId = null;
    private $message = null;
    private $statusCode = null;
    private $completedState = null;
    private $isProcessError = null;
    private $dbImpactResult = null;
    private $frImpactResult = null;
    private $tcImpactResult = null;
    private $rtmImpactResult = null;

    public const LAST_STATE_NO = 2;

    public function __construct(array $request)
    {
        $this->request = $request;
        $this->state = new ImportState;
    }

    public function process(): bool
    {
       return $this->state->process($this);
    }

    public function setState(StateInterface $state): void
    {
        $this->state = $state;
    }

    public function setProjectId(int $projectId): void
    {
        $this->projectId = $projectId;
    }

    public function setChangeRequestId(int $changeRequestId): void
    {
        $this->changeRequestId = $changeRequestId;
    }

    public function setMessage(string $msg): void
    {
        $this->message = $msg;
    }

    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
    }

    public function getRequest(): array
    {
        return $this->request;
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function getChangeRequestId(): int
    {
        return $this->changeRequestId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }




}
