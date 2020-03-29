<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Mosque;
use AppBundle\Service\Cloudflare;
use AppBundle\Service\RequestService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class MosqueCacheListener implements EventSubscriber
{
    /**
     * @var Cloudflare
     */
    private $cloudflare;

    /**
     * @var RequestService
     */
    private $requestService;

    public function __construct(Cloudflare $cloudflare, RequestService $requestService)
    {
        $this->cloudflare = $cloudflare;
        $this->requestService = $requestService;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postRemove,
            Events::preUpdate,
        ];
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->purgeCloudFlareCache($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->purgeCloudFlareCache($args);
    }

    private function purgeCloudFlareCache(LifecycleEventArgs $args)
    {
        if ($this->requestService->isLocal()) {
            return;
        }

        $mosque = $args->getObject();
        if (!$mosque instanceof Mosque) {
            return;
        }

        $this->cloudflare->purgeCache($mosque);
    }

}
