<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for new actions
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class NewBuilder extends BaseBuilder
{
    public function getYamlKey(): string
    {
        return 'new';
    }

    public function getFormType(): string
    {
        return sprintf(
            '%s%s\\Form\Type\\%s\\NewType',
            $this->getVariable('namespace_prefix') ? $this->getVariable('namespace_prefix') . '\\' : '',
            $this->getVariable('bundle_name'),
            $this->getBaseGeneratorName()
        );
    }
}
