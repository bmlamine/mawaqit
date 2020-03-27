<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Mosque;
use AppBundle\Service\Cloudflare;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class MosqueCacheListener implements EventSubscriber
{
    /**
     * @var Cloudflare
     */
    private $cloudflare;

    public function __construct(Cloudflare $cloudflare)
    {
        $this->cloudflare = $cloudflare;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postRemove,
            Events::postUpdate,
        ];
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->purgeCloudFlareCache($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->purgeCloudFlareCache($args);
    }

    private function purgeCloudFlareCache(LifecycleEventArgs $args)
    {
        $mosque = $args->getObject();
        if (!$mosque instanceof Mosque) {
            return;
        }

        $this->cloudflare->purgeCache($mosque);
    }

}
