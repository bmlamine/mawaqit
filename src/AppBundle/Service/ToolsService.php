<?php

namespace AppBundle\Service;

use AppBundle\Entity\Mosque;
use AppBundle\Exception\GooglePositionException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;

class ToolsService
{


    /**
     * @var EntityManager
     */
    private $em;


    /**
     * @var GoogleService
     */
    private $googleService;

    public function __construct(ContainerInterface $container)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 120);
        $this->em = $container->get("doctrine.orm.entity_manager");
        $this->googleService = $container->get("app.google_service");
    }


    public function updateLocations($offset = 0)
    {
        $mosques = $this->em
            ->getRepository("AppBundle:Mosque")
            ->createQueryBuilder("m")
            ->where("m.city IS NOT NULL")
            ->andWhere("m.zipcode IS NOT NULL")
            ->andWhere("m.address IS NOT NULL")
            ->andWhere("m.country IS NOT NULL")
            ->andWhere("m.type = 'mosque'")
            ->setFirstResult($offset)
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        /**
         * @var $mosque Mosque
         */

        $editedMosques = [];
        foreach ($mosques as $mosque) {

            $latBefore = $mosque->getLatitude();
            $lonBefore = $mosque->getLongitude();

            $status = "OK";
            try {
                $gps = $this->googleService->getPosition($mosque);
                $mosque->setLatitude($gps->lat);
                $mosque->setLongitude($gps->lng);
                $this->em->persist($mosque);

            } catch (GooglePositionException $e) {
                $status = "KO";
            }

            $editedMosques[] = $mosque->getId() . ',' . $mosque->getName() . ',' . $mosque->getCity() . ',' . $mosque->getCountry() . ',' . $latBefore . ',' . $lonBefore . ',' . $mosque->getLatitude() . ',' . $mosque->getLongitude() . ',' . $status;
        }

        file_put_contents("/tmp/rapport_gps_$offset.csv", implode("\t\n", $editedMosques));
        $this->em->flush();
    }


    public function fixEuropeantimetables()
    {
        ini_set('memory_limit', '512M');
        $mosques = $this->em
            ->getRepository("AppBundle:Mosque")
            ->createQueryBuilder("m")
            ->innerJoin("m.configuration", "c")
            ->where("m.type = 'mosque'")
            ->andWhere("c.timezoneName like 'Europe%'")
            ->andWhere("c.sourceCalcul = 'calendar'")
            ->andWhere("c.dst = 0")
            ->getQuery()
            ->getResult();

        /**
         * @var $mosque Mosque
         */

        $editedMosques = [];
        foreach ($mosques as $mosque) {
            $cal = $mosque->getConfiguration()->getCalendar();
            if (!empty($cal) && is_array($cal)) {
                $editedMosques[] = $mosque->getId() . ',' .$mosque->getName() . ',' . $mosque->getCity() . ',' . $mosque->getCountry() . ',' . $mosque->getUser()->getEmail();
                for ($month = 2; $month <= 9; $month++) {
                    $firstDay=1; $lastDay=count($cal[$month]);
                    if($month === 2){
                        $firstDay=29;
                    }
                    if($month === 9){
                        $lastDay=26;
                    }

                    for ($day = $firstDay; $day <= $lastDay; $day++) {
                        for ($prayer = 1; $prayer <= count($cal[$month][$day]); $prayer++) {
                            if (!empty($cal[$month][$day][$prayer])) {
                                $cal[$month][$day][$prayer] = $this->removeOneHour($cal[$month][$day][$prayer]);
                            }
                        }
                    }
                }

                $mosque->getConfiguration()->setDst(2);
                $mosque->getConfiguration()->setCalendar($cal);

                if($mosque->isSuspended() && $mosque->getReason() === 'prayer_times_not_correct'){
                    $mosque->setStatus(Mosque::STATUS_VALIDATED);
                }
            }
        }

        file_put_contents("/application/docker/data/rapport.csv", implode("\t\n", $editedMosques));
        $this->em->flush();

    }

    private function removeOneHour($time)
    {
        try {
            $date = new \DateTime("2020-03-01 $time:00");
            $date->sub(new \DateInterval('PT1H'));
            return $date->format("H:i");
        } catch (\Exception $e) {

        }
        return $time;
    }

    public static function getCountryNameByCode($countryCode, $locale = null)
    {
        return Intl::getRegionBundle()->getCountryName($countryCode, $locale);
    }

}
