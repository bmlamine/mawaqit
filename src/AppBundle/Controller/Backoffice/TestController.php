<?php


namespace AppBundle\Controller\Backoffice;

use AppBundle\Entity\Mosque;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

/**
 * @Route("/backoffice/admin/test", options={"i18n"="false"})
 */
class TestController extends Controller
{

    /**
     * @Route("")
     */
    public function testAction()
    {

        $process = new Process("ssh pi@192.168.1.48 'bash -s' rtsp://admin:admin@192.168.1.36:554/cam/realmonitor?channel=1 0</application/raspberry/omxplayer.sh 10");
        $process->run();

        return new Response($process->getOutput());
    }

    /**
     * @Route("/mail-preview/{template}/{id}")
     */
    public function mailPreviewAction($template, Mosque $mosque)
    {
        return $this->render(":email_templates:$template.html.twig", [
            "mosque" => $mosque,
            "content" => 'toto'
        ]);
    }

}
