<?php

namespace AppBundle\Services;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;

class GithubApiCaller
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * Caller constructor.
     */
    public function __construct($token)
    {
        $this->authent = ['Authorization' => 'token ' . $token];
        $this->httpClient = new Client(
            'https://api.github.com'
        );
    }

    public function getReposStats()
    {
        $nbPulls = [];
        $nbRepos = 0;
        $next = 'orgs/CanalTP/repos';
        do {
            $response = $this->httpClient->get($next, $this->authent)->send();
            preg_match('/^<https:\/\/api.github.com(.+)>; rel="next", .+; rel="last"/', $response->getHeader('Link'), $matches);
            $repos = $response->json();
            $nbRepos += count($repos);
            $next = !empty($matches[1]) ? $matches[1] : null;
            foreach ($repos as $repo) {
                $pullsUrl = 'repos/CanalTP/' . $repo['name'] . '/pulls?state=all';
                try {
                    $pulls = $this->httpClient->get($pullsUrl, $this->authent)->send()->json();
                    foreach ($pulls as $pr) {
                        if (isset($nbPulls[$pr['state']])) {
                            $nbPulls[$pr['state']]++;
                        } else {
                            $nbPulls[$pr['state']] = 1;
                        }
                    }
                }
                catch (ClientErrorResponseException $exception) {

                }
            }
        } while (!is_null($next));
        $result = [
            'nbPulls' => $nbPulls,
            'nbRepos' => $nbRepos,
        ];

        return $result;
    }
}
