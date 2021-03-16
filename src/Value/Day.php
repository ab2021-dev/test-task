<?php

namespace App\Value;

use DateTime;

class Day extends AbstractDayTime
{
    private string $date;

    /**
     * Create object from date string.
     * @param string $date String in Y-m-d format.
     */
    public function __construct(string $date)
    {
        $this->dateTime = new DateTime($date);
        $this->date = $date;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function isGreaterOrEqualThan(Day $day): bool
    {
        return $this->getDateTime() >= $day->getDateTime();
    }

    public function getYear(): string
    {
        return $this->format('Y');
    }

    private function format(string $format)
    {
        return $this->getDateTime()->format($format);
    }

    public function getMonth(): string
    {
        return $this->format('m');
    }

    public function getDayNumber(): string
    {
        return $this->format('d');
    }

    public function getDayOfWeek(): string
    {
        return $this->format('D');
    }
}
