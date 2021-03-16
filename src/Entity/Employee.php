<?php

namespace App\Entity;

use App\Value\Time;

class Employee
{
    private string $id;
    private Time $workStart;
    private Time $lunchStart;

    public function __construct(string $id, Time $workStart, Time $lunchStart)
    {
        $this->id = $id;
        $this->workStart = $workStart;
        $this->lunchStart = $lunchStart;
    }

    public function getWorkStart(): Time
    {
        return $this->workStart;
    }

    public function getLunchStart(): Time
    {
        return $this->lunchStart;
    }
}
