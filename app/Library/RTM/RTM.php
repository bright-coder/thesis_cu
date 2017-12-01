<?php

namespace App\Library\RTM;

class RTM
{
    private $frNoList;

    public function __construct() {
        $this->frNoList = [];
    }

    public function getTestCaseNoListByFR(string $frNo): array
    {
        return $this->frNoList[$frNo];
    }

    public function setRelation(string $frNo, array $testCaseNoList): void
    {
        $this->frNoList[$frNo] = $testCaseNoList;
    }

}
