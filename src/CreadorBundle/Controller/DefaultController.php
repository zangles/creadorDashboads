<?php

namespace CreadorBundle\Controller;

use Gitlab\Exception\RuntimeException;
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
    public function indexAction(Request $request)
    {
        $projectId = $request->get('project');

        $gitLabService = $this->get('creator.gitLabService');
        $projects = $gitLabService->getAllProjects();

        $ymlParsed = '';
        $error = '';

        if (!is_null($projectId)) {
            $ymlContent = $gitLabService->getFileContent($projectId, 'app/config/dashboard.yml');
            if ($ymlContent instanceof RuntimeException) {
                $error = $ymlContent->getMessage();
            } else {
                $ymlParsed = Yaml::parse($ymlContent);
            }
        }

        $configCreatorService = $this->get('creator.configCreatorService');
        $dictionary = json_encode($configCreatorService->get_BaseDictionaryESConfig());

        return $this->render(
            'CreadorBundle::yml.html.twig',
            [
                'projectId' => $projectId,
                'dictionary' => $dictionary,
                'yml' => $ymlParsed,
                'projects' => $projects,
                'errors' => $error,
            ]
        );
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
     */
    public function testAction(Request $request)
    {

//        ldd($request);

        $projectId = $request->get('project');
        if (is_null($projectId)) {
            return $this->indexAction();
        } else {
            $gitLabService = $this->get('creator.gitLabService');
            $projects = $gitLabService->getAllProjects();

            $ymlContent = $gitLabService->getFileContent($projectId, 'app/config/dashboard.yml');
            if ($ymlContent instanceof RuntimeException) {
                $error = $ymlContent->getMessage();
                $ymlParsed = '{}';
            } else {
                $error = '';
                $ymlParsed = Yaml::parse($ymlContent);
            }


            $configCreatorService = $this->get('creator.configCreatorService');
            $dictionary = json_encode($configCreatorService->get_BaseDictionaryESConfig());


            return $this->render(
                'CreadorBundle::yml.html.twig',
                [
                    'projectId' => $projectId,
                    'dictionary' => $dictionary,
                    'yml' => $ymlParsed,
                    'projects' => $projects,
                    'errors' => $error,
                ]
            );
        }

    }
}
