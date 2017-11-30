<?php

namespace App\Library\TestCase;

class TestCaseInput
{

    private $id;
    private $name;
    private $value;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

}
