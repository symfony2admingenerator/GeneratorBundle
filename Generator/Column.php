<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Admingenerator\GeneratorBundle\Exception\InvalidOptionException;

/**
 * This class describe a column
 *
 * @author cedric Lombardot
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
use Doctrine\Inflector\InflectorFactory;

class Column
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $sortable = true;

    /**
     * @var string
     */
    protected $sortOn;

    /**
     * @var string
     */
    protected $sortType = 'default';

    /**
     * @var bool
     */
    protected $filterable = false;

    /**
     * @var string
     */
    protected $filterOn;

    /**
     * @var string
     */
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
     * Since the functions may vary in different Database types, Admingenerator does not,
     * by default, format fields in any way. It is up to developer to implement this for his fields.
     *
     * Note: this feature was created mainly for Date/DateTime fields.
     */
    protected $dbFormat;

    /**
     * @var string
     */
    protected $customView = null;

    /**
     * @var string
     */
    protected $formType;

    /**
     * @var string
     */
    protected $filterType;

    /**
     * @var array
     */
    protected $formOptions = array();

    /**
     * @var array
     */
    protected $filterOptions = array();

    /**
     * @var string
     */
    protected $getter;

    /**
     * @var string
     */
    protected $label = null;

    /**
     * @var string
     */
    protected $help;

    /**
     * @var string
     */
    protected $localizedDateFormat;

    /**
     * @var string
     */
    protected $localizedTimeFormat;

    /**
     * @var string
     */
    protected $primaryKey = null;

    /**
     * For special columns template
     *
     * @var array
     */
    protected $extras = array();

    /**
     * @var string
     */
    protected $credentials = 'AdmingenAllowed';

    /**
     * @var string
     */
    protected $filtersCredentials = false;

    /**
     * @var string
     */
    protected $gridClass = '';

    /* Used for more verbose error messages */
    protected $debug = array();

    protected $manyToMany = false;

    /**
     * @param string $name
     * @param array  $debug
     */
    public function __construct($name, $debug)
    {
        $this->name     = $name;
        $this->filterOn = $name;
        $this->debug    = $debug;
    }

    public function setProperty($option, $value)
    {
        $setter = 'set'.InflectorFactory::create()->build()->classify($option);

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
        return $this->getter ? $this->getter : InflectorFactory::create()->build()->camelize($this->name);
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
        return $this->filterOn;
    }

    public function setFilterOn($filterOn)
    {
        return $this->filterOn = $filterOn;
    }

    /**
     * @param string $text
     */
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

    public function setFilterOptions($filterOptions)
    {
        $this->filterOptions = $filterOptions;
    }

    public function getFilterOptions()
    {
        return $this->filterOptions;
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

    public function setAddFormOptions(array $additionalOptions = array())
    {
        foreach ($additionalOptions as $name => $option) {
            $this->formOptions[$name] = $this->parseOption($option);
        }
    }

    public function setAddFilterOptions(array $additionalOptions = array())
    {
        foreach ($additionalOptions as $name => $option) {
           $this->filterOptions[$name] = $this->parseOption($option);
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

    public function setCredentials($credentials = '')
    {
        return $this->credentials = $credentials;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }

    public function setFiltersCredentials($credentials = '')
    {
        $this->filtersCredentials = $credentials;
    }

    public function getFiltersCredentials()
    {
        if (false === $this->filtersCredentials) {
            return $this->credentials;
        }

        return $this->filtersCredentials;
    }

    public function setGridClass($gridClass)
    {
        $this->gridClass = $gridClass;
    }

    public function getGridClass()
    {
        return $this->gridClass;
    }

    public function setManyToMany($manyToMany)
    {
        $this->manyToMany = $manyToMany;
    }

    public function getManyToMany()
    {
        return $this->manyToMany;
    }

    protected function parseOption($option)
    {
        if (!is_array($option)) {
            return $option;
        }

        foreach ($option as $k => $v) {
            if (preg_match('/^\.(.+)/i', $k, $matches)) {
                // enable to call php function to build your form options
                // Only if key STARTS with a dot (.). Values are used a params for the
                // function. See tests for sample.
                return call_user_func_array($matches[1], $v);
            }
        }

        return $option;
    }
}
