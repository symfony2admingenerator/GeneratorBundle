<?php

namespace Admingenerator\GeneratorBundle\Controller\Propel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * A base controller for Propel
 *
 * @author cedric Lombardot
 *
 */
abstract class BaseController extends AbstractController
{
    /**
     * @var Request
     */
    protected $request;
}
