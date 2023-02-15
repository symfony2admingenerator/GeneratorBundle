<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @author Stéphane Escandell
 */
class CsrfTokenExtension extends AbstractExtension
{
    public function __construct(protected readonly CsrfTokenManagerInterface $csrfTokenManager)
    {
    }

    public function getFilters(): array
    {
        $options = ['is_safe' => ['html']];
        return [
            'csrf_token' => new TwigFilter('csrf_token', $this->getCsrfToken(...), $options),
        ];
    }

    public function getCsrfToken(string $intention): CsrfToken
    {
        return $this->csrfTokenManager->getToken($intention);
    }

    public function getName(): string
    {
        return 'admingenerator_csrf';
    }
}
