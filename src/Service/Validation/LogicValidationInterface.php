<?php

namespace App\Service\Validation;

use App\Value\Day;
use App\Value\Time;

interface LogicValidationInterface
{
    public function isDateIntervalCorrect(Day $startDay, Day $endDay): bool;
    public function isWorkStartTimeCorrect(Time $workStart, Time $lunchStart): bool;
}
