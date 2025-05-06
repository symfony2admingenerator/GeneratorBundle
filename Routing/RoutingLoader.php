<?php

namespace Admingenerator\GeneratorBundle\Routing;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class RoutingLoader extends FileLoader
{
    // Assoc between a controller and its route path
    //@todo make an object for this
    protected array $actions = [
        'list' => [
            'path'         => '/',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['GET'],
        ],
        'excel'=> [
            'path'         => '/excel/{key}',
            'defaults'     => ['key'=>null],
            'requirements' => [],
            'methods'      => ['GET'],
            'controller'   => 'excel',
        ],
        'edit' => [
            'path'         => '/{pk}/edit',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['GET'],
        ],
        'update' => [
            'path'         => '/{pk}/update',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['POST'],
            'controller'   => 'edit',
        ],
        'show' => [
            'path'         => '/{pk}/show',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['GET'],
        ],
        'object' => [
            'path'         => '/{pk}/{action}',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['GET', 'POST'],
            'controller'   => 'actions',
        ],
        'batch' => [
            'path'         => '/batch',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['POST'],
            'controller'   => 'actions',
        ],
        'new' => [
            'path'         => '/new',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['GET'],
        ],
        'create' => [
            'path'         => '/create',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['POST'],
            'controller'   => 'new',
        ],
        'filters' => [
            'path'         => '/filter',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['POST', 'GET'],
            'controller'   => 'list',
        ],
        'scopes' => [
            'path'         => '/scope/{group}/{scope}',
            'defaults'     => [],
            'requirements' => [],
            'methods'      => ['POST', 'GET'],
            'controller'   => 'list',
        ],
    ];

    protected array $yaml = [];

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $collection = new RouteCollection();

        $resource = str_replace('\\', '/', $resource);
        $this->yaml = Yaml::parse(file_get_contents($this->getGeneratorFilePath($resource)));

        $namespace = $this->getNamespaceFromResource($resource);
        $bundle_name = $this->getBundleNameFromResource($resource);

        foreach ($this->actions as $controller => $datas) {
            $action = 'index';

            $loweredNamespace = str_replace(['/', '\\'], '_', $namespace);
            if ($controller_folder = $this->getControllerFolder($resource)) {
                if ($this->bundleContext($resource)) {
                    $route_name = $loweredNamespace . '_' . $bundle_name . '_' . $controller_folder . '_' . $controller;
                } else {
                    $route_name = $loweredNamespace . '_' . $controller_folder . '_' . $controller;
                }
            } else {
                $route_name = $loweredNamespace . '_' . $bundle_name . '_' . $controller;
            }

            if (in_array($controller, ['edit', 'update', 'object', 'show']) &&
                null !== $pk_requirement = $this->getFromYaml('params.pk_requirement', null)) {
                $datas['requirements'] = array_merge(
                    $datas['requirements'],
                    ['pk' => $pk_requirement]
                );
            }

            if (isset($datas['controller'])) {
                $action     = $controller;
                $controller = $datas['controller'];
            }

            $controllerName = $resource.ucfirst($controller).'Controller.php';
            if (!is_file($controllerName)) {
                // TODO: what does it mean if controller is not a file??
                continue;
            }

            if ($controller_folder) {
                if ($this->bundleContext($resource)) {
                  $datas['defaults']['_controller'] = $namespace . '\\'
                      . $bundle_name . '\\Controller\\'
                      . $controller_folder . '\\'
                      . ucfirst($controller) . 'Controller::'
                      . $action . 'Action';
                } else {
                  $datas['defaults']['_controller'] = $namespace . '\\Controller\\'
                      . $controller_folder . '\\'
                      . ucfirst($controller) . 'Controller::'
                      . $action . 'Action';
                }
            } else {
                $datas['defaults']['_controller'] = $loweredNamespace
                        . $bundle_name . ':'
                        . ucfirst($controller) . ':' . $action;
            }

            $route = new Route($datas['path'], $datas['defaults'], $datas['requirements']);
            $route->setMethods($datas['methods']);

            $route_name = ltrim($route_name, '_'); // fix routes in AppBundle without vendor

            $collection->add($route_name, $route);
            $collection->addResource(new FileResource($controllerName));
        }

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'admingenerator' == $type;
    }

    protected function getControllerFolder(mixed $resource): string
    {
        if ($this->bundleContext($resource)) {
            preg_match('#.+/.+Bundle/Controller?/(.*?)/?$#', $resource, $matches);
        } else {
            preg_match('#.+/.+/Controller?/(.*?)/?$#', $resource, $matches);
        }
        return $matches[1];
    }

    protected function getBundleNameFromResource(mixed $resource): string
    {
        if ($this->bundleContext($resource)) {
            preg_match('#.+/(.+Bundle)/Controller?/(.*?)/?$#', $resource, $matches);

            return $matches[1];
        } else {
            return '';
        }
    }

    protected function getNamespaceFromResource(mixed $resource): string
    {
        if ($this->bundleContext($resource)) {
            $finder = Finder::create()
                ->name('*Bundle.php')
                ->depth(0)
                ->in(realpath($resource . '/../../')) // ressource is controller folder
                ->getIterator();

            foreach ($finder as $file) {
                preg_match('/namespace (.+);/', file_get_contents($file->getRealPath()), $matches);

                return implode('\\', explode('\\', $matches[1], -1)); // Remove the short bundle name
            }

            throw new \Exception(sprintf('Bundle file not found in %s.', realpath($resource . '/../../')));
        } else {
            return 'App';
        }
    }

    protected function getGeneratorFilePath(mixed $resource): string
    {
        // TODO: use the GeneratorsFinder
        // Find the *-generator.yml
        $finder = Finder::create()
            ->name($this->getControllerFolder($resource).'-generator.yml');
        if ($this->bundleContext($resource)) {
            $finder->in($resource.'/../../Resources/config/');
        } else {
            $finder->in($resource.'/../../../config/admin');
        }
        $finder = $finder->getIterator();

        foreach ($finder as $file) {
            return $file->getRealPath();
        }

        throw new \Exception(sprintf(
            'Generator file for %s not found in %s',
            $this->getControllerFolder($resource),
            realpath($resource.'/../../Resources/config/')
        ));
    }

    protected function getFromYaml(string $yamlPath, mixed $default = null): mixed
    {
        $search_in = $this->yaml;
        $yamlPath = explode('.', $yamlPath);
        foreach ($yamlPath as $key) {
            if (!isset($search_in[$key])) {
                return $default;
            }
            $search_in = $search_in[$key];
        }

        return $search_in;
    }

    private function bundleContext(mixed $resource): bool
    {
        return str_contains($resource, 'Bundle');
    }
}
