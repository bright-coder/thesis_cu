<?php

namespace App\Library\ChangeRequest;

class ChangeInputInfo
{
    private $inputName;
    private $changeType;
    private $changeInfo;
    private $modifyFlag;

    public function setInputName(string $name): void
    {
        $this->inputName = $name;
    }

    public function getInputName(): string
    {
        return $this->inputName;
    }

    public function setChangeType(string $type): void
    {
        $this->changeType = $type;
    }

    public function getChangeType(): string
    {
        return $this->changeType;
    }

    public function setChangeInfo(array $info): void
    {
        // ["inputName"] => "Student id"
        // ["dataType"] => NULL
        // ["dataLength"] => "20"
        // ["scale"] => NULL
        // ["unique"] => NULL
        // ["notNull"] => NULL
        // ["default"] => NULL
        // ["min"] => NULL
        // ["max"] => NULL
        // ["tableName"] => NULL
        // ["columnName"] => NULL
        $this->$info = $info;
    }

    public function getChangeInfo(): array
    {
        return $this->changeInfo;
    }

    public function setModifyFlag(bool $flag): void
    {
        $this->modifyFlag = $flag;
    }

    public function getModifyFlag(): bool
    {
        return $this->modifyFlag;
    }

}
