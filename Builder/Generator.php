<?php

namespace Admingenerator\GeneratorBundle\Builder;

/**
 * @author Cedric LOMBARDOT
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
use Symfony\Component\Yaml\Yaml;
use TwigGenerator\Builder\Generator as TwigGeneratorGenerator;
use TwigGenerator\Builder\BuilderInterface;
use Symfony\Component\Routing\RouterInterface;

class Generator extends TwigGeneratorGenerator
{
    const TEMP_DIR_PREFIX = 'Admingenerator';

    /**
     * @var array $yaml The yaml array.
     */
    protected $yaml;

    /**
     * @var string $baseController The base controller.
     */
    protected $baseController;

    /**
     * @var string $columnClass The column class.
     */
    protected $columnClass = 'Admingenerator\GeneratorBundle\Generator\Column';

    /**
     * @var string $baseAdminTemplate The base admin template.
     */
    protected $baseAdminTemplate = 'AdmingeneratoroldThemeBundle::base.html.twig';

    /**
     * @var string $baseGeneratorName The base generator name.
     */
    protected $baseGeneratorName;

    /**
     * @var array $bundleConfig Generator bundle config.
     */
    protected $bundleConfig;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * Init a new generator and automatically define the base of tempDir
     *
     * @param string $cacheDir
     * @param Filepath $yaml
     */
    public function __construct($cacheDir, $yaml)
    {
        parent::__construct($cacheDir);
        $this->setYamlConfig(Yaml::parse(file_get_contents($yaml)));
    }

    /**
     * @return string The base admin template.
     */
    public function getBaseAdminTemplate()
    {
        return $this->baseAdminTemplate;
    }

    /**
     * @param string $baseAdminTemplate The base admin template.
     * @return void
     */
    public function setBaseAdminTemplate($baseAdminTemplate)
    {
        $this->baseAdminTemplate = $baseAdminTemplate;
    }

    /**
     * Add a builder
     * @param BuilderInterface $builder
     *
     * @return void
     */
    public function addBuilder(BuilderInterface $builder)
    {
        parent::addBuilder($builder);

        $params = $this->getFromYaml('params', array());
        $params = $this->applyActionsBuilderDefaults($params);

        $params = $this->mergeParameters(
            $params,
            $this->getFromYaml(sprintf('builders.%s.params', $builder->getYamlKey()), array())
        );

        $builder->setVariables($params);
        $builder->setColumnClass($this->getColumnClass());
    }

    /**
     * Merge parameters from global definition with builder definition
     * Fields and actions have special behaviors:
     *     - fields are merged and all global fields are still available
     *     from a builder
     *     - actions depend of builder. List of available actions come
     *     from builder, configuration is a merge between builder configuration
     *     and global configuration
     *
     * @param  array $global
     * @param  array $builder
     *
     * @return array
     */
    protected function mergeParameters(array $global, array $builder)
    {
        foreach ($global as $param => &$value) {
            if (!array_key_exists($param, $builder)) {
                continue;
            }

            if (!in_array($param, array('fields', 'actions', 'object_actions', 'batch_actions'))) {
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

            $configurations = array();
            foreach ($builder[$param] as $name => $configuration) {
                if (!is_array($configuration) && !is_null($configuration)) {
                    throw new \InvalidArgumentException(
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

            if (in_array($param, array('actions', 'object_actions', 'batch_actions'))) {
                // Actions list comes from builder
                $value = $configurations;
            } else {
                // All fields are still available in a builder
                $value = array_merge($value ?:array(), $configurations);
            }
        }

        // If builder doesn't have actions/object_actions/batch_actions remove it from merge.
        $global['actions'] = array_key_exists('actions', $builder) ? $global['actions'] : array();
        $global['object_actions'] = array_key_exists('object_actions', $builder) ? $global['object_actions'] : array();
        $global['batch_actions'] = array_key_exists('batch_actions', $builder) ? $global['batch_actions'] : array();

        return array_merge($global, array_diff_key($builder, $global));
    }

    /**
     * Merge configuration on a single level
     *
     * @param  array $global
     * @param  array $builder
     *
     * @return array
     */
    protected function mergeConfiguration(array $global, array $builder)
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
                throw new \InvalidArgumentException('Invalid generator');
            }

            $value = array_replace($value, $builder[$name]);
        }

        return array_merge($global, array_diff_key($builder, $global));
    }

    /**
     * Recursively replaces Base array values with Replacement array values
     * while keeping indexes of Replacement array
     *
     * @param array $base        Base array
     * @param array $replacement Replacement array
     *
     * @return array
     */
    protected function recursiveReplace($base, $replacement)
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
     *
     * @return array
     */
    protected function applyActionsBuilderDefaults(array $params)
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

                    $params['object_actions'][$name] = $this->recursiveReplace(array(
                        'route'         => $routeBase.'_object',
                        'label'         => $baseKey.'.label',
                        'csrfProtected' => true,
                        'params'        => array(
                            'pk'        => '{{ '.$this->getBaseGeneratorName().'.id }}',
                            'action'    => $name,
                        ),
                        'options'       => array(
                            'title'     => $baseKey.'.title',
                            'success'   => $baseKey.'.success',
                            'error'     => $baseKey.'.error',
                        ),
                    ), $customized);
                }
            }
        }

        if (array_key_exists('batch_actions', $params) && is_array($params['batch_actions'])) {
            foreach ($params['batch_actions'] as $name => $config) {
                $baseKey = 'batch_actions.'.$name;

                if (is_array($config) && array_key_exists('route', $config) && $config['route'] === 'inject_batch_defaults') {
                    $params['batch_actions'][$name] = $this->recursiveReplace($params['batch_actions'][$name], array(
                        'route'         => $routeBase.'_batch',
                        'label'         => $baseKey.'.label',
                        'csrfProtected' => true,
                        'params'        => array(
                            'action'    => $name,
                        ),
                        'options'       => array(
                            'title'     => $baseKey.'.title',
                            'success'   => $baseKey.'.success',
                            'error'     => $baseKey.'.error',
                        ),
                    ));
                }
            }
        }

        return $params;
    }

    /**
     * @param string $columnClass
     * @return void
     */
    public function setColumnClass($columnClass)
    {
        $this->columnClass = $columnClass;
    }

    /**
     * @return string The column class.
     */
    public function getColumnClass()
    {
        return $this->columnClass;
    }

    /**
     * Set the yaml to pass all the vars to the builders
     *
     * @param Yaml $yaml
     * @return void
     */
    protected function setYamlConfig(array $yaml)
    {
        $this->yaml = array_replace_recursive(
            Yaml::parse(file_get_contents(__DIR__.'/../Resources/config/default.yml')),
            $yaml
        );
    }

    /**
     * @param string    $yamlPath   Path string with point for levels.
     * @param mixed     $default    Value to default to, path key not found.
     * @return mixed
     */
    public function getFromYaml($yamlPath, $default = null)
    {
        return $this->getFromArray(
            $this->yaml,
            $yamlPath,
            $default
        );
    }

    /**
     * @param object $fieldGuesser The fieldguesser.
     * @return void
     */
    public function setFieldGuesser($fieldGuesser)
    {
        $this->fieldGuesser = $fieldGuesser;
    }

    /**
     * @return object The fieldguesser.
     */
    public function getFieldGuesser()
    {
        return $this->fieldGuesser;
    }

    /**
     * @param string $baseController
     */
    public function setBaseController($baseController)
    {
        $this->baseController = $baseController;
    }

    /**
     * @return string Base controller.
     */
    public function getBaseController()
    {
        return $this->baseController;
    }

    /**
     * @param string $baseGeneratorName
     */
    public function setBaseGeneratorName($baseGeneratorName)
    {
        $this->baseGeneratorName = $baseGeneratorName;
    }

    /**
     * @return string Base generator name.
     */
    public function getBaseGeneratorName()
    {
        return $this->baseGeneratorName;
    }

    /**
     * @return string Generated controller directory.
     */
    public function getGeneratedControllerFolder()
    {
        return 'Base'.$this->baseGeneratorName.'Controller';
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return void
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface $router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param array $bundleConfig
     */
    public function setBundleConfig(array $bundleConfig)
    {
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * @return array
     */
    public function getBundleConfig()
    {
        return $this->bundleConfig;
    }

    /**
     * @param string    $configPath Path string with point for levels.
     * @param mixed     $default    Value to default to, path key not found.
     * @return mixed
     */
    public function getFromBundleConfig($configPath, $default = null)
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
     * @return mixed
     */
    protected function getFromArray($array, $path, $default = null)
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
