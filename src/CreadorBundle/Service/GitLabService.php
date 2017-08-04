<?php
/**
 * Created by PhpStorm.
 * User: gfonticelli
 * Date: 01/08/17
 * Time: 13:48
 */

namespace CreadorBundle\Service;

use Gitlab\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;

class GitLabService
{

    /**
     * @var
     */
    protected $client;

    protected $token;

    protected $gitLabUrl;
    /**
     * CreatorService constructor.
     */
    public function __construct($token, $url)
    {
        $this->gitLabUrl = $url;
        $this->token = $token;

        $this->client = \Gitlab\Client::create($url)
            ->authenticate($token, \Gitlab\Client::AUTH_URL_TOKEN);
    }

    public function getAllProjects()
    {
        $page = 1;
        $projectsResponse = $this->client->projects()->all(['per_page'=>100, 'page'=>$page]);
        $projects = [];
        while(count($projectsResponse) > 0 ) {
            $projects = array_merge($projects, $projectsResponse);

            $page++;
            $projectsResponse = $this->client->projects()->all(['per_page'=>100, 'page'=>$page]);
        }

        return $projects;
    }

    public function getFile($projectId, $filePath, $ref = 'master')
    {
        $path = $this->gitLabUrl . "/api/v3/projects/" . $projectId . '/repository/files?private_token=' . $this->token . '&file_path=' . rawurlencode($filePath) . "&ref=" . $ref;

        try {
            $response = $this->client->getHttpClient()->get($path);
        } catch (RuntimeException $e) {
            return $e;
        }

        return \GuzzleHttp\json_decode($response->getBody()->getContents());
    }

    public function getFileContent($projectId, $filePath, $ref = 'master')
    {
        $file = $this->getFile($projectId,$filePath,$ref);

        if ($file instanceof RuntimeException) {
            return $file;
        } else {
            return base64_decode($file->content);
        }
    }

}