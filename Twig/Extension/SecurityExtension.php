<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class SecurityExtension extends \Twig_Extension
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'is_one_admingenerator_granted' => new \Twig_SimpleFunction('is_one_admingenerator_granted', array($this, 'isOneGranted')),
        );
    }

    /**
     * @param  array  $credentials
     * @return bool
     */
    public function isOneGranted(array $credentials, $object = null)
    {
        if (empty($credentials)) {
            return true;
        }

        foreach ($credentials as $credential) {
            if ($this->authorizationChecker->isGranted($credential, $object)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'admingenerator_security';
    }
}
