<?php

namespace App\Value;

use DateTime;

abstract class AbstractDayTime
{
    protected DateTime $dateTime;

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function isGreaterThan(AbstractDayTime $target): bool
    {
        return $this->getDateTime() > $target->getDateTime();
    }
}
