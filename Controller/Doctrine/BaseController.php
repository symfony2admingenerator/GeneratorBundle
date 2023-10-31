<?php

namespace Admingenerator\GeneratorBundle\Controller\Doctrine;

use Admingenerator\GeneratorBundle\Controller\AdminBaseController;
use Doctrine\Persistence\ManagerRegistry;

/**
 * A base controller for Doctrine
 *
 * @author cedric Lombardot
 */
abstract class BaseController extends AdminBaseController
{
    public function __construct(protected readonly ManagerRegistry $doctrine)
    {
    }
}
