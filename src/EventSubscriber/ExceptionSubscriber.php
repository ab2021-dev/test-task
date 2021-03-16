<?php

namespace App\EventSubscriber;

use App\Exception\CalendarApiException;
use App\Exception\UnableToLoadEmployeeDataException;
use App\Service\ResponseFormatterInterface;
use http\Exception\BadMethodCallException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private ResponseFormatterInterface $responseFormatter;

    public function __construct(LoggerInterface $logger, ResponseFormatterInterface $responseFormatter)
    {
        $this->logger = $logger;
        $this->responseFormatter = $responseFormatter;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof UnableToLoadEmployeeDataException || $exception instanceof BadMethodCallException) {
            $this->logger->critical($exception->getMessage());
            $event->setResponse(
                new JsonResponse(
                    ['errors' => $this->responseFormatter->formatExceptionErrorMessage($exception, 'Internal server error.')],
                    JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                )
            );
        }

        if ($exception instanceof NotFoundHttpException) {
            $this->logger->info($exception->getMessage());
            $event->setResponse(
                new JsonResponse(
                    ['errors' => $this->responseFormatter->formatExceptionErrorMessage($exception)],
                    $exception->getStatusCode()
                )
            );
        }

        if ($exception instanceof CalendarApiException || $exception instanceof BadRequestHttpException || $exception instanceof NotFoundHttpException) {
            $this->logger->error($exception->getMessage());
            $event->setResponse(
                new JsonResponse(
                    ['errors' => $this->responseFormatter->formatExceptionErrorMessage($exception)],
                    $exception->getStatusCode()
                )
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', -1],
            ],
        ];
    }

}

