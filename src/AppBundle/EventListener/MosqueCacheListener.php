<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Mosque;
use AppBundle\Service\Cloudflare;
use AppBundle\Service\RequestService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
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
        $mosque = $args->getObject();
        if (!$mosque instanceof Mosque) {
            return;
        }

        $this->purgeCloudFlareCache($mosque, $mosque->getUpdated());
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $mosque = $args->getObject();
        if (!$mosque instanceof Mosque) {
            return;
        }

        $updated = $args->getOldValue("updated");
        $this->purgeCloudFlareCache($mosque, $updated);
    }

    /**
     * @param Mosque    $mosque
     * @param \DateTime $updated
     */
    private function purgeCloudFlareCache(Mosque $mosque, \DateTime $updated)
    {
        if ($this->requestService->isLocal()) {
            return;
        }

        $this->cloudflare->purgeCache($mosque, $updated);
    }

}
