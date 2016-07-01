<?php

namespace AppBundle\Services;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;

class KisioApiCaller
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * Caller constructor.
     */
    public function __construct()
    {
        $this->httpClient = new Client('http://par-vm191.srv.canaltp.fr/kisiowall-api');
    }

    public function getAverageResponseTime()
    {
        $volume = $this->httpClient->get('volume_call')->send()->json();
        $slices = $volume['metric_data']['metrics'][0]['timeslices'];
        $responseTimes = [];
        foreach ($slices as $slice) {
            $date = \DateTime::createFromFormat(\DateTime::ISO8601, $slice['from'])->format('H:i');
            $responseTimes[$date] = $slice['values']['average_response_time'];
        }
        return [
            'min' => min($responseTimes),
            'max' => max($responseTimes),
            'responseTimes' => $responseTimes,
        ];
    }

    public function getNumberOfCalls()
    {
        $calls = $this->httpClient->get('volume_call_summarize')->send()->json();
        return $calls['metric_data']['metrics'][0]['timeslices'][0]['values']['call_count'];
    }

    public function getNumberOfErrors()
    {
        $calls = $this->httpClient->get('volume_errors')->send()->json();
        return $calls['metric_data']['metrics'][0]['timeslices'][0]['values']['error_count'];
    }

    public function getTotalNavitiaCalls()
    {
        $response = $this->httpClient->get('total_call')->send()->json();
        return $response['metric_data']['metrics'][0]['timeslices'][0]['values']['call_count'];
    }

    public function getActiveUsers()
    {
        return $this->httpClient->get('active_users')->send()->json();
    }
}
