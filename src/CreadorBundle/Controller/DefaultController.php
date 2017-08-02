<?php

namespace CreadorBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
    const DELETE_ME = 'deleteMe';

    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $configCreatorService = $this->get('creator.configCreatorService');
        $dictionary = json_encode($configCreatorService->get_BaseDictionaryESConfig());

        return $this->render('CreadorBundle::yml.html.twig', ['dictionary' => $dictionary]);
    }

    /**
     * @Route("/create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $creatorService = $this->get('creator.creatorService');
        $creatorService->createDashbord($request);

        $filePath = $this->get('kernel')->getRootDir().'/generated/dashboard.yml';
        $content = file_get_contents($filePath);

        return $this->render('CreadorBundle::show.html.twig', ['dashboardYML' => trim($content)]);
    }
}
