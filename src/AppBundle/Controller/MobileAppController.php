<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Mosque;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Response;

class MobileAppController extends Controller
{
    /**
     * @Route("/static/mobile/android/{mosque}/manifest", name="manifest", options={"i18n"="false"})
     * @Cache(public=true, maxage="86400", smaxage="86400", expires="+86400 sec")
     * @return JsonResponse
     */
    public function androidManifestAction(Mosque $mosque)
    {
        $manifest = [
            "short_name" => "Mawaqit",
            "name" => "Mawaqit",
            "icons" => [
                [
                    "src" => "/android-chrome-512x512.png",
                    "type" => "image/png",
                    "sizes" => "512x512"
                ], [
                    "src" => "/android-chrome-192x192.png",
                    "type" => "image/png",
                    "sizes" => "192x192"
                ]
            ],
            "start_url" => $this->generateUrl('mosque', ['slug' => $mosque->getSlug()]),
            "background_color" => "#286029",
            "theme_color" => "#286029",
            "display" => "standalone",
            "prefer_related_applications" => true,
            "related_applications" => [
                [
                    "platform" => "play",
                    "id" => "com.kanout.mawaqit"
                ]
            ],
        ];

        return new JsonResponse($manifest);
    }

    /**
     * @Route("/mobile/store-url", name="store_url", options={"i18n"="false"})
     * @return Response
     */
    public function getStoreUrlAction(\Mobile_Detect $mobileDretect)
    {
        $url = $this->getParameter("app_google_play_url");

        if ($mobileDretect->is('iOs')) {
            $url = $this->getParameter("app_apple_store_url");
        }

        return $this->redirect($url);
    }

}
