<?php

namespace Admingenerator\GeneratorBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminBaseController extends AbstractController
{
    protected ?Request $request = null;

    /** Ensure the translator and logger services are available for usage */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'translator' => '?'.TranslatorInterface::class,
                'logger' => '?'.LoggerInterface::class,
                'validator' => '?'.ValidatorInterface::class,
            ]
        );
    }

    protected function getLogger(): LoggerInterface
    {
        if (!$this->container->has('logger')) {
            throw new \LogicException('Logger service not found');
        }

        return $this->container->get('logger');
    }

    protected function getSession(): SessionInterface
    {
        if (!$this->request) {
            throw new \LogicException('Request object not found');
        }

        return $this->request->getSession();
    }

    protected function getTranslator() : TranslatorInterface
    {
        if (!$this->container->has('translator')) {
            throw new \LogicException('Logger service not found');
        }

        return $this->container->get('translator');
    }

    protected function getValidator() : ValidatorInterface
    {
        if (!$this->container->has('validator')) {
            throw new \LogicException('Logger service not found');
        }

        return $this->container->get('validator');
    }
}