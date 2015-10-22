<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @author Stéphane Escandell
 */
class CsrfTokenExtension extends \Twig_Extension
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
    public function getFilters()
    {
        return array(
            'csrf_token' => new \Twig_SimpleFilter('csrf_filter', array($this, 'getCsrfToken')),
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
