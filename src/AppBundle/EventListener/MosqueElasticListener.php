<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Configuration;
use AppBundle\Entity\Mosque;
use AppBundle\Service\MosqueService;
use AppBundle\Service\RequestService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;

class MosqueElasticListener implements EventSubscriber
{
    /**
     * @var MosqueService
     */
    private $mosqueService;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var RequestService
     */
    private $requestService;

    public function __construct(
        MosqueService $mosqueService,
        EntityManagerInterface $em,
        RequestService $requestService
    )
    {
        $this->mosqueService = $mosqueService;
        $this->em = $em;
        $this->requestService = $requestService;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
            Events::preUpdate,
        ];
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        if ($this->requestService->isLocal()) {
            return;
        }

        $entity = $args->getObject();

        if (!$entity instanceof Mosque) {
            return;
        }

        $this->mosqueService->elasticDelete($entity);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        if ($this->requestService->isLocal()) {
            return;
        }

        $entity = $args->getObject();

        if (!$entity instanceof Mosque && !$entity instanceof Configuration) {
            return;
        }

        if ($entity instanceof Configuration) {
            $entity = $this->em->getRepository(Mosque::class)->findOneBy([
                'configuration' => $entity
            ]);

            if (!$entity instanceof Mosque) {
                return;
            }
        }

        // Remove the mosque from ES if no longer indexable
        if (!$entity->isElasticIndexable()) {
            $this->mosqueService->elasticDelete($entity);
            return;
        }

        $this->mosqueService->elasticCreate($entity);

    }

}
