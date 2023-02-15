<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

use Admingenerator\GeneratorBundle\Guesser\FieldGuesser;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\DependencyInjection\Container;
use Admingenerator\GeneratorBundle\Builder\BaseBuilder as GenericBaseBuilder;
use Admingenerator\GeneratorBundle\Generator\Column;
use Admingenerator\GeneratorBundle\Generator\Action;

/**
 * Base builder generating php for actions.
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class BaseBuilder extends GenericBaseBuilder
{
    protected ?array $columns = null;

    protected ?array $actions = null;

    protected ?array $objectActions = null;

    protected string $columnClass = 'Column';

    public function getBaseAdminTemplate(): string
    {
        return $this->getGenerator()->getBaseAdminTemplate();
    }

    /**
     * Return a list of columns from list.display.
     */
    public function getColumns(): array
    {
        if (null === $this->columns) {
            $this->columns = [];
            $this->findColumns();
        }

        return $this->columns;
    }

    protected function addColumn(Column $column): void
    {
        $this->columns[$column->getName()] = $column;
    }

    protected function findColumns(): void
    {
        foreach ($this->getDisplayColumns() as $columnName) {
            $column = $this->createColumn($columnName, true);

            //Set the user parameters
            $this->addColumn($column);
        }
    }

    public function getColumnGroups(): array
    {
        $groups = [];

        foreach ($this->getColumns() as $column) {
            $groups = array_merge($groups, $column->getGroups());
        }

        return $groups;
    }

    /**
     * Creates new column instance.
     */
    protected function createColumn(string $columnName, bool $withForms = false): Column
    {
        $column = new $this->columnClass($columnName, [
            /* used for more verbose error messages */
            'builder' => $this->getYamlKey(),
            'generator' => $this->getBaseGeneratorName(),
        ]);

        //Set the user parameters
        $this->setUserColumnConfiguration($column);

        $column->setDbType($this->getFieldOption(
            $column,
            'dbType',
            $this->getFieldGuesser()->getDbType(
                $this->getVariable('model'),
                $columnName
            )
        ));

        $column->setManyToMany($this->getFieldGuesser()->getManyToMany($this->getVariable('model'), $columnName));

        $column->setSortType($this->getFieldGuesser()->getSortType($column->getDbType()));

        $column->setPrimaryKey($this->getFieldOption(
            $column,
            'primaryKey',
            $this->getFieldGuesser()->getPrimaryKeyFor(
                $this->getVariable('model'),
                $columnName
            )
        ));

        if ($withForms) {
            $column->setFormType($this->getFieldOption(
                $column,
                'formType',
                $this->getFieldGuesser()->getFormType(
                    $column->getDbType(),
                    $this->getVariable('model'),
                    $columnName
                )
            ));

            // We don't use $column->getDbType because filtered column
            // might be on a field from an association. So we need to
            // resolve the filtered field dbType (and not the column
            // one).
            $filteredFieldDbType = $this->getFieldGuesser()->getDbType(
                $this->getVariable('model'),
                $column->getFilterOn()
            );

            $column->setFilterType($this->getFieldOption(
                $column,
                'filterType',
                $this->getFieldGuesser()->getFilterType(
                    $filteredFieldDbType,
                    $this->getVariable('model'),
                    $column->getFilterOn()
                )
            ));

            if ($this->getYamlKey() === 'list') {
                // Filters
                $column->setFilterOptions($this->getFieldOption(
                    $column,
                    'filterOptions',
                    $this->getFieldOption(
                        $column,
                        'formOptions',
                        $this->getFieldGuesser()->getFilterOptions(
                            $column->getFilterType(),
                            $filteredFieldDbType,
                            $this->getVariable('model'),
                            $column->getFilterOn()
                        )
                    )
                ));
            } else {
                $column->setFormOptions($this->getFieldOption(
                    $column,
                    'formOptions',
                    $this->getFieldGuesser()->getFormOptions(
                        $column->getFormType(),
                        $column->getDbType(),
                        $this->getVariable('model'),
                        $columnName
                    )
                ));
            }

            $fields = $this->getVariable('fields', []);
            $fieldOptions = is_array($fields) && array_key_exists($column->getName(), $fields)
                ? $fields[$column->getName()]
                : [];

            if (array_key_exists('addFormOptions', $fieldOptions)) {
                $column->setAddFormOptions($fieldOptions['addFormOptions']);
            }

            if (array_key_exists('addFilterOptions', $fieldOptions)) {
                $column->setAddFilterOptions($fieldOptions['addFilterOptions']);
            } elseif (array_key_exists('addFormOptions', $fieldOptions)) {
                $column->setAddFilterOptions($fieldOptions['addFormOptions']);
            }
        }

        return $column;
    }

    /**
     * @return array|mixed
     */
    public function getDisplay(): mixed
    {
        $display = $this->getVariable('display');

        // tabs
        if (null == $display || (is_array($display) && 0 == count($display))) {
            $tabs = $this->getVariable('tabs');

            if (null != $tabs || (is_array($tabs) && 0 < count($tabs))) {
                $display = [];

                foreach ($tabs as $tab) {
                    $display = array_merge($display, $tab);
                }
            }
        }
        return $display;
    }

    protected function getColumnClass(): string
    {
        return $this->columnClass;
    }

    public function setColumnClass($columnClass): string
    {
        return $this->columnClass = $columnClass;
    }

    protected function getFieldOption(Column $column, string $optionName, mixed $default = null): mixed
    {
        $optionsFields = $this->getVariable('fields', []);
        $options = is_array($optionsFields) && array_key_exists($column->getName(), $optionsFields) ? $optionsFields[$column->getName()] : [];

        return $options[$optionName] ?? $default;
    }

    protected function setUserColumnConfiguration(Column $column): void
    {
        $optionsFields = $this->getVariable('fields', []);
        $options = is_array($optionsFields) && array_key_exists($column->getName(), $optionsFields) ? $optionsFields[$column->getName()] : [];

        foreach ($options as $option => $value) {
            $column->setProperty($option, $value);
        }
    }

    public function getFieldGuesser(): FieldGuesser
    {
        return $this->getGenerator()->getFieldGuesser();
    }

    protected function getDisplayColumns(): array
    {
        $display = $this->getDisplay();

        if (null == $display || (is_array($display) && 0 == count($display))) {
            return $this->getAllFields();
        }

        if (isset($display[0])) {
            return $display;
        }

        //there is fieldsets
        $return = [];

        foreach ($display as $fieldset => $rows_or_fields) {
            foreach ($rows_or_fields as $fields) {
                if (is_array($fields)) { //It s a row
                    $return = array_merge($return, $fields);
                } else {
                    $return[$fields] = $fields;
                }
            }
        }

        return $return;
    }

    protected function getAllFields(): array
    {
        return $this->getFieldGuesser()->getAllFields($this->getVariable('model'));
    }

    public function getFieldsets(): array
    {
        $display = $this->getDisplay();

        if (null == $display || (is_array($display) && 0 == count($display))) {
            $display = $this->getAllFields();
        }

        if (isset($display[0])) {
            $display = ['NONE' => $display];
        }

        foreach ($display as $fieldset => $rows_or_fields) {
            $display[$fieldset] = $this->getRowsFromFieldset($rows_or_fields);
        }

        return $display;
    }

    protected function getRowsFromFieldset(array $rows_or_fields): array
    {
        $rows = [];

        foreach ($rows_or_fields as $key => $field) {
            if (is_array($field)) { //The row is defined in yaml
                $rows[$key] = $field;
            } else {
                $rows[$key][] = $field;
            }
        }

        return $rows;
    }

    /**
     * Get columns for tab, fieldset, row or field.
     */
    public function getColumnsFor(mixed $input): array
    {
        if (!is_array($input)) {
            $input = array($input);
        }

        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($input));

        $fieldsNames = iterator_to_array($it, false);

        return array_intersect_key($this->getColumns(), array_flip($fieldsNames));
    }

    /**
     * Return a list of action from list.actions.
     */
    public function getActions(): array
    {
        if (null === $this->actions) {
            $this->actions = [];
            $this->findActions();
        }

        return $this->actions;
    }

    protected function setUserActionConfiguration(Action $action): void
    {
        $actions = $this->getVariable('actions', []);
        $builderOptions = is_array($actions) && array_key_exists($action->getName(), $actions) ? $actions[$action->getName()] : [];

        $globalOptions = $this->getGenerator()->getFromYaml(
            'params.actions.'.$action->getName(),
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

        if ('generic' == $action->getType()) {
            // Let's try to get credentials from builder for consistency
            if ($credentials = $this->generator->getFromYaml(sprintf('builders.%s.params.credentials', $action->getName()))) {
                $action->setCredentials($credentials);
            }
        }
    }

    protected function addAction(Action $action): void
    {
        $this->actions[$action->getName()] = $action;
    }

    protected function findActions(): void
    {
        foreach ($this->getVariable('actions', []) as $actionName => $actionParams) {
            $action = $this->findGenericAction($actionName);

            if (!$action) {
                $action = $this->findObjectAction($actionName);
            }

            if (!$action) {
                $action = new Action($actionName);
            }

            if ($globalCredentials = $this->getGenerator()->getFromYaml('params.credentials')) {
                // If generator is globally protected by credentials
                // actions are also protected
                $action->setCredentials($globalCredentials);
            }

            $this->setUserActionConfiguration($action);
            $this->addAction($action);
        }
    }

    /**
     * Return a list of action from list.object_actions.
     */
    public function getObjectActions(): array
    {
        if (null === $this->objectActions) {
            $this->objectActions = [];
            $this->findObjectActions();
        }

        return $this->objectActions;
    }

    protected function setUserObjectActionConfiguration(Action $action): void
    {
        $objectActions = $this->getVariable('object_actions', []);
        $builderOptions = is_array($objectActions) && array_key_exists($action->getName(), $objectActions)
            ? $objectActions[$action->getName()]
            : [];

        $globalOptions = $this->getGenerator()->getFromYaml(
            'params.object_actions.'.$action->getName(),
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

    protected function addObjectAction(Action $action): void
    {
        $this->objectActions[$action->getName()] = $action;
    }

    protected function findObjectActions(): void
    {
        $objectActions = $this->getVariable('object_actions', []);

        foreach ($objectActions as $actionName => $actionParams) {
            $action = $this->findObjectAction($actionName);
            if (!$action) {
                $action = new Action($actionName);
            }

            if ($globalCredentials = $this->getGenerator()->getFromYaml('params.credentials')) {
                // If generator is globally protected by credentials
                // object actions are also protected
                $action->setCredentials($globalCredentials);
            }

            $this->setUserObjectActionConfiguration($action);
            $this->addObjectAction($action);
        }
    }

    public function findGenericAction($actionName): object|false
    {
        $class = 'Admingenerator\\GeneratorBundle\\Generator\\Action\\Generic\\'
                .Container::camelize(str_replace('-', '_', $actionName).'Action');

        return (class_exists($class)) ? new $class($actionName, $this) : false;
    }

    public function findObjectAction($actionName): object|false
    {
        $class = 'Admingenerator\\GeneratorBundle\\Generator\\Action\\Object\\'
                .Container::camelize(str_replace('-', '_', $actionName).'Action');

        return (class_exists($class)) ? new $class($actionName, $this) : false;
    }

    public function findBatchAction($actionName): object|false
    {
        $class = 'Admingenerator\\GeneratorBundle\\Generator\\Action\\Batch\\'
                .Container::camelize(str_replace('-', '_', $actionName).'Action');

        return (class_exists($class)) ? new $class($actionName, $this) : false;
    }

    public function getBaseGeneratorName(): string
    {
        return $this->getGenerator()->getBaseGeneratorName();
    }

    public function getNamespacePrefixWithSubfolder(): string
    {
        return $this->getVariable('namespace_prefix')
               .($this->hasVariable('subfolder') ? '\\'.$this->getVariable('subfolder') : '');
    }

    public function getRoutePrefixWithSubfolder(): string
    {
        return str_replace('\\', '_', $this->getNamespacePrefixWithSubfolder());
    }

    public function getNamespacePrefixForTemplate(): string
    {
        return str_replace('\\', '', $this->getVariable('namespace_prefix'));
    }

    public function getBaseActionsRoute(): string
    {
        return ltrim(
            str_replace(
                '\\',
                '_',
                $this->getVariable('namespace_prefix')
                .(($this->hasVariable('subfolder')) ? '_'.$this->getVariable('subfolder') : '')
                .'_'.$this->getVariable('bundle_name')
                .'_'.$this->getBaseGeneratorName()
            ),
            '_' // fix routes in AppBundle without vendor
        );
    }

    public function getObjectActionsRoute(): string
    {
        return $this->getBaseActionsRoute().'_object';
    }

    /**
     * Get the PK column name.
     */
    public function getModelPrimaryKeyName(): string
    {
        return $this->getGenerator()->getFieldGuesser()->getModelPrimaryKeyName($this->getVariable('model'));
    }

    /**
     * Allow to add complementary strylesheets.
     *
     * param:
     *   stylesheets:
     *     - path/css.css
     *     - { path: path/css.css, media: all }
     */
    public function getStylesheets(): array
    {
        $parse_stylesheets = function ($params, $stylesheets) {
            foreach ($params as $css) {
                if (is_string($css)) {
                    $css = array(
                        'path' => $css,
                        'media' => 'all',
                    );
                }

                $stylesheets[] = $css;
            }

            return $stylesheets;
        };

        // From config.yml
        $stylesheets = $parse_stylesheets(
            $this->getGenerator()->getFromBundleConfig('stylesheets', array()), array()
        );

        // From generator.yml
        return $parse_stylesheets(
            $this->getVariable('stylesheets', array()), $stylesheets
        );
    }

    /**
     * Allow to add complementary javascripts.
     *
     * param:
     *   javascripts:
     *     - path/js.js
     *     - { path: path/js.js }
     *     - { route: my_route, routeparams: {} }
     */
    public function getJavascripts(): array
    {
        $self = $this;
        $parse_javascripts = function ($params, $javascripts) use ($self) {
            foreach ($params as $js) {
                if (is_string($js)) {
                    $js = array(
                        'path' => $js,
                    );
                } elseif (isset($js['route'])) {
                    $js = array(
                        'path' => $self->getGenerator()
                                        ->getRouter()
                                        ->generate($js['route'], $js['routeparams']),
                    );
                }

                $javascripts[] = $js;
            }

            return $javascripts;
        };

        // From config.yml
        $javascripts = $parse_javascripts(
            $this->getGenerator()->getFromBundleConfig('javascripts', array()), array()
        );

        // From generator.yml
        return $parse_javascripts(
            $this->getVariable('javascripts', array()), $javascripts
        );
    }

    public function isBundleContext(): bool
    {
        return str_contains($this->getVariable('bundle_name'), 'Bundle');
    }
}
