<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

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
    /**
     * @var array
     */
    protected $columns = null;

    /**
     * @var array
     */
    protected $actions = null;

    /**
     * @var array
     */
    protected $objectActions = null;

    /**
     * @var string
     */
    protected $columnClass = 'Column';

    public function getBaseAdminTemplate()
    {
        return $this->getGenerator()->getBaseAdminTemplate();
    }

    /**
     * Return a list of columns from list.display.
     *
     * @return array
     */
    public function getColumns()
    {
        if (null === $this->columns) {
            $this->columns = array();
            $this->findColumns();
        }

        return $this->columns;
    }

    protected function addColumn(Column $column)
    {
        $this->columns[$column->getName()] = $column;
    }

    protected function findColumns()
    {
        foreach ($this->getDisplayColumns() as $columnName) {
            $column = $this->createColumn($columnName, true);

            //Set the user parameters
            $this->addColumn($column);
        }
    }

    public function getColumnGroups()
    {
        $groups = array();

        foreach ($this->getColumns() as $column) {
            $groups = array_merge($groups, $column->getGroups());
        }

        return $groups;
    }

    /**
     * Creates new column instance.
     *
     * @param string $columnName The name of the column.
     * @param bool   $withForms  If true, add column form configuration.
     *
     * @return Column
     */
    protected function createColumn($columnName, $withForms = false)
    {
        $column = new $this->columnClass($columnName, array(
            /* used for more verbose error messages */
            'builder' => $this->getYamlKey(),
            'generator' => $this->getBaseGeneratorName(),
        ));

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

            $fields = $this->getVariable('fields', array());
            $fieldOptions = is_array($fields) && array_key_exists($column->getName(), $fields)
                ? $fields[$column->getName()]
                : array();

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

    protected function getColumnClass()
    {
        return $this->columnClass;
    }

    public function setColumnClass($columnClass)
    {
        return $this->columnClass = $columnClass;
    }

    /**
     * @param Column $column
     * @param string $optionName
     * @param string $default
     *
     * @return string
     */
    protected function getFieldOption(Column $column, $optionName, $default = null)
    {
        $optionsFields = $this->getVariable('fields', array());
        $options = is_array($optionsFields) && array_key_exists($column->getName(), $optionsFields) ? $optionsFields[$column->getName()] : array();

        return isset($options[$optionName]) ? $options[$optionName] : $default;
    }

    protected function setUserColumnConfiguration(Column $column)
    {
        $optionsFields = $this->getVariable('fields', array());
        $options = is_array($optionsFields) && array_key_exists($column->getName(), $optionsFields) ? $optionsFields[$column->getName()] : array();

        foreach ($options as $option => $value) {
            $column->setProperty($option, $value);
        }
    }

    public function getFieldGuesser()
    {
        return $this->getGenerator()->getFieldGuesser();
    }

    /**
     * @return array Display column names
     */
    protected function getDisplayColumns()
    {
        $display = $this->getVariable('display');

        // tabs
        if (null == $display || (is_array($display) && 0 == count($display))) {
            $tabs = $this->getVariable('tabs');

            if (null != $tabs || (is_array($tabs) && 0 < count($tabs))) {
                $display = array();

                foreach ($tabs as $tab) {
                    $display = array_merge($display, $tab);
                }
            }
        }

        if (null == $display || (is_array($display) && 0 == count($display))) {
            return $this->getAllFields();
        }

        if (isset($display[0])) {
            return $display;
        }

        //there is fieldsets
        $return = array();

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

    /**
     * Retrieve all columns.
     *
     * @return array
     */
    protected function getAllFields()
    {
        return $this->getFieldGuesser()->getAllFields($this->getVariable('model'));
    }

    /**
     * @return array
     */
    public function getFieldsets()
    {
        $display = $this->getVariable('display');

        // tabs
        if (null == $display || (is_array($display) && 0 == count($display))) {
            $tabs = $this->getVariable('tabs');

            if (null != $tabs || (is_array($tabs) && 0 < count($tabs))) {
                $display = array();

                foreach ($tabs as $tab) {
                    $display = array_merge($display, $tab);
                }
            }
        }

        if (null == $display || (is_array($display) && 0 == count($display))) {
            $display = $this->getAllFields();
        }

        if (isset($display[0])) {
            $display = array('NONE' => $display);
        }

        foreach ($display as $fieldset => $rows_or_fields) {
            $display[$fieldset] = $this->getRowsFromFieldset($rows_or_fields);
        }

        return $display;
    }

    protected function getRowsFromFieldset(array $rows_or_fields)
    {
        $rows = array();

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
     *
     * @param mixed $input
     *
     * @return array Array of columns.
     */
    public function getColumnsFor($input)
    {
        if (!is_array($input)) {
            $input = array($input);
        }

        $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($input));

        $fieldsNames = iterator_to_array($it, false);

        return array_intersect_key($this->getColumns(), array_flip($fieldsNames));
    }

    /**
     * Return a list of action from list.actions.
     *
     * @return array
     */
    public function getActions()
    {
        if (null === $this->actions) {
            $this->actions = array();
            $this->findActions();
        }

        return $this->actions;
    }

    protected function setUserActionConfiguration(Action $action)
    {
        $actions = $this->getVariable('actions', array());
        $builderOptions = is_array($actions) && array_key_exists($action->getName(), $actions) ? $actions[$action->getName()] : array();

        $globalOptions = $this->getGenerator()->getFromYaml(
            'params.actions.'.$action->getName(),
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

        if ('generic' == $action->getType()) {
            // Let's try to get credentials from builder for consistency
            if ($credentials = $this->generator->getFromYaml(sprintf('builders.%s.params.credentials', $action->getName()))) {
                $action->setCredentials($credentials);
            }
        }
    }

    protected function addAction(Action $action)
    {
        $this->actions[$action->getName()] = $action;
    }

    protected function findActions()
    {
        foreach ($this->getVariable('actions', array()) as $actionName => $actionParams) {
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
     *
     * @return array
     */
    public function getObjectActions()
    {
        if (null === $this->objectActions) {
            $this->objectActions = array();
            $this->findObjectActions();
        }

        return $this->objectActions;
    }

    protected function setUserObjectActionConfiguration(Action $action)
    {
        $objectActions = $this->getVariable('object_actions', array());
        $builderOptions = is_array($objectActions) && array_key_exists($action->getName(), $objectActions)
            ? $objectActions[$action->getName()]
            : array();

        $globalOptions = $this->getGenerator()->getFromYaml(
            'params.object_actions.'.$action->getName(),
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

    protected function addObjectAction(Action $action)
    {
        $this->objectActions[$action->getName()] = $action;
    }

    protected function findObjectActions()
    {
        $objectActions = $this->getVariable('object_actions', array());

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

    public function findGenericAction($actionName)
    {
        $class = 'Admingenerator\\GeneratorBundle\\Generator\\Action\\Generic\\'
                .Container::camelize(str_replace('-', '_', $actionName).'Action');

        return (class_exists($class)) ? new $class($actionName, $this) : false;
    }

    public function findObjectAction($actionName)
    {
        $class = 'Admingenerator\\GeneratorBundle\\Generator\\Action\\Object\\'
                .Container::camelize(str_replace('-', '_', $actionName).'Action');

        return (class_exists($class)) ? new $class($actionName, $this) : false;
    }

    public function findBatchAction($actionName)
    {
        $class = 'Admingenerator\\GeneratorBundle\\Generator\\Action\\Batch\\'
                .Container::camelize(str_replace('-', '_', $actionName).'Action');

        return (class_exists($class)) ? new $class($actionName, $this) : false;
    }

    public function getBaseGeneratorName()
    {
        return $this->getGenerator()->getBaseGeneratorName();
    }

    public function getNamespacePrefixWithSubfolder()
    {
        return $this->getVariable('namespace_prefix')
               .($this->hasVariable('subfolder') ? '\\'.$this->getVariable('subfolder') : '');
    }

    public function getRoutePrefixWithSubfolder()
    {
        return str_replace('\\', '_', $this->getNamespacePrefixWithSubfolder());
    }

    public function getNamespacePrefixForTemplate()
    {
        return str_replace('\\', '', $this->getVariable('namespace_prefix'));
    }

    public function getBaseActionsRoute()
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

    public function getObjectActionsRoute()
    {
        return $this->getBaseActionsRoute().'_object';
    }

    /**
     * Get the PK column name.
     *
     * @return string parameter
     */
    public function getModelPrimaryKeyName()
    {
        return $this->getGenerator()->getFieldGuesser()->getModelPrimaryKeyName($this->getVariable('model'));
    }

    /**
     * Allow to add complementary strylesheets.
     *
     *
     * param:
     *   stylesheets:
     *     - path/css.css
     *     - { path: path/css.css, media: all }
     *
     * @return array
     */
    public function getStylesheets()
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
        $stylesheets = $parse_stylesheets(
            $this->getVariable('stylesheets', array()), $stylesheets
        );

        return $stylesheets;
    }

    /**
     * Allow to add complementary javascripts.
     *
     *
     * param:
     *   javascripts:
     *     - path/js.js
     *     - { path: path/js.js }
     *     - { route: my_route, routeparams: {} }
     *
     * @return array
     */
    public function getJavascripts()
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
        $javascripts = $parse_javascripts(
            $this->getVariable('javascripts', array()), $javascripts
        );

        return $javascripts;
    }

    public function isBundleContext() {
        return str_contains($this->getVariable('bundle_name'), 'Bundle');
    }
}
