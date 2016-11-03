<?php

namespace AppBundle\Services;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Lsw\MemcacheBundle\Cache\MemcacheInterface;

class KisioApiCaller
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var MemcacheInterface
     */
    private $memcache;
    private $twitterKey;
    private $twitterSecret;

    /**
     * @param MemcacheInterface $memcache
     */
    public function __construct(MemcacheInterface $memcache, $twitterKey, $twitterSecret)
    {
        $this->memcache = $memcache;
        $this->twitterKey = $twitterKey;
        $this->twitterSecret = $twitterSecret;
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
        try {
            return $this->httpClient->get('active_users')->send()->json();
        }
        catch (ServerErrorResponseException $exception) {
            return 'N/A';
        }
    }

    public function getTotalRegions()
    {
        try {
            return $this->httpClient->get('total_regions')->send()->json();
        }
        catch (ServerErrorResponseException $exception) {
            return 'N/A';
        }
    }

    public function getDataUpdate()
    {
        try {
            return $this->httpClient->get('weekly_data_update')->send()->json();
        }
        catch (ServerErrorResponseException $exception) {
            return 'N/A';
        }
    }

    public function getDownloadsByStore()
    {
        $result = $this->memcache->get(__METHOD__);
        if ($result) {
            return $result;
        }
        else {
            $result = $this->httpClient->get('downloads_by_store')->send()->json();
            $this->memcache->set(__METHOD__, $result, 0, 3600);
            return $result;
        }
    }

    public function getTwitter()
    {
        // Set here your twitter application tokens
        $settings = array(
            'consumer_key' => $this->twitterKey,
            'consumer_secret' => $this->twitterSecret,

            // These two can be left empty since we'll only read from the Twitter's 
            // timeline
            'oauth_access_token' => '',
            'oauth_access_token_secret' => '',
        );

// Set here the Twitter account from where getting latest tweets
        $screen_name = 'KisioDigital';

// Get timeline using TwitterAPIExchange
        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
        $getfield = "?screen_name={$screen_name}";
        $requestMethod = 'GET';

        $twitter = new \TwitterAPIExchange($settings);
        $user_timeline = $twitter
            ->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest();

        $user_timeline = json_decode($user_timeline);
        $displayResult = [];
        foreach ($user_timeline as $tweet) {
            $displayResult[] = $tweet->text;
        }
        return $displayResult;
    }
}
