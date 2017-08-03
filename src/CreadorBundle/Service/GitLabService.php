<?php
/**
 * Created by PhpStorm.
 * User: gfonticelli
 * Date: 01/08/17
 * Time: 13:48
 */

namespace CreadorBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class GitLabService
{

    /**
     * @var
     */
    protected $client;

    /**
     * CreatorService constructor.
     */
    public function __construct($token, $url)
    {
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

}