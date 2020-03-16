<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Mosque extends Constraint {

    public $maxReachedMsg = 'form.mosque.max_reached';
    public $justificatoryMandatory = 'form.mosque.justificatoryMandatory';
    public $imageMandatory = 'form.mosque.imageMandatory';

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

}
