<?php

namespace Admingenerator\GeneratorBundle\Form;

/**
 * Base class for form options.
 * 
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
abstract class BaseOptions
{
    protected $securityContext;

    public function setSecurityContext($securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function getName()
    {
        return 'admingenerator_base_options';
    }
}
