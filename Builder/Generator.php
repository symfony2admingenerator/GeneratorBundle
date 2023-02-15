<?php

namespace Admingenerator\GeneratorBundle\Builder;

/**
 * @author Cedric LOMBARDOT
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */

use Admingenerator\GeneratorBundle\Generator\Column;
use Admingenerator\GeneratorBundle\Guesser\FieldGuesser;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;
use TwigGenerator\Builder\Generator as TwigGeneratorGenerator;
use TwigGenerator\Builder\BuilderInterface;
use Symfony\Component\Routing\RouterInterface;

class Generator extends TwigGeneratorGenerator
{
    protected const TEMP_DIR_PREFIX = 'Admingenerator';

    protected array $yaml = [];

    protected string $baseController = '';

    protected string $columnClass = Column::class;

    protected string $baseAdminTemplate = 'AdmingeneratoroldThemeBundle::base.html.twig';

    protected string $baseGeneratorName = '';

    protected array $bundleConfig = [];

    protected RouterInterface $router;

    protected string $templateBaseDir = '';

    protected FieldGuesser $fieldGuesser;

    public function setTemplateBaseDir($templateBaseDir)
    {
        $this->templateBaseDir = $templateBaseDir;
    }

    public function getTemplateBaseDir(): string
    {
        return $this->templateBaseDir;
    }

    /**
     * Init a new generator and automatically define the base of tempDir
     */
    public function __construct(string $cacheDir, string $yaml)
    {
        parent::__construct($cacheDir);
        $this->setYamlConfig(Yaml::parse(file_get_contents($yaml)));
    }

    public function getBaseAdminTemplate(): string
    {
        return $this->baseAdminTemplate;
    }

    public function setBaseAdminTemplate(string $baseAdminTemplate): void
    {
        $this->baseAdminTemplate = $baseAdminTemplate;
    }

    public function addBuilder(BuilderInterface $builder): BuilderInterface
    {
        parent::addBuilder($builder);

        $params = $this->getFromYaml('params', []);
        $params = $this->applyActionsBuilderDefaults($params);

        $params = $this->mergeParameters(
            $params,
            $this->getFromYaml(sprintf('builders.%s.params', $builder->getYamlKey()), [])
        );

        $builder->setVariables($params);
        $builder->setColumnClass($this->getColumnClass());

        return $builder;
    }

    /**
     * Merge parameters from global definition with builder definition
     * Fields and actions have special behaviors:
     *     - fields are merged and all global fields are still available
     *     from a builder
     *     - actions depend of builder. List of available actions come
     *     from builder, configuration is a merge between builder configuration
     *     and global configuration
     */
    protected function mergeParameters(array $global, array $builder): array
    {
        foreach ($global as $param => &$value) {
            if (!array_key_exists($param, $builder)) {
                continue;
            }

            if (!in_array($param, ['fields', 'actions', 'object_actions', 'batch_actions'])) {
                if (is_array($value)) {
                    $value = $this->recursiveReplace($value, $builder[$param]);
                } else {
                    $value = $builder[$param];
                }

                continue;
            }

            // Grab builder configuration only if defined
            if (is_null($builder[$param])) {
                continue;
            }

            $configurations = [];
            foreach ($builder[$param] as $name => $configuration) {
                if (!is_array($configuration) && !is_null($configuration)) {
                    throw new InvalidArgumentException(
                        sprintf('Invalid %s "%s" builder definition for %s', $param, $name, $this->getFromYaml('params.model'))
                    );
                }

                if (!is_null($value) && array_key_exists($name, $value)) {
                    $configurations[$name] = $configuration
                        ? $this->mergeConfiguration($value[$name], $configuration) // Override definition
                        : $value[$name]; // Configuration is null => use global definition
                } else {
                    // New definition (new field, new action) from builder
                    $configurations[$name] = $configuration;
                }
            }

            if (in_array($param, ['actions', 'object_actions', 'batch_actions'])) {
                // Actions list comes from builder
                $value = $configurations;
            } else {
                // All fields are still available in a builder
                $value = array_merge($value ?: [], $configurations);
            }
        }

        // If builder doesn't have actions/object_actions/batch_actions remove it from merge.
        $global['actions'] = array_key_exists('actions', $builder) ? $global['actions'] : [];
        $global['object_actions'] = array_key_exists('object_actions', $builder) ? $global['object_actions'] : [];
        $global['batch_actions'] = array_key_exists('batch_actions', $builder) ? $global['batch_actions'] : [];

        return array_merge($global, array_diff_key($builder, $global));
    }

    /**
     * Merge configuration on a single level
     */
    protected function mergeConfiguration(array $global, array $builder): array
    {
        foreach ($global as $name => &$value) {
            if (!array_key_exists($name, $builder) || is_null($builder[$name])) {
                continue;
            }

            if (!is_array($value)) {
                $value = $builder[$name];

                continue;
            }

            if (!is_array($builder[$name])) {
                throw new InvalidArgumentException('Invalid generator');
            }

            $value = array_replace($value, $builder[$name]);
        }

        return array_merge($global, array_diff_key($builder, $global));
    }

