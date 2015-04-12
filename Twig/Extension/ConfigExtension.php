<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class ConfigExtension extends \Twig_Extension
{
    /**
     * @var array
     */
    protected $bundleConfig;

    /**
     * @param array $bundleConfig
     */
    public function __construct(array $bundleConfig)
    {
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'admingenerator_config' => new \Twig_Function_Method($this, 'getAdmingeneratorConfig'),
        );
    }

    /**
     * Returns admingenerator parameter
     *
     * @param  string   $name
     * @return string   Parameter value
     */
    public function getAdmingeneratorConfig($name)
    {
        $search_in = $this->bundleConfig;
        $path = explode('.', $name);
        foreach ($path as $key) {
            if (!isset($search_in[$key])) {
                throw new \InvalidArgumentException('Unknown parameter "admingenerator.'+$name+'".');
            }
            $search_in = $search_in[$key];
        }
        return $search_in;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'admingenerator_config';
    }
}
