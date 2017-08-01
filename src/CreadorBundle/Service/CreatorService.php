<?php
/**
 * Created by PhpStorm.
 * User: gfonticelli
 * Date: 01/08/17
 * Time: 13:48
 */

namespace CreadorBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class CreatorService
{

    /**
     * @var ConfigCreatorService
     */
    protected $configCreatorService;
    /**
     * CreatorService constructor.
     */
    public function __construct($configCreatorService)
    {
        $this->configCreatorService = $configCreatorService;
    }

    public function createDashbord(Request $request)
    {
        $this->configCreatorService->setRequestData($request);
        $this->configCreatorService->createDashboardYML();
    }

}