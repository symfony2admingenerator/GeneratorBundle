<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @author Stéphane Escandell
 */
class CsrfTokenExtension extends AbstractExtension
{
    /**
     * @var CsrfTokenManagerInterface
     */
    protected $csrfTokenManager;

    /**
     * @param CsrfTokenManagerInterface $csrfTokenManager
     */
    public function __construct(CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        $options = ['is_safe' => ['html']];
        return array(
            'csrf_token' => new TwigFilter('csrf_token', array($this, 'getCsrfToken'), $options),
        );
    }

    public function getCsrfToken($intention)
    {
        return $this->csrfTokenManager->getToken($intention);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'admingenerator_csrf';
    }
}
