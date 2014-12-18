<?php

namespace Admingenerator\GeneratorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Base class for new and edit forms.
 * 
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
abstract class BaseType extends AbstractType
{
    protected $securityContext;

    protected $groups = array();

    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'groups' => array(),
            'cascade_validation' => true
        ));

        $resolver->setAllowedTypes(array(
            'groups' => 'array',
        ));
    }

    /**
     * Checks if groups intersect.
     *
     * @param array $groups         Column groups.
     * @return boolean
     */
    protected function checkGroups(array $groups)
    {
        if (count($this->groups) === 0 || count($groups) === 0) {
            return true;
        }

        return count(array_intersect($this->groups, $groups)) > 0;
    }

    /**
     * This method is used to pass the securityContext and groups into custom formTypes.
     *
     * @param string|FormTypeInterface $formType
     * @return string|FormTypeInterface
     */
    protected function inject($formType)
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
    protected function resolveOptions($name, array $fieldOptions, array $builderOptions = array(), $optionsClass = null)
    {
        $getter = 'get'.ucfirst($name).'Options';

        if ($optionsClass && method_exists($optionsClass, 'setSecurityContext')) {
            $optionsClass->setSecurityContext($this->securityContext);
        }

        if ($optionsClass && method_exists($this, $getter)) {
            // merge options from form type class
            $fieldOptions = $this->$getter($fieldOptions, $builderOptions);
        } else if ($optionsClass && method_exists($optionsClass, $getter)) {
            // merge options from options class
            $fieldOptions = $optionsClass->$getter($fieldOptions, $builderOptions);
        }
        
        // Pass on securityContext to collection types
        if (array_key_exists('type', $fieldOptions)) {
            $fieldType = $fieldOptions['type'];
            $fieldOptions['type'] = $this->inject($fieldType);
            $fieldOptions['validation_groups'] = $builderOptions['validation_groups'];
            
            if (is_object($fieldType)) {
                $fieldOptions['options']['groups'] = $builderOptions['groups'];
            }
        }

        return $fieldOptions;
    }

    public function getName()
    {
        return 'admingenerator_base_type';
    }
}
