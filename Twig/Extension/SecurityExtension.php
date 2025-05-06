<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class SecurityExtension extends AbstractExtension
{

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly bool $useExpression = false
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            'is_one_admingenerator_granted' => new TwigFunction('is_one_admingenerator_granted', $this->isOneGranted(...)),
        ];
    }

    public function isOneGranted(array $credentials, ?object $object = null): bool
    {
        if (empty($credentials)) {
            return true;
        }

        foreach ($credentials as $credential) {
            if ('AdmingenAllowed' == $credential) {
                return true;
            }

            if ($this->useExpression) {
                $credential = new \JMS\SecurityExtraBundle\Security\Authorization\Expression\Expression($credential);
            }

            if ($this->authorizationChecker->isGranted($credential, $object)) {
                return true;
            }
        }

        return false;
    }

    public function getName(): string
    {
        return 'admingenerator_security';
    }
}
