<?php

namespace AppBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class MobileBlockedException extends HttpException
{
    public function __construct()
    {
        parent::__construct(503);
    }
}
