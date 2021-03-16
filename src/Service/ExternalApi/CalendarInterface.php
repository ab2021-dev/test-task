<?php

namespace App\Service\ExternalApi;

use App\Value\Day;

interface CalendarInterface
{
    public function isDayOff(Day $day): bool;
    public function isDayWorkingButShort(Day $day): bool;
}
