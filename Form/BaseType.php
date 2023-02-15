<?php

namespace Admingenerator\GeneratorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Base class for new and edit forms.
 * 
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
abstract class BaseType extends AbstractType
{
    protected AuthorizationCheckerInterface $authorizationChecker;

    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    protected function checkCredentials(mixed $credentials, mixed $model): bool
    {
        return 'AdmingenAllowed' == $credentials
            || $this->authorizationChecker->isGranted($credentials, $model);
    }

    /**
     * Resolve field options.
     * 
     * @param  string       $name               Field name.
     * @param  array        $fieldOptions       Field options.
     * @param  array        $builderOptions     Form builder options.
     * @param  object|null  $optionsClass       The options class.
     * @return array                            Resolved field options.
     */
    protected function resolveOptions(
        string $name,
        array $fieldOptions,
        array $builderOptions = [],
        ?object $optionsClass = null
    ): array
    {
        $getter = 'get'.ucfirst($name).'Options';

        if ($optionsClass) {
            if (method_exists($optionsClass, 'setAuthorizationChecker')) {
                $optionsClass->setAuthorizationChecker($this->authorizationChecker);
            }

            if (method_exists($optionsClass, $getter)) {
                // merge options from options class
                $fieldOptions = $optionsClass->$getter($fieldOptions, $builderOptions);
            }
        }

        if (method_exists($this, $getter)) {
            // merge options from form type class
            $fieldOptions = $this->$getter($fieldOptions, $builderOptions);
        }

        return $fieldOptions;
    }
}
