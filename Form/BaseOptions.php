<?php

namespace Admingenerator\GeneratorBundle\Form;

use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Base class for form options.
 * 
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
abstract class BaseOptions
{
    protected $securityContext;

    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function getName()
    {
        return 'admingenerator_base_options';
    }
}
