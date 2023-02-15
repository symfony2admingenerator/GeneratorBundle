<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

/**
 * This builder generates php for lists actions
 * @author cedric Lombardot
 */
class NestedListBuilder extends ListBuilder
{
    protected array $treeConfiguration = [];

    protected mixed $indentationColumnIndex = null;

    public function getYamlKey(): string
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
     */
    public function getTreeConfiguration(): array
    {
        if (empty($this->treeConfiguration)) {
            $this->findTreeConfiguration();
        }

        return $this->treeConfiguration;
    }

    /**
     * Get the indentation field that should be used.
     * No validity is made upon the field name.
     */
    public function getIndentationColumnIndex(): mixed
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
    protected function findTreeConfiguration(): void
    {
        $this->treeConfiguration = array_merge([
            'root'   => 'root',
            'left'   => 'lft',
            'right'  => 'rgt',
            'parent' => 'parent'
        ], $this->getGenerator()->getFromYaml('builders.nested_list.tree') ?: []);
    }
}
