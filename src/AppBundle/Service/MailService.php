<?php

namespace AppBundle\Service;

use AppBundle\Entity\Mosque;

class MailService
{

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $contactEmail;

    /**
     * @var array
     */
    private $doNotReplyEmail;

    /**
     * @var array
     */
    private $postmasterEmail;

    public function __construct(
        \Swift_Mailer $mailer,
        \Twig_Environment $twig,
        $contactEmail,
        $doNotReplyEmail,
        $postmasterEmail
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->contactEmail = $contactEmail; // contact@mawaqit.net
        $this->postmasterEmail = $postmasterEmail; // postmaster@mawaqit.net
        $this->doNotReplyEmail = $doNotReplyEmail; // no-reply@mawaqit.net
    }

    /**
     * Send email when mosque created
     *
     * @param Mosque $mosque
     *
     * @throws @see sendEmail
     */
    function mosqueCreated(Mosque $mosque)
    {
        $title = 'Nouvelle mosquée (' . $mosque->getCountryFullName() . ')';
        $this->sendEmail($mosque, $title, $this->postmasterEmail, $this->postmasterEmail, 'created');
    }

    /**
     * @param $title
     * @param $mosque
     * @param $to
     * @param $from
     * @param $status
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function sendEmail(Mosque $mosque, $title, $to, $from, $status)
    {
        $body = $this->twig->render("email_templates/mosque_$status.html.twig", ['mosque' => $mosque]);
        $message = $this->mailer->createMessage();
        $message->setSubject($title)
            ->setCharset("utf-8")
            ->setFrom($from)
            ->setTo($to)
            ->addPart($body, 'text/html');

        if($status === "duplicated"){
            $text = $this->twig->render("email_templates/mosque_$status.txt.twig", ['mosque' => $mosque]);
            $message->setBody($text);
        }

        $this->mailer->send($message);
    }

    /**
     * Send email when mosque has been validated by admin
     *
     * @param Mosque $mosque
     *
     * @throws @see sendEmail
     */
    function mosqueValidated(Mosque $mosque)
    {
        $title = $mosque->getTitle() . " (ID " . $mosque->getId() . ") | a été validée / has been validated";
        $this->sendEmail($mosque, $title, $mosque->getUser()->getEmail(), $this->doNotReplyEmail, 'validated');
    }

    /**
     * Send email when mosque has been suspended by admin
     *
     * @param Mosque $mosque
     *
     * @throws @see sendEmail
     */
    function mosqueSuspended(Mosque $mosque)
    {
        $title = $mosque->getTitle() . " (ID " . $mosque->getId() . ") | a été suspendue / has been suspended";
        $this->sendEmail($mosque, $title, $mosque->getUser()->getEmail(), $this->postmasterEmail, 'suspended');
    }

    /**
     * Send email to user to check information of the mosque
     *
     * @param Mosque $mosque
     *
     * @throws @see sendEmail
     */
    function checkMosque(Mosque $mosque)
    {
        $title = $mosque->getTitle() . " (ID " . $mosque->getId() . ") | Nous avons besoin d'informations / We need informations";
        $this->sendEmail($mosque, $title, $mosque->getUser()->getEmail(), $this->postmasterEmail, 'check');
    }

    /**
     * Send email to user to check information of the mosque when duplicated
     *
     * @param Mosque $mosque
     *
     * @throws @see sendEmail
     */
    function duplicatedMosque(Mosque $mosque)
    {
        $title = $mosque->getTitle() . " (ID " . $mosque->getId() . ") | est en double sur Mawaqit / is duplicated on Mawaqit";
        $this->sendEmail($mosque, $title, $mosque->getUser()->getEmail(), $this->postmasterEmail, 'duplicated');
    }

    /**
     * Send email to user to inform him that his photo is deleted
     *
     * @param Mosque $mosque
     *
     * @throws @see sendEmail
     */
    function rejectScreenPhoto(Mosque $mosque)
    {
        $title = $mosque->getTitle() . " (ID " . $mosque->getId() . ") | Screen photo";
        $this->sendEmail($mosque, $title, $mosque->getUser()->getEmail(), $this->postmasterEmail, 'screen_photo_rejected');
    }

}
