<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Admingenerator\GeneratorBundle\Exception\InvalidOptionException;

/**
 * This class describe a column
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
use Doctrine\Common\Util\Inflector;

class Column
{
    protected $name;

    protected $sortable;

    protected $sortOn;

    protected $sortType;

    protected $filterable;

    protected $filterOn;

    protected $dbType;

    /**
     * If set, formats field for scopes and filters. The formatting is a simple
     * sprintf with one string argument (field name).
     *
     * Example:
     *    for field:  "createdAt"
     *    dbFormat:   "DATE(%s)""
     *    the output will be "DATE(createdAt)"
     *
     * If undefined, the field will not be formatted.
     *
     * Since the functions may vary in diffrent Database types, Admingenerator does not,
     * by default, format fields in any way. It is up to developer to implement this for his fields.
     *
     * Note: this feature was created mainly for Date/DateTime fields.
     */
    protected $dbFormat;

    protected $customView = null;

    protected $formType;

    protected $filterType;

    protected $formOptions = array();

    protected $getter;

    protected $label = null;

    protected $help;

    protected $localizedDateFormat;

    protected $localizedTimeFormat;

    protected $primaryKey = null;

    /** For special columns template */
    protected $extras;

    protected $groups = array();

    /* Used for more verbose error messages */
    protected $debug = array();

    /**
     * @param boolean $debug
     */
    public function __construct($name, $debug)
    {
        $this->name     = $name;
        $this->debug    = $debug;
        $this->sortable = true;
        $this->sortType = 'default';
        $this->filterable = false;
    }

    public function setProperty($option, $value)
    {
        $setter = 'set'.Inflector::classify($option);

        if (method_exists($this, $setter)) {
            $this->{$setter}($value);
        } else {
            throw new InvalidOptionException($option, $this->name, $this->debug['generator'], $this->debug['builder']);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getGetter()
    {
        return $this->getter ? $this->getter : Inflector::camelize($this->name);
    }

    public function setGetter($getter)
    {
        $this->getter = $getter;
    }

    public function getLabel()
    {
        return false !== $this->label && empty($this->label)
            ? $this->humanize($this->getName())
            : $this->label;
    }

    public function setLabel($label)
    {
        return $this->label = $label;
    }

    public function getHelp()
    {
        return $this->help;
    }

    public function setHelp($help)
    {
        return $this->help = $help;
    }

    public function isSortable()
    {
        return $this->sortable;
    }

    public function isFilterable()
    {
        return $this->filterable;
    }

    public function isReal()
    {
        return $this->dbType != 'virtual';
    }

    public function getSortable()
    {
        return $this->sortable;
    }

    public function setSortable($sortable)
    {
        return $this->sortable = filter_var($sortable, FILTER_VALIDATE_BOOLEAN);
    }

    public function getSortOn()
    {
        return $this->sortOn != "" ? $this->sortOn : $this->name;
    }

    public function setSortOn($sort_on)
    {
        return $this->sortOn = $sort_on;
    }

    public function getFilterable()
    {
        return $this->filterable;
    }

    public function setFilterable($filterable)
    {
        return $this->filterable = filter_var($filterable, FILTER_VALIDATE_BOOLEAN);
    }

    public function getFilterOn()
    {
        return $this->filterOn != "" ? $this->filterOn : $this->name;
    }

    public function setFilterOn($filter_on)
    {
        return $this->filterOn = $filter_on;
    }

    private function humanize($text)
    {
        return ucfirst(str_replace('_', ' ', $text));
    }

    public function setDbType($dbType)
    {
        $this->dbType = $dbType;
    }

    public function getDbType()
    {
        return $this->dbType;
    }

    public function setDbFormat($dbFormat)
    {
        $this->dbFormat = $dbFormat;
    }

    public function getDbFormat()
    {
        return $this->dbFormat;
    }

    public function setFormType($formType)
    {
        $this->formType = $formType;
    }

    public function getFormType()
    {
        return $this->formType;
    }

    public function setFormOptions($formOptions)
    {
        $this->formOptions = $formOptions;
    }

    public function getFormOptions()
    {
        return $this->formOptions;
    }

    public function setFilterType($filterType)
    {
        $this->filterType = $filterType;
    }

    public function getFilterType()
    {
        return $this->filterType;
    }

    public function setLocalizedDateFormat($localizedDateFormat)
    {
        $this->localizedDateFormat = $localizedDateFormat;
    }

    public function getLocalizedDateFormat()
    {
        return $this->localizedDateFormat;
    }

    public function setLocalizedTimeFormat($localizedTimeFormat)
    {
        $this->localizedTimeFormat = $localizedTimeFormat;
    }

    public function getLocalizedTimeFormat()
    {
        return $this->localizedTimeFormat;
    }

    public function setAddFormOptions(array $complementary_options = array())
    {
        foreach ($complementary_options as $option => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (preg_match('/\.(.+)/i', $k, $matches)) {
                        // enable to call php function to build your form options
                        $value = call_user_func_array($matches[1], $v);
                    }
                }
            }

            $this->formOptions[$option] = $value;
        }
    }

    public function setExtras(array $values)
    {
        $this->extras = $values;
    }

    public function getExtras()
    {
        return $this->extras;
    }

    public function setSortType($type)
    {
        $this->sortType = $type;
    }

    public function getSortType()
    {
        return $this->sortType;
    }

    public function getCustomView()
    {
        return $this->customView;
    }

    public function setCustomView($customView)
    {
        $this->customView = $customView;
    }

    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function setGroups(array $groups = array())
    {
        return $this->groups = $groups;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}
