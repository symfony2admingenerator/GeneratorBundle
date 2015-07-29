<?php

namespace Admingenerator\GeneratorBundle\Controller\Doctrine;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * A base controller for Doctrine
 *
 * @author cedric Lombardot
 *
 */
abstract class BaseController extends Controller
{
    /**
     * @var Request
     */
    protected $request;
}
