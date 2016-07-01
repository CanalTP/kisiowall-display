<?php

namespace AppBundle\Services;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Lsw\MemcacheBundle\Cache\MemcacheInterface;

class GithubApiCaller
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var MemcacheInterface
     */
    private $memcache;

    /**
     * Caller constructor.
     */
    public function __construct(MemcacheInterface $memcache, $token)
    {
        $this->authent = ['Authorization' => 'token ' . $token];
        $this->memcache = $memcache;
        $this->httpClient = new Client(
            'https://api.github.com'
        );
    }

    public function getReposStats()
    {
        $result = $this->memcache->get(__METHOD__);
        if ($result) {
            return $result;
        }
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
                $pulls = $this->httpClient->get($pullsUrl, $this->authent)->send()->json();
                foreach ($pulls as $pr) {
                    if (isset($nbPulls[$pr['state']])) {
                        $nbPulls[$pr['state']]++;
                    } else {
                        $nbPulls[$pr['state']] = 1;
                    }
                }
            }
        } while (!is_null($next));
        $result = [
            'nbPulls' => $nbPulls,
            'nbRepos' => $nbRepos,
        ];
        $this->memcache->set(__METHOD__, $result, 0, 3600);

        return $result;
    }
}
