<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for lists actions
 * @author cedric Lombardot
 */
class NestedListBuilder extends ListBuilder
{
    /**
     * @var array
     */
    protected $treeConfiguration = array();

    /**
     * @var mixed
     */
    protected $indentationColumnIndex = null;

    /**
     * (non-PHPdoc)
     * @see Admingenerator\GeneratorBundle\Builder.BaseBuilder::getYamlKey()
     */
    public function getYamlKey()
    {
        return 'nested_list';
    }

    /**
     * Returns tree configuration, an array containing nested tree fields identifiers:
     * array:
     *    root   => root field
     *    left   => left field
     *    right  => right field
     *    parent => parent field
     *
     * @return array
     */
    public function getTreeConfiguration()
    {
        if (empty($this->treeConfiguration)) {
            $this->findTreeConfiguration();
        }

        return $this->treeConfiguration;
    }

    /**
     * Get the indentation field that should be used.
     * No validity is made uppon the field name.
     *
     * @return mixed
     */
    public function getIndentationColumnIndex()
    {
        if (null === $this->indentationColumnIndex) {
            $field = $this->getGenerator()->getFromYaml('builders.nested_list.indentation.field');
            if (!$field) {
                return $this->indentationColumnIndex = 0;
            }

            $this->indentationColumnIndex = array_search($field, array_keys($this->getColumns()));
        }

        return $this->indentationColumnIndex;
    }

    /**
     * Extract tree configuration from generator.
     * If none defined, default is:
     * array:
     *     root   => root
     *     left   => lft
     *     right  => rgt
     *     parent => parent
     */
    protected function findTreeConfiguration()
    {
        $this->treeConfiguration = array_merge(array(
            'root'   => 'root',
            'left'   => 'lft',
            'right'  => 'rgt',
            'parent' => 'parent'
        ), $this->getGenerator()->getFromYaml('builders.nested_list.tree') ?: array());
    }
}
