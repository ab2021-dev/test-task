<?php

namespace App\Service\Factory;

use App\Value\Day;

interface DayFactoryInterface
{
    public function createFromString(string $date): Day;
    public function createNextDayAfter(Day $day): Day;
}
