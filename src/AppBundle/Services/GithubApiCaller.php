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

    private $authent = ['Authorization' => 'token 6e140d817faf23260eda1386423d97af8bd8d921'];

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
//            ['headers' => ['Authorization' => 'token 6e140d817faf23260eda1386423d97af8bd8d921']]
        );
    }

    public function getReposStats()
    {
        $result = $this->memcache->get('someKey');
        if($result) {
            var_dump($result);die;
            return $result;
        }
        $nbPulls = 0;
        $nbRepos = 0;
        $next = 'orgs/CanalTP/repos';
        do {
//            var_dump($next);
            $response = $this->httpClient->get($next, $this->authent)->send();
            preg_match('/^<https:\/\/api.github.com(.+)>; rel="next", .+; rel="last"/', $response->getHeader('Link'), $matches);
//            var_dump($matches);
            $repos = $response->json();
            $nbRepos+=count($repos);
            $next = !empty($matches[1]) ? $matches[1] :null;
//            var_dump($next);
            foreach ($repos as $repo) {
                $pullsUrl = 'repos/CanalTP/' . $repo['name'] . '/pulls';
//                var_dump($pullsUrl);
                $pulls = $this->httpClient->get($pullsUrl, $this->authent)->send()->json();
//                var_dump($pulls);
//                var_dump(count($pulls));
                $nbPulls += count($pulls);
            }
//            var_dump($next);
        }
        while(!is_null($next));
        $result = [
            'nbPulls' => $nbPulls,
            'nbRepos' => $nbRepos,
        ];
        $this->memcache->set('getReposStats', $result, 0, 60);
        echo 'fin';
        var_dump($result);die;
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
