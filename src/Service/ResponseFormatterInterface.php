<?php

namespace App\Service;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Exception;

interface ResponseFormatterInterface
{
    public function formatValidationErrorsMessages(ConstraintViolationListInterface $errors): array;
    public function formatExceptionErrorMessage(Exception $exception, ?string $message = null): array;
    public function formatSchedule(array $employeeSchedule): array;
}
