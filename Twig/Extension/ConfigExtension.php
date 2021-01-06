<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class ConfigExtension extends AbstractExtension
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
            'admingenerator_config' => new TwigFunction('admingenerator_config', array($this, 'getAdmingeneratorConfig')),
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
            if (!array_key_exists($key, $search_in)) {
                throw new \InvalidArgumentException('Unknown parameter "admingenerator.' . $name . '".');
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
