<?php

namespace AppBundle\Controller\API\V1;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api/1.0.0/hadith", options={"i18n"="false"})
 */
class HadithController extends Controller
{

    /**
     * @Cache(public=true, maxage="300", smaxage="300", expires="+300 sec")
     * @Route("/random")
     * @Method("GET")
     * @param Request $request
     * @return Response
     */
    public function randomAction(Request $request)
    {
        return $this->forward("AppBundle:API\V2\Hadith:random", [
            "request" => $request,
        ]);
    }

}
