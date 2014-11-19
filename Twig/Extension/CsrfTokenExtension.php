<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

/**
 * @author Stéphane Escandell
 */
class CsrfTokenExtension extends \Twig_Extension
{
    /**
     * @var CsrfProviderInterface
     */
    protected $csrfProvider;

    /**
     * @param CsrfProviderInterface $csrfProvider
     */
    public function __construct(CsrfProviderInterface $csrfProvider)
    {
        $this->csrfProvider = $csrfProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'csrf_token' => new \Twig_Filter_Method($this, 'getCsrfToken'),
        );
    }

    public function getCsrfToken($intention)
    {
        return $this->csrfProvider->generateCsrfToken($intention);
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
