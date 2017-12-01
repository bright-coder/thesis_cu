<?php

namespace App\Library\TestCase;

class TestCase
{

    private $no;
    private $desc;
    private $version;
    private $type;
    private $testCaseInputList;

    public function __construct()
    {
        $this->testCaseInputList = [];
    }

    public function setNo(string $no): void
    {
        $this->no = $no;
    }

    public function getNo(): string
    {
        return $this->no;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setDescription(string $desc): void
    {
        $this->desc = $desc;
    }

    public function getDescription(): string
    {
        return $this->desc;
    }

    public function addTestCaseInput(TestCaseInput $input): void
    {
        $this->testCaseInputList[$input->getName()] = $input;
    }

    public function setTestCaseInputList(TestCaseInput $inputList): void
    {
        $this->testCaseInputList = $inputList;
    }

    public function getTestCaseInputByName(string $inputName): TestCaseInput
    {
        return $this->testCaseInputList[$inputName];
    }

    public function getTestCaseInputList(): array
    {
        return $this->testCaseInputList;
    }

}
