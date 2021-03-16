<?php

namespace App\Controller;

use App\Service\ResponseFormatterInterface;
use App\Service\Builder\ScheduleBuilderInterface;
use App\Service\ScheduleDirectorInterface;
use App\Service\Builder\WorkScheduleBuilderInterface;
use App\Service\Builder\NonWorkScheduleBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/employee-schedule")
 */
class EmployeeScheduleController extends AbstractController
{
    private ScheduleDirectorInterface $scheduleDirector;
    private ResponseFormatterInterface $responseFormatter;

    public function __construct(ScheduleDirectorInterface $scheduleDirector, ResponseFormatterInterface $responseFormatter)
    {
        $this->scheduleDirector = $scheduleDirector;
        $this->responseFormatter = $responseFormatter;
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getWorkSchedule(WorkScheduleBuilderInterface $workScheduleBuilder, Request $request): JsonResponse
    {
        $schedule = $this->getScheduleFrom($workScheduleBuilder, $request);
        return $this->createResponse($schedule);
    }

    private function getScheduleFrom(ScheduleBuilderInterface $builder, Request $request): array
    {
        $this->scheduleDirector->setBuilder($builder);
        $this->scheduleDirector->createSchedule(
            $request->query->get('startDate'),
            $request->query->get('endDate'),
            $request->query->get('employeeId')
        );

        return $builder->getSchedule();
    }

    private function createResponse(array $schedule): JsonResponse
    {
        $formattedSchedule = $this->responseFormatter->formatSchedule($schedule);
        return new JsonResponse(['schedule' => $formattedSchedule], JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/non-work", methods={"GET"})
     */
    public function getNonWorkSchedule(NonWorkScheduleBuilderInterface $nonWorkScheduleBuilder, Request $request): JsonResponse
    {
        $schedule = $this->getScheduleFrom($nonWorkScheduleBuilder, $request);
        return $this->createResponse($schedule);
    }
}
