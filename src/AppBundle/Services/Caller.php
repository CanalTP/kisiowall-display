<?php

namespace AppBundle\Services;

use Guzzle\Http\Client;

class Caller
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
        $this->githubClient = new Client('https://api.github.com/orgs/CanalTP');
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

    public function getReposNumber()
    {
        $headers = ['Authorization' => 'token 8d2502554ab117b88d8ed2f4f16a8f47111704ac'];
        $repos = $this->githubClient->get('repos', $headers)->send();
        preg_match('/^.+; rel="next", <https:\/\/api.github.com(.+)>; rel="last"$/', $repos->getHeader('Link'), $matches);
        $last = $matches[1];
        list(, $page) = explode('page=', $last);
        $count = count($repos->json()) * ($page - 1);
        $repos = $this->githubClient->get($last, $headers)->send();
        $count += count($repos->json());
        return $count;
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
}
