<?php

namespace AppBundle\Services;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;

class AvailableRoomsCaller
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
        $this->httpClient = new Client(
            'http://par-vm241.srv.canaltp.fr:8888'
        );
    }

    public function getCurrentNbMeetings()
    {
        return $this->httpClient->get('booking/rooms/occupied_count')->send()->json();
    }
}
