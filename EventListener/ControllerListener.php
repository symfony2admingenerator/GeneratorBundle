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
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Extension\CoreExtension;

class ControllerListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var CacheInterface
     */
    protected $cacheProvider;

    /**
     * @var string
     */
    protected $cacheSuffix;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->cacheProvider = new ArrayAdapter();
        $this->cacheSuffix = 'default';
    }

    /**
     * @param CacheInterface $cacheProvider
     * @param string         $cacheSuffix
     */
    public function setCacheProvider(CacheInterface $cacheProvider = null, $cacheSuffix = 'default')
    {
        $this->cacheProvider = $cacheProvider;
        $this->cacheSuffix = $cacheSuffix;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            try {
                $controller = $event->getRequest()->attributes->get('_controller');

                if (strstr($controller, '::')) { //Check if its a "real controller" not assetic for example
                    $generatorYaml = $this->getGeneratorYml($controller);

                    $generator = $this->getGenerator($generatorYaml);
                    $generator->setGeneratorYml($generatorYaml);
                    $generator->setBaseGeneratorName($this->getBaseGeneratorName($controller));
                    $generator->build();

                }
            } catch (NotAdminGeneratedException $e) {
                //Lets the word running this is not an admin generated module
            }
        }

        if ($this->container->hasParameter('admingenerator.twig')) {
            $twig_params = $this->container->getParameter('admingenerator.twig');

            if (isset($twig_params['date_format'])) {
                $this->container->get('twig')->getExtension(CoreExtension::class)->setDateFormat($twig_params['date_format'], '%d days');
            }

            if (isset($twig_params['number_format'])) {
                $this->container->get('twig')->getExtension(CoreExtension::class)->setNumberFormat($twig_params['number_format']['decimal'], $twig_params['number_format']['decimal_point'], $twig_params['number_format']['thousand_separator']);
            }
        }
    }

    /**
     * @param string $generatorYaml
     */
    protected function getGenerator($generatorYaml)
    {
        $generatorName = $this->cacheProvider->get($this->getCacheKey($generatorYaml.'_generator'), function (ItemInterface $item) use ($generatorYaml) {
            return Yaml::parse(file_get_contents($generatorYaml))['generator'];
        });

        return $this->container->get($generatorName);
    }

    protected function getBaseGeneratorName($controller)
    {
        preg_match('/(.+)Controller(.+)::.+/', $controller, $matches);

        //Find if its a name-generator or generator.yml
        if (isset($matches[2]) && strstr($matches[2], '\\')) {
            if (3 != count(explode('\\', $matches[2]))) {
                return '';
            }

            list($firstSlash, $generatorName) = explode('\\', $matches[2], 3);

            return $generatorName;
        }

        return '';
    }

    /**
     * @param  string                     $controller
     * @throws NotAdminGeneratedException
     * @return string
     */
    protected function getGeneratorYml($controller)
    {
        $generatorYml = $this->cacheProvider->get($this->getCacheKey($controller), function (ItemInterface $item) use ($controller) {
            try {
                return $this->findGeneratorYml($controller);
            } catch (NotAdminGeneratedException $e) {
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
     * @param  string                     $controller
     * @throws NotAdminGeneratedException
     */
    protected function findGeneratorYml($controller)
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

    /**
     * @param  string $key
     * @return string
     */
    protected function getCacheKey($key)
    {
        return str_replace(str_split('@{}()\/:'), '_ ', sprintf('admingen_controller_%s_%s', $key, $this->cacheSuffix));
    }

}
