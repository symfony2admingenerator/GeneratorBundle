<?php

namespace Admingenerator\GeneratorBundle\Controller\Doctrine;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * A base controller for Doctrine
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
