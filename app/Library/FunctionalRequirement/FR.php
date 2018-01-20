<?php

namespace App\Library\FunctionalRequirement;
use App\Library\FunctionalRequirement\FRInput;

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
        return $this->no;
    }

    /**
     * setNo function
     *
     * @param string $no
     * @return void
     */
    public function setNo(string $no): void
    {
        $this->no = $no;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Undocumented function
     *
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Undocumented function
     *
     * @param string $version
     * @return void
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * Undocumented function
     *
     * @param FRInput $frInput
     * @return void
     */
    public function addInput(FRInput $frInput): void
    {
        $this->frInputs[$frInput->getName()] = $frInput;
    }
    
    /**
     * Undocumented function
     *
     * @return array
     */
    public function getAllInputs(): array
    {
        return $this->frInputs;
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @return FRInput
     */
    public function getInputByName(string $name): FRInput
    {
        return $this->frInputs[$name];
    }

}
