<?php

namespace AppBundle\Service;

use AppBundle\Entity\Mosque;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class Cloudflare
{
    /**
     * @var Client
     */
    private $cloudflareClient;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $zoneId;

    /**
     * @var string
     */
    private $site;
    /**
     * @var array
     */
    private $languages;

    public function __construct(Client $cloudflareClient, LoggerInterface $logger, $zoneId, $site, array $languages)
    {
        $this->cloudflareClient = $cloudflareClient;
        $this->logger = $logger;
        $this->zoneId = $zoneId;
        $this->site = $site;
        $this->languages = $languages;
    }

    public function purgeCache(Mosque $mosque)
    {
        $files = [];

        foreach ($this->languages as $language) {
            $files[] = sprintf("%s/%s/m/%s", $this->site, $language, $mosque->getSlug());
        }

        try {
            $this->cloudflareClient->post("v4/zones/{$this->zoneId}/purge_cache", [
                "json" => [
                    "files" => $files
                ]
            ]);
        } catch (\Exception $exception) {
            $this->logger->error("Can't purge cloudflare cache", [
                "exception" => $exception->getMessage(),
                "mosque" => $mosque->getId(),
            ]);
        }
    }
}
