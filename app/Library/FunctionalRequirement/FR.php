<?php

namespace App\Library\FunctionalRequirement;

class FR
{

    private $no;

    private $description;

    private $version;

    private $frInputs;

    public function __construct()
    {
        $this->frInputs = [];
    }

    public function getNo(): string
    {
        return $this->$no;
    }

    public function setNo(string $no): void
    {
        $this->$no = $no;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function addInput(FRInput $frInput): void
    {
        $this->frInputs[$frInput->getId()] = $frInput;
    }

    public function getAllInputs(): array
    {
        return $this->frInputs;
    }

    public function getInputById(string $id): FRInput
    {
        return $this->frInputs[$id];
    }

}
