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
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var bool
     */
    private $useExpression;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param bool $useExpression
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, $useExpression = false)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->useExpression = $useExpression;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'is_one_admingenerator_granted' => new TwigFunction('is_one_admingenerator_granted', array($this, 'isOneGranted')),
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
