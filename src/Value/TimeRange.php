<?php

namespace App\Value;

class TimeRange
{
    private Time $start;
    private Time $end;

    public function __construct(Time $start, Time $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function __clone()
    {
        $start = clone $this->start;
        $end = clone $this->end;
        return new TimeRange($start, $end);
    }

    public function getStart(): Time
    {
        return $this->start;
    }

    public function getEnd(): Time
    {
        return $this->end;
    }

    public function toArray(): array
    {
        return [
            'start' => $this->start->getTime(),
            'end' => $this->end->getTime(),
        ];
    }
}
