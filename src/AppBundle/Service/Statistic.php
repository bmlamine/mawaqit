<?php

namespace AppBundle\Service;

use AppBundle\Entity\Mosque;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class Statistic
{

    const ELASTIC_INDEX = "statistic";
    const ELASTIC_TYPE = "mosque";

    /**
     * @var Client
     */
    private $elasticClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Client $elasticClient, LoggerInterface $logger)
    {
        $this->elasticClient = $elasticClient;
        $this->logger = $logger;
    }

    public function incrementFavoriteCounter(Mosque $mosque)
    {

        $uri = sprintf("%s/%s/%s", self::ELASTIC_INDEX, self::ELASTIC_TYPE, $mosque->getId());

        if ($this->exist($mosque)) {
            // if exist we increment couter
            try {
                $this->elasticClient->post("$uri/_update", [
                    "json" => [
                        "script" => [
                            "inline" => "ctx._source.mobileFavorite++"
                        ]
                    ]
                ]);
            } catch (\Exception $e) {
                $this->logger->critical("Elastic: Can't post on $uri", [$e->getMessage()]);
            }

            return;
        }

        // if not exist we init couter
        try {
            $this->elasticClient->post("$uri", [
                "json" => [
                    "mobileFavorite" => 0
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->critical("Elastic: Can't post on $uri", [$e->getMessage()]);
        }
    }

    public function decrementFavoriteCounter(Mosque $mosque)
    {
        if ($this->exist($mosque)) {
            $uri = sprintf("%s/%s/%s/_update", self::ELASTIC_INDEX, self::ELASTIC_TYPE, $mosque->getId());
            try {
                $this->elasticClient->post("$uri", [
                    "json" => [
                        "script" => [
                            "inline" => "ctx._source.mobileFavorite--"
                        ]
                    ]
                ]);
            } catch (\Exception $e) {
                $this->logger->critical("Elastic: Can't post on $uri", [$e->getMessage()]);
            }
        }
    }

    private function exist(Mosque $mosque): bool
    {
        $uri = sprintf("%s/%s/%s", self::ELASTIC_INDEX, self::ELASTIC_TYPE, $mosque->getId());

        try {
            $response = $this->elasticClient->head($uri);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

}
