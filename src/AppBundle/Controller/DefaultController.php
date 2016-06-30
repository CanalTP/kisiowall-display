<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $reposNumber = $this->get('kisiowall.caller.service')->getReposNumber();
        $responseTimes = $this->get('kisiowall.caller.service')->getAverageResponseTime();
        $calls = $this->get('kisiowall.caller.service')->getNumberOfCalls();
        $errors = $this->get('kisiowall.caller.service')->getNumberOfErrors();
        $percent = ($errors / $calls) * 100;
        return $this->render('default/index.html.twig', [
            'reposNumber' => $reposNumber,
            'responseTimes' => json_encode($responseTimes),
            'calls' => $calls,
            'errorsPercent' => $percent,
        ]);
    }
}
