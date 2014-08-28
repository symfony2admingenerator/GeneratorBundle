<?php

namespace Admingenerator\GeneratorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use JMS\SecurityExtraBundle\Security\Authorization\Expression\Expression;

/**
 * Base class for new and edit forms.
 * 
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class BaseType extends AbstractType
{
    protected $securityContext;

    protected $fieldGroups;

    public function setSecurityContext($securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function setFieldGroups(array $fieldGroups = array())
    {
        $this->fieldGroups = $fieldGroups;
    }

    /**
     * Checks if expression evaluates to true.
     * 
     * @param string       $expression The security expression.
     * @param object|null  $object     The object.
     * @return boolean
     */
    protected function checkCredentials($expression, $object = null)
    {
        return $this->securityContext->isGranted(array(new Expression($expression)), $object);
    }

    /**
     * Checks if groups intersect.
     *
     * @param array $groups         Column groups.
     * @return boolean
     */
    protected function checkGroups(array $groups)
    {
        if (count($this->fieldGroups) === 0 || count($groups) === 0) {
            return true;
        }

        return count(array_intersect($this->fieldGroups, $groups)) > 0;
    }

    /**
     * This method is used to pass the securityContext into custom formTypes.
     *
     * @param string|FormTypeInterface $formType
     * @return string|FormTypeInterface
     */
    protected function injectSecurityContext($formType)
    {
        if (is_object($formType) && method_exists($formType, 'setSecurityContext')) {
            $formType->setSecurityContext($this->securityContext);
        }

        return $formType;
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
    protected function resolveOptions($name, array $fieldOptions, array $builderOptions, $optionsClass = null)
    {
        $getter = 'get'.ucfirst($name).'Options';

        if ($optionsClass && method_exists($optionsClass, $getter)) {
            $fieldOptions = $optionsClass->$getter($fieldOptions, $builderOptions);
        }
        
        // Pass on securityContext to collection types
        if (array_key_exists('type', $fieldOptions)) {
            $fieldType = $fieldOptions['type'];
            $fieldOptions['type'] = $this->injectSecurityContext($fieldType);
        }

        return $fieldOptions;
    }

    public function getName()
    {
        return 'admingenerator_base_type';
    }
}
