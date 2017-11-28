<?php

namespace App\Library\ChangeRequest;

class ChangeRequest
{

    private $frNo;
    private $frVersion;
    private $changeInputInfoList;

    public function __construct()
    {
        $this->changeInputInfoList = [];
    }

    public function setFrNo(string $no): void
    {
        $this->frNo = $no;
    }

    public function getFrNo(): string
    {
        return $this->frNo;
    }

    public function setFrVersion(string $version): void
    {
        $this->frVersion = $version;
    }

    public function getFrVersion(): string
    {
        return $this->frVersion;
    }

    public function addChangeInputInfo(ChangeInputInfo $changeInputInfo): void
    {
        $this->changeInputInfoList[$changeInputInfo->getInputName()] = $changeInputInfo;
    }

    public function getChangeInputInfoByInputName(string $inputName): ChangeInputInfo
    {
        return $this->changeInputInfoList[$inputName];
    }

    public function getAllChangeInputInfo(): array
    {
        return $this->changeInputInfoList;
    }

}
