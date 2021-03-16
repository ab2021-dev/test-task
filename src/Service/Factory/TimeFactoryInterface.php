<?php

namespace App\Service\Factory;

use App\Value\Time;

interface TimeFactoryInterface
{
    public function createFromString(string $time): Time;
    public function createByAddHoursToTime(Time $origin, int $numberOfHours): Time;
    public function createBySubHoursFromTime(Time $origin, int $numberOfHours): Time;
    public function createDayStartTime(): Time;
    public function createDayEndTime(): Time;
}
