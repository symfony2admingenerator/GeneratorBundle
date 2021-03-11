<?php

namespace Admingenerator\GeneratorBundle\Controller\Propel;

use Psr\Log\LoggerInterface;
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

    /**
     * Ensure the translator and logger services are available for usage
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'translator' => interface_exists('Symfony\Contracts\Translation\TranslatorInterface')
                    ? \Symfony\Contracts\Translation\TranslatorInterface::class
                    : \Symfony\Component\Translation\TranslatorInterface::class,
                'logger' => LoggerInterface::class,
            ]
        );
    }
}
