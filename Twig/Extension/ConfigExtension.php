<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class ConfigExtension extends AbstractExtension
{
    public function __construct(protected readonly array $bundleConfig)
    {
    }

    public function getFunctions(): array
    {
        $options = ['is_safe' => ['html']];
        return [
            'admingenerator_config' => new TwigFunction('admingenerator_config', $this->getAdmingeneratorConfig(...), $options),
        ];
    }

    public function getAdmingeneratorConfig(string $name): ?string
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

    public function getName(): string
    {
        return 'admingenerator_config';
    }
}
