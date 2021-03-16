<?php

namespace App\EventSubscriber;

use App\Service\ResponseFormatterInterface;
use App\Service\Validation\ValidationHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ScheduleRequestSubscriber implements EventSubscriberInterface
{
    private array $scheduleControllers;
    private ValidationHelperInterface $validationHelper;
    private ResponseFormatterInterface $responseFormatter;

    public function __construct(array $scheduleControllers, ValidationHelperInterface $validationHelper, ResponseFormatterInterface $responseFormatter)
    {
        $this->scheduleControllers = $scheduleControllers;
        $this->validationHelper = $validationHelper;
        $this->responseFormatter = $responseFormatter;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $controller = $request->attributes->get('_controller');
        if (!in_array($controller, $this->scheduleControllers)) {
            return;
        }

        $errors = $this->validationHelper->validateScheduleRequestParams(
            $request->query->get('startDate') ?? '',
            $request->query->get('endDate') ?? '',
            $request->query->get('employeeId') ?? ''
        );
        if (count($errors) !== 0) {
            $errorsMessages = $this->responseFormatter->formatValidationErrorsMessages($errors);
            $event->setResponse(
                new JsonResponse(
                    ['errors' => $errorsMessages],
                    JsonResponse::HTTP_BAD_REQUEST
                )
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 31],
            ],
        ];
    }

}

