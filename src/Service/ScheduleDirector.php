<?php

namespace App\Service;

use App\Repository\EmployeeRepository;
use App\Service\Factory\DayFactoryInterface;
use App\Service\Builder\ScheduleBuilderInterface;
use App\Service\Validation\LogicValidationInterface;
use http\Exception\BadMethodCallException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScheduleDirector implements ScheduleDirectorInterface
{
    private EmployeeRepository $employeeRepository;
    private DayFactoryInterface $dayFactory;
    private LogicValidationInterface $logicValidation;
    private ?ScheduleBuilderInterface $scheduleBuilder;

    public function __construct(DayFactoryInterface $dayFactory, LogicValidationInterface $logicValidation, EmployeeRepository $employeeRepository)
    {
        $this->dayFactory = $dayFactory;
        $this->logicValidation = $logicValidation;
        $this->employeeRepository = $employeeRepository;
        $this->scheduleBuilder = null;
    }

    public function setBuilder(ScheduleBuilderInterface $scheduleBuilder)
    {
        $this->scheduleBuilder = $scheduleBuilder;
    }

    public function createSchedule(string $startDate, string $endDate, string $employeeId)
    {
        if (is_null($this->scheduleBuilder)) {
            throw new BadMethodCallException("ScheduleBuilderInterface is not set.");
        }

        $startDay = $this->dayFactory->createFromString($startDate);
        $endDay = $this->dayFactory->createFromString($endDate);
        if (!$this->logicValidation->isDateIntervalCorrect($startDay, $endDay)) {
            throw new BadRequestHttpException("Date interval is not correct.");
        }

        $employee = $this->employeeRepository->findById($employeeId);
        if (is_null($employee)) {
            throw new NotFoundHttpException("Employee not found.");
        }

        $this->scheduleBuilder->reset();
        $this->scheduleBuilder->createDays($startDay, $endDay);
        if ($this->scheduleBuilder->getDaysCount() === 0) {
            return;
        }

        $this->scheduleBuilder->createWorkDayTimeRanges(
            $employee->getWorkStart(),
            $employee->getLunchStart()
        );
        $this->scheduleBuilder->createSchedule();
    }
}
