<?php

namespace Admingenerator\GeneratorBundle\EventListener;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Finder\Finder;
use Admingenerator\GeneratorBundle\Exception\NotAdminGeneratedException;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Environment;
use Twig\Extension\CoreExtension;

class ControllerListener
{
    protected CacheInterface $cacheProvider;

    protected string $cacheSuffix;

    public function __construct(
        protected readonly ContainerInterface $container,
        protected readonly Environment $twig)
    {
        $this->cacheProvider = new ArrayAdapter();
        $this->cacheSuffix = 'default';
    }

    public function setCacheProvider(?CacheInterface $cacheProvider = null, $cacheSuffix = 'default'): void
    {
        $this->cacheProvider = $cacheProvider;
        $this->cacheSuffix = $cacheSuffix;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST === $event->getRequestType()
            && !$this->container->hasParameter('admingenerator.generate_base_in_project_dir_directory')) {
            try {
                $controller = $event->getRequest()->attributes->get('_controller');

                if (is_string($controller) && strstr($controller, '::')) { //Check if it's a "real controller", not assetic for example
                    $generatorYaml = $this->getGeneratorYml($controller);

                    $generator = $this->getGenerator($generatorYaml);
                    $generator->setGeneratorYml($generatorYaml);
                    $generator->setBaseGeneratorName($this->getBaseGeneratorName($controller));
                    $generator->build();

                }
            } catch (NotAdminGeneratedException) {
                //Lets the word running this is not an admin generated module
            }
        }

        if ($this->container->hasParameter('admingenerator.twig')) {
            $twig_params = $this->container->getParameter('admingenerator.twig');

            if (isset($twig_params['date_format'])) {
                $this->twig->getExtension(CoreExtension::class)->setDateFormat($twig_params['date_format'], '%d days');
            }

            if (isset($twig_params['number_format'])) {
                $this->twig->getExtension(CoreExtension::class)->setNumberFormat($twig_params['number_format']['decimal'], $twig_params['number_format']['decimal_point'], $twig_params['number_format']['thousand_separator']);
            }
        }
    }

    protected function getGenerator(string $generatorYaml): ?object
    {
        $generatorName = $this->cacheProvider->get(
            $this->getCacheKey($generatorYaml.'_generator'),
            fn () => Yaml::parse(file_get_contents($generatorYaml))['generator']
        );

        return $this->container->get($generatorName);
    }

    protected function getBaseGeneratorName($controller): string
    {
        preg_match('/(.+)Controller(.+)::.+/', $controller, $matches);

        //Find if it's a name-generator or generator.yml
        if (isset($matches[2]) && strstr($matches[2], '\\')) {
            if (3 != count(explode('\\', $matches[2]))) {
                return '';
            }

            list(, $generatorName) = explode('\\', $matches[2], 3);

            return $generatorName;
        }

        return '';
    }

    protected function getGeneratorYml(string $controller): string
    {
        $generatorYml = $this->cacheProvider->get($this->getCacheKey($controller), function () use ($controller) {
            try {
                return $this->findGeneratorYml($controller);
            } catch (NotAdminGeneratedException) {
                return 'NotAdminGeneratedException';
            }
        });

        if ('NotAdminGeneratedException' == $generatorYml) {
            throw new NotAdminGeneratedException();
        }

        return $generatorYml;
    }

    /**
     * @TODO: Find objects in vendor dirs
     */
    protected function findGeneratorYml(string $controller): string|bool
    {
        preg_match('/(.+)?Controller.+::.+/', $controller, $matches);
        if (count($matches) > 1) {
          $dir = str_replace('\\', DIRECTORY_SEPARATOR, $matches[1]);

          $generatorName = $this->getBaseGeneratorName($controller) ? $this->getBaseGeneratorName($controller) . '-' : '';
          $generatorName .= 'generator.yml';

          $finder = new Finder();
          $finder->files()
              ->name($generatorName);

          if (is_dir($src = realpath($this->container->getParameter('kernel.project_dir') . '/src/' . $dir . '/Resources/config'))) {
            $namespace_directory = $src;
          } else {
            $namespace_directory = realpath($this->container->getParameter('kernel.project_dir') . '/vendor/bundles/' . $dir . '/Resources/config');
          }

          if (is_dir($namespace_directory)) {
            $finder->in($namespace_directory);
            $it = $finder->getIterator();
            $it->rewind();

            if ($it->valid()) {
              return $it->current()->getRealpath();
            }
          }
        }

        throw new NotAdminGeneratedException;

    }

    protected function getCacheKey(string $key): string
    {
        return str_replace(str_split('@{}()\/:'), '_ ', sprintf('admingen_controller_%s_%s', $key, $this->cacheSuffix));
    }

}
