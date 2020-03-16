<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\Mosque;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MosqueValidator extends ConstraintValidator
{

    const MAX_DEFAULT_MOSQUE_QUOTA = 10;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationChecker $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Mosque     $mosque
     * @param Constraint $constraint
     */
    public function validate($mosque, Constraint $constraint)
    {
        /**
         * @var User $user
         */
        $user = $this->tokenStorage->getToken()->getUser();

        // validate max authorized mosques
        $quota = self::MAX_DEFAULT_MOSQUE_QUOTA;
        if ($user->getMosqueQuota() !== null) {
            $quota = $user->getMosqueQuota();
        }

        if (!$this->authorizationChecker->isGranted("ROLE_ADMIN") && $user->getMosques()->count() >= $quota) {
            $this->context->buildViolation($constraint->maxReachedMsg)->addViolation();
        }

        // validate justificatory
        if (!$this->authorizationChecker->isGranted("ROLE_ADMIN") && $mosque->isMosque() && !$mosque->isEditAllowed()) {
            if (!$mosque->getJustificatoryfile()) {
                $this->context->buildViolation($constraint->justificatoryMandatory)->addViolation();
            }

            // validate main photo
            if (!$mosque->getFile1() || !$mosque->getFile2()) {
                $this->context->buildViolation($constraint->imageMandatory)->addViolation();
            }
        }
    }
}
