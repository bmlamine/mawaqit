<?php

namespace AppBundle\Controller\API\V2;

use AppBundle\Service\YamlLoader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api/2.0/support", options={"i18n"="false"})
 */
class SupportController extends Controller
{

    /**
     * @Cache(public=true, maxage="86400", smaxage="86400", expires="+86400 sec")
     * @Route("")
     * @Method("GET")
     * @param Request    $request
     * @param YamlLoader $yamlLoader
     *
     * @return Response
     */
    public function supportAction(Request $request, YamlLoader $yamlLoader)
    {
        $country = $request->query->get("country", "OC");

        $support = $yamlLoader->getSupport();

        if (!isset($support[$country])) {
            $country = "OC";
        }

        $phone = substr(array_rand($support[$country]), 2);

        return new JsonResponse([
            "mobile" => [
                "whatsapp" => "whatsapp://send?phone=$phone",
            ],
            "web" => [
                "whatsapp" => "https://wa.me/$phone",
            ]
        ]);
    }

}