    /**
     * Recursively replaces Base array values with Replacement array values
     * while keeping indexes of Replacement array
     */
    protected function recursiveReplace(array $base, array $replacement): array
    {
        $replace_values_recursive = function (array $array, array $order) use (&$replace_values_recursive) {
            $array = array_replace($order, array_replace($array, $order));

            foreach ($array as $key => &$value) {
                if (is_array($value)) {
                    $value = (array_key_exists($key, $order) && is_array($order[$key]))
                        ? $replace_values_recursive($value, $order[$key])
                        : $value
                    ;
                }
            }

            return $array;
        };

        return $replace_values_recursive($base, $replacement);
    }

    /**
     * Inject default batch and object actions settings
     */
    protected function applyActionsBuilderDefaults(array $params): array
    {
        if (!array_key_exists('namespace_prefix', $params) || !array_key_exists('bundle_name', $params)) {
            return $params;
        }

        $routeBase = $params['namespace_prefix'].'_'.$params['bundle_name'].'_'.$this->getBaseGeneratorName();

        if (array_key_exists('object_actions', $params) && is_array($params['object_actions'])) {
            foreach ($params['object_actions'] as $name => $config) {
                $baseKey = 'object_actions.'.$name;

                if (is_array($config) && array_key_exists('route', $config) && $config['route'] === 'inject_object_defaults') {
                    $customized = $params['object_actions'][$name];
                    unset($customized['route']);

                    $params['object_actions'][$name] = $this->recursiveReplace([
                        'route'         => $routeBase.'_object',
                        'label'         => $baseKey.'.label',
                        'csrfProtected' => true,
                        'params'        => [
                            'pk'        => '{{ '.$this->getBaseGeneratorName().'.id }}',
                            'action'    => $name,
                        ],
                        'options'       => [
                            'title'     => $baseKey.'.title',
                            'success'   => $baseKey.'.success',
                            'error'     => $baseKey.'.error',
                        ],
                    ], $customized);
                }
            }
        }

        if (array_key_exists('batch_actions', $params) && is_array($params['batch_actions'])) {
            foreach ($params['batch_actions'] as $name => $config) {
                $baseKey = 'batch_actions.'.$name;

                if (is_array($config) && array_key_exists('route', $config) && $config['route'] === 'inject_batch_defaults') {
                    $params['batch_actions'][$name] = $this->recursiveReplace($params['batch_actions'][$name], [
                        'route'         => $routeBase.'_batch',
                        'label'         => $baseKey.'.label',
                        'csrfProtected' => true,
                        'params'        => [
                            'action'    => $name,
                        ],
                        'options'       => [
                            'title'     => $baseKey.'.title',
                            'success'   => $baseKey.'.success',
                            'error'     => $baseKey.'.error',
                            'notfound'  => $baseKey.'.notfound',
                        ],
                    ]);
                }
            }
        }

        return $params;
    }

    public function setColumnClass(string $columnClass): void
    {
        $this->columnClass = $columnClass;
    }

    protected function getColumnClass(): string
    {
        return $this->columnClass;
    }

    /**
     * Set the yaml to pass all the vars to the builders
     */
    protected function setYamlConfig(array $yaml): void
    {
        $this->yaml = array_replace_recursive(
            Yaml::parse(file_get_contents(__DIR__.'/../Resources/config/default.yml')),
            $yaml
        );
    }

    /**
     * @param string    $yamlPath   Path string with point for levels.
     * @param mixed     $default    Value to default to, path key not found.
     */
    public function getFromYaml(string $yamlPath, mixed $default = null): mixed
    {
        return $this->getFromArray(
            $this->yaml,
            $yamlPath,
            $default
        );
    }

    public function setFieldGuesser(FieldGuesser $fieldGuesser): void
    {
        $this->fieldGuesser = $fieldGuesser;
    }

    public function getFieldGuesser(): FieldGuesser
    {
        return $this->fieldGuesser;
    }

    public function setBaseController(string $baseController): void
    {
        $this->baseController = $baseController;
    }

    public function getBaseController(): string
    {
        return $this->baseController;
    }

    public function setBaseGeneratorName(string $baseGeneratorName): void
    {
        $this->baseGeneratorName = $baseGeneratorName;
    }

    public function getBaseGeneratorName(): string
    {
        return $this->baseGeneratorName;
    }

    public function getGeneratedControllerFolder(): string
    {
        return 'Base'.$this->baseGeneratorName.'Controller';
    }

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    public function setBundleConfig(array $bundleConfig): void
    {
        $this->bundleConfig = $bundleConfig;
    }

    public function getBundleConfig(): array
    {
        return $this->bundleConfig;
    }

    /**
     * @param string    $configPath Path string with point for levels.
     * @param mixed     $default    Value to default to, path key not found.
     */
    public function getFromBundleConfig(string $configPath, mixed $default = null): mixed
    {
        return $this->getFromArray(
            $this->bundleConfig,
            $configPath,
            $default
        );
    }

    /**
     * @param array     $array      The array to traverse.
     * @param string    $path       Path string with point for levels.
     * @param mixed     $default    Value to default to, path key not found.
     */
    protected function getFromArray(array $array, string $path, mixed $default = null): mixed
    {
        $search_in = $array;
        $path = explode('.', $path);
        foreach ($path as $key) {
            if (!isset($search_in[$key])) {
                return $default;
            }
            $search_in = $search_in[$key];
        }

        return $search_in;
    }
}
