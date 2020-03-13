<?php

namespace AppBundle\Controller\API\V2;

use AppBundle\Service\YamlLoader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * @Route("/api/2.0/support", options={"i18n"="false"})
 */
class SupportController extends Controller
{

    /**
     * @Route("")
     * @Cache(public=true, maxage="86400", smaxage="86400", expires="+86400 sec")
     * @Method("GET")
     * @param Request $request
     * @param YamlLoader $yamlLoader
     * @return Response
     */
    public function randomAction(Request $request, YamlLoader $yamlLoader)
    {
        $country = $request->query->get("country", "OC");

        $support = $yamlLoader->getSupport();

        if(!isset($support[$country])){
            $country = "OC";
        }

        $phone = array_rand($support[$country]);

        return new JsonResponse([
            "deeplink"=> "whatsapp://send?phone=$phone"
        ]);
    }

}
