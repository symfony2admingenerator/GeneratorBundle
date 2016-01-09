<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for edit actions
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class EditBuilder extends BaseBuilder
{
    /**
     * (non-PHPdoc)
     * @see Admingenerator\GeneratorBundle\Builder.BaseBuilder::getYamlKey()
     */
    public function getYamlKey()
    {
        return 'edit';
    }

    /**
     * Retrieve the FQCN formType used by this builder
     *
     * @return string
     */
    public function getFormType()
    {
        return sprintf(
            '%s\\%s\\Form\Type\\%s\\EditType',
            $this->getVariable('namespace_prefix'),
            $this->getVariable('bundle_name'),
            $this->getModelClass()
        );
    }
}
