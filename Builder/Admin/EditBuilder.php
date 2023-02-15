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
    public function getYamlKey(): string
    {
        return 'edit';
    }

    /**
     * Retrieve the FQCN formType used by this builder
     */
    public function getFormType(): string
    {
        return sprintf(
            '%s%s\\Form\Type\\%s\\EditType',
            $this->getVariable('namespace_prefix') ? $this->getVariable('namespace_prefix') . '\\' : '',
            $this->getVariable('bundle_name'),
            $this->getBaseGeneratorName()
        );
    }
}
