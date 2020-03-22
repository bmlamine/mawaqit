<?php

namespace AppBundle\Controller\Backoffice;

use AppBundle\Entity\Mosque;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/backoffice/admin/test", options={"i18n"="false"})
 */
class TestController extends Controller
{

    /**
     * @Route("/{id}")
     */
    public function testAction(EntityManagerInterface $em, Mosque $mosque)
    {
        return $this->render(":tools:test.html.twig", []);
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