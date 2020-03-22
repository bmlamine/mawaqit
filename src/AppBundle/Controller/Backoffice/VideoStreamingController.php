<?php

namespace AppBundle\Controller\Backoffice;

use AppBundle\Entity\Mosque;
use AppBundle\Form\StreamType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/backoffice")
 */
class VideoStreamingController extends Controller
{

    /**
     * @Route("/stream/edit/{id}", name="stream_edit")
     * @param Request                $request
     * @param Mosque                 $mosque
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    public function editAction(Request $request, Mosque $mosque, EntityManagerInterface $em)
    {

        $user = $this->getUser();
        if (!$this->isGranted("ROLE_ADMIN")) {
            if ($user !== $mosque->getUser()) {
                throw new AccessDeniedException;
            }

            if (!$mosque->isFullyValidated()) {
                throw new AccessDeniedException;
            }
        }

        $form = $this->createForm(StreamType::class, $mosque);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', "form.edit.success");
            return $this->redirectToRoute('mosque_index');
        }

        return $this->render('mosque/stream_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
