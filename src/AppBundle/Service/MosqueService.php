<?php

namespace AppBundle\Service;

use AppBundle\Entity\Mosque;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Handler\AbstractHandler;

class MosqueService
{

    const ELASTIC_INDEX = "app";
    const ELASTIC_TYPE = "mosque";

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var AbstractHandler
     */
    private $vichUploadHandler;

    /**
     * @var MailService
     */
    private $mailService;

    /**
     * @var PrayerTime
     */
    private $prayerTime;

    /**
     * @var Client
     */
    private $elasticClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        AbstractHandler $vichUploadHandler,
        MailService $mailService,
        PrayerTime $prayerTime,
        Client $elasticClient,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->vichUploadHandler = $vichUploadHandler;
        $this->mailService = $mailService;
        $this->prayerTime = $prayerTime;
        $this->elasticClient = $elasticClient;
        $this->logger = $logger;
    }

    public function listUUID(int $page, int $size = 20)
    {
        $query = [
            "_source" => ["uuid"],
            "sort" => "id",
            "size" => $size,
            "from" => ($page - 1) * $size
        ];

        $mosques = $this->doSearch($query);

        $result = [];
        if (isset($mosques["hits"]["hits"])) {
            foreach ($mosques["hits"]["hits"] as $hit) {
                $result[] = $hit["_source"]["uuid"];
            }
        }

        return $result;
    }

    /**
     * @param string $word
     * @param string $lat
     * @param string $lon
     * @param int    $page
     * @param int    $size
     * @param bool   $stringify
     *
     * @return array
     */
    public function search($word, $lat, $lon, int $page, int $size = 20, bool $stringify = false)
    {
        $word = trim($word);
        if (strlen($word) < 2 && (empty($lat) || empty($lon))) {
            return [];
        }

        if (!empty($word)) {

            $query = [
                "query" => [
                    "query_string" => [
                        "query" => "*$word*",
                        "fields" => ["name^3", "localisation^2", "associationName"],
                        "default_operator" => "OR"
                    ]
                ]
            ];
        }

        if (!empty($lat) && !empty($lon)) {
            $query = [
                'sort' => [
                    '_geo_distance' => [
                        'location' => ["$lat,$lon"]
                    ]
                ]
            ];
        }

        $query["size"] = $size;
        $query["from"] = ($page - 1) * $size;

        $mosques = $this->doSearch($query);

        $result = [];
        if (isset($mosques["hits"]["hits"])) {
            foreach ($mosques["hits"]["hits"] as $hit) {
                $mosque = $hit["_source"];
                unset($mosque["location"]);
                if (isset($hit["sort"])) {
                    $mosque["proximity"] = (int)$hit["sort"][0];
                }

                if ($stringify) {
                    $this->stringify($mosque);
                }

                $result[] = $mosque;
            }
        }

        return $result;
    }

    /**
     * get similar mosques
     *
     * @param Mosque $mosque
     *
     * @return array
     */
    public function getSimilarByLocalization(Mosque $mosque)
    {

        $query = [
            "_source" => ["id"],
            "size" => 3,
            "min_score" => 10,
            "query" => [
                "query_string" => [
                    "query" => $mosque->getLocalisation(),
                    "fields" => ["localisation"],
                ]
            ]
        ];

        $mosques = $this->doSearch($query);
        $similar = [];
        if (isset($mosques["hits"]["hits"])) {
            foreach ($mosques["hits"]["hits"] as $hit) {
                $similar[] = $hit["_source"]["id"];
            }
        }

        return $similar;
    }

    public function searchV1($word, $lat, $lon, $page)
    {
        return $this->search($word, $lat, $lon, $page, 20, true);
    }

    public function searchV2($word, $lat, $lon, $page)
    {
        return $this->search($word, $lat, $lon, $page, 10);
    }

    public function elasticDropIndex()
    {
        try {
            $this->elasticClient->delete(self::ELASTIC_INDEX);
        } catch (\Exception $e) {
            $this->logger->error("Elastic: Can't drop index " . self::ELASTIC_INDEX, [$e->getTrace()]);
        }
    }

    public function elasticCreate(Mosque $mosque)
    {
        if (!$mosque->isElasticIndexable()) {
            return;
        }

        $mosque = $this->serializer->normalize($mosque, 'json', ["groups" => ["elastic"]]);

        $uri = sprintf("%s/%s/%s", self::ELASTIC_INDEX, self::ELASTIC_TYPE, $mosque["id"]);
        $this->elasticClient->post($uri, [
            "json" => $mosque
        ]);

        try {
            $this->elasticClient->post($uri, [
                "json" => $mosque
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Elastic: Can't post on $uri", [$mosque, $e->getTrace()]);
        }
    }

    public function elasticDelete(Mosque $mosque)
    {
        if (!$mosque->isMosque()) {
            return;
        }

        try {
            $uri = sprintf("%s/%s/%s", self::ELASTIC_INDEX, self::ELASTIC_TYPE, $mosque->getId());
            $this->elasticClient->delete($uri);
        } catch (\Exception $e) {
            $this->logger->error("Elastic: Can't delete $uri", [$e->getTrace()]);
        }
    }

    public function initElasticIndex()
    {
        try {
            $this->elasticClient->put(self::ELASTIC_INDEX, [
                "json" => [
                    "settings" => [
                        "analysis" => [
                            "analyzer" => [
                                "default" => [
                                    "tokenizer" => "standard",
                                    "filter" => ["lowercase", "asciifolding"],
                                ]
                            ]
                        ]

                    ],
                    "mappings" => [
                        self::ELASTIC_TYPE => [
                            "properties" => [
                                "location" => [
                                    "type" => "geo_point"
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Elastic: init index " . self::ELASTIC_INDEX, [$e->getTrace()]);
        }
    }

    public function elasticBulkPopulate(\Iterator $mosques)
    {
        $payload = [];

        foreach ($mosques as $mosque) {
            if (!$mosque->isElasticIndexable()) {
                continue;
            }

            $payload[] = json_encode([
                "index" => [
                    "_index" => self::ELASTIC_INDEX,
                    "_type" => self::ELASTIC_TYPE,
                    "_id" => $mosque->getId()
                ]
            ]);
            $payload[] = $this->serializer->serialize($mosque, 'json', ["groups" => ["elastic"]]);
        }

        try {
            $this->elasticClient->post("_bulk", [
                "body" => implode("\n", $payload) . "\n",
                "headers" => ["content-type" => "application/json"],
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Elastic: Can't bulk insert");
        }

    }

    /**
     * @param Mosque $mosque
     *
     * @throws @see MailService
     */
    public function validate(Mosque $mosque)
    {
        $mosque->setStatus(Mosque::STATUS_VALIDATED);
        $this->vichUploadHandler->remove($mosque, 'justificatoryFile');
        $mosque->setJustificatoryFile(null);
        $mosque->setJustificatory(null);
        $this->em->flush();
        $this->mailService->mosqueValidated($mosque);
    }

    /**
     * @param Mosque $mosque
     *
     * @throws @see MailService
     */
    public function check(Mosque $mosque)
    {
        $mosque->setStatus(Mosque::STATUS_CHECK);
        $this->em->flush();
        $this->mailService->checkMosque($mosque);
    }

    /**
     * @param Mosque $mosque
     *
     * @throws @see MailService
     */
    public function duplicated(Mosque $mosque)
    {
        $mosque->setStatus(Mosque::STATUS_DUPLICATED);
        $this->em->flush();
        $this->mailService->duplicatedMosque($mosque);
    }

    /**
     * @param Mosque $mosque
     *
     * @throws @see MailService
     */
    public function rejectScreenPhoto(Mosque $mosque)
    {
        $mosque->setStatus(Mosque::STATUS_VALIDATED);
        $this->vichUploadHandler->remove($mosque, 'file3');
        $mosque->setImage3(null);
        $mosque->setFile3(null);
        $this->em->flush();
        $this->mailService->rejectScreenPhoto($mosque);
    }

    /**
     * @param $query
     *
     * @return array
     */
    private function doSearch($query)
    {
        try {
            $uri = sprintf("%s/%s/_search", self::ELASTIC_INDEX, self::ELASTIC_TYPE);
            $mosques = $this->elasticClient->get($uri, [
                "json" => $query
            ]);

            return json_decode($mosques->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->logger->error("Elastic: query KO on $uri", [$query, $e->getMessage()]);

        }

        return [];
    }

    private function stringify(&$mosque)
    {
        foreach ($mosque as $k => $v) {
            if (is_null($v)) {
                continue;
            }
            if (is_bool($v)) {
                $mosque[$k] = $v === true ? "1" : "0";
                continue;
            }

            settype($mosque[$k], "string");
        }
    }

}
