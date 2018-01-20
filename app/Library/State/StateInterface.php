<?php

namespace App\Library\State;

interface StateInterface {
    public function getState(): string;
    public function goNextState(): void;
}