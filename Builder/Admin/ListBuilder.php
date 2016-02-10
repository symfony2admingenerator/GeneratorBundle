<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

use Admingenerator\GeneratorBundle\Generator\Column;
use Admingenerator\GeneratorBundle\Generator\Action;

/**
 * This builder generates php for list actions
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class ListBuilder extends BaseBuilder
{
    /**
     * @var array
     */
    protected $batchActions = null;

    /**
     * @var array
     */
    protected $scopeColumns = null;

    /**
     * @var array
     */
    protected $filterColumns = null;

    /**
     * (non-PHPdoc)
     * @see Admingenerator\GeneratorBundle\Builder.BaseBuilder::getYamlKey()
     */
    public function getYamlKey()
    {
        return 'list';
    }

    /**
     * Retrieve the FQCN formType used by this builder
     *
     * @return string
     */
    public function getFormType()
    {
        return sprintf(
            '%s%s\\Form\Type\\%s\\FiltersType',
            $this->getVariable('namespace_prefix') ? $this->getVariable('namespace_prefix') . '\\' : '',
            $this->getVariable('bundle_name'),
            $this->getBaseGeneratorName()
        );
    }

    public function getFilterColumns()
    {
        if (null === $this->filterColumns) {
            $this->filterColumns = array();
            $this->findFilterColumns();
        }

        return $this->filterColumns;
    }

    protected function findFilterColumns()
    {
        $columnsName = $this->getVariable('filters');
        $fromFilterConfiguration = true;
        if (null === $columnsName) {
            $fromFilterConfiguration = false;
            $columnsName = $this->getAllFields();
        }

        foreach ($columnsName as $columnName) {
            $column = $this->createColumn($columnName, true);

            if ($fromFilterConfiguration || $column->isFilterable()) {
                $this->addFilterColumn($column);
            }
        }
    }

    protected function addFilterColumn(Column $column)
    {
        $this->filterColumns[$column->getName()] = $column;
    }

    public function getFilterColumnsCredentials()
    {
        $credentials = array();

        foreach($this->getFilterColumns() as $column) {
            if (! $filterCredentials = $column->getFiltersCredentials()) {
                // If one column has no Credentials constraint, we always
                // have to display the filter panel
                return array();
            }

            $credentials[] = $filterCredentials;
        }

        return $credentials;
    }

    /**
     * Find scopes parameters
     */
    public function getScopes()
    {
        return $this->getGenerator()->getFromYaml('builders.list.params.scopes');
    }

    /**
     * @return array
     */
    public function getScopeColumns()
    {
        if (null === $this->scopeColumns) {
            $this->scopeColumns = array();
            $this->findScopeColumns();
        }

        return $this->scopeColumns;
    }

    protected function findScopeColumns()
    {
        foreach ($this->getScopesDisplayColumns() as $columnName) {
            $column = $this->createColumn($columnName, true);

            // Set the user parameters
            $this->addScopeColumn($column);
        }
    }

    /**
     * @return array Scopes display column names
     */
    protected function getScopesDisplayColumns()
    {
        $scopeGroups = $this->getGenerator()->getFromYaml('builders.list.params.scopes', array());
        $scopeColumns = array();

        foreach ($scopeGroups as $scopeGroup) {
            foreach ($scopeGroup as $scopeFilter) {
                if (array_key_exists('filters', $scopeFilter) && is_array($scopeFilter['filters'])) {
                    foreach ($scopeFilter['filters'] as $field => $value) {
                        $scopeColumns[] = $field;
                    }
                }
            }
        }

        return $scopeColumns;
    }

    protected function addScopeColumn(Column $column)
    {
        $this->scopeColumns[$column->getName()] = $column;
    }

    /**
     * Return a list of batch action from list.batch_actions
     * @return array
     */
    public function getBatchActions()
    {
        if (null === $this->batchActions) {
            $this->batchActions = array();
            $this->findBatchActions();
        }

        return $this->batchActions;
    }

    protected function setUserBatchActionConfiguration(Action $action)
    {
        $batchActions = $this->getVariable('batch_actions', array());
        $builderOptions = is_array($batchActions) && array_key_exists($action->getName(), $batchActions)
            ? $batchActions[$action->getName()]
            : array();

        $globalOptions = $this->getGenerator()->getFromYaml(
            'params.batch_actions.'.$action->getName(),
            array()
        );

        if (null !== $builderOptions) {
            foreach ($builderOptions as $option => $value) {
                $action->setProperty($option, $value);
            }
        } elseif (null !== $globalOptions) {
            foreach ($globalOptions as $option => $value) {
                $action->setProperty($option, $value);
            }
        }
    }

    protected function addBatchAction(Action $action)
    {
        $this->batchActions[$action->getName()] = $action;
    }

    protected function findBatchActions()
    {
        $batchActions = $this->getVariable('batch_actions', array());

        foreach ($batchActions as $actionName => $actionParams) {
            $action = $this->findBatchAction($actionName);

            if (!$action) {
                $action = new Action($actionName);
            }

            if ($globalCredentials = $this->getGenerator()->getFromYaml('params.credentials')) {
                // If generator is globally protected by credentials
                // batch actions are also protected
                $action->setCredentials($globalCredentials);
            }

            $this->setUserBatchActionConfiguration($action);
            $this->addBatchAction($action);
        }
    }
}
