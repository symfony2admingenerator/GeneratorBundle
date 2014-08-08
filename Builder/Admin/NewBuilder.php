<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for new actions
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class NewBuilder extends BaseBuilder
{
    /**
     * (non-PHPdoc)
     * @see Admingenerator\GeneratorBundle\Builder.BaseBuilder::getYamlKey()
     */
    public function getYamlKey()
    {
        return 'new';
    }

    protected function findColumns()
    {
        foreach ($this->getDisplayColumns() as $columnName) {
            $column = $this->createColumn($columnName, true);

            //Set the user parameters
            $this->setUserColumnConfiguration($column);
            $this->addColumn($column);
        }
    }
}
