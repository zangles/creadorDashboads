<?php

namespace CreadorBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
    const DELETE_ME = 'deleteMe';

    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $gitLabService = $this->get('creator.gitLabService');

        $projects = $gitLabService->getAllProjects();

        $configCreatorService = $this->get('creator.configCreatorService');
        $dictionary = json_encode($configCreatorService->get_BaseDictionaryESConfig());

        return $this->render('CreadorBundle::yml.html.twig', ['dictionary' => $dictionary, 'projects' => $projects]);
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

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    /**
     * @Route("/test")
     * @Method("GET")
     */
    public function testAction()
    {

        $filePath = $this->get('kernel')->getRootDir().'/generated/dashboard.yml';
        $yml = json_encode(Yaml::parse(file_get_contents($filePath)));

        $configCreatorService = $this->get('creator.configCreatorService');
        $dictionary = json_encode($configCreatorService->get_BaseDictionaryESConfig());

        return $this->render('CreadorBundle::yml.html.twig', ['dictionary' => $dictionary, 'yml'  => $yml]);

    }
}
