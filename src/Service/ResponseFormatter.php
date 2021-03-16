<?php

namespace App\Service;

use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ResponseFormatter implements ResponseFormatterInterface
{
    public function formatValidationErrorsMessages(ConstraintViolationListInterface $errors): array
    {
        $errorsMessages = [];
        foreach ($errors as $error) {
            $errorsMessages[]['message'] = $error->getMessage();
        }

        return $errorsMessages;
    }

    public function formatSchedule(array $schedule): array
    {
        $result = [];
        foreach ($schedule as $daySchedule)
        {
            $day = $daySchedule->getDay();
            $timeRanges = $daySchedule->getTimeRanges();
            $result[] = [
                'day' => $day->getDate(),
                'timeRanges' => $this->formatTimeRanges($timeRanges)
            ];
        }

        return $result;
    }

    private function formatTimeRanges(array $timeRanges): array
    {
        $result = [];
        foreach ($timeRanges as $timeRange) {
            $result[] = [
                'start' => $timeRange->getStart()->getTime(),
                'end' => $timeRange->getEnd()->getTime(),
            ];
        }
        return $result;
    }

    public function formatExceptionErrorMessage(Exception $exception, ?string $message = null): array
    {
        return [
            "message" => $message ?? $exception->getMessage()
        ];
    }
}
