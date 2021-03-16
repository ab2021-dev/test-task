<?php

namespace App\Service\ExternalApi;

use App\Exception\CalendarApiException;
use App\Value\Day;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SimpleXMLElement;
use Exception;

class Calendar implements CalendarInterface
{
    public const DAY_IS_HOLIDAY = 1;
    public const DAY_IS_WORKING_BUT_SHORT = 2;
    public const DAY_IS_WORKING = 3;
    public const SERVICE_URL = "http://xmlcalendar.ru/data/ru/{year}/calendar.xml";
    public const WEEKEND_DAYS = ['Sat', 'Sun'];
    public const RESPONSE_DATE_ATTRIBUTE_NAME = 'd';
    public const RESPONSE_TYPE_ATTRIBUTE_NAME = 't';
    public const RESPONSE_DAY_IS_HOLIDAY = '1';
    public const RESPONSE_DAY_IS_WORKING_BUT_SHORT = '2';
    private array $dayType;
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->dayType = [];
    }

    public function isDayOff(Day $day): bool
    {
        return $this->getDayType($day) === self::DAY_IS_HOLIDAY;
    }

    private function fetchDataFromAPI($year)
    {
        $url = str_replace("{year}", $year, self::SERVICE_URL);

        try {
            $response = $this->client->request('GET', $url);
            $content = $response->getContent();
            $this->setDayTypeFromContentAndPerYear($content, $year);
        } catch (
        ClientExceptionInterface | ServerExceptionInterface |
        RedirectionExceptionInterface | TransportExceptionInterface | Exception $e) {
            throw new CalendarApiException(JsonResponse::HTTP_SERVICE_UNAVAILABLE, $e->getMessage());
        }
    }

    private function setDayTypeFromContentAndPerYear($content, $year)
    {
        $data = new SimpleXMLElement($content);

        foreach ($data->days->day as $day) {
            $dayTypeKey = '';
            $dayTypeValue = '';
            foreach ($day->attributes() as $attribute => $value) {
                $value = (string) $value;

                if ($attribute == self::RESPONSE_DATE_ATTRIBUTE_NAME) {
                    $dayTypeKey = $value;
                }

                if ($attribute == self::RESPONSE_TYPE_ATTRIBUTE_NAME) {
                    switch ($value) {
                        case self::RESPONSE_DAY_IS_HOLIDAY:
                            $dayTypeValue = self::DAY_IS_HOLIDAY;
                            break;
                        case self::RESPONSE_DAY_IS_WORKING_BUT_SHORT:
                            $dayTypeValue = self::DAY_IS_WORKING_BUT_SHORT;
                            break;
                        default:
                            $dayTypeValue = self::DAY_IS_WORKING;
                            break;
                    }
                }

                if ($this->isKeyAndValueFound($dayTypeKey, $dayTypeValue)) {
                    break;
                }
            }

            $this->dayType[$year][$dayTypeKey] = $dayTypeValue;
        }
    }

    private function isKeyAndValueFound(string $key, string $value): bool
    {
        return (!empty($key) && !empty($value));
    }

    private function getDayType(Day $day): int
    {
        $year = $day->getYear();
        if (empty($this->dayType[$year])) {
            $this->fetchDataFromAPI($year);
        }

        $month = $day->getMonth();
        $dayNumber = $day->getDayNumber();
        $key = $month . '.' . $dayNumber;

        if (empty($this->dayType[$year][$key])) {
            $dayOfWeek = $day->getDayOfWeek();
            if (in_array($dayOfWeek, self::WEEKEND_DAYS)) {
                return self::DAY_IS_HOLIDAY;
            }

            return self::DAY_IS_WORKING;
        }

        return $this->dayType[$year][$key];
    }

    public function isDayWorkingButShort(Day $day): bool
    {
        return $this->getDayType($day) === self::DAY_IS_WORKING_BUT_SHORT;
    }
}
