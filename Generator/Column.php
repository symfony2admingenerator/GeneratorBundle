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

    protected bool $sortable = true;

    protected string $sortOn = '';

    protected string $sortType = 'default';

    protected bool $filterable = false;

    protected ?string $filterOn = null;

    protected ?string $dbType = null;

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
    protected ?string $dbFormat = null;

    protected ?string $customView = null;

    protected ?string $formType = null;

    protected ?string $filterType = null;

    protected array $formOptions = [];

    protected array $filterOptions = [];

    protected ?string $getter = null;

    protected ?string $label = null;

    protected ?string $help = null;

    protected ?string $localizedDateFormat = null;

    protected ?string $localizedTimeFormat = null;

    protected ?string $primaryKey = null;

    /**
     * For special columns template
     */
    protected array $extras = [];

    protected string|array $credentials = 'AdmingenAllowed';

    protected bool $filtersCredentials = false;

    protected string $gridClass = '';

    protected bool $manyToMany = false;

    public function __construct(protected readonly string $name, protected array|false $debug)
    {
        $this->filterOn = $name;
    }

    public function setProperty(string $option, mixed $value): void
    {
        $setter = 'set'.InflectorFactory::create()->build()->classify($option);

        if (method_exists($this, $setter)) {
            $this->{$setter}($value);
        } else {
            throw new InvalidOptionException($option, $this->name, $this->debug['generator'], $this->debug['builder']);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGetter(): string
    {
        return $this->getter ?: InflectorFactory::create()->build()->camelize($this->name);
    }

    public function setGetter(string $getter): void
    {
        $this->getter = $getter;
    }

    public function getLabel(): string
    {
        return false !== $this->label && empty($this->label)
            ? $this->humanize($this->getName())
            : $this->label;
    }

    public function setLabel(string $label): string
    {
        return $this->label = $label;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(string $help): string
    {
        return $this->help = $help;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function isReal(): bool
    {
        return $this->dbType != 'virtual';
    }

    public function getSortable(): bool
    {
        return $this->sortable;
    }

    public function setSortable(bool $sortable): bool
    {
        return $this->sortable = filter_var($sortable, FILTER_VALIDATE_BOOLEAN);
    }

    public function getSortOn(): string
    {
        return $this->sortOn != "" ? $this->sortOn : $this->name;
    }

    public function setSortOn(string $sort_on): string
    {
        return $this->sortOn = $sort_on;
    }

    public function getFilterable(): bool
    {
        return $this->filterable;
    }

    public function setFilterable(bool $filterable): bool
    {
        return $this->filterable = filter_var($filterable, FILTER_VALIDATE_BOOLEAN);
    }

    public function getFilterOn(): ?string
    {
        return $this->filterOn;
    }

    public function setFilterOn(string $filterOn): string
    {
        return $this->filterOn = $filterOn;
    }

    private function humanize(string $text): string
    {
        return ucfirst(str_replace('_', ' ', $text));
    }

    public function setDbType(string $dbType): void
    {
        $this->dbType = $dbType;
    }

    public function getDbType(): ?string
    {
        return $this->dbType;
    }

    public function setDbFormat(string $dbFormat): void
    {
        $this->dbFormat = $dbFormat;
    }

    public function getDbFormat(): ?string
    {
        return $this->dbFormat;
    }

    public function setFormType(string $formType): void
    {
        $this->formType = $formType;
    }

    public function getFormType(): ?string
    {
        return $this->formType;
    }

    public function setFormOptions(array $formOptions): void
    {
        $this->formOptions = $formOptions;
    }

    public function getFormOptions(): array
    {
        return $this->formOptions;
    }

    public function setFilterType(string $filterType): void
    {
        $this->filterType = $filterType;
    }

    public function getFilterType(): ?string
    {
        return $this->filterType;
    }

    public function setFilterOptions(array $filterOptions): void
    {
        $this->filterOptions = $filterOptions;
    }

    public function getFilterOptions(): array
    {
        return $this->filterOptions;
    }

    public function setLocalizedDateFormat(string $localizedDateFormat): void
    {
        $this->localizedDateFormat = $localizedDateFormat;
    }

    public function getLocalizedDateFormat(): ?string
    {
        return $this->localizedDateFormat;
    }

    public function setLocalizedTimeFormat(string $localizedTimeFormat): void
    {
        $this->localizedTimeFormat = $localizedTimeFormat;
    }

    public function getLocalizedTimeFormat(): ?string
    {
        return $this->localizedTimeFormat;
    }

    public function setAddFormOptions(array $additionalOptions = []): void
    {
        foreach ($additionalOptions as $name => $option) {
            $this->formOptions[$name] = $this->parseOption($option);
        }
    }

    public function setAddFilterOptions(array $additionalOptions = []): void
    {
        foreach ($additionalOptions as $name => $option) {
           $this->filterOptions[$name] = $this->parseOption($option);
        }
    }

    public function setExtras(array $values): void
    {
        $this->extras = $values;
    }

    public function getExtras(): array
    {
        return $this->extras;
    }

    public function setSortType(string $type): void
    {
        $this->sortType = $type;
    }

    public function getSortType(): ?string
    {
        return $this->sortType;
    }

    public function getCustomView(): ?string
    {
        return $this->customView;
    }

    public function setCustomView(string $customView): void
    {
        $this->customView = $customView;
    }

    public function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    public function setCredentials(string|array $credentials = ''): string|array
    {
        return $this->credentials = $credentials;
    }

    public function getCredentials(): string|array
    {
        return $this->credentials;
    }

    public function setFiltersCredentials(string|array $credentials = ''): void
    {
        $this->filtersCredentials = $credentials;
    }

    public function getFiltersCredentials(): string|array
    {
        if (false === $this->filtersCredentials) {
            return $this->credentials;
        }

        return $this->filtersCredentials;
    }

    public function setGridClass(string $gridClass): void
    {
        $this->gridClass = $gridClass;
    }

    public function getGridClass(): string
    {
        return $this->gridClass;
    }

    public function setManyToMany(bool $manyToMany): void
    {
        $this->manyToMany = $manyToMany;
    }

    public function getManyToMany(): bool
    {
        return $this->manyToMany;
    }

    protected function parseOption(mixed $option): mixed
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
