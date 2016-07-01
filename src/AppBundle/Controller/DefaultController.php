<?php

namespace AppBundle\Controller;

use AppBundle\Services\GithubApiCaller;
use AppBundle\Services\KisioApiCaller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @var GithubApiCaller
     */
    private $githubApiService;

    /**
     * @var KisioApiCaller
     */
    private $kisioWallApiService;

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $this->kisioWallApiService = $this->get('kisiowall.caller.service');

        $calls = $this->kisioWallApiService->getNumberOfCalls();
        $errors = $this->kisioWallApiService->getNumberOfErrors();
        $totalCalls = $this->kisioWallApiService->getTotalNavitiaCalls();
        $activeUsers = $this->kisioWallApiService->getActiveUsers();
        $downloads = $this->kisioWallApiService->getDownloadsByStore();
        
        $percent = (1 - $errors / $calls) * 100;
        return $this->render('default/index.html.twig', [
            'calls' => $calls,
            'errorsPercent' => $percent,
            'totalCalls' => $totalCalls,
            'activeUsers' => $activeUsers,
            'downloads' => $downloads,
        ]);
    }
        
    /**
     * @Route("/tech", name="tech")
     */
    public function techAction(Request $request)
    {
        $now = new \DateTime();
        $beginHours = 8;
        $dayInHours = 10;
        $nbCoffees = 190;
        $hoursMinutes = intval($now->format('H')) * 60 + intval($now->format('i'));
        
        $this->githubApiService = $this->get('github.caller.service');
        $nbCoffeeRealTime = round(($hoursMinutes - ($beginHours * 60)) * ($nbCoffees / (60 * $dayInHours)));
        $occupiedRooms = $this->get('rooms.caller.service')->getCurrentNbMeetings();
        $reposStats = $this->githubApiService->getReposStats();
        
        return $this->render('default/tech.html.twig', [
            'occupiedRooms' => $occupiedRooms,
            'nbCoffeeRealTime' => $nbCoffeeRealTime,
            'reposStats' => $reposStats
        ]);
    }
    
    /**
     * @Route("/home2", name="homepage2")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index2action(Request $request)
    {
        
        $this->kisioWallApiService = $this->get('kisiowall.caller.service');
        
        $now = new \DateTime();
        $beginHours = 8;
        $dayInHours = 10;
        $nbCoffees = 190;
        $hoursMinutes = intval($now->format('H')) * 60 + intval($now->format('i'));
        
        $nbCoffeeRealTime = round(($hoursMinutes - ($beginHours * 60)) * ($nbCoffees / (60 * $dayInHours)));
        
        $responseTimes = $this->kisioWallApiService->getAverageResponseTime();
        $calls = $this->kisioWallApiService->getNumberOfCalls();
        $errors = $this->kisioWallApiService->getNumberOfErrors();
        $totalCalls = $this->kisioWallApiService->getTotalNavitiaCalls();
        $activeUsers = $this->kisioWallApiService->getActiveUsers();
        $downloads = $this->kisioWallApiService->getDownloadsByStore();
        $occupiedRooms = $this->get('rooms.caller.service')->getCurrentNbMeetings();
        
        
        $percent = (1 - $errors / $calls) * 100;
        return $this->render('default/index2.html.twig', [
            'nbCoffeeRealTime' => $nbCoffeeRealTime,
            'responseTimes' => json_encode($responseTimes),
            'calls' => $calls,
            'errorsPercent' => $percent,
            'totalCalls' => $totalCalls,
            'activeUsers' => $activeUsers,
            'occupiedRooms' => $occupiedRooms,
            'downloads' => $downloads,
        ]);
    }
}
