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

//    private $authent = ['Authorization' => 'token 6e140d817faf23260eda1386423d97af8bd8d921'];
//    private $authent = ['Authorization' => 'token 8d2502554ab117b88d8ed2f4f16a8f47111704ac'];
    private $authent = ['Authorization' => 'token 85b8d3da6b9f7407e806941ac69e305086bca2f6']; //vchabot

    /**
     * @var MemcacheInterface
     */
    private $memcache;

    /**
     * Caller constructor.
     */
    public function __construct(MemcacheInterface $memcache)
    {
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

    public function getReposNumber()
    {
        try {
            $repos = $this->httpClient->get('orgs/CanalTP/repos', $this->authent)->send();
            preg_match('/^.+; rel="next", <https:\/\/api.github.com(.+)>; rel="last"$/', $repos->getHeader('Link'), $matches);
            $last = $matches[1];
            list(, $page) = explode('page=', $last);
            $count = count($repos->json()) * ($page - 1);
            $repos = $this->httpClient->get($last)->send();
            $count += count($repos->json());
        } catch (ClientErrorResponseException $e) {
            $count = $e->getResponse()->getStatusCode();
        }
        return $count;
    }
}
