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
     * @Route("/", name="Home")
     */
    public function indexAction(Request $request)
    {
        $tweets = $this->get('kisiowall.caller.service')->getTwitter();

        $this->kisioWallApiService = $this->get('kisiowall.caller.service');

        $calls = $this->kisioWallApiService->getNumberOfCalls();
        $errors = $this->kisioWallApiService->getNumberOfErrors();
        $totalCalls = $this->kisioWallApiService->getTotalNavitiaCalls();
        $activeUsers = $this->kisioWallApiService->getActiveUsers();
        $downloads = $this->kisioWallApiService->getDownloadsByStore();
        $dataUpdate = $this->kisioWallApiService->getDataUpdate();
        $regions = $this->kisioWallApiService->getTotalRegions();

        $percent = (1 - $errors / $calls) * 100;
        return $this->render('default/index.html.twig', [
            'calls' => $calls,
            'errorsPercent' => $percent,
            'totalCalls' => $totalCalls,
            'activeUsers' => $activeUsers,
            'downloads' => $downloads,
            'dataUpdate' => $dataUpdate,
            'regions' => $regions,
            'tweets' => $tweets,
        ]);
    }
        
    /**
     * @Route("/tech", name="Geekzor")
     */
    public function techAction(Request $request)
    {
        $tweets = $this->get('kisiowall.caller.service')->getTwitter();
        $now = new \DateTime();
        $beginHours = 8;
        $dayInHours = 10;
        $nbCoffees = 190;
        $nbPascalHitsDays = 1614;
        $hoursMinutes = intval($now->format('H')) * 60 + intval($now->format('i'));
        
        $this->githubApiService = $this->get('github.caller.service');
        $nbCoffeeRealTime = round(($hoursMinutes - ($beginHours * 60)) * ($nbCoffees / (60 * $dayInHours)));
        $nbPascalHits = round(($hoursMinutes - ($beginHours * 60)) * ($nbPascalHitsDays / (60 * $dayInHours)));
        $occupiedRooms = $this->get('rooms.caller.service')->getCurrentNbMeetings();
        $reposStats = $this->githubApiService->getReposStats();
        
        return $this->render('default/tech.html.twig', [
            'occupiedRooms' => $occupiedRooms,
            'nbCoffeeRealTime' => $nbCoffeeRealTime,
            'reposStats' => $reposStats,
            'nbPascalHits' => $nbPascalHits,
            'tweets' => $tweets,
        ]);
    }

    /**
     * @Route("/stephane", name="Stephane")
     */
    public function stephaneAction(Request $request)
    {
        return $this->render('default/stephane.html.twig');
    }
}
