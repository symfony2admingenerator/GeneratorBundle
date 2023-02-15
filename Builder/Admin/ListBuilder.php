<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

use Admingenerator\GeneratorBundle\Generator\Column;
use Admingenerator\GeneratorBundle\Generator\Action;
use Admingenerator\GeneratorBundle\Generator\Action\Generic\ExcelAction;

/**
 * This builder generates php for list actions
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class ListBuilder extends BaseBuilder
{
    protected ?array $excelActions = null;

    protected ?array $batchActions = null;

    protected ?array $scopeColumns = null;

    protected ?array $filterColumns = null;

    public function getYamlKey(): string
    {
        return 'list';
    }

    public function getFormType(): string
    {
        return sprintf(
            '%s%s\\Form\Type\\%s\\FiltersType',
            $this->getVariable('namespace_prefix') ? $this->getVariable('namespace_prefix') . '\\' : '',
            $this->getVariable('bundle_name'),
            $this->getBaseGeneratorName()
        );
    }

    public function getFilterColumns(): array
    {
        if (null === $this->filterColumns) {
            $this->filterColumns = [];
            $this->findFilterColumns();
        }

        return $this->filterColumns;
    }

    protected function findFilterColumns(): void
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

    protected function addFilterColumn(Column $column): void
    {
        $this->filterColumns[$column->getName()] = $column;
    }

    public function getFilterColumnsCredentials(): array
    {
        $credentials = [];

        foreach($this->getFilterColumns() as $column) {
            if (! $filterCredentials = $column->getFiltersCredentials()) {
                // If one column has no Credentials constraint, we always
                // have to display the filter panel
                return [];
            }

            $credentials[] = $filterCredentials;
        }

        return $credentials;
    }

    public function getScopes(): mixed
    {
        return $this->getGenerator()->getFromYaml('builders.list.params.scopes');
    }

    public function getScopeColumns(): array
    {
        if (null === $this->scopeColumns) {
            $this->scopeColumns = [];
            $this->findScopeColumns();
        }

        return $this->scopeColumns;
    }

    protected function findScopeColumns(): void
    {
        foreach ($this->getScopesDisplayColumns() as $columnName) {
            $column = $this->createColumn($columnName, true);

            // Set the user parameters
            $this->addScopeColumn($column);
        }
    }

    protected function getScopesDisplayColumns(): array
    {
        $scopeGroups = $this->getGenerator()->getFromYaml('builders.list.params.scopes', []);
        $scopeColumns = [];

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

    protected function addScopeColumn(Column $column): void
    {
        $this->scopeColumns[$column->getName()] = $column;
    }

    public function getBatchActions(): array
    {
        if (null === $this->batchActions) {
            $this->batchActions = [];
            $this->findBatchActions();
        }

        return $this->batchActions;
    }

    protected function setUserBatchActionConfiguration(Action $action): void
    {
        $batchActions = $this->getVariable('batch_actions', []);
        $builderOptions = is_array($batchActions) && array_key_exists($action->getName(), $batchActions)
            ? $batchActions[$action->getName()]
            : [];

        $globalOptions = $this->getGenerator()->getFromYaml(
            'params.batch_actions.'.$action->getName(),
            []
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

    protected function addBatchAction(Action $action): void
    {
        $this->batchActions[$action->getName()] = $action;
    }

    protected function findBatchActions(): void
    {
        $batchActions = $this->getVariable('batch_actions', []);

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

  public function getExcelActions(): array
  {
      if (null === $this->excelActions) {
          $this->excelActions = array();
          $this->fillExportActions();
      }

      return $this->excelActions;
  }

  protected function fillExportActions(): void
  {
      $export = $this->getGenerator()->getFromYaml('builders.excel.params.export', []);
      if (!count($export)) return;

      foreach ($export as $keyName => $params ) {
          if (!isset($params['show_button']) || filter_var($params['show_button'], FILTER_VALIDATE_BOOLEAN)) {
              $action = new ExcelAction($keyName, $this);
              $action->setCredentials($this->getExportParamsForKey($keyName, 'credentials', 'AdmingenAllowed'));
              $action->setClass($this->getExportParamsForKey($keyName, 'class', 'btn-info'));
              $action->setIcon($this->getExportParamsForKey($keyName, 'icon', 'fa-file-excel-o'));
              $action->setLabel($this->getExportParamsForKey($keyName, 'label', 'action.generic.excel'));
              $this->excelActions[$keyName] = $action;
          }
      }
  }

  public function getExportParamsForKey(string|int|null $key, string|int|null $name, mixed $default): mixed
  {
      if (!$key) return $default;

      $export = $this->getGenerator()->getFromYaml('builders.excel.params.export', []);
      if (!count($export) || !isset($export[$key]) || !count($export[$key]) || !isset($export[$key][$name])) return $default;

      return $export[$key][$name];
  }
}
