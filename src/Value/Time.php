<?php

namespace App\Value;

use DateTime;

class Time extends AbstractDayTime
{
    private string $time;

    /**
     * Create object from time string.
     * @param string $time String in H:i format.
     */
    public function __construct(string $time)
    {
        $this->time = $time;
        $this->dateTime = new DateTime();
        $timeFromString = explode(":", $time);
        $this->dateTime->setTime($timeFromString[0], $timeFromString[1], 0);
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function getHoursDiffTo(Time $target): int
    {
        return (int) $this->getDateTime()->diff($target->getDateTime())->format('%h');
    }

    public function __clone()
    {
        return new Time((clone $this->getDateTime())->format('H:i'));
    }
}
